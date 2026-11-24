-- [1.10] Sincroniza inst_secoes com CORPORERM_HML.dbo.PSECAO.
-- Chave: cod_secao (CODIGO). Remoção: soft delete.
-- O INSERT vem antes do UPDATE: uma seção existente pode passar a apontar para uma seção pai nova.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_inst_secoes
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    WITH base AS (
        SELECT LEFT(s.CODIGO, 20) COLLATE DATABASE_DEFAULT AS cod_secao,
               LEFT(s.DESCRICAO, 150) COLLATE DATABASE_DEFAULT AS descricao,
               LEFT(NULLIF(LTRIM(RTRIM(s.CODIGOPAI)), ''), 20) COLLATE DATABASE_DEFAULT AS cod_secao_pai,
               ROW_NUMBER() OVER (PARTITION BY s.CODIGO ORDER BY s.CODCOLIGADA) AS rn
        FROM CORPORERM_HML.dbo.PSECAO s
        WHERE s.CODIGO IS NOT NULL
          AND s.DESCRICAO IS NOT NULL
    )
    SELECT cod_secao, descricao, cod_secao_pai
    INTO #origem
    FROM base
    WHERE rn = 1;

    -- Pai que não existe na origem fica vazio, para não violar a FK.
    UPDATE o
    SET o.cod_secao_pai = NULL
    FROM #origem o
    WHERE o.cod_secao_pai IS NOT NULL
      AND NOT EXISTS (SELECT 1 FROM #origem p WHERE p.cod_secao = o.cod_secao_pai);

    BEGIN TRAN;

    INSERT INTO inst_secoes (cod_secao, descricao, cod_secao_pai, ativo, created_at, updated_at)
    SELECT o.cod_secao, o.descricao, NULL, 1, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM inst_secoes x WHERE x.cod_secao = o.cod_secao);

    UPDATE d
    SET d.descricao = o.descricao,
        d.cod_secao_pai = o.cod_secao_pai,
        d.ativo = 1,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM inst_secoes d
    JOIN #origem o ON o.cod_secao = d.cod_secao
    WHERE EXISTS (SELECT d.descricao, d.cod_secao_pai EXCEPT SELECT o.descricao, o.cod_secao_pai)
       OR d.deleted_at IS NOT NULL;

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM inst_secoes d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o WHERE o.cod_secao = d.cod_secao);

    COMMIT;
END;
