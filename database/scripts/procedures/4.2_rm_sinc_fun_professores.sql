-- [4.2] Sincroniza fun_professores com RM_IMPORTVC.dbo.SPROFESSOR (coligada 1).
-- Cada chapa do professor na PFUNC (CORPORERM_HML, ligada pelo CPF) vira uma linha.
-- Chave: cod_professor (CODPROF) + chapa. Remoção: soft delete.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_fun_professores
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    WITH base AS (
        SELECT TRY_CAST(sp.CODPROF AS bigint) AS cod_professor,
               ip.cod_pessoa,
               ff.chapa,
               CAST(CASE WHEN pf.CODSITUACAO = 'A' THEN 1 ELSE 0 END AS bit) AS ativo,
               ROW_NUMBER() OVER (PARTITION BY TRY_CAST(sp.CODPROF AS bigint), ff.chapa ORDER BY ip.cod_pessoa) AS rn
        FROM RM_IMPORTVC.dbo.SPROFESSOR sp
        JOIN inst_pessoas ip ON ip.cod_pessoa = sp.CODPESSOA AND ip.deleted_at IS NULL
        JOIN RM_IMPORTVC.dbo.PPESSOA p ON p.CODIGO = sp.CODPESSOA
        JOIN CORPORERM_HML.dbo.PPESSOA p2 ON p2.CPF = p.CPF
        JOIN CORPORERM_HML.dbo.PFUNC pf ON pf.CODPESSOA = p2.CODIGO
        JOIN fun_funcionarios ff ON ff.chapa = pf.CHAPA COLLATE DATABASE_DEFAULT AND ff.deleted_at IS NULL
        WHERE sp.CODCOLIGADA = 1
          AND TRY_CAST(sp.CODPROF AS bigint) IS NOT NULL
          AND NULLIF(LTRIM(RTRIM(p.CPF)), '') IS NOT NULL
    )
    SELECT cod_professor, cod_pessoa, chapa, ativo
    INTO #origem
    FROM base
    WHERE rn = 1;

    BEGIN TRAN;

    UPDATE d
    SET d.cod_pessoa = o.cod_pessoa,
        d.ativo = o.ativo,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM fun_professores d
    JOIN #origem o ON o.cod_professor = d.cod_professor AND o.chapa = d.chapa
    WHERE EXISTS (SELECT d.cod_pessoa, d.ativo EXCEPT SELECT o.cod_pessoa, o.ativo)
       OR d.deleted_at IS NOT NULL;

    INSERT INTO fun_professores (cod_professor, cod_pessoa, chapa, ativo, created_at, updated_at)
    SELECT o.cod_professor, o.cod_pessoa, o.chapa, o.ativo, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM fun_professores x WHERE x.cod_professor = o.cod_professor AND x.chapa = o.chapa);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM fun_professores d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o WHERE o.cod_professor = d.cod_professor AND o.chapa = d.chapa);

    COMMIT;
END;
