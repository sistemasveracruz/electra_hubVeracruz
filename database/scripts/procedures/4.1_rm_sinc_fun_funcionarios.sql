-- [4.1] Sincroniza fun_funcionarios com CORPORERM_HML.dbo.PFUNC.
-- A pessoa é ligada pelo CPF: CORPORERM_HML.PPESSOA -> RM_IMPORTVC.PPESSOA -> inst_pessoas.
-- Chave: chapa. Remoção: soft delete.
-- tipo_cadastro é do HUB: entra como PESSOA e não é alterado.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_fun_funcionarios
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    WITH base AS (
        SELECT LEFT(pf.CHAPA, 20) COLLATE DATABASE_DEFAULT AS chapa,
               p2.cod_pessoa,
               s.cod_secao,
               fu.cod_funcao,
               st.cod_fun_situacao,
               pf.DATAADMISSAO AS data_admissao,
               pf.DATADEMISSAO AS data_desligamento,
               CAST(CASE WHEN pf.CODSITUACAO IN ('A', 'E', 'F', 'G', 'W', 'Y') THEN 1 ELSE 0 END AS bit) AS ativo,
               ROW_NUMBER() OVER (PARTITION BY pf.CHAPA ORDER BY p2.cod_pessoa) AS rn
        FROM CORPORERM_HML.dbo.PFUNC pf
        JOIN CORPORERM_HML.dbo.PPESSOA p ON p.CODIGO = pf.CODPESSOA
        JOIN RM_IMPORTVC.dbo.PPESSOA p1 ON p1.CPF = p.CPF
        JOIN inst_pessoas p2 ON p2.cod_pessoa = p1.CODIGO AND p2.deleted_at IS NULL AND p2.cpf IS NOT NULL
        JOIN inst_secoes s ON s.cod_secao = pf.CODSECAO COLLATE DATABASE_DEFAULT
        JOIN fun_funcoes fu ON fu.cod_funcao = pf.CODFUNCAO COLLATE DATABASE_DEFAULT
        JOIN fun_situacao st ON st.cod_fun_situacao = pf.CODSITUACAO COLLATE DATABASE_DEFAULT
        WHERE NULLIF(LTRIM(RTRIM(p.CPF)), '') IS NOT NULL
    )
    SELECT chapa, cod_pessoa, cod_secao, cod_funcao, cod_fun_situacao, data_admissao, data_desligamento, ativo
    INTO #origem
    FROM base
    WHERE rn = 1;

    BEGIN TRAN;

    UPDATE d
    SET d.cod_pessoa = o.cod_pessoa,
        d.cod_secao = o.cod_secao,
        d.cod_funcao = o.cod_funcao,
        d.cod_fun_situacao = o.cod_fun_situacao,
        d.data_admissao = o.data_admissao,
        d.data_desligamento = o.data_desligamento,
        d.ativo = o.ativo,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM fun_funcionarios d
    JOIN #origem o ON o.chapa = d.chapa
    WHERE EXISTS (SELECT d.cod_pessoa, d.cod_secao, d.cod_funcao, d.cod_fun_situacao,
                         d.data_admissao, d.data_desligamento, d.ativo
                  EXCEPT
                  SELECT o.cod_pessoa, o.cod_secao, o.cod_funcao, o.cod_fun_situacao,
                         o.data_admissao, o.data_desligamento, o.ativo)
       OR d.deleted_at IS NOT NULL;

    INSERT INTO fun_funcionarios (chapa, cod_pessoa, tipo_cadastro, cod_secao, cod_funcao, cod_fun_situacao,
                                  data_admissao, data_desligamento, ativo, created_at, updated_at)
    SELECT o.chapa, o.cod_pessoa, 'PESSOA', o.cod_secao, o.cod_funcao, o.cod_fun_situacao,
           o.data_admissao, o.data_desligamento, o.ativo, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM fun_funcionarios x WHERE x.chapa = o.chapa);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM fun_funcionarios d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o WHERE o.chapa = d.chapa);

    COMMIT;
END;
