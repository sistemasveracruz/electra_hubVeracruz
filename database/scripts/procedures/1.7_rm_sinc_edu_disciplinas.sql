-- [1.7] Sincroniza edu_disciplinas com RM_IMPORTVC.dbo.SDISCIPLINA.
-- Chave: sigla (CODDISC). Remoção: soft delete.
-- observacoes é do HUB e não é alterada.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_edu_disciplinas
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    WITH base AS (
        SELECT LEFT(d.CODDISC, 30) COLLATE DATABASE_DEFAULT AS sigla,
               LEFT(d.NOME, 120) COLLATE DATABASE_DEFAULT AS nome,
               ROW_NUMBER() OVER (PARTITION BY LEFT(d.CODDISC, 30) ORDER BY d.CODDISC) AS rn
        FROM RM_IMPORTVC.dbo.SDISCIPLINA d
        WHERE d.CODCOLIGADA = 1
          AND d.NOME IS NOT NULL
          AND d.CODDISC IS NOT NULL
    )
    SELECT sigla, nome
    INTO #origem
    FROM base
    WHERE rn = 1;

    BEGIN TRAN;

    UPDATE d
    SET d.nome = o.nome,
        d.ativo = 1,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM edu_disciplinas d
    JOIN #origem o ON o.sigla = d.sigla
    WHERE EXISTS (SELECT d.nome EXCEPT SELECT o.nome)
       OR d.deleted_at IS NOT NULL;

    INSERT INTO edu_disciplinas (nome, sigla, ativo, created_at, updated_at)
    SELECT o.nome, o.sigla, 1, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM edu_disciplinas x WHERE x.sigla = o.sigla);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM edu_disciplinas d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o WHERE o.sigla = d.sigla);

    COMMIT;
END;
