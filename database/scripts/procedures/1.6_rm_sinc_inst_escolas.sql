-- [1.6] Sincroniza inst_escolas com RM_IMPORTVC.dbo.GCOLIGADA (coligada 1).
-- Chave: cod_escola (CODCOLIGADA). Remoção: soft delete.
-- descricao é do HUB e não é alterada.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_inst_escolas
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    SELECT c.CODCOLIGADA AS cod_escola,
           LEFT(c.NOME, 150) COLLATE DATABASE_DEFAULT AS nome,
           LEFT(c.NOMEFANTASIA, 150) COLLATE DATABASE_DEFAULT AS nome_fantasia,
           REPLACE(REPLACE(REPLACE(c.CGC, '.', ''), '/', ''), '-', '') COLLATE DATABASE_DEFAULT AS cnpj
    INTO #origem
    FROM RM_IMPORTVC.dbo.GCOLIGADA c
    WHERE c.CODCOLIGADA = 1
      AND c.NOME IS NOT NULL;

    BEGIN TRAN;

    UPDATE d
    SET d.nome = o.nome,
        d.nome_fantasia = o.nome_fantasia,
        d.cnpj = o.cnpj,
        d.ativo = 1,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM inst_escolas d
    JOIN #origem o ON o.cod_escola = d.cod_escola
    WHERE EXISTS (SELECT d.nome, d.nome_fantasia, d.cnpj EXCEPT SELECT o.nome, o.nome_fantasia, o.cnpj)
       OR d.deleted_at IS NOT NULL;

    INSERT INTO inst_escolas (cod_escola, nome, nome_fantasia, cnpj, ativo, created_at, updated_at)
    SELECT o.cod_escola, o.nome, o.nome_fantasia, o.cnpj, 1, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM inst_escolas x WHERE x.cod_escola = o.cod_escola);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM inst_escolas d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o WHERE o.cod_escola = d.cod_escola);

    COMMIT;
END;
