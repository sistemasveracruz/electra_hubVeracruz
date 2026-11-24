-- [3.5] Sincroniza edu_grades_curriculares com RM_IMPORTVC.dbo.SGRADE (coligada 1).
-- Chave: cod_curso + cod_etapa + cod_grade (CODGRADE sem espaços nas pontas). Remoção: soft delete.
-- data_inicio, data_fim e carga_horaria são do HUB e não são alteradas.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_edu_grades_curriculares
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    WITH base AS (
        SELECT LEFT(LTRIM(RTRIM(g.CODGRADE)), 20) COLLATE DATABASE_DEFAULT AS cod_grade,
               et.cod_curso,
               et.cod_etapa,
               LEFT(ISNULL(g.DESCRICAO, g.CODGRADE), 255) COLLATE DATABASE_DEFAULT AS descricao,
               ROW_NUMBER() OVER (PARTITION BY et.cod_curso, et.cod_etapa, LEFT(LTRIM(RTRIM(g.CODGRADE)), 20)
                                  ORDER BY g.CODGRADE) AS rn
        FROM RM_IMPORTVC.dbo.SGRADE g
        JOIN edu_etapas et
             ON et.cod_curso = TRY_CAST(g.CODCURSO AS bigint)
            AND et.cod_etapa = TRY_CAST(g.CODHABILITACAO AS bigint)
        WHERE g.CODCOLIGADA = 1
          AND NULLIF(LTRIM(RTRIM(g.CODGRADE)), '') IS NOT NULL
    )
    SELECT cod_grade, cod_curso, cod_etapa, descricao
    INTO #origem
    FROM base
    WHERE rn = 1;

    BEGIN TRAN;

    UPDATE d
    SET d.descricao = o.descricao,
        d.ativo = 1,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM edu_grades_curriculares d
    JOIN #origem o ON o.cod_curso = d.cod_curso AND o.cod_etapa = d.cod_etapa AND o.cod_grade = d.cod_grade
    WHERE EXISTS (SELECT d.descricao EXCEPT SELECT o.descricao)
       OR d.deleted_at IS NOT NULL;

    INSERT INTO edu_grades_curriculares (cod_grade, cod_curso, cod_etapa, descricao, ativo, created_at, updated_at)
    SELECT o.cod_grade, o.cod_curso, o.cod_etapa, o.descricao, 1, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM edu_grades_curriculares x
                      WHERE x.cod_curso = o.cod_curso AND x.cod_etapa = o.cod_etapa AND x.cod_grade = o.cod_grade);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM edu_grades_curriculares d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o
                          WHERE o.cod_curso = d.cod_curso AND o.cod_etapa = d.cod_etapa AND o.cod_grade = d.cod_grade);

    COMMIT;
END;
