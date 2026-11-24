-- [2.7] Sincroniza edu_responsaveis_financeiros com RM_IMPORTVC.dbo.SALUNO + FCFO (coligada 1).
-- O responsável financeiro é o cliente/fornecedor pessoa física, ligado a inst_pessoas pelo CPF.
-- Chave: cod_pessoa_aluno + cod_pessoa_responsavel. Remoção: soft delete.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_edu_responsaveis_financeiros
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    WITH base AS (
        SELECT pa.cod_pessoa AS cod_pessoa_aluno,
               pr.cod_pessoa AS cod_pessoa_responsavel,
               ap.cod_parentesco AS cod_parentesco_responsavel,
               ROW_NUMBER() OVER (PARTITION BY pa.cod_pessoa, pr.cod_pessoa ORDER BY ap.cod_parentesco DESC) AS rn
        FROM RM_IMPORTVC.dbo.SALUNO a
        JOIN inst_pessoas pa ON pa.cod_pessoa = a.CODPESSOA
        JOIN RM_IMPORTVC.dbo.FCFO f ON f.CODCOLIGADA = a.CODCOLCFO AND f.CODCFO = a.CODCFO
        JOIN inst_pessoas pr
             ON pr.cpf = REPLACE(REPLACE(REPLACE(f.CGCCFO, '.', ''), '-', ''), '/', '') COLLATE DATABASE_DEFAULT
        LEFT JOIN RM_IMPORTVC.dbo.PCODPARENT pc ON pc.CODCLIENTE = a.CODPARENTCFO
        LEFT JOIN aux_parentesco ap
               ON ap.descricao = LTRIM(RTRIM(pc.DESCRICAO)) COLLATE DATABASE_DEFAULT
              AND ap.deleted_at IS NULL
        WHERE a.CODCOLIGADA = 1
          AND f.PESSOAFISOUJUR = 'F'
    )
    SELECT cod_pessoa_aluno, cod_pessoa_responsavel, cod_parentesco_responsavel
    INTO #origem
    FROM base
    WHERE rn = 1;

    BEGIN TRAN;

    UPDATE d
    SET d.cod_parentesco_responsavel = o.cod_parentesco_responsavel,
        d.ativo = 1,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM edu_responsaveis_financeiros d
    JOIN #origem o ON o.cod_pessoa_aluno = d.cod_pessoa_aluno AND o.cod_pessoa_responsavel = d.cod_pessoa_responsavel
    WHERE EXISTS (SELECT d.cod_parentesco_responsavel EXCEPT SELECT o.cod_parentesco_responsavel)
       OR d.deleted_at IS NOT NULL;

    INSERT INTO edu_responsaveis_financeiros (cod_pessoa_aluno, cod_pessoa_responsavel, cod_parentesco_responsavel,
                                              ativo, created_at, updated_at)
    SELECT o.cod_pessoa_aluno, o.cod_pessoa_responsavel, o.cod_parentesco_responsavel, 1, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM edu_responsaveis_financeiros x
                      WHERE x.cod_pessoa_aluno = o.cod_pessoa_aluno AND x.cod_pessoa_responsavel = o.cod_pessoa_responsavel);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM edu_responsaveis_financeiros d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o
                          WHERE o.cod_pessoa_aluno = d.cod_pessoa_aluno AND o.cod_pessoa_responsavel = d.cod_pessoa_responsavel);

    COMMIT;
END;
