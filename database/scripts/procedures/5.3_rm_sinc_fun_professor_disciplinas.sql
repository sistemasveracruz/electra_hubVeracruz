-- [5.3] Sincroniza fun_professor_disciplinas com RM_IMPORTVC.dbo.SPROFESSORTURMA (coligada 1).
-- cod_professor guarda fun_professores.codigo; quando o professor tem mais de uma chapa, fica a ativa mais recente.
-- tipo_professor é gravado por extenso, com os valores do enum App\Enums\TipoProfessor; código desconhecido fica NULL.
-- Chave: cod_turma + cod_disciplina + cod_professor. Remoção: soft delete.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_fun_professor_disciplinas
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    WITH base AS (
        SELECT t.cod_turma,
               d.cod_disciplina,
               fp.codigo AS cod_professor,
               CASE pt.TIPOPROF
                    WHEN 'J' THEN 'ADJUNTO'
                    WHEN 'I' THEN 'ASSISTENTE'
                    WHEN 'X' THEN 'AUXILIAR'
                    WHEN 'D' THEN 'COORDENADOR'
                    WHEN 'C' THEN 'COLABORADOR'
                    WHEN 'P' THEN 'CONTEUDISTA'
                    WHEN 'E' THEN 'DIRETOR_DE_TURMA'
                    WHEN 'Q' THEN 'MEDIADOR_PEDAGOGICO'
                    WHEN 'M' THEN 'MONITOR'
                    WHEN 'N' THEN 'NAO_DOCENTE'
                    WHEN 'R' THEN 'ORIENTADOR'
                    WHEN 'O' THEN 'OUTROS'
                    WHEN 'S' THEN 'SUBSTITUTO'
                    WHEN 'T' THEN 'TITULAR'
                    WHEN 'U' THEN 'TUTOR'
               END COLLATE DATABASE_DEFAULT AS tipo_professor,
               CAST(CASE WHEN td.ATIVA = 'S' THEN 1 ELSE 0 END AS bit) AS ativo,
               ROW_NUMBER() OVER (PARTITION BY t.cod_turma, d.cod_disciplina, fp.codigo
                                  ORDER BY CASE WHEN pt.TIPOPROF = 'T' THEN 0 ELSE 1 END, pt.TIPOPROF) AS rn
        FROM RM_IMPORTVC.dbo.STURMADISC td
        JOIN edu_turmas t
             ON t.cod_periodo_letivo = td.IDPERLET
            AND t.codigo_turma = td.CODTURMA COLLATE DATABASE_DEFAULT
            AND t.deleted_at IS NULL
        JOIN edu_disciplinas d ON d.sigla = td.CODDISC COLLATE DATABASE_DEFAULT
        JOIN RM_IMPORTVC.dbo.SPROFESSORTURMA pt
             ON pt.CODCOLIGADA = td.CODCOLIGADA AND pt.IDTURMADISC = td.IDTURMADISC
        CROSS APPLY (SELECT TOP 1 x.codigo
                     FROM fun_professores x
                     WHERE x.cod_professor = TRY_CAST(pt.CODPROF AS bigint)
                       AND x.deleted_at IS NULL
                     ORDER BY x.ativo DESC, x.codigo DESC) fp
        WHERE td.CODCOLIGADA = 1
    )
    SELECT cod_turma, cod_disciplina, cod_professor, tipo_professor, ativo
    INTO #origem
    FROM base
    WHERE rn = 1;

    BEGIN TRAN;

    UPDATE d
    SET d.tipo_professor = o.tipo_professor,
        d.ativo = o.ativo,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM fun_professor_disciplinas d
    JOIN #origem o
         ON o.cod_turma = d.cod_turma AND o.cod_disciplina = d.cod_disciplina AND o.cod_professor = d.cod_professor
    WHERE EXISTS (SELECT d.tipo_professor, d.ativo EXCEPT SELECT o.tipo_professor, o.ativo)
       OR d.deleted_at IS NOT NULL;

    INSERT INTO fun_professor_disciplinas (cod_disciplina, cod_professor, cod_turma, tipo_professor, ativo, created_at, updated_at)
    SELECT o.cod_disciplina, o.cod_professor, o.cod_turma, o.tipo_professor, o.ativo, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM fun_professor_disciplinas x
                      WHERE x.cod_turma = o.cod_turma AND x.cod_disciplina = o.cod_disciplina AND x.cod_professor = o.cod_professor);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM fun_professor_disciplinas d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o
                          WHERE o.cod_turma = d.cod_turma AND o.cod_disciplina = d.cod_disciplina AND o.cod_professor = d.cod_professor);

    COMMIT;
END;
