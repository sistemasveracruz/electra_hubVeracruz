-- [1.1] Sincroniza edu_status com RM_IMPORTVC.dbo.SSTATUS.
-- Chave: cod_status. Remoção: soft delete.
-- descricao e acessa_vera_digital são do HUB e não são alteradas.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_edu_status
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    SELECT s.CODSTATUS AS cod_status,
           LEFT(MAX(s.DESCRICAO), 120) COLLATE DATABASE_DEFAULT AS nome
    INTO #origem
    FROM RM_IMPORTVC.dbo.SSTATUS s
    WHERE s.CODCOLIGADA = 1
      AND s.DESCRICAO IS NOT NULL
    GROUP BY s.CODSTATUS;

    BEGIN TRAN;

    UPDATE d
    SET d.nome = o.nome,
        d.ativo = 1,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM edu_status d
    JOIN #origem o ON o.cod_status = d.cod_status
    WHERE EXISTS (SELECT d.nome EXCEPT SELECT o.nome)
       OR d.deleted_at IS NOT NULL;

    INSERT INTO edu_status (cod_status, nome, ativo, created_at, updated_at)
    SELECT o.cod_status, o.nome, 1, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM edu_status x WHERE x.cod_status = o.cod_status);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM edu_status d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o WHERE o.cod_status = d.cod_status);

    COMMIT;
END;
