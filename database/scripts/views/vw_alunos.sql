-- Alunos do HUB: uma linha por aluno (RA), com a matrícula mais recente, a turma e a família.
--
-- A matrícula é a de edu_alunos.cod_matricula, que a sincronização (6.2) aponta para o período letivo
-- mais recente do RA. Para filtrar o ano corrente: WHERE ano = YEAR(GETDATE()).
--
-- Pai, mãe e responsáveis usam LEFT JOIN: o aluno aparece mesmo sem algum deles (as colunas ficam NULL).
CREATE OR ALTER VIEW dbo.vw_alunos
AS
SELECT
    -- Aluno
    ea.cod_aluno,
    ra.registro_academico,
    p.cod_pessoa,
    p.cpf,
    p.nome,
    p.data_nascimento,
    p.email,
    p.telefone_pessoal,

    -- Matrícula
    em.cod_matricula,
    YEAR(pl.data_inicio) AS ano,
    pl.cod_periodo_letivo,
    ue.cod_unidade_escolar,
    ue.nome AS unidade_escolar,
    em.cod_curso,
    ec.nome AS curso,
    em.cod_etapa,
    et.nome AS etapa,

    -- Turma
    t.cod_turma,
    t.codigo_turma,
    t.nome AS turma,
    tu.nome AS turno,
    en.numero_chamada,

    -- Situação
    em.cod_status,
    es.nome AS status,
    es.acessa_vera_digital,

    -- Pai
    ea.cod_pessoa_pai,
    pai.nome AS nome_pai,
    pai.cpf AS cpf_pai,

    -- Mãe
    ea.cod_pessoa_mae,
    mae.nome AS nome_mae,
    mae.cpf AS cpf_mae,

    -- Responsável pedagógico
    ea.cod_pessoa_responsavel_pedagogico,
    respp.nome AS nome_responsavel_pedagogico,
    respp.cpf AS cpf_responsavel_pedagogico,
    parp.descricao AS parentesco_responsavel_pedagogico,

    -- Responsável financeiro
    ea.cod_pessoa_responsavel_financeiro,
    respf.nome AS nome_responsavel_financeiro,
    respf.cpf AS cpf_responsavel_financeiro,
    parf.descricao AS parentesco_responsavel_financeiro
FROM edu_alunos ea
JOIN inst_pessoas p ON p.cod_pessoa = ea.cod_pessoa
JOIN edu_registros_academicos ra ON ra.cod_registro_academico = ea.cod_registro_academico
JOIN edu_matriculas em ON em.cod_matricula = ea.cod_matricula
JOIN edu_status es ON es.cod_status = em.cod_status
JOIN edu_cursos ec ON ec.cod_curso = em.cod_curso
JOIN edu_etapas et
     ON et.cod_curso = em.cod_curso
    AND et.cod_etapa = em.cod_etapa
JOIN edu_turmas t ON t.cod_turma = em.cod_turma
JOIN edu_periodos_letivos pl ON pl.cod_periodo_letivo = t.cod_periodo_letivo
JOIN inst_unidades_escolares ue ON ue.cod_unidade_escolar = em.cod_unidade_escolar
LEFT JOIN edu_turnos tu ON tu.cod_turno = t.cod_turno
LEFT JOIN edu_enturmas en
       ON en.cod_matricula = em.cod_matricula
      AND en.deleted_at IS NULL
LEFT JOIN inst_pessoas pai   ON pai.cod_pessoa   = ea.cod_pessoa_pai
LEFT JOIN inst_pessoas mae   ON mae.cod_pessoa   = ea.cod_pessoa_mae
LEFT JOIN inst_pessoas respp ON respp.cod_pessoa = ea.cod_pessoa_responsavel_pedagogico
LEFT JOIN inst_pessoas respf ON respf.cod_pessoa = ea.cod_pessoa_responsavel_financeiro
-- Parentesco do responsável com o aluno.
OUTER APPLY (SELECT TOP 1 ap.descricao
             FROM edu_responsaveis_pedagogico rp
             JOIN aux_parentesco ap ON ap.cod_parentesco = rp.cod_parentesco_responsavel
             WHERE rp.cod_pessoa_aluno = ea.cod_pessoa
               AND rp.cod_pessoa_responsavel = ea.cod_pessoa_responsavel_pedagogico
               AND rp.deleted_at IS NULL) parp
OUTER APPLY (SELECT TOP 1 ap.descricao
             FROM edu_responsaveis_financeiros rf
             JOIN aux_parentesco ap ON ap.cod_parentesco = rf.cod_parentesco_responsavel
             WHERE rf.cod_pessoa_aluno = ea.cod_pessoa
               AND rf.cod_pessoa_responsavel = ea.cod_pessoa_responsavel_financeiro
               AND rf.deleted_at IS NULL) parf
WHERE ea.deleted_at IS NULL
  AND p.deleted_at IS NULL
  AND ra.deleted_at IS NULL
  AND em.deleted_at IS NULL;
