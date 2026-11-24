-- [6.3] Sincroniza edu_responsaveis: liga cada aluno ao seu responsável pedagógico e financeiro atuais
-- (SALUNO.CODPESSOARACA e SALUNO.CODCFO, coligada 1).
-- Chave: cod_aluno + cod_responsavel_pedagogico + cod_responsavel_financeiro (a PK da tabela).
-- Remoção: DELETE físico (a tabela não tem deleted_at e nenhuma outra aponta para ela).
CREATE OR ALTER PROCEDURE dbo.rm_sinc_edu_responsaveis
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    WITH base AS (
        SELECT al.cod_aluno, al.cod_pessoa AS cod_pessoa_aluno,
               rp.cod_responsavel_pedagogico, rf.cod_responsavel_financeiro,
               ROW_NUMBER() OVER (PARTITION BY al.cod_aluno
                                  ORDER BY rp.cod_responsavel_pedagogico, rf.cod_responsavel_financeiro) AS rn
        FROM RM_IMPORTVC.dbo.SALUNO a
        JOIN edu_registros_academicos ra
             ON ra.cod_escola = a.CODCOLIGADA
            AND ra.registro_academico = LTRIM(RTRIM(a.RA)) COLLATE DATABASE_DEFAULT
        JOIN edu_alunos al ON al.cod_registro_academico = ra.cod_registro_academico AND al.deleted_at IS NULL
        JOIN edu_responsaveis_pedagogico rp
             ON rp.cod_pessoa_aluno = al.cod_pessoa
            AND rp.cod_pessoa_responsavel = a.CODPESSOARACA
            AND rp.deleted_at IS NULL
        JOIN RM_IMPORTVC.dbo.FCFO f ON f.CODCOLIGADA = a.CODCOLCFO AND f.CODCFO = a.CODCFO
        JOIN inst_pessoas pf
             ON pf.cpf = REPLACE(REPLACE(REPLACE(f.CGCCFO, '.', ''), '-', ''), '/', '') COLLATE DATABASE_DEFAULT
        JOIN edu_responsaveis_financeiros rf
             ON rf.cod_pessoa_aluno = al.cod_pessoa
            AND rf.cod_pessoa_responsavel = pf.cod_pessoa
            AND rf.deleted_at IS NULL
        WHERE a.CODCOLIGADA = 1
          AND f.PESSOAFISOUJUR = 'F'
    )
    SELECT cod_aluno, cod_pessoa_aluno, cod_responsavel_pedagogico, cod_responsavel_financeiro
    INTO #origem
    FROM base
    WHERE rn = 1;

    BEGIN TRAN;

    -- Remove primeiro: quando o responsável muda, a linha antiga sai e a nova entra no INSERT.
    IF EXISTS (SELECT 1 FROM #origem)
        DELETE d
        FROM edu_responsaveis d
        WHERE NOT EXISTS (SELECT 1 FROM #origem o
                          WHERE o.cod_aluno = d.cod_aluno
                            AND o.cod_responsavel_pedagogico = d.cod_responsavel_pedagogico
                            AND o.cod_responsavel_financeiro = d.cod_responsavel_financeiro);

    UPDATE d
    SET d.cod_pessoa_aluno = o.cod_pessoa_aluno,
        d.updated_at = GETDATE()
    FROM edu_responsaveis d
    JOIN #origem o
         ON o.cod_aluno = d.cod_aluno
        AND o.cod_responsavel_pedagogico = d.cod_responsavel_pedagogico
        AND o.cod_responsavel_financeiro = d.cod_responsavel_financeiro
    WHERE d.cod_pessoa_aluno <> o.cod_pessoa_aluno;

    INSERT INTO edu_responsaveis (cod_aluno, cod_pessoa_aluno, cod_responsavel_pedagogico, cod_responsavel_financeiro,
                                  created_at, updated_at)
    SELECT o.cod_aluno, o.cod_pessoa_aluno, o.cod_responsavel_pedagogico, o.cod_responsavel_financeiro,
           GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM edu_responsaveis x
                      WHERE x.cod_aluno = o.cod_aluno
                        AND x.cod_responsavel_pedagogico = o.cod_responsavel_pedagogico
                        AND x.cod_responsavel_financeiro = o.cod_responsavel_financeiro);

    COMMIT;
END;
