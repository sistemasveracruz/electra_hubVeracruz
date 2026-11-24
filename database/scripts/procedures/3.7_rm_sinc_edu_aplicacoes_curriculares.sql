-- [3.7] Sincroniza edu_aplicacoes_curriculares com RM_IMPORTVC.dbo.SHABILITACAOFILIAL (coligada 1):
-- em qual unidade escolar e turno cada grade é usada.
-- Chave: cod_aplicacao_curricular (IDHABILITACAOFILIAL).
-- Remoção: DELETE físico (a tabela não tem deleted_at e nenhuma outra aponta para ela).
-- regime_grade e periodo_grade são do HUB e não são alteradas.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_edu_aplicacoes_curriculares
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    WITH base AS (
        SELECT hf.IDHABILITACAOFILIAL AS cod_aplicacao_curricular,
               gc.cod_grade_curricular,
               u.cod_unidade_escolar,
               gc.cod_etapa,
               tu.cod_turno,
               gc.data_inicio,
               gc.data_fim,
               ROW_NUMBER() OVER (PARTITION BY hf.IDHABILITACAOFILIAL ORDER BY gc.cod_grade_curricular) AS rn
        FROM RM_IMPORTVC.dbo.SHABILITACAOFILIAL hf
        JOIN edu_grades_curriculares gc
             ON gc.cod_curso = TRY_CAST(hf.CODCURSO AS bigint)
            AND gc.cod_etapa = TRY_CAST(hf.CODHABILITACAO AS bigint)
            AND gc.cod_grade = LTRIM(RTRIM(hf.CODGRADE)) COLLATE DATABASE_DEFAULT
            AND gc.deleted_at IS NULL
        LEFT JOIN inst_unidades_escolares u ON u.cod_unidade_escolar = hf.CODFILIAL
        LEFT JOIN edu_turnos tu ON tu.cod_turno = hf.CODTURNO
        WHERE hf.CODCOLIGADA = 1
    )
    SELECT cod_aplicacao_curricular, cod_grade_curricular, cod_unidade_escolar, cod_etapa, cod_turno, data_inicio, data_fim
    INTO #origem
    FROM base
    WHERE rn = 1;

    BEGIN TRAN;

    UPDATE d
    SET d.cod_grade_curricular = o.cod_grade_curricular,
        d.cod_unidade_escolar = o.cod_unidade_escolar,
        d.cod_etapa = o.cod_etapa,
        d.cod_turno = o.cod_turno,
        d.data_inicio = o.data_inicio,
        d.data_fim = o.data_fim
    FROM edu_aplicacoes_curriculares d
    JOIN #origem o ON o.cod_aplicacao_curricular = d.cod_aplicacao_curricular
    WHERE EXISTS (SELECT d.cod_grade_curricular, d.cod_unidade_escolar, d.cod_etapa, d.cod_turno, d.data_inicio, d.data_fim
                  EXCEPT
                  SELECT o.cod_grade_curricular, o.cod_unidade_escolar, o.cod_etapa, o.cod_turno, o.data_inicio, o.data_fim);

    INSERT INTO edu_aplicacoes_curriculares (cod_aplicacao_curricular, cod_grade_curricular, cod_unidade_escolar,
                                             cod_etapa, cod_turno, regime_grade, periodo_grade, data_inicio, data_fim)
    SELECT o.cod_aplicacao_curricular, o.cod_grade_curricular, o.cod_unidade_escolar,
           o.cod_etapa, o.cod_turno, NULL, NULL, o.data_inicio, o.data_fim
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM edu_aplicacoes_curriculares x
                      WHERE x.cod_aplicacao_curricular = o.cod_aplicacao_curricular);

    IF EXISTS (SELECT 1 FROM #origem)
        DELETE d
        FROM edu_aplicacoes_curriculares d
        WHERE NOT EXISTS (SELECT 1 FROM #origem o WHERE o.cod_aplicacao_curricular = d.cod_aplicacao_curricular);

    COMMIT;
END;
