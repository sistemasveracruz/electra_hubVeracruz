-- [1.4] Sincroniza inst_pessoas com RM_IMPORTVC.dbo.PPESSOA.
-- Chave: cod_pessoa (PPESSOA.CODIGO). Remoção: soft delete.
-- CPF: só quando tem 11 dígitos e, se o RM repetir o mesmo CPF, fica com a pessoa de menor código.
-- email_alternativo é do HUB e não é alterado.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_inst_pessoas
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    WITH base AS (
        SELECT p.CODIGO AS cod_pessoa,
               NULLIF(REPLACE(REPLACE(REPLACE(p.CPF, '.', ''), '-', ''), ' ', ''), '') COLLATE DATABASE_DEFAULT AS cpf_rm,
               LEFT(p.NOME, 150) COLLATE DATABASE_DEFAULT AS nome,
               CAST(p.DTNASCIMENTO AS date) AS data_nascimento,
               LEFT(p.EMAIL, 250) COLLATE DATABASE_DEFAULT AS email,
               LEFT(p.EMAILPESSOAL, 250) COLLATE DATABASE_DEFAULT AS email_pessoal,
               LEFT(p.TELEFONE1, 20) COLLATE DATABASE_DEFAULT AS telefone_fixo,
               LEFT(p.TELEFONE2, 20) COLLATE DATABASE_DEFAULT AS telefone_pessoal,
               LEFT(p.TELEFONE3, 20) COLLATE DATABASE_DEFAULT AS telefone_empresa,
               CAST(ISNULL(p.FALECIDO, 0) AS bit) AS falecido
        FROM RM_IMPORTVC.dbo.PPESSOA p
        WHERE p.NOME IS NOT NULL
    )
    SELECT b.cod_pessoa,
           CAST(CASE WHEN LEN(b.cpf_rm) = 11
                      AND ROW_NUMBER() OVER (PARTITION BY b.cpf_rm ORDER BY b.cod_pessoa) = 1
                     THEN b.cpf_rm END AS varchar(11)) COLLATE DATABASE_DEFAULT AS cpf,
           b.nome, b.data_nascimento, b.email, b.email_pessoal,
           b.telefone_fixo, b.telefone_pessoal, b.telefone_empresa, b.falecido
    INTO #origem
    FROM base b;

    -- CPF que ainda está numa pessoa que saiu do RM fica vazio aqui, para não violar o índice único.
    UPDATE o
    SET o.cpf = NULL
    FROM #origem o
    WHERE o.cpf IS NOT NULL
      AND EXISTS (SELECT 1 FROM inst_pessoas x
                  WHERE x.cpf = o.cpf
                    AND x.cod_pessoa <> o.cod_pessoa
                    AND NOT EXISTS (SELECT 1 FROM #origem y WHERE y.cod_pessoa = x.cod_pessoa));

    BEGIN TRAN;

    UPDATE d
    SET d.cpf = o.cpf,
        d.nome = o.nome,
        d.data_nascimento = o.data_nascimento,
        d.email = o.email,
        d.email_pessoal = o.email_pessoal,
        d.telefone_fixo = o.telefone_fixo,
        d.telefone_pessoal = o.telefone_pessoal,
        d.telefone_empresa = o.telefone_empresa,
        d.falecido = o.falecido,
        d.ativo = 1,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM inst_pessoas d
    JOIN #origem o ON o.cod_pessoa = d.cod_pessoa
    WHERE EXISTS (SELECT d.cpf, d.nome, d.data_nascimento, d.email, d.email_pessoal,
                         d.telefone_fixo, d.telefone_pessoal, d.telefone_empresa, d.falecido
                  EXCEPT
                  SELECT o.cpf, o.nome, o.data_nascimento, o.email, o.email_pessoal,
                         o.telefone_fixo, o.telefone_pessoal, o.telefone_empresa, o.falecido)
       OR d.deleted_at IS NOT NULL;

    INSERT INTO inst_pessoas (cod_pessoa, cpf, nome, data_nascimento, email, email_pessoal,
                              telefone_fixo, telefone_pessoal, telefone_empresa, falecido, ativo, created_at, updated_at)
    SELECT o.cod_pessoa, o.cpf, o.nome, o.data_nascimento, o.email, o.email_pessoal,
           o.telefone_fixo, o.telefone_pessoal, o.telefone_empresa, o.falecido, 1, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM inst_pessoas x WHERE x.cod_pessoa = o.cod_pessoa);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM inst_pessoas d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o WHERE o.cod_pessoa = d.cod_pessoa);

    COMMIT;
END;
