-- [1.8] Sincroniza fun_situacao com CORPORERM_HML.dbo.PCODSITUACAO.
-- Chave: cod_fun_situacao (CODCLIENTE). Remoção: soft delete.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_fun_situacao
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    WITH base AS (
        SELECT CAST(s.CODCLIENTE AS char(1)) COLLATE DATABASE_DEFAULT AS cod_fun_situacao,
               LEFT(s.DESCRICAO, 150) COLLATE DATABASE_DEFAULT AS descricao,
               ROW_NUMBER() OVER (PARTITION BY s.CODCLIENTE ORDER BY s.DESCRICAO) AS rn
        FROM CORPORERM_HML.dbo.PCODSITUACAO s
        WHERE s.CODCLIENTE IS NOT NULL
          AND s.DESCRICAO IS NOT NULL
    )
    SELECT cod_fun_situacao, descricao
    INTO #origem
    FROM base
    WHERE rn = 1;

    BEGIN TRAN;

    UPDATE d
    SET d.descricao = o.descricao,
        d.ativo = 1,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM fun_situacao d
    JOIN #origem o ON o.cod_fun_situacao = d.cod_fun_situacao
    WHERE EXISTS (SELECT d.descricao EXCEPT SELECT o.descricao)
       OR d.deleted_at IS NOT NULL;

    INSERT INTO fun_situacao (cod_fun_situacao, descricao, ativo, created_at, updated_at)
    SELECT o.cod_fun_situacao, o.descricao, 1, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM fun_situacao x WHERE x.cod_fun_situacao = o.cod_fun_situacao);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM fun_situacao d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o WHERE o.cod_fun_situacao = d.cod_fun_situacao);

    COMMIT;
END;
