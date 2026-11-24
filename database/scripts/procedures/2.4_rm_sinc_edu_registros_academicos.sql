-- [2.4] Sincroniza edu_registros_academicos com RM_IMPORTVC.dbo.SALUNO (coligada 1).
-- Chave: cod_escola + registro_academico (RA). Remoção: soft delete.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_edu_registros_academicos
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    WITH base AS (
        SELECT pe.cod_pessoa,
               es.cod_escola,
               LTRIM(RTRIM(a.RA)) COLLATE DATABASE_DEFAULT AS registro_academico,
               ROW_NUMBER() OVER (PARTITION BY es.cod_escola, LTRIM(RTRIM(a.RA)) ORDER BY a.CODPESSOA) AS rn
        FROM RM_IMPORTVC.dbo.SALUNO a
        JOIN inst_pessoas pe ON pe.cod_pessoa = a.CODPESSOA
        JOIN inst_escolas es ON es.cod_escola = a.CODCOLIGADA
        WHERE a.CODCOLIGADA = 1
          AND LEN(LTRIM(RTRIM(a.RA))) BETWEEN 1 AND 12
    )
    SELECT cod_pessoa, cod_escola, registro_academico
    INTO #origem
    FROM base
    WHERE rn = 1;

    BEGIN TRAN;

    UPDATE d
    SET d.cod_pessoa = o.cod_pessoa,
        d.ativo = 1,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM edu_registros_academicos d
    JOIN #origem o ON o.cod_escola = d.cod_escola AND o.registro_academico = d.registro_academico
    WHERE d.cod_pessoa <> o.cod_pessoa
       OR d.deleted_at IS NOT NULL;

    INSERT INTO edu_registros_academicos (cod_pessoa, cod_escola, registro_academico, ativo, created_at, updated_at)
    SELECT o.cod_pessoa, o.cod_escola, o.registro_academico, 1, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM edu_registros_academicos x
                      WHERE x.cod_escola = o.cod_escola AND x.registro_academico = o.registro_academico);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM edu_registros_academicos d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o
                          WHERE o.cod_escola = d.cod_escola AND o.registro_academico = d.registro_academico);

    COMMIT;
END;
