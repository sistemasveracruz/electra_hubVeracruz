-- [3.1] Sincroniza edu_salas com RM_IMPORTVC.dbo.SSALA (coligada 1).
-- Chave: cod_unidade_escolar + nome (CODSALA). Remoção: soft delete.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_edu_salas
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    WITH base AS (
        SELECT u.cod_unidade_escolar,
               ts.cod_tipo_sala,
               LEFT(LTRIM(RTRIM(s.CODSALA)), 120) COLLATE DATABASE_DEFAULT AS nome,
               LEFT(s.DESCRICAO, 250) COLLATE DATABASE_DEFAULT AS descricao,
               ROW_NUMBER() OVER (PARTITION BY u.cod_unidade_escolar, LTRIM(RTRIM(s.CODSALA)) ORDER BY s.CODSALA) AS rn
        FROM RM_IMPORTVC.dbo.SSALA s
        JOIN inst_unidades_escolares u ON u.cod_unidade_escolar = s.CODFILIAL
        JOIN edu_tipo_salas ts ON ts.cod_tipo_sala = TRY_CAST(s.CODTIPOSALA AS bigint)
        WHERE s.CODCOLIGADA = 1
          AND NULLIF(LTRIM(RTRIM(s.CODSALA)), '') IS NOT NULL
    )
    SELECT cod_unidade_escolar, cod_tipo_sala, nome, descricao
    INTO #origem
    FROM base
    WHERE rn = 1;

    BEGIN TRAN;

    UPDATE d
    SET d.cod_tipo_sala = o.cod_tipo_sala,
        d.descricao = o.descricao,
        d.ativo = 1,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM edu_salas d
    JOIN #origem o ON o.cod_unidade_escolar = d.cod_unidade_escolar AND o.nome = d.nome
    WHERE EXISTS (SELECT d.cod_tipo_sala, d.descricao EXCEPT SELECT o.cod_tipo_sala, o.descricao)
       OR d.deleted_at IS NOT NULL;

    INSERT INTO edu_salas (cod_unidade_escolar, cod_tipo_sala, nome, descricao, ativo, created_at, updated_at)
    SELECT o.cod_unidade_escolar, o.cod_tipo_sala, o.nome, o.descricao, 1, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM edu_salas x
                      WHERE x.cod_unidade_escolar = o.cod_unidade_escolar AND x.nome = o.nome);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM edu_salas d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o
                          WHERE o.cod_unidade_escolar = d.cod_unidade_escolar AND o.nome = d.nome);

    COMMIT;
END;
