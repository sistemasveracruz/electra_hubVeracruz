-- [3.4] Sincroniza edu_etapas com RM_IMPORTVC.dbo.SHABILITACAO.
-- Chave: cod_curso + cod_etapa (CODHABILITACAO numérico). Remoção: soft delete.
-- sigla é do HUB e não é alterada.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_edu_etapas
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    WITH base AS (
        SELECT TRY_CAST(h.CODHABILITACAO AS bigint) AS cod_etapa,
               c.cod_curso,
               LEFT(h.NOME, 150) COLLATE DATABASE_DEFAULT AS nome,
               ROW_NUMBER() OVER (PARTITION BY c.cod_curso, TRY_CAST(h.CODHABILITACAO AS bigint) ORDER BY h.CODCOLIGADA) AS rn
        FROM RM_IMPORTVC.dbo.SHABILITACAO h
        JOIN edu_cursos c ON c.cod_curso = TRY_CAST(h.CODCURSO AS bigint)
        WHERE TRY_CAST(h.CODHABILITACAO AS bigint) IS NOT NULL
    )
    SELECT cod_etapa, cod_curso, nome
    INTO #origem
    FROM base
    WHERE rn = 1;

    BEGIN TRAN;

    UPDATE d
    SET d.nome = o.nome,
        d.ativo = 1,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM edu_etapas d
    JOIN #origem o ON o.cod_curso = d.cod_curso AND o.cod_etapa = d.cod_etapa
    WHERE EXISTS (SELECT d.nome EXCEPT SELECT o.nome)
       OR d.deleted_at IS NOT NULL;

    INSERT INTO edu_etapas (cod_etapa, cod_curso, nome, ativo, created_at, updated_at)
    SELECT o.cod_etapa, o.cod_curso, o.nome, 1, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM edu_etapas x WHERE x.cod_curso = o.cod_curso AND x.cod_etapa = o.cod_etapa);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM edu_etapas d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o WHERE o.cod_curso = d.cod_curso AND o.cod_etapa = d.cod_etapa);

    COMMIT;
END;
