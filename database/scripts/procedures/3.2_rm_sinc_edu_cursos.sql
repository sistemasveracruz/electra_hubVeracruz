-- [3.2] Sincroniza edu_cursos com RM_IMPORTVC.dbo.SCURSO (coligada 1).
-- Chave: cod_curso (CODCURSO numérico). Remoção: soft delete.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_edu_cursos
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    SELECT TRY_CAST(s.CODCURSO AS bigint) AS cod_curso,
           es.cod_escola,
           tc.cod_tipo_curso,
           LEFT(s.NOME, 150) COLLATE DATABASE_DEFAULT AS nome,
           LEFT(s.CODCURSO, 12) COLLATE DATABASE_DEFAULT AS sigla
    INTO #origem
    FROM RM_IMPORTVC.dbo.SCURSO s
    JOIN inst_escolas es ON es.cod_escola = s.CODCOLIGADA
    JOIN edu_tipo_cursos tc ON tc.cod_tipo_curso = s.CODTIPOCURSO
    WHERE s.CODCOLIGADA = 1
      AND TRY_CAST(s.CODCURSO AS bigint) IS NOT NULL;

    BEGIN TRAN;

    UPDATE d
    SET d.cod_escola = o.cod_escola,
        d.cod_tipo_curso = o.cod_tipo_curso,
        d.nome = o.nome,
        d.sigla = o.sigla,
        d.ativo = 1,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM edu_cursos d
    JOIN #origem o ON o.cod_curso = d.cod_curso
    WHERE EXISTS (SELECT d.cod_escola, d.cod_tipo_curso, d.nome, d.sigla
                  EXCEPT
                  SELECT o.cod_escola, o.cod_tipo_curso, o.nome, o.sigla)
       OR d.deleted_at IS NOT NULL;

    INSERT INTO edu_cursos (cod_curso, cod_escola, cod_tipo_curso, nome, sigla, ativo, created_at, updated_at)
    SELECT o.cod_curso, o.cod_escola, o.cod_tipo_curso, o.nome, o.sigla, 1, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM edu_cursos x WHERE x.cod_curso = o.cod_curso);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM edu_cursos d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o WHERE o.cod_curso = d.cod_curso);

    COMMIT;
END;
