-- [5.1] Sincroniza edu_turmas com RM_IMPORTVC.dbo.STURMA (coligada 1).
-- Coordenador, orientador e professor responsável vêm de SPROFESSORTURMA.TIPOPROF (D, R e E).
-- Chave: cod_periodo_letivo + codigo_turma. Remoção: soft delete.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_edu_turmas
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    WITH papeis AS (
        SELECT td.IDPERLET, td.CODTURMA,
               MIN(CASE WHEN pt.TIPOPROF = 'D' THEN p.cod_pessoa END) AS coordenador,
               MIN(CASE WHEN pt.TIPOPROF = 'R' THEN p.cod_pessoa END) AS orientador,
               MIN(CASE WHEN pt.TIPOPROF = 'E' THEN p.cod_pessoa END) AS professor_responsavel
        FROM RM_IMPORTVC.dbo.STURMADISC td
        JOIN RM_IMPORTVC.dbo.SPROFESSORTURMA pt
             ON pt.CODCOLIGADA = td.CODCOLIGADA AND pt.IDTURMADISC = td.IDTURMADISC
        JOIN RM_IMPORTVC.dbo.SPROFESSOR sp
             ON sp.CODCOLIGADA = pt.CODCOLIGADA AND sp.CODPROF = pt.CODPROF
        JOIN inst_pessoas p ON p.cod_pessoa = sp.CODPESSOA AND p.deleted_at IS NULL
        WHERE td.CODCOLIGADA = 1
          AND pt.TIPOPROF IN ('D', 'R', 'E')
        GROUP BY td.IDPERLET, td.CODTURMA
    ),
    base AS (
        SELECT LEFT(t.CODTURMA, 20) COLLATE DATABASE_DEFAULT AS codigo_turma,
               u.cod_unidade_escolar,
               pl.cod_periodo_letivo,
               c.cod_curso,
               et.cod_etapa,
               es.cod_sala,
               tu.cod_turno,
               pa.coordenador AS cod_pessoa_coordenador,
               pa.orientador AS cod_pessoa_orientador,
               pa.professor_responsavel AS cod_pessoa_professor_responsavel,
               LEFT(ISNULL(t.NOME, t.CODTURMA), 120) COLLATE DATABASE_DEFAULT AS nome,
               LEFT(ISNULL(t.NOMERED, t.CODTURMA), 12) COLLATE DATABASE_DEFAULT AS nome_reduzido,
               CAST(CASE WHEN t.TURMAENCERRADA = 'S' THEN 0 ELSE 1 END AS bit) AS ativo,
               ROW_NUMBER() OVER (PARTITION BY t.IDPERLET, LEFT(t.CODTURMA, 20) ORDER BY es.cod_sala) AS rn
        FROM RM_IMPORTVC.dbo.STURMA t
        JOIN RM_IMPORTVC.dbo.SHABILITACAOFILIAL hf
             ON hf.CODCOLIGADA = t.CODCOLIGADA AND hf.IDHABILITACAOFILIAL = t.IDHABILITACAOFILIAL
        JOIN inst_unidades_escolares u ON u.cod_unidade_escolar = t.CODFILIAL
        JOIN edu_periodos_letivos pl ON pl.cod_periodo_letivo = t.IDPERLET
        JOIN edu_cursos c ON c.cod_curso = TRY_CAST(hf.CODCURSO AS bigint)
        JOIN edu_etapas et
             ON et.cod_curso = c.cod_curso
            AND et.cod_etapa = TRY_CAST(hf.CODHABILITACAO AS bigint)
        JOIN edu_turnos tu ON tu.cod_turno = hf.CODTURNO
        LEFT JOIN edu_salas es
               ON es.cod_unidade_escolar = u.cod_unidade_escolar
              AND es.nome = LTRIM(RTRIM(t.CODSALA)) COLLATE DATABASE_DEFAULT
              AND es.deleted_at IS NULL
        LEFT JOIN papeis pa ON pa.IDPERLET = t.IDPERLET AND pa.CODTURMA = t.CODTURMA
        WHERE t.CODCOLIGADA = 1
    )
    SELECT codigo_turma, cod_unidade_escolar, cod_periodo_letivo, cod_curso, cod_etapa, cod_sala, cod_turno,
           cod_pessoa_coordenador, cod_pessoa_orientador, cod_pessoa_professor_responsavel,
           nome, nome_reduzido, ativo
    INTO #origem
    FROM base
    WHERE rn = 1;

    BEGIN TRAN;

    UPDATE d
    SET d.cod_unidade_escolar = o.cod_unidade_escolar,
        d.cod_curso = o.cod_curso,
        d.cod_etapa = o.cod_etapa,
        d.cod_sala = o.cod_sala,
        d.cod_turno = o.cod_turno,
        d.cod_pessoa_coordenador = o.cod_pessoa_coordenador,
        d.cod_pessoa_orientador = o.cod_pessoa_orientador,
        d.cod_pessoa_professor_responsavel = o.cod_pessoa_professor_responsavel,
        d.nome = o.nome,
        d.nome_reduzido = o.nome_reduzido,
        d.ativo = o.ativo,
        d.deleted_at = NULL,
        d.updated_at = GETDATE()
    FROM edu_turmas d
    JOIN #origem o ON o.cod_periodo_letivo = d.cod_periodo_letivo AND o.codigo_turma = d.codigo_turma
    WHERE EXISTS (SELECT d.cod_unidade_escolar, d.cod_curso, d.cod_etapa, d.cod_sala, d.cod_turno,
                         d.cod_pessoa_coordenador, d.cod_pessoa_orientador, d.cod_pessoa_professor_responsavel,
                         d.nome, d.nome_reduzido, d.ativo
                  EXCEPT
                  SELECT o.cod_unidade_escolar, o.cod_curso, o.cod_etapa, o.cod_sala, o.cod_turno,
                         o.cod_pessoa_coordenador, o.cod_pessoa_orientador, o.cod_pessoa_professor_responsavel,
                         o.nome, o.nome_reduzido, o.ativo)
       OR d.deleted_at IS NOT NULL;

    INSERT INTO edu_turmas (codigo_turma, cod_unidade_escolar, cod_periodo_letivo, cod_curso, cod_etapa, cod_sala, cod_turno,
                            cod_pessoa_coordenador, cod_pessoa_orientador, cod_pessoa_professor_responsavel,
                            nome, nome_reduzido, ativo, created_at, updated_at)
    SELECT o.codigo_turma, o.cod_unidade_escolar, o.cod_periodo_letivo, o.cod_curso, o.cod_etapa, o.cod_sala, o.cod_turno,
           o.cod_pessoa_coordenador, o.cod_pessoa_orientador, o.cod_pessoa_professor_responsavel,
           o.nome, o.nome_reduzido, o.ativo, GETDATE(), GETDATE()
    FROM #origem o
    WHERE NOT EXISTS (SELECT 1 FROM edu_turmas x
                      WHERE x.cod_periodo_letivo = o.cod_periodo_letivo AND x.codigo_turma = o.codigo_turma);

    IF EXISTS (SELECT 1 FROM #origem)
        UPDATE d
        SET d.ativo = 0, d.deleted_at = GETDATE(), d.updated_at = GETDATE()
        FROM edu_turmas d
        WHERE d.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM #origem o
                          WHERE o.cod_periodo_letivo = d.cod_periodo_letivo AND o.codigo_turma = d.codigo_turma);

    COMMIT;
END;
