-- [2.3] Sincroniza edu_tipo_cursos com RM_IMPORTVC.dbo.STIPOCURSO (coligada 1).
-- Chave: cod_tipo_curso (CODTIPOCURSO). Remoção: soft delete.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_edu_tipo_cursos
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    SELECT t.CODTIPOCURSO AS cod_tipo_curso,
           es.cod_escola,
           LEFT(t.NOME, 150) COLLATE DATABASE_DEFAULT AS nome
    INTO #origem
    FROM RM_IMPORTVC.dbo.STIPOCURSO t
    JOIN inst_escolas es ON es.cod_escola = t.CODCOLIGADA
    WHERE t.CODCOLIGADA = 1;

    BEGIN TRAN;

    UPDATE d
    SET d.cod_escola = o.cod_escola,
        d.nome = o.nome,
        d.ativo = 1,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM edu_tipo_cursos d
    JOIN #origem o ON o.cod_tipo_curso = d.cod_tipo_curso
    WHERE EXISTS (SELECT d.cod_escola, d.nome EXCEPT SELECT o.cod_escola, o.nome)
       OR d.deleted_at IS NOT NULL;

    INSERT INTO edu_tipo_cursos (cod_tipo_curso, cod_escola, nome, ativo, created_at, updated_at)
    SELECT o.cod_tipo_curso, o.cod_escola, o.nome, 1, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM edu_tipo_cursos x WHERE x.cod_tipo_curso = o.cod_tipo_curso);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM edu_tipo_cursos d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o WHERE o.cod_tipo_curso = d.cod_tipo_curso);

    COMMIT;
END;
