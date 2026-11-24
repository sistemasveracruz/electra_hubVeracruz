-- [2.5] Sincroniza edu_familias com RM_IMPORTVC.dbo.VFILIACAO (pai e mãe de cada aluno).
-- Chave: cod_familia (CODFILIACAO). Remoção: soft delete.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_edu_familias
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    WITH base AS (
        SELECT f.CODFILIACAO AS cod_familia,
               pp.cod_pessoa,
               pa.cod_pessoa AS cod_pessoa_aluno,
               ap.cod_parentesco,
               LEFT(ap.descricao, 25) AS parentesco,
               ROW_NUMBER() OVER (PARTITION BY f.CODFILIACAO ORDER BY ap.cod_parentesco) AS rn
        FROM RM_IMPORTVC.dbo.VFILIACAO f
        JOIN inst_pessoas pp ON pp.cod_pessoa = f.CODPESSOAFILIACAO
        JOIN inst_pessoas pa ON pa.cod_pessoa = f.CODPESSOAFILHO
        JOIN aux_parentesco ap
             ON UPPER(ap.descricao) = CASE WHEN f.TIPORELACIONAMENTO = 'M' THEN N'MÃE' ELSE N'PAI' END
            AND ap.deleted_at IS NULL
    )
    SELECT cod_familia, cod_pessoa, cod_pessoa_aluno, cod_parentesco, parentesco
    INTO #origem
    FROM base
    WHERE rn = 1;

    BEGIN TRAN;

    UPDATE d
    SET d.cod_pessoa = o.cod_pessoa,
        d.cod_pessoa_aluno = o.cod_pessoa_aluno,
        d.cod_parentesco = o.cod_parentesco,
        d.parentesco = o.parentesco,
        d.ativo = 1,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM edu_familias d
    JOIN #origem o ON o.cod_familia = d.cod_familia
    WHERE EXISTS (SELECT d.cod_pessoa, d.cod_pessoa_aluno, d.cod_parentesco, d.parentesco
                  EXCEPT
                  SELECT o.cod_pessoa, o.cod_pessoa_aluno, o.cod_parentesco, o.parentesco)
       OR d.deleted_at IS NOT NULL;

    INSERT INTO edu_familias (cod_familia, cod_pessoa, cod_pessoa_aluno, cod_parentesco, parentesco, ativo, created_at, updated_at)
    SELECT o.cod_familia, o.cod_pessoa, o.cod_pessoa_aluno, o.cod_parentesco, o.parentesco, 1, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM edu_familias x WHERE x.cod_familia = o.cod_familia);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM edu_familias d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o WHERE o.cod_familia = d.cod_familia);

    COMMIT;
END;
