-- [3.6] Sincroniza edu_grade_curricular_disciplinas com RM_IMPORTVC.dbo.SDISCGRADE (coligada 1).
-- Chave: cod_grade_curricular + cod_disciplina.
-- Remoção: DELETE físico (a tabela não tem deleted_at e nenhuma outra aponta para ela).
-- periodo_grade, aulas_semanais e obrigatoria são do HUB e não são alteradas.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_edu_grade_curricular_disciplinas
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    WITH base AS (
        SELECT gc.cod_grade_curricular, gc.cod_curso, gc.cod_etapa, d.cod_disciplina,
               sdg.CH AS carga_horaria,
               ROW_NUMBER() OVER (PARTITION BY gc.cod_grade_curricular, d.cod_disciplina ORDER BY sdg.CODCOLIGADA) AS rn
        FROM RM_IMPORTVC.dbo.SDISCGRADE sdg
        JOIN edu_grades_curriculares gc
             ON gc.cod_curso = TRY_CAST(sdg.CODCURSO AS bigint)
            AND gc.cod_etapa = TRY_CAST(sdg.CODHABILITACAO AS bigint)
            AND gc.cod_grade = LTRIM(RTRIM(sdg.CODGRADE)) COLLATE DATABASE_DEFAULT
            AND gc.deleted_at IS NULL
        JOIN edu_disciplinas d ON d.sigla = sdg.CODDISC COLLATE DATABASE_DEFAULT
        WHERE sdg.CODCOLIGADA = 1
    )
    SELECT cod_grade_curricular, cod_curso, cod_etapa, cod_disciplina, carga_horaria
    INTO #origem
    FROM base
    WHERE rn = 1;

    BEGIN TRAN;

    UPDATE d
    SET d.cod_curso = o.cod_curso,
        d.cod_etapa = o.cod_etapa,
        d.carga_horaria = o.carga_horaria
    FROM edu_grade_curricular_disciplinas d
    JOIN #origem o ON o.cod_grade_curricular = d.cod_grade_curricular AND o.cod_disciplina = d.cod_disciplina
    WHERE EXISTS (SELECT d.cod_curso, d.cod_etapa, d.carga_horaria EXCEPT SELECT o.cod_curso, o.cod_etapa, o.carga_horaria);

    INSERT INTO edu_grade_curricular_disciplinas
        (cod_grade_curricular, cod_curso, cod_etapa, cod_disciplina, periodo_grade, aulas_semanais, carga_horaria, obrigatoria)
    SELECT o.cod_grade_curricular, o.cod_curso, o.cod_etapa, o.cod_disciplina, NULL, NULL, o.carga_horaria, 1
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM edu_grade_curricular_disciplinas x
                      WHERE x.cod_grade_curricular = o.cod_grade_curricular AND x.cod_disciplina = o.cod_disciplina);

    IF EXISTS (SELECT 1 FROM #origem)
        DELETE d
        FROM edu_grade_curricular_disciplinas d
        WHERE NOT EXISTS (SELECT 1 FROM #origem o
                          WHERE o.cod_grade_curricular = d.cod_grade_curricular AND o.cod_disciplina = d.cod_disciplina);

    COMMIT;
END;
