-- [1.5] Sincroniza aux_parentesco com RM_IMPORTVC.dbo.PCODPARENT.
-- Chave: descricao. Remoção: soft delete.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_aux_parentesco
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    SELECT DISTINCT LTRIM(RTRIM(pc.DESCRICAO)) COLLATE DATABASE_DEFAULT AS descricao
    INTO #origem
    FROM RM_IMPORTVC.dbo.PCODPARENT pc
    WHERE NULLIF(LTRIM(RTRIM(pc.DESCRICAO)), '') IS NOT NULL;

    BEGIN TRAN;

    -- Só reativa: a descrição é a própria chave.
    UPDATE d
    SET d.ativo = 1, d.deleted_at = NULL, d.updated_at = GETDATE()
    FROM aux_parentesco d
    JOIN #origem o ON o.descricao = d.descricao
    WHERE d.deleted_at IS NOT NULL;

    INSERT INTO aux_parentesco (descricao, ativo, created_at, updated_at)
    SELECT o.descricao, 1, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM aux_parentesco x WHERE x.descricao = o.descricao);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM aux_parentesco d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o WHERE o.descricao = d.descricao);

    COMMIT;
END;
