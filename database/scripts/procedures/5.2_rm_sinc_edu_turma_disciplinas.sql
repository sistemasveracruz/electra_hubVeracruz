-- [5.2] Sincroniza edu_turma_disciplinas com RM_IMPORTVC.dbo.STURMADISC (coligada 1).
-- Chave: cod_turma_disciplina (IDTURMADISC). Remoção: soft delete.
-- Uma linha por IDTURMADISC e uma por turma + disciplina (índice único uk_turma_disciplinas_disciplina_turma).
CREATE OR ALTER PROCEDURE dbo.rm_sinc_edu_turma_disciplinas
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    WITH base AS (
        SELECT td.IDTURMADISC AS cod_turma_disciplina, t.cod_turma, d.cod_disciplina,
               ROW_NUMBER() OVER (PARTITION BY t.cod_turma, d.cod_disciplina ORDER BY td.IDTURMADISC) AS rn_par,
               ROW_NUMBER() OVER (PARTITION BY td.IDTURMADISC ORDER BY t.cod_turma, d.cod_disciplina) AS rn_id
        FROM RM_IMPORTVC.dbo.STURMADISC td
        JOIN edu_turmas t
             ON t.cod_periodo_letivo = td.IDPERLET
            AND t.codigo_turma = td.CODTURMA COLLATE DATABASE_DEFAULT
            AND t.deleted_at IS NULL
        JOIN edu_disciplinas d ON d.sigla = td.CODDISC COLLATE DATABASE_DEFAULT
        WHERE td.CODCOLIGADA = 1
    )
    SELECT cod_turma_disciplina, cod_turma, cod_disciplina
    INTO #origem
    FROM base
    WHERE rn_par = 1 AND rn_id = 1;

    BEGIN TRAN;

    UPDATE d
    SET d.cod_turma = o.cod_turma,
        d.cod_disciplina = o.cod_disciplina,
        d.ativo = 1,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM edu_turma_disciplinas d
    JOIN #origem o ON o.cod_turma_disciplina = d.cod_turma_disciplina
    WHERE d.cod_turma <> o.cod_turma
       OR d.cod_disciplina <> o.cod_disciplina
       OR d.deleted_at IS NOT NULL;

    INSERT INTO edu_turma_disciplinas (cod_turma_disciplina, cod_turma, cod_disciplina, ativo, created_at, updated_at)
    SELECT o.cod_turma_disciplina, o.cod_turma, o.cod_disciplina, 1, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM edu_turma_disciplinas x WHERE x.cod_turma_disciplina = o.cod_turma_disciplina)
      AND NOT EXISTS (SELECT 1 FROM edu_turma_disciplinas x
                      WHERE x.cod_turma = o.cod_turma AND x.cod_disciplina = o.cod_disciplina);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM edu_turma_disciplinas d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o WHERE o.cod_turma_disciplina = d.cod_turma_disciplina);

    COMMIT;
END;
