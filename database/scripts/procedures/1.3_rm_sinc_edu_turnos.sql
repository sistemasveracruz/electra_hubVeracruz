-- [1.3] Sincroniza edu_turnos com RM_IMPORTVC.dbo.STURNO.
-- Chave: cod_turno. Remoção: nenhuma (a tabela não tem deleted_at e turmas apontam para ela).
-- sigla é do HUB e não é alterada.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_edu_turnos
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    SELECT t.CODTURNO AS cod_turno,
           LEFT(MAX(t.NOME), 80) COLLATE DATABASE_DEFAULT AS nome
    INTO #origem
    FROM RM_IMPORTVC.dbo.STURNO t
    WHERE t.CODCOLIGADA = 1
      AND t.NOME IS NOT NULL
    GROUP BY t.CODTURNO;

    BEGIN TRAN;

    UPDATE d
    SET d.nome = o.nome
    FROM edu_turnos d
    JOIN #origem o ON o.cod_turno = d.cod_turno
    WHERE EXISTS (SELECT d.nome EXCEPT SELECT o.nome);

    INSERT INTO edu_turnos (cod_turno, nome)
    SELECT o.cod_turno, o.nome
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM edu_turnos x WHERE x.cod_turno = o.cod_turno);

    COMMIT;
END;
