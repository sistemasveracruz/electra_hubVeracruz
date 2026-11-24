-- [2.1] Sincroniza inst_pessoa_enderecos (tipo PESSOAL) com RM_IMPORTVC.dbo.PPESSOA.
-- Chave: cod_pessoa + tipo_endereco = PESSOAL. Remoção: soft delete (só endereços PESSOAL).
CREATE OR ALTER PROCEDURE dbo.rm_sinc_inst_pessoa_enderecos
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    SELECT ip.cod_pessoa,
           LEFT(REPLACE(REPLACE(p.CEP, '-', ''), '.', ''), 8) COLLATE DATABASE_DEFAULT AS cep,
           LEFT(p.RUA, 200) COLLATE DATABASE_DEFAULT AS logradouro,
           LEFT(p.NUMERO, 20) COLLATE DATABASE_DEFAULT AS numero,
           LEFT(p.COMPLEMENTO, 100) COLLATE DATABASE_DEFAULT AS complemento,
           LEFT(p.BAIRRO, 100) COLLATE DATABASE_DEFAULT AS bairro,
           LEFT(p.CIDADE, 100) COLLATE DATABASE_DEFAULT AS cidade,
           LEFT(p.ESTADO, 2) COLLATE DATABASE_DEFAULT AS uf
    INTO #origem
    FROM RM_IMPORTVC.dbo.PPESSOA p
    JOIN inst_pessoas ip ON ip.cod_pessoa = p.CODIGO;

    BEGIN TRAN;

    UPDATE d
    SET d.cep = o.cep,
        d.logradouro = o.logradouro,
        d.numero = o.numero,
        d.complemento = o.complemento,
        d.bairro = o.bairro,
        d.cidade = o.cidade,
        d.uf = o.uf,
        d.ativo = 1,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM inst_pessoa_enderecos d
    JOIN #origem o ON o.cod_pessoa = d.cod_pessoa
    WHERE d.tipo_endereco = 'PESSOAL'
      AND (EXISTS (SELECT d.cep, d.logradouro, d.numero, d.complemento, d.bairro, d.cidade, d.uf
                   EXCEPT
                   SELECT o.cep, o.logradouro, o.numero, o.complemento, o.bairro, o.cidade, o.uf)
           OR d.deleted_at IS NOT NULL);

    INSERT INTO inst_pessoa_enderecos (cod_pessoa, tipo_endereco, cep, logradouro, numero, complemento,
                                       bairro, cidade, uf, ativo, created_at, updated_at)
    SELECT o.cod_pessoa, 'PESSOAL', o.cep, o.logradouro, o.numero, o.complemento,
           o.bairro, o.cidade, o.uf, 1, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM inst_pessoa_enderecos x
                      WHERE x.cod_pessoa = o.cod_pessoa AND x.tipo_endereco = 'PESSOAL');

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM inst_pessoa_enderecos d
        WHERE d.deleted_at IS NULL
          AND d.tipo_endereco = 'PESSOAL'
          AND NOT EXISTS (SELECT 1 FROM #origem o WHERE o.cod_pessoa = d.cod_pessoa);

    COMMIT;
END;
