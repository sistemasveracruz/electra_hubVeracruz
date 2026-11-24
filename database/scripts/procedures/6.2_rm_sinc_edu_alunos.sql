-- [6.2] Sincroniza edu_alunos a partir das matrículas do HUB e de RM_IMPORTVC.dbo.SALUNO / VFILIACAO / FCFO.
-- Um aluno por RA, apontando para a matrícula do período letivo mais recente.
-- Pai, mãe e responsáveis são opcionais: quando não existem no RM ou em inst_pessoas, ficam NULL.
-- Chave: cod_registro_academico. Remoção: soft delete.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_edu_alunos
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    WITH ultima AS (
        SELECT m.cod_registro_academico, m.cod_pessoa, m.cod_matricula,
               ROW_NUMBER() OVER (PARTITION BY m.cod_registro_academico
                                  ORDER BY pl.data_inicio DESC, m.cod_matricula DESC) AS rn
        FROM edu_matriculas m
        JOIN edu_turmas t ON t.cod_turma = m.cod_turma
        JOIN edu_periodos_letivos pl ON pl.cod_periodo_letivo = t.cod_periodo_letivo
        WHERE m.deleted_at IS NULL
    )
    SELECT u.cod_pessoa,
           u.cod_registro_academico,
           u.cod_matricula,
           pai.cod_pessoa AS cod_pessoa_pai,
           mae.cod_pessoa AS cod_pessoa_mae,
           rp.cod_pessoa AS cod_pessoa_responsavel_pedagogico,
           rf.cod_pessoa AS cod_pessoa_responsavel_financeiro
    INTO #origem
    FROM ultima u
    JOIN edu_registros_academicos ra ON ra.cod_registro_academico = u.cod_registro_academico
    OUTER APPLY (SELECT TOP 1 a.CODPESSOARACA, a.CODCOLCFO, a.CODCFO
                 FROM RM_IMPORTVC.dbo.SALUNO a
                 WHERE a.CODCOLIGADA = ra.cod_escola
                   AND LTRIM(RTRIM(a.RA)) COLLATE DATABASE_DEFAULT = ra.registro_academico) sa
    OUTER APPLY (SELECT TOP 1 x.cod_pessoa
                 FROM RM_IMPORTVC.dbo.VFILIACAO f
                 JOIN inst_pessoas x ON x.cod_pessoa = f.CODPESSOAFILIACAO
                 WHERE f.CODPESSOAFILHO = u.cod_pessoa AND f.TIPORELACIONAMENTO = 'P'
                 ORDER BY f.CODFILIACAO) pai
    OUTER APPLY (SELECT TOP 1 x.cod_pessoa
                 FROM RM_IMPORTVC.dbo.VFILIACAO f
                 JOIN inst_pessoas x ON x.cod_pessoa = f.CODPESSOAFILIACAO
                 WHERE f.CODPESSOAFILHO = u.cod_pessoa AND f.TIPORELACIONAMENTO = 'M'
                 ORDER BY f.CODFILIACAO) mae
    LEFT JOIN inst_pessoas rp ON rp.cod_pessoa = sa.CODPESSOARACA
    OUTER APPLY (SELECT TOP 1 x.cod_pessoa
                 FROM RM_IMPORTVC.dbo.FCFO f
                 JOIN inst_pessoas x
                      ON x.cpf = REPLACE(REPLACE(REPLACE(f.CGCCFO, '.', ''), '-', ''), '/', '') COLLATE DATABASE_DEFAULT
                 WHERE f.CODCOLIGADA = sa.CODCOLCFO AND f.CODCFO = sa.CODCFO) rf
    WHERE u.rn = 1;

    BEGIN TRAN;

    UPDATE d
    SET d.cod_pessoa = o.cod_pessoa,
        d.cod_matricula = o.cod_matricula,
        d.cod_pessoa_pai = o.cod_pessoa_pai,
        d.cod_pessoa_mae = o.cod_pessoa_mae,
        d.cod_pessoa_responsavel_pedagogico = o.cod_pessoa_responsavel_pedagogico,
        d.cod_pessoa_responsavel_financeiro = o.cod_pessoa_responsavel_financeiro,
        d.ativo = 1,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM edu_alunos d
    JOIN #origem o ON o.cod_registro_academico = d.cod_registro_academico
    WHERE EXISTS (SELECT d.cod_pessoa, d.cod_matricula, d.cod_pessoa_pai, d.cod_pessoa_mae,
                         d.cod_pessoa_responsavel_pedagogico, d.cod_pessoa_responsavel_financeiro
                  EXCEPT
                  SELECT o.cod_pessoa, o.cod_matricula, o.cod_pessoa_pai, o.cod_pessoa_mae,
                         o.cod_pessoa_responsavel_pedagogico, o.cod_pessoa_responsavel_financeiro)
       OR d.deleted_at IS NOT NULL;

    INSERT INTO edu_alunos (cod_pessoa, cod_registro_academico, cod_matricula, cod_pessoa_pai, cod_pessoa_mae,
                            cod_pessoa_responsavel_pedagogico, cod_pessoa_responsavel_financeiro,
                            ativo, created_at, updated_at)
    SELECT o.cod_pessoa, o.cod_registro_academico, o.cod_matricula, o.cod_pessoa_pai, o.cod_pessoa_mae,
           o.cod_pessoa_responsavel_pedagogico, o.cod_pessoa_responsavel_financeiro,
           1, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM edu_alunos x WHERE x.cod_registro_academico = o.cod_registro_academico);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM edu_alunos d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o WHERE o.cod_registro_academico = d.cod_registro_academico);

    COMMIT;
END;
