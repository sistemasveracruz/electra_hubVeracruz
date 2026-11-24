-- [6.4] Sincroniza edu_enturmas: uma enturmação por matrícula, com o número de chamada de SMATRICPL.NUMALUNO.
-- Chave: cod_matricula. Remoção: soft delete.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_edu_enturmas
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    WITH base AS (
        SELECT a.cod_aluno, m.cod_matricula, m.cod_turma, m.cod_status,
               CAST(n.NUMALUNO AS varchar(120)) COLLATE DATABASE_DEFAULT AS numero_chamada,
               ROW_NUMBER() OVER (PARTITION BY m.cod_matricula ORDER BY a.cod_aluno) AS rn
        FROM edu_matriculas m
        JOIN edu_alunos a ON a.cod_registro_academico = m.cod_registro_academico AND a.deleted_at IS NULL
        JOIN edu_registros_academicos ra ON ra.cod_registro_academico = m.cod_registro_academico
        JOIN edu_turmas t ON t.cod_turma = m.cod_turma
        OUTER APPLY (SELECT TOP 1 sm.NUMALUNO
                     FROM RM_IMPORTVC.dbo.SMATRICPL sm
                     WHERE sm.CODCOLIGADA = 1
                       AND sm.IDPERLET = t.cod_periodo_letivo
                       AND LTRIM(RTRIM(sm.RA)) COLLATE DATABASE_DEFAULT = ra.registro_academico
                       AND sm.CODTURMA COLLATE DATABASE_DEFAULT = t.codigo_turma) n
        WHERE m.deleted_at IS NULL
    )
    SELECT cod_aluno, cod_matricula, cod_turma, cod_status, numero_chamada
    INTO #origem
    FROM base
    WHERE rn = 1;

    BEGIN TRAN;

    UPDATE d
    SET d.cod_aluno = o.cod_aluno,
        d.cod_turma = o.cod_turma,
        d.cod_status = o.cod_status,
        d.numero_chamada = o.numero_chamada,
        d.ativo = 1,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM edu_enturmas d
    JOIN #origem o ON o.cod_matricula = d.cod_matricula
    WHERE EXISTS (SELECT d.cod_aluno, d.cod_turma, d.cod_status, d.numero_chamada
                  EXCEPT
                  SELECT o.cod_aluno, o.cod_turma, o.cod_status, o.numero_chamada)
       OR d.deleted_at IS NOT NULL;

    INSERT INTO edu_enturmas (cod_aluno, cod_matricula, cod_turma, cod_status, numero_chamada, ativo, created_at, updated_at)
    SELECT o.cod_aluno, o.cod_matricula, o.cod_turma, o.cod_status, o.numero_chamada, 1, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM edu_enturmas x WHERE x.cod_matricula = o.cod_matricula);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM edu_enturmas d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o WHERE o.cod_matricula = d.cod_matricula);

    COMMIT;
END;
