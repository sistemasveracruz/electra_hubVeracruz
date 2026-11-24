-- [2.6] Sincroniza edu_responsaveis_pedagogico com RM_IMPORTVC.dbo.SALUNO.CODPESSOARACA (coligada 1).
-- Chave: cod_pessoa_aluno + cod_pessoa_responsavel. Remoção: soft delete.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_edu_responsaveis_pedagogico
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
        JOIN inst_pessoas pr ON pr.cod_pessoa = a.CODPESSOARACA
        LEFT JOIN RM_IMPORTVC.dbo.PCODPARENT pc ON pc.CODCLIENTE = a.CODPARENTRACA
        LEFT JOIN aux_parentesco ap
               ON ap.descricao = LTRIM(RTRIM(pc.DESCRICAO)) COLLATE DATABASE_DEFAULT
              AND ap.deleted_at IS NULL
        WHERE a.CODCOLIGADA = 1
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
    FROM edu_responsaveis_pedagogico d
    JOIN #origem o ON o.cod_pessoa_aluno = d.cod_pessoa_aluno AND o.cod_pessoa_responsavel = d.cod_pessoa_responsavel
    WHERE EXISTS (SELECT d.cod_parentesco_responsavel EXCEPT SELECT o.cod_parentesco_responsavel)
       OR d.deleted_at IS NOT NULL;

    INSERT INTO edu_responsaveis_pedagogico (cod_pessoa_aluno, cod_pessoa_responsavel, cod_parentesco_responsavel,
                                             ativo, created_at, updated_at)
    SELECT o.cod_pessoa_aluno, o.cod_pessoa_responsavel, o.cod_parentesco_responsavel, 1, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM edu_responsaveis_pedagogico x
                      WHERE x.cod_pessoa_aluno = o.cod_pessoa_aluno AND x.cod_pessoa_responsavel = o.cod_pessoa_responsavel);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM edu_responsaveis_pedagogico d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o
                          WHERE o.cod_pessoa_aluno = d.cod_pessoa_aluno AND o.cod_pessoa_responsavel = d.cod_pessoa_responsavel);

    COMMIT;
END;
