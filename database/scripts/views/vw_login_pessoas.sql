-- View de login do App VC / VeraDigital: uma linha por CPF com os vínculos, os alunos,
-- os cursos/etapas e as permissões (habilita_*) usadas pelo appapi.
--
-- Uso esperado: SELECT * FROM dbo.vw_login_pessoas WHERE cpf = '12345678901';
--
-- Desempenho: a view parte de inst_pessoas e calcula cada pessoa com CROSS APPLY. Com o filtro por CPF,
-- tudo vira busca por índice (ix_pessoas_cpf_cod_pessoa + índices da migration 2026_11_20_000000).
-- Os vínculos são agregados numa passada só (MAX(CASE ...)), em vez de um EXISTS por permissão.
--
-- Depende de bases externas no mesmo servidor: ExAlunos (ex-alunos) e Senior_VeraCruz (títulos em aberto).
CREATE OR ALTER VIEW dbo.vw_login_pessoas
AS
WITH
-- Enturmações do ano corrente com status que acessa o Vera Digital.
enturmas_ano AS (
    SELECT ea.cod_aluno,
           ea.cod_pessoa AS cod_pessoa_aluno,
           ee.cod_status,
           et.cod_curso,
           et.cod_etapa
    FROM edu_enturmas ee
    JOIN edu_status es
         ON es.cod_status = ee.cod_status
        AND es.acessa_vera_digital = 1
    JOIN edu_alunos ea ON ea.cod_aluno = ee.cod_aluno
    JOIN edu_turmas et ON et.cod_turma = ee.cod_turma
    JOIN edu_periodos_letivos epl ON epl.cod_periodo_letivo = et.cod_periodo_letivo
    WHERE epl.data_inicio >= DATEFROMPARTS(YEAR(GETDATE()), 1, 1)
      AND epl.data_inicio <  DATEFROMPARTS(YEAR(GETDATE()) + 1, 1, 1)
),
-- Quem está ligado a cada aluno enturmado no ano, e como.
vinculos AS (
    -- Alunos
    SELECT e.cod_pessoa_aluno AS cod_pessoa, 'ESTUDANTE' AS papel,
           e.cod_aluno, e.cod_status, e.cod_curso, e.cod_etapa
    FROM enturmas_ano e

    UNION ALL

    -- Pais e mães
    SELECT ef.cod_pessoa, 'PAI_OU_MAE',
           e.cod_aluno, e.cod_status, e.cod_curso, e.cod_etapa
    FROM edu_familias ef
    JOIN enturmas_ano e ON e.cod_pessoa_aluno = ef.cod_pessoa_aluno
    WHERE ef.ativo = 1

    UNION ALL

    -- Responsáveis financeiros
    SELECT rf.cod_pessoa_responsavel, 'RESP_FINANCEIRO',
           e.cod_aluno, e.cod_status, e.cod_curso, e.cod_etapa
    FROM edu_responsaveis_financeiros rf
    JOIN edu_responsaveis er ON er.cod_responsavel_financeiro = rf.cod_responsavel_financeiro
    JOIN enturmas_ano e
         ON e.cod_aluno = er.cod_aluno
        AND e.cod_pessoa_aluno = er.cod_pessoa_aluno
    WHERE rf.ativo = 1

    UNION ALL

    -- Responsáveis pedagógicos
    SELECT rp.cod_pessoa_responsavel, 'RESP_PEDAGOGICO',
           e.cod_aluno, e.cod_status, e.cod_curso, e.cod_etapa
    FROM edu_responsaveis_pedagogico rp
    JOIN edu_responsaveis er ON er.cod_responsavel_pedagogico = rp.cod_responsavel_pedagogico
    JOIN enturmas_ano e
         ON e.cod_aluno = er.cod_aluno
        AND e.cod_pessoa_aluno = er.cod_pessoa_aluno
    WHERE rp.ativo = 1
),
-- CPF dos ex-alunos já sem pontuação, lido uma vez só.
ex_alunos AS (
    SELECT DISTINCT REPLACE(REPLACE(REPLACE(ex.cpf, '.', ''), '-', ''), ' ', '') COLLATE DATABASE_DEFAULT AS cpf
    FROM ExAlunos.dbo.exaluno ex
)
SELECT
    p.cpf,
    p.nome,
    p.cod_pessoa,

    COALESCE(j.cod_alunos, '[]') AS cod_alunos,
    COALESCE(j.cod_cursos, '{}') AS cod_cursos,

    v.estudante,
    v.ingressante,
    f.funcionario,
    v.pai_ou_mae,
    v.responsavel_financeiro,
    v.responsavel_pedagogico,
    x.ex_aluno,
    x.parcelas_em_aberto,

    -- LOGIN
    CASE
        WHEN v.estudante = 1
          OR f.funcionario = 1
          OR v.pai_ou_mae = 1
          OR v.responsavel_financeiro = 1
          OR v.responsavel_pedagogico = 1
          OR (x.ex_aluno = 1 AND x.parcelas_em_aberto = 1)
        THEN CAST(1 AS bit)
        ELSE CAST(0 AS bit)
    END AS habilita_login,

    -- MENU PADRÃO
    b.opcoes_padrao AS habilita_menu,
    b.opcoes_padrao AS habilita_informacoes_gerais,
    b.opcoes_padrao AS habilita_lista_de_materiais,

    -- CALENDÁRIO E CIRCULARES
    CASE WHEN x.ex_aluno = 1
           OR ((v.estudante = 1 OR v.responsavel_pedagogico = 1 OR v.pai_ou_mae = 1) AND v.curso_234 = 1)
         THEN 1 ELSE 0 END AS habilita_calendario,
    CASE WHEN x.ex_aluno = 1
           OR ((v.estudante = 1 OR v.responsavel_pedagogico = 1 OR v.pai_ou_mae = 1) AND v.curso_234 = 1)
         THEN 1 ELSE 0 END AS habilita_circular,

    -- MATERIAIS E UNIFORMES
    b.opcoes_padrao AS habilita_materiais_e_uniformes,

    -- REMATRÍCULA
    r.rematricula AS habilita_rematricula,

    -- ATUALIZAÇÃO DE DADOS
    CASE WHEN v.pai_ou_mae = 1 OR v.responsavel_pedagogico = 1 THEN 1 ELSE 0 END AS habilita_atualizacao_de_dados,

    -- FINANCEIRO E DOAÇÃO
    v.responsavel_financeiro AS habilita_financeiro,
    1 AS habilita_doacao,

    -- SAÍDA REGULAR
    CASE WHEN (v.pai_ou_mae = 1 OR v.responsavel_pedagogico = 1) AND v.curso_23 = 1
         THEN 1 ELSE 0 END AS habilita_saida_regular,

    -- SAÍDA PEDAGÓGICA
    CASE WHEN v.pai_ou_mae = 1 AND v.curso_234 = 1 THEN 1 ELSE 0 END AS habilita_saida_pedagogica,

    -- RESTRIÇÃO ALIMENTAR
    CASE WHEN v.pai_ou_mae = 1 AND (v.ei_1_5 = 1 OR v.ef_1_5 = 1)
         THEN 1 ELSE 0 END AS habilita_programa_educacao_alimentar,

    -- OFICINAS DE FÉRIAS
    CASE WHEN v.responsavel_financeiro = 1 AND (v.ei_1_5 = 1 OR v.ef_1_5 = 1)
         THEN 1 ELSE 0 END AS habilita_atelie_de_ferias,

    -- OFICINAS OPTATIVAS
    CASE WHEN v.responsavel_financeiro = 1 AND v.ef_6_9 = 1 THEN 1 ELSE 0 END AS habilita_oficinas_optativas,

    -- TREINAMENTO ESPORTIVO
    CASE WHEN v.pai_ou_mae = 1 AND (v.ef_6_9 = 1 OR v.em_1_3 = 1)
         THEN 1 ELSE 0 END AS habilita_treinamento_esportivo,

    -- VERA INTEGRAL
    CASE WHEN v.responsavel_financeiro = 1 AND (v.ei_1_5 = 1 OR v.ef_1_5 = 1)
         THEN 1 ELSE 0 END AS habilita_vera_integral,

    -- COMPRA DE MATERIAIS E UNIFORMES
    CASE WHEN (v.pai_ou_mae = 1 OR v.responsavel_financeiro = 1) AND (v.ei_1_5 = 1 OR v.ef_1_9 = 1)
         THEN 1 ELSE 0 END AS habilita_compra_de_materiais,

    -- UNIFORME ESPORTIVO
    CAST(0 AS bit) AS habilita_compra_de_uniforme_esportivo,

    -- TRANSPORTE ESCOLAR E BILHETE ÚNICO
    b.transporte AS habilita_transporte_escolar,
    CASE WHEN v.responsavel_financeiro = 1 AND v.curso_4 = 1
         THEN 1 ELSE 0 END AS habilita_transporte_escolar_ensino_medio,
    b.transporte AS habilita_bilhete_unico

FROM inst_pessoas p
LEFT JOIN ex_alunos exa ON exa.cpf = p.cpf
-- Vínculos, ingressante e cursos/etapas numa passada só.
CROSS APPLY (
    SELECT
        COUNT(*) AS qtd_vinculos,
        ISNULL(MAX(CASE WHEN vi.papel = 'ESTUDANTE'       THEN 1 END), 0) AS estudante,
        ISNULL(MAX(CASE WHEN vi.papel = 'PAI_OU_MAE'      THEN 1 END), 0) AS pai_ou_mae,
        ISNULL(MAX(CASE WHEN vi.papel = 'RESP_FINANCEIRO' THEN 1 END), 0) AS responsavel_financeiro,
        ISNULL(MAX(CASE WHEN vi.papel = 'RESP_PEDAGOGICO' THEN 1 END), 0) AS responsavel_pedagogico,
        ISNULL(MAX(CASE WHEN vi.cod_status = 2            THEN 1 END), 0) AS ingressante,

        ISNULL(MAX(CASE WHEN vi.cod_curso IN (2, 3, 4) THEN 1 END), 0) AS curso_234,
        ISNULL(MAX(CASE WHEN vi.cod_curso IN (2, 3)    THEN 1 END), 0) AS curso_23,
        ISNULL(MAX(CASE WHEN vi.cod_curso = 4          THEN 1 END), 0) AS curso_4,
        ISNULL(MAX(CASE WHEN vi.cod_curso = 2 AND vi.cod_etapa BETWEEN 1 AND 5 THEN 1 END), 0) AS ei_1_5,
        ISNULL(MAX(CASE WHEN vi.cod_curso = 2 AND vi.cod_etapa = 5             THEN 1 END), 0) AS ei_5,
        ISNULL(MAX(CASE WHEN vi.cod_curso = 3 AND vi.cod_etapa BETWEEN 1 AND 5 THEN 1 END), 0) AS ef_1_5,
        ISNULL(MAX(CASE WHEN vi.cod_curso = 3 AND vi.cod_etapa BETWEEN 6 AND 9 THEN 1 END), 0) AS ef_6_9,
        ISNULL(MAX(CASE WHEN vi.cod_curso = 3 AND vi.cod_etapa BETWEEN 1 AND 9 THEN 1 END), 0) AS ef_1_9,
        ISNULL(MAX(CASE WHEN vi.cod_curso = 4 AND vi.cod_etapa BETWEEN 1 AND 3 THEN 1 END), 0) AS em_1_3
    FROM vinculos vi
    WHERE vi.cod_pessoa = p.cod_pessoa
) v
-- Funcionário
CROSS APPLY (
    SELECT CASE WHEN EXISTS (SELECT 1 FROM fun_funcionarios ff WHERE ff.cod_pessoa = p.cod_pessoa)
                THEN 1 ELSE 0 END AS funcionario
) f
-- Ex-aluno e, para ex-aluno, títulos em aberto na Senior.
CROSS APPLY (
    SELECT
        CASE WHEN exa.cpf IS NOT NULL THEN 1 ELSE 0 END AS ex_aluno,
        CASE WHEN exa.cpf IS NOT NULL
              AND EXISTS (SELECT 1
                          FROM Senior_VeraCruz.Senior.E085CLI cli
                          JOIN Senior_VeraCruz.Senior.e301tcr tcr
                               ON tcr.codcli = cli.CodCli
                              AND tcr.sittit IN ('AB', 'CE')
                          -- Comparação numérica: usa o índice de CgcCpf e não perde o zero à esquerda do CPF.
                          WHERE cli.CgcCpf = TRY_CAST(p.cpf AS numeric(14, 0)))
             THEN 1 ELSE 0 END AS parcelas_em_aberto
) x
-- Rematrícula: resp. financeiro de aluno do ano com status 2 ou 7 em EI 1-5, EF 1-9 ou EM 1-3.
CROSS APPLY (
    SELECT CASE WHEN EXISTS (
               SELECT 1
               FROM edu_responsaveis_financeiros rf
               JOIN edu_responsaveis er ON er.cod_responsavel_financeiro = rf.cod_responsavel_financeiro
               JOIN edu_alunos ea
                    ON ea.cod_aluno = er.cod_aluno
                   AND ea.cod_pessoa = er.cod_pessoa_aluno
               JOIN edu_enturmas ee ON ee.cod_aluno = ea.cod_aluno
               JOIN edu_turmas et ON et.cod_turma = ee.cod_turma
               JOIN edu_periodos_letivos epl ON epl.cod_periodo_letivo = et.cod_periodo_letivo
               WHERE rf.cod_pessoa_responsavel = p.cod_pessoa
                 AND rf.ativo = 1
                 AND epl.data_inicio >= DATEFROMPARTS(YEAR(GETDATE()), 1, 1)
                 AND epl.data_inicio <  DATEFROMPARTS(YEAR(GETDATE()) + 1, 1, 1)
                 AND ee.cod_status IN (2, 3, 7)
                 AND (   (et.cod_curso = 2 AND et.cod_etapa BETWEEN 1 AND 5)
                      OR (et.cod_curso = 3 AND et.cod_etapa BETWEEN 1 AND 9)
                      OR (et.cod_curso = 4 AND et.cod_etapa BETWEEN 1 AND 3)))
           THEN 1 ELSE 0 END AS rematricula
) r
-- Expressões repetidas entre as permissões.
CROSS APPLY (
    SELECT
        CASE WHEN (v.estudante = 1 OR v.responsavel_pedagogico = 1 OR v.responsavel_financeiro = 1 OR v.pai_ou_mae = 1)
              AND v.curso_234 = 1
             THEN 1 ELSE 0 END AS opcoes_padrao,
        CASE WHEN (v.pai_ou_mae = 1 OR v.responsavel_pedagogico = 1 OR v.responsavel_financeiro = 1)
              AND (v.ei_5 = 1 OR v.ef_1_9 = 1 OR v.em_1_3 = 1)
             THEN 1 ELSE 0 END AS transporte
) b
-- JSONs: cod_alunos = [1,2]; cod_cursos = {"2":[1,2],"3":[6]}.
CROSS APPLY (
    SELECT
        (SELECT '[' + STRING_AGG(CONVERT(varchar(20), d.cod_aluno), ',') WITHIN GROUP (ORDER BY d.cod_aluno) + ']'
         FROM (SELECT DISTINCT vi.cod_aluno
               FROM vinculos vi
               WHERE vi.cod_pessoa = p.cod_pessoa) d) AS cod_alunos,
        (SELECT '{' + STRING_AGG('"' + CONVERT(varchar(20), g.cod_curso) + '":[' + g.etapas + ']', ',')
                          WITHIN GROUP (ORDER BY g.cod_curso) + '}'
         FROM (SELECT d.cod_curso,
                      STRING_AGG(CONVERT(varchar(20), d.cod_etapa), ',') WITHIN GROUP (ORDER BY d.cod_etapa) AS etapas
               FROM (SELECT DISTINCT vi.cod_curso, vi.cod_etapa
                     FROM vinculos vi
                     WHERE vi.cod_pessoa = p.cod_pessoa) d
               GROUP BY d.cod_curso) g) AS cod_cursos
) j
WHERE NULLIF(LTRIM(RTRIM(p.cpf)), '') IS NOT NULL
  -- Só quem tem algum vínculo, como na consulta original.
  AND (v.qtd_vinculos > 0 OR f.funcionario = 1 OR exa.cpf IS NOT NULL);
