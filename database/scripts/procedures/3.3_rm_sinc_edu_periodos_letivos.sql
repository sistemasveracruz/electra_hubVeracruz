-- [3.3] Sincroniza edu_periodos_letivos com RM_IMPORTVC.dbo.SPLETIVO (coligada 1).
-- Chave: cod_periodo_letivo (IDPERLET). Remoção: soft delete.
-- O INSERT vem antes do UPDATE: um período existente pode passar a apontar para um período anterior novo.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_edu_periodos_letivos
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    SELECT p.IDPERLET AS cod_periodo_letivo,
           u.cod_unidade_escolar,
           tc.cod_tipo_curso,
           p.IDPERLETANT AS cod_periodo_letivo_anterior,
           p.DTINICIO AS data_inicio,
           p.DTFIM AS data_fim,
           p.DTPREVISTA AS data_estimada,
           CAST(CASE WHEN p.ENCERRADO = 'S' THEN 0 ELSE 1 END AS bit) AS ativo
    INTO #origem
    FROM RM_IMPORTVC.dbo.SPLETIVO p
    JOIN inst_unidades_escolares u ON u.cod_unidade_escolar = p.CODFILIAL
    JOIN edu_tipo_cursos tc ON tc.cod_tipo_curso = p.CODTIPOCURSO
    WHERE p.CODCOLIGADA = 1;

    -- Período anterior que não existe na origem fica vazio, para não violar a FK.
    UPDATE o
    SET o.cod_periodo_letivo_anterior = NULL
    FROM #origem o
    WHERE o.cod_periodo_letivo_anterior IS NOT NULL
      AND NOT EXISTS (SELECT 1 FROM #origem a WHERE a.cod_periodo_letivo = o.cod_periodo_letivo_anterior);

    BEGIN TRAN;

    INSERT INTO edu_periodos_letivos (cod_periodo_letivo, cod_unidade_escolar, cod_tipo_curso, cod_periodo_letivo_anterior,
                                      data_inicio, data_fim, data_estimada, ativo, created_at, updated_at)
    SELECT o.cod_periodo_letivo, o.cod_unidade_escolar, o.cod_tipo_curso, NULL,
           o.data_inicio, o.data_fim, o.data_estimada, o.ativo, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM edu_periodos_letivos x WHERE x.cod_periodo_letivo = o.cod_periodo_letivo);

    UPDATE d
    SET d.cod_unidade_escolar = o.cod_unidade_escolar,
        d.cod_tipo_curso = o.cod_tipo_curso,
        d.cod_periodo_letivo_anterior = o.cod_periodo_letivo_anterior,
        d.data_inicio = o.data_inicio,
        d.data_fim = o.data_fim,
        d.data_estimada = o.data_estimada,
        d.ativo = o.ativo,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM edu_periodos_letivos d
    JOIN #origem o ON o.cod_periodo_letivo = d.cod_periodo_letivo
    WHERE EXISTS (SELECT d.cod_unidade_escolar, d.cod_tipo_curso, d.cod_periodo_letivo_anterior,
                         d.data_inicio, d.data_fim, d.data_estimada, d.ativo
                  EXCEPT
                  SELECT o.cod_unidade_escolar, o.cod_tipo_curso, o.cod_periodo_letivo_anterior,
                         o.data_inicio, o.data_fim, o.data_estimada, o.ativo)
       OR d.deleted_at IS NOT NULL;

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM edu_periodos_letivos d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o WHERE o.cod_periodo_letivo = d.cod_periodo_letivo);

    COMMIT;
END;
