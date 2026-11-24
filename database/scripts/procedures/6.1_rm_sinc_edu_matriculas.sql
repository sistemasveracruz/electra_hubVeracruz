-- [6.1] Sincroniza edu_matriculas com RM_IMPORTVC.dbo.SMATRICPL (coligada 1).
-- Chave: cod_registro_academico + cod_turma. Remoção: soft delete.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_edu_matriculas
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    WITH base AS (
        SELECT ra.cod_pessoa, ra.cod_escola, ra.cod_registro_academico,
               t.cod_unidade_escolar, t.cod_curso, t.cod_etapa, t.cod_turma,
               st.cod_status,
               ROW_NUMBER() OVER (PARTITION BY ra.cod_registro_academico, t.cod_turma
                                  ORDER BY m.IDHABILITACAOFILIAL) AS rn
        FROM RM_IMPORTVC.dbo.SMATRICPL m
        JOIN edu_registros_academicos ra
             ON ra.cod_escola = m.CODCOLIGADA
            AND ra.registro_academico = LTRIM(RTRIM(m.RA)) COLLATE DATABASE_DEFAULT
            AND ra.deleted_at IS NULL
        JOIN edu_turmas t
             ON t.cod_periodo_letivo = m.IDPERLET
            AND t.codigo_turma = m.CODTURMA COLLATE DATABASE_DEFAULT
            AND t.deleted_at IS NULL
        JOIN edu_status st ON st.cod_status = m.CODSTATUS
        WHERE m.CODCOLIGADA = 1
    )
    SELECT cod_pessoa, cod_escola, cod_registro_academico, cod_unidade_escolar, cod_curso, cod_etapa, cod_turma, cod_status
    INTO #origem
    FROM base
    WHERE rn = 1;

    BEGIN TRAN;

    UPDATE d
    SET d.cod_pessoa = o.cod_pessoa,
        d.cod_escola = o.cod_escola,
        d.cod_unidade_escolar = o.cod_unidade_escolar,
        d.cod_curso = o.cod_curso,
        d.cod_etapa = o.cod_etapa,
        d.cod_status = o.cod_status,
        d.ativo = 1,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM edu_matriculas d
    JOIN #origem o ON o.cod_registro_academico = d.cod_registro_academico AND o.cod_turma = d.cod_turma
    WHERE EXISTS (SELECT d.cod_pessoa, d.cod_escola, d.cod_unidade_escolar, d.cod_curso, d.cod_etapa, d.cod_status
                  EXCEPT
                  SELECT o.cod_pessoa, o.cod_escola, o.cod_unidade_escolar, o.cod_curso, o.cod_etapa, o.cod_status)
       OR d.deleted_at IS NOT NULL;

    INSERT INTO edu_matriculas (cod_pessoa, cod_escola, cod_unidade_escolar, cod_curso, cod_etapa,
                                cod_turma, cod_status, cod_registro_academico, ativo, created_at, updated_at)
    SELECT o.cod_pessoa, o.cod_escola, o.cod_unidade_escolar, o.cod_curso, o.cod_etapa,
           o.cod_turma, o.cod_status, o.cod_registro_academico, 1, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM edu_matriculas x
                      WHERE x.cod_registro_academico = o.cod_registro_academico AND x.cod_turma = o.cod_turma);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM edu_matriculas d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o
                          WHERE o.cod_registro_academico = d.cod_registro_academico AND o.cod_turma = d.cod_turma);

    COMMIT;
END;
