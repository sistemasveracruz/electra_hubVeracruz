-- [1.9] Sincroniza fun_funcoes com CORPORERM_HML.dbo.PFUNCAO.
-- Chave: cod_funcao (CODIGO). Remoção: soft delete.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_fun_funcoes
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    WITH base AS (
        SELECT LEFT(f.CODIGO, 20) COLLATE DATABASE_DEFAULT AS cod_funcao,
               LEFT(f.NOME, 150) COLLATE DATABASE_DEFAULT AS nome,
               CAST(CASE WHEN f.INATIVA = 0 THEN 1 ELSE 0 END AS bit) AS ativo,
               ROW_NUMBER() OVER (PARTITION BY f.CODIGO ORDER BY f.CODCOLIGADA) AS rn
        FROM CORPORERM_HML.dbo.PFUNCAO f
        WHERE f.CODIGO IS NOT NULL
          AND f.NOME IS NOT NULL
    )
    SELECT cod_funcao, nome, ativo
    INTO #origem
    FROM base
    WHERE rn = 1;

    BEGIN TRAN;

    UPDATE d
    SET d.nome = o.nome,
        d.ativo = o.ativo,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM fun_funcoes d
    JOIN #origem o ON o.cod_funcao = d.cod_funcao
    WHERE EXISTS (SELECT d.nome, d.ativo EXCEPT SELECT o.nome, o.ativo)
       OR d.deleted_at IS NOT NULL;

    INSERT INTO fun_funcoes (cod_funcao, nome, ativo, created_at, updated_at)
    SELECT o.cod_funcao, o.nome, o.ativo, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM fun_funcoes x WHERE x.cod_funcao = o.cod_funcao);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM fun_funcoes d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o WHERE o.cod_funcao = d.cod_funcao);

    COMMIT;
END;
