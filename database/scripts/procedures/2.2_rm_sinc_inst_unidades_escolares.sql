-- [2.2] Sincroniza inst_unidades_escolares com RM_IMPORTVC.dbo.GFILIAL (coligada 1).
-- Chave: cod_unidade_escolar (CODFILIAL). Remoção: soft delete.
-- descricao é do HUB e não é alterada.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_inst_unidades_escolares
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    SELECT g.CODFILIAL AS cod_unidade_escolar,
           es.cod_escola,
           LEFT(ISNULL(g.NOMEFANTASIA, g.NOME), 150) COLLATE DATABASE_DEFAULT AS nome,
           LEFT(g.NOMEFANTASIA, 20) COLLATE DATABASE_DEFAULT AS sigla
    INTO #origem
    FROM RM_IMPORTVC.dbo.GFILIAL g
    JOIN inst_escolas es ON es.cod_escola = g.CODCOLIGADA
    WHERE g.CODCOLIGADA = 1;

    BEGIN TRAN;

    UPDATE d
    SET d.cod_escola = o.cod_escola,
        d.nome = o.nome,
        d.sigla = o.sigla,
        d.ativo = 1,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM inst_unidades_escolares d
    JOIN #origem o ON o.cod_unidade_escolar = d.cod_unidade_escolar
    WHERE EXISTS (SELECT d.cod_escola, d.nome, d.sigla EXCEPT SELECT o.cod_escola, o.nome, o.sigla)
       OR d.deleted_at IS NOT NULL;

    INSERT INTO inst_unidades_escolares (cod_unidade_escolar, cod_escola, nome, sigla, ativo, created_at, updated_at)
    SELECT o.cod_unidade_escolar, o.cod_escola, o.nome, o.sigla, 1, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM inst_unidades_escolares x WHERE x.cod_unidade_escolar = o.cod_unidade_escolar);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM inst_unidades_escolares d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o WHERE o.cod_unidade_escolar = d.cod_unidade_escolar);

    COMMIT;
END;
