-- [1.2] Sincroniza edu_tipo_salas com RM_IMPORTVC.dbo.STIPOSALA.
-- Chave: cod_tipo_sala. Remoção: soft delete.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_edu_tipo_salas
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    WITH base AS (
        SELECT TRY_CAST(ts.CODTIPOSALA AS bigint) AS cod_tipo_sala,
               LEFT(ts.NOME, 120) COLLATE DATABASE_DEFAULT AS nome,
               CAST(CASE WHEN ts.PERMITERESERVA = 'S' THEN 1 ELSE 0 END AS bit) AS permite_reserva,
               ROW_NUMBER() OVER (PARTITION BY TRY_CAST(ts.CODTIPOSALA AS bigint) ORDER BY ts.NOME) AS rn
        FROM RM_IMPORTVC.dbo.STIPOSALA ts
        WHERE TRY_CAST(ts.CODTIPOSALA AS bigint) IS NOT NULL
          AND ts.NOME IS NOT NULL
    )
    SELECT cod_tipo_sala, nome, permite_reserva
    INTO #origem
    FROM base
    WHERE rn = 1;

    BEGIN TRAN;

    UPDATE d
    SET d.nome = o.nome,
        d.permite_reserva = o.permite_reserva,
        d.ativo = 1,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM edu_tipo_salas d
    JOIN #origem o ON o.cod_tipo_sala = d.cod_tipo_sala
    WHERE EXISTS (SELECT d.nome, d.permite_reserva EXCEPT SELECT o.nome, o.permite_reserva)
       OR d.deleted_at IS NOT NULL;

    INSERT INTO edu_tipo_salas (cod_tipo_sala, nome, permite_reserva, ativo, created_at, updated_at)
    SELECT o.cod_tipo_sala, o.nome, o.permite_reserva, 1, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM edu_tipo_salas x WHERE x.cod_tipo_sala = o.cod_tipo_sala);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM edu_tipo_salas d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o WHERE o.cod_tipo_sala = d.cod_tipo_sala);

    COMMIT;
END;
