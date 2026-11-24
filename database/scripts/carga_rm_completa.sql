
-- ===================================================================================== --
--                                       ÍNDICE
--        Para ir até um bloco, busque o código com Ctrl+F (ex.: [5.1])
-- ===================================================================================== --
--
--  [0]    CONFIGURAÇÃO (USE / SET XACT_ABORT / BEGIN TRAN)
--  
--  [1]    SEM DEPENDÊNCIA
--  [1.1]    edu_status ............................... status educacional
--  [1.2]    edu_tipo_salas ........................... tipos de sala
--  [1.3]    edu_turnos ............................... turnos
--  [1.4]    inst_pessoas ............................. pessoas
--  [1.5]    aux_parentesco ........................... parentescos
--  [1.6]    inst_escolas ............................. escolas (coligada)
--  [1.7]    edu_disciplinas .......................... disciplinas
--  [1.8]    fun_situacao ............................. situações de funcionário
--  [1.9]    fun_funcoes .............................. funções de funcionário
--  [1.10]   inst_secoes .............................. seções
--  [1.11]   doc_prefixos ............................. prefixos do banco
--
--  [2]    BASE INSTITUCIONAL E PESSOAS
--  [2.1]    inst_pessoa_enderecos .................... endereços
--  [2.2]    inst_unidades_escolares .................. unidades (filiais)
--  [2.3]    edu_tipo_cursos .......................... tipos de curso
--  [2.4]    edu_registros_academicos ................. RAs
--  [2.5]    edu_familias ............................. famílias (filiação)
--  [2.6]    edu_responsaveis_pedagogico .............. responsáveis pedagógicos
--  [2.7]    edu_responsaveis_financeiros ............. responsáveis financeiros
--
--  [3]    ESTRUTURA ACADÊMICA
--  [3.1]    edu_salas ................................ salas
--  [3.2]    edu_cursos ............................... cursos
--  [3.3]    edu_periodos_letivos ..................... períodos letivos
--  [3.4]    edu_etapas ............................... etapas (habilitações)
--  [3.5]    edu_grades_curriculares .................. grades curriculares
--  [3.6]    edu_grade_curricular_disciplinas ......... disciplinas da grade
--
--  [4]    FUNCIONÁRIOS
--  [4.1]    fun_funcionarios ......................... funcionários (PFUNC)
--  [4.2]    fun_professores .......................... professores
--
--  [5]    TURMAS
--  [5.1]    edu_turmas ............................... turmas (sala + coordenador/orientador/responsável)
--  [5.2]    edu_turma_disciplinas .................... disciplinas da turma
--  [5.3]    fun_professor_disciplinas ................ professores por turma/disciplina
--
--  [6]    VIDA ESCOLAR
--  [6.1]    edu_matriculas ........................... matrículas
--  [6.2]    edu_alunos ............................... alunos
--  [6.3]    edu_responsaveis ......................... vínculo aluno x responsáveis
--  [6.4]    edu_enturmas ............................. enturmações
--
--  [7]    ATUALIZAÇÕES (recargas)
--  [7.1]    UPDATE edu_alunos ........................ matrícula atual
--  [7.2]    UPDATE edu_turmas ........................ sala
--  [7.3]    UPDATE edu_turmas ........................ etapa
--  [7.4]    UPDATE edu_matriculas .................... etapa (segue a turma)
--
--  [8]    COMMIT
--  [9]    CONFERÊNCIA (SELECTs)
--
-- ===================================================================================== --


-- ===================================================================================== --
-- [0]                             CONFIIGURAÇÕES
-- ===================================================================================== --

-- SELECIONE O BANCO A SER PREENCHIDO

use DSV_HUB_VERACRUZ;
-- use HMLG_HUB_VERACRUZ;

-- ===================================================================================== --



BEGIN TRAN;



-- ===================================================================================== --
-- [1.1]                         INSERE STATUS EDUCACIONAL
-- ===================================================================================== --

-- DEPENDENCIAS: NENHUMA

-- ===================================================================================== --

INSERT INTO edu_status (cod_status, nome, ativo, created_at, updated_at)
SELECT s.CODSTATUS, LEFT(MAX(s.DESCRICAO), 120), 1, GETDATE(), GETDATE()
FROM RM_IMPORTVC.dbo.SSTATUS s
WHERE s.CODCOLIGADA = 1 AND s.DESCRICAO IS NOT NULL AND NOT EXISTS (SELECT 1 FROM edu_status x WHERE x.cod_status = s.CODSTATUS) GROUP BY s.CODSTATUS;



-- ===================================================================================== --
-- [1.2]                              INSERE TIPO SALA 
-- ===================================================================================== --

-- DEPENDENCIAS: NENHUMA

-- ===================================================================================== --

insert into edu_tipo_salas (cod_tipo_sala, nome, permite_reserva, ativo, created_at, updated_at)
select CODTIPOSALA, NOME, CASE WHEN PERMITERESERVA = 'S' THEN 1 ELSE 0 END, 1,  GETDATE(),  GETDATE() from RM_IMPORTVC.dbo.STIPOSALA;




-- ===================================================================================== --
-- [1.3]                              INSERE TURNOS
-- ===================================================================================== --

-- DEPENDENCIAS: NENHUMA

-- ===================================================================================== --

INSERT INTO edu_turnos (cod_turno, nome) 
SELECT t.CODTURNO, LEFT(t.NOME, 80) FROM RM_IMPORTVC.dbo.STURNO t
WHERE t.CODCOLIGADA = 1 AND NOT EXISTS (SELECT 1 FROM edu_turnos x WHERE x.cod_turno = t.CODTURNO);



-- ===================================================================================== --
-- [1.4]                                INSERE PESSOAS
-- ===================================================================================== --

-- DEPENDENCIAS: NENHUMA

-- ===================================================================================== --
WITH origem AS (
    SELECT p.CODIGO, p.NOME, p.DTNASCIMENTO, p.EMAIL, p.EMAILPESSOAL, p.TELEFONE1, p.TELEFONE2, p.TELEFONE3,p.FALECIDO,
           NULLIF(REPLACE(REPLACE(REPLACE(p.CPF, '.', ''), '-', ''), ' ', ''), '') AS cpf
    FROM RM_IMPORTVC.dbo.PPESSOA p
    WHERE p.NOME IS NOT NULL
)
INSERT INTO inst_pessoas (cod_pessoa, cpf, nome, data_nascimento, email, email_pessoal,
                          telefone_fixo, telefone_pessoal, telefone_empresa, falecido, ativo, created_at, updated_at)
SELECT o.CODIGO,
       -- CPF só quando válido e ainda não usado: o RM pode ter o mesmo CPF em duas pessoas
       CASE WHEN LEN(o.cpf) = 11
             AND ROW_NUMBER() OVER (PARTITION BY o.cpf ORDER BY o.CODIGO) = 1
             AND NOT EXISTS (SELECT 1 FROM inst_pessoas x WHERE x.cpf = o.cpf COLLATE DATABASE_DEFAULT)
            THEN o.cpf END,
       LEFT(o.NOME, 150), o.DTNASCIMENTO,
       LEFT(o.EMAIL, 250), LEFT(o.EMAILPESSOAL, 250),
       LEFT(o.TELEFONE1, 20), LEFT(o.TELEFONE2, 20), LEFT(o.TELEFONE3, 20),
	   o.FALECIDO,
       1, GETDATE(), GETDATE()
FROM origem o
WHERE NOT EXISTS (SELECT 1 FROM inst_pessoas x WHERE x.cod_pessoa = o.CODIGO);




-- ===================================================================================== --
-- [1.5]                    INSERE TABELA AUXILIAR DE PARENTESCO
-- ===================================================================================== --

-- DEPENDENCIAS: NENHUMA

-- ===================================================================================== --

INSERT INTO aux_parentesco (descricao, ativo, created_at, updated_at)
SELECT DISTINCT LTRIM(RTRIM(pc.DESCRICAO)), 1, GETDATE(), GETDATE()
FROM RM_IMPORTVC.dbo.PCODPARENT pc
WHERE pc.DESCRICAO IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM aux_parentesco x
                  WHERE x.descricao = LTRIM(RTRIM(pc.DESCRICAO)) COLLATE DATABASE_DEFAULT);




-- ===================================================================================== --
-- [1.6]                             INSERE ESCOLAS
-- ===================================================================================== --

-- DEPENDENCIAS: NENHUMA

-- ===================================================================================== --

INSERT INTO inst_escolas (cod_escola, nome, nome_fantasia, cnpj, ativo, created_at, updated_at)
SELECT c.CODCOLIGADA, LEFT(c.NOME, 150), LEFT(c.NOMEFANTASIA, 150),
       REPLACE(REPLACE(REPLACE(c.CGC, '.', ''), '/', ''), '-', ''),
       1, GETDATE(), GETDATE()
FROM RM_IMPORTVC.dbo.GCOLIGADA c
WHERE c.CODCOLIGADA = 1
  AND c.NOME IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM inst_escolas x WHERE x.cod_escola = c.CODCOLIGADA);


  
-- ===================================================================================== --
-- [1.7]                             INSERE ESCOLAS
-- ===================================================================================== --

-- DEPENDENCIAS: NENHUMA

-- ===================================================================================== --

INSERT INTO edu_disciplinas ( nome, sigla, ativo, created_at, updated_at)
SELECT LEFT(d.NOME, 120),
       LEFT(d.CODDISC, 30),
       1, GETDATE(), GETDATE()
FROM RM_IMPORTVC.dbo.SDISCIPLINA d
WHERE d.CODCOLIGADA = 1
  AND d.NOME IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM edu_disciplinas x
                  WHERE x.sigla = d.CODDISC COLLATE DATABASE_DEFAULT);



-- ===================================================================================== --
-- [1.8]                     INSERE SITUAÇÕES DE FUNCIONARIOS
-- ===================================================================================== --

-- DEPENDENCIAS: NENHUMA

-- ===================================================================================== --

INSERT INTO fun_situacao (cod_fun_situacao, descricao, ativo, created_at, updated_at) 
select CODCLIENTE, DESCRICAO, 1, GETDATE(), GETDATE()  from CORPORERM_HML.dbo.PCODSITUACAO;



-- ===================================================================================== --
-- [1.9]                      INSERE FUNÇÕES DE FUNCIONARIOS
-- ===================================================================================== --

-- DEPENDENCIAS: NENHUMA

-- ===================================================================================== --

INSERT INTO fun_funcoes (cod_funcao, nome, ativo, created_at, updated_at)
select CODIGO, NOME, CASE WHEN INATIVA = 0 THEN 1 ELSE 0 END, GETDATE(), GETDATE()  from CORPORERM_HML.dbo.PFUNCAO;



-- ===================================================================================== --
-- [1.10]                   INSERE SEÇÕES INSTITUCIONAIS DE ENSINO
-- ===================================================================================== --

-- DEPENDENCIAS: NENHUMA

-- ===================================================================================== --

INSERT INTO inst_secoes (cod_secao, descricao, cod_secao_pai, ativo, created_at, updated_at)
select CODIGO, DESCRICAO, CODIGOPAI, 1, GETDATE(), GETDATE() from CORPORERM_HML.dbo.PSECAO;



-- ===================================================================================== --
--  [1.11]                     INSERE PREFIXOS DE DOMÍNIO
-- ===================================================================================== --
-- DEPENDÊNCIAS: NENHUMA
-- ===================================================================================== --

INSERT INTO doc_prefixos (cod_prefixo, sigla, descricao)
SELECT v.cod_prefixo, v.sigla, v.descricao
FROM (VALUES
        (1, 'INST', 'Institucional'),
        (2, 'EDU',  'Educacional'),
        (3, 'FUN',  'Funcionários'),
        (4, 'AUX',  'Auxiliares'),
        (5, 'DOC',  'Documentação')
     ) AS v (cod_prefixo, sigla, descricao)
WHERE NOT EXISTS (SELECT 1 FROM doc_prefixos x
                  WHERE x.cod_prefixo = v.cod_prefixo OR x.sigla = v.sigla);



-- ===================================================================================== --
-- [2.1]                         INSERE PESSOAS ENDERECOS
-- ===================================================================================== --

-- DEPENDENCIAS:
--      • TABELA INST_PESSOAS

-- ===================================================================================== --

insert into inst_pessoa_enderecos (cod_pessoa, tipo_endereco, cep, logradouro, numero, complemento, bairro, cidade, uf, ativo)
SELECT cod_pessoa, 'PESSOAL', CEP, RUA ,NUMERO,COMPLEMENTO, BAIRRO, CIDADE, ESTADO, 1 
FROM RM_IMPORTVC.dbo.PPESSOA
JOIN inst_pessoas on inst_pessoas.cod_pessoa = CODIGO
WHERE NOT EXISTS (SELECT 1 FROM inst_pessoa_enderecos e WHERE e.cod_pessoa = inst_pessoas.cod_pessoa);



-- ===================================================================================== --
-- [2.2]                       INSERE UNIDADES ESCOLARES
-- ===================================================================================== --

-- DEPENDENCIAS: 
--      • TABELA INST_ESCOLAS

-- ===================================================================================== --

INSERT INTO inst_unidades_escolares (cod_unidade_escolar, cod_escola, nome, sigla, ativo, created_at, updated_at)
SELECT g.CODFILIAL, es.cod_escola, LEFT(ISNULL(g.NOMEFANTASIA, g.NOME), 150), LEFT(g.NOMEFANTASIA, 20), 1, GETDATE(), GETDATE()
FROM RM_IMPORTVC.dbo.GFILIAL g
JOIN inst_escolas es ON es.cod_escola = g.CODCOLIGADA
WHERE g.CODCOLIGADA = 1 AND NOT EXISTS (SELECT 1 FROM inst_unidades_escolares x WHERE x.cod_unidade_escolar = g.CODFILIAL);




-- ===================================================================================== --
-- [2.3]                        INSERE TIPO DE CURSOS
-- ===================================================================================== --

-- DEPENDENCIAS: 
--      • TABELA INST_ESCOLAS

-- ===================================================================================== --

INSERT INTO edu_tipo_cursos (cod_tipo_curso, cod_escola, nome, ativo, created_at, updated_at)
SELECT t.CODTIPOCURSO, es.cod_escola, LEFT(t.NOME, 150), 1, GETDATE(), GETDATE()
FROM RM_IMPORTVC.dbo.STIPOCURSO t
JOIN inst_escolas es ON es.cod_escola = t.CODCOLIGADA
WHERE t.CODCOLIGADA = 1 AND NOT EXISTS (SELECT 1 FROM edu_tipo_cursos x WHERE x.cod_tipo_curso = t.CODTIPOCURSO);



-- ===================================================================================== --
-- [2.4]                         INSERE REGISTROS ACADEMICOS
-- ===================================================================================== --

-- DEPENDENCIAS: 
--      • TABELA INST_PESSOAS
--      • TABELA INST_ESCOLAS

-- ===================================================================================== --

INSERT INTO edu_registros_academicos (cod_pessoa, cod_escola, registro_academico, ativo, created_at, updated_at)
SELECT pe.cod_pessoa, es.cod_escola, LTRIM(RTRIM(a.RA)), 1, GETDATE(), GETDATE()
FROM RM_IMPORTVC.dbo.SALUNO a
	JOIN inst_pessoas pe ON pe.cod_pessoa = a.CODPESSOA
	JOIN inst_escolas es ON es.cod_escola = a.CODCOLIGADA
WHERE a.CODCOLIGADA = 1
	AND LEN(LTRIM(RTRIM(a.RA))) <= 12
	AND NOT EXISTS (SELECT 1 FROM edu_registros_academicos x WHERE x.cod_escola = es.cod_escola AND x.registro_academico = LTRIM(RTRIM(a.RA)) COLLATE DATABASE_DEFAULT);


	
-- ===================================================================================== --
-- [2.5] INSERE FAMÍLIAS (casal pai + mãe e seus filhos, a partir da filiação do RM)
-- ===================================================================================== --

-- DEPENDENCIAS: 
--      • TABELA AUX_PARENTESCO
--      • TABELA INST_PESSOAS

-- ===================================================================================== --

  INSERT INTO edu_familias (cod_familia, cod_pessoa, cod_pessoa_aluno, cod_parentesco, parentesco, ativo, created_at, updated_at) 
SELECT CODFILIACAO, CODPESSOAFILIACAO, CODPESSOAFILHO, cod_parentesco, descricao,  1, GETDATE(), GETDATE() FROM RM_IMPORTVC.dbo.VFILIACAO 
JOIN aux_parentesco on (CASE WHEN TIPORELACIONAMENTO = 'M' THEN 'MÃE' ELSE 'PAI' END) = UPPER(aux_parentesco.descricao);



-- =========================================================================== --
-- [2.6]                 INSERE RESPONSAVEIS PEDAGOGICOS
-- =========================================================================== --

-- DEPENDENCIAS: 
--      • TABELA AUX_PARENTESCO
--      • TABELA INST_PESSOAS

-- =========================================================================== --

INSERT INTO edu_responsaveis_pedagogico (cod_pessoa_aluno, cod_pessoa_responsavel, cod_parentesco_responsavel,
                                         ativo, created_at, updated_at)
SELECT pa.cod_pessoa, pr.cod_pessoa, ap.cod_parentesco, 1, GETDATE(), GETDATE()
FROM RM_IMPORTVC.dbo.SALUNO a
JOIN inst_pessoas pa ON pa.cod_pessoa = a.CODPESSOA           -- aluno
JOIN inst_pessoas pr ON pr.cod_pessoa = a.CODPESSOARACA       -- responsável
LEFT JOIN RM_IMPORTVC.dbo.PCODPARENT pc ON pc.CODCLIENTE = a.CODPARENTRACA
LEFT JOIN aux_parentesco ap ON ap.descricao = LTRIM(RTRIM(pc.DESCRICAO)) COLLATE DATABASE_DEFAULT
WHERE a.CODCOLIGADA = 1
  AND NOT EXISTS (SELECT 1 FROM edu_responsaveis_pedagogico x
                  WHERE x.cod_pessoa_aluno = pa.cod_pessoa AND x.cod_pessoa_responsavel = pr.cod_pessoa);



-- =========================================================================== --
-- [2.7]                  INSERE RESPONSVEIS FINANCEIROS
-- =========================================================================== --

-- DEPENDENCIAS: 
--      • TABELA AUX_PARENTESCO
--      • TABELA INST_PESSOAS

-- =========================================================================== --

INSERT INTO edu_responsaveis_financeiros (cod_pessoa_aluno, cod_pessoa_responsavel, cod_parentesco_responsavel,
                                          ativo, created_at, updated_at)
SELECT DISTINCT pa.cod_pessoa, pr.cod_pessoa, ap.cod_parentesco, 1, GETDATE(), GETDATE()
FROM RM_IMPORTVC.dbo.SALUNO a
JOIN inst_pessoas pa ON pa.cod_pessoa = a.CODPESSOA
JOIN RM_IMPORTVC.dbo.FCFO f ON f.CODCOLIGADA = a.CODCOLCFO AND f.CODCFO = a.CODCFO
JOIN inst_pessoas pr
     ON pr.cpf = REPLACE(REPLACE(REPLACE(f.CGCCFO, '.', ''), '-', ''), '/', '') COLLATE DATABASE_DEFAULT
LEFT JOIN RM_IMPORTVC.dbo.PCODPARENT pc ON pc.CODCLIENTE = a.CODPARENTCFO
LEFT JOIN aux_parentesco ap ON ap.descricao = LTRIM(RTRIM(pc.DESCRICAO)) COLLATE DATABASE_DEFAULT
WHERE a.CODCOLIGADA = 1
  AND f.PESSOAFISOUJUR = 'F'
  AND NOT EXISTS (SELECT 1 FROM edu_responsaveis_financeiros x
                  WHERE x.cod_pessoa_aluno = pa.cod_pessoa AND x.cod_pessoa_responsavel = pr.cod_pessoa);


-- ===================================================================================== --
-- [3.1]                            INSERE SALAS
-- ===================================================================================== --

-- DEPENDENCIAS: 
--      • TABELA EDU_TIPO_SALAS
--      • TABELA INST_UNIDADES_ESCOLARES

-- ===================================================================================== --

WITH origem AS (
    SELECT u.cod_unidade_escolar, ts.cod_tipo_sala, LTRIM(RTRIM(s.CODSALA)) AS codsala, s.DESCRICAO, ROW_NUMBER() OVER (PARTITION BY u.cod_unidade_escolar, LTRIM(RTRIM(s.CODSALA)) ORDER BY s.CODSALA) AS rn
FROM RM_IMPORTVC.dbo.SSALA s
    JOIN inst_unidades_escolares u ON u.cod_unidade_escolar = s.CODFILIAL
    JOIN edu_tipo_salas ts ON ts.cod_tipo_sala = TRY_CAST(s.CODTIPOSALA AS bigint)
WHERE s.CODCOLIGADA = 1
      AND NOT EXISTS (SELECT 1 FROM edu_salas x WHERE x.cod_unidade_escolar = u.cod_unidade_escolar AND x.nome = LTRIM(RTRIM(s.CODSALA)) COLLATE DATABASE_DEFAULT))
INSERT INTO edu_salas ( cod_unidade_escolar, cod_tipo_sala, nome, descricao, ativo, created_at, updated_at)
SELECT o.cod_unidade_escolar, o.cod_tipo_sala, LEFT(o.codsala, 120), LEFT(o.DESCRICAO, 250), 1, GETDATE(), GETDATE()
FROM origem o WHERE o.rn = 1;




-- ===================================================================================== --
-- [3.2]                            INSERE CURSOS
-- ===================================================================================== --

-- DEPENDENCIAS: 
--      • TABELA INST_ESCOLAS
--      • TABELA EDU_TIPO_CURSOS

-- ===================================================================================== --

INSERT INTO edu_cursos (cod_curso, cod_escola, nome, sigla, ativo, created_at, updated_at, cod_tipo_curso)
SELECT TRY_CAST(s.CODCURSO AS bigint), es.cod_escola, LEFT(s.NOME, 150), LEFT(s.CODCURSO, 12),
       1, GETDATE(), GETDATE(), etc.cod_tipo_curso
FROM RM_IMPORTVC.dbo.SCURSO s
JOIN inst_escolas es ON es.cod_escola = s.CODCOLIGADA
JOIN edu_tipo_cursos etc ON etc.cod_tipo_curso = s.CODTIPOCURSO
WHERE s.CODCOLIGADA = 1
  AND TRY_CAST(s.CODCURSO AS bigint) IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM edu_cursos x WHERE x.cod_curso = TRY_CAST(s.CODCURSO AS bigint));


  
-- ===================================================================================== --
-- [3.3]                        INSERE PERÍODOS LETIVOS
-- ===================================================================================== --

-- DEPENDENCIAS: 
--      • TABELA INST_ESCOLAS
--      • TABELA INST_UNIDADES_ESCOLARES
--      • TABELA EDU_TIPO_CURSOS

-- ===================================================================================== --

INSERT INTO edu_periodos_letivos (cod_periodo_letivo, cod_unidade_escolar, cod_tipo_curso, cod_periodo_letivo_anterior,
                                  data_inicio, data_fim, data_estimada, ativo, created_at, updated_at)
SELECT p.IDPERLET, u.cod_unidade_escolar, tc.cod_tipo_curso, p.IDPERLETANT,
       p.DTINICIO, p.DTFIM, p.DTPREVISTA,
       CASE WHEN p.ENCERRADO = 'S' THEN 0 ELSE 1 END,
       GETDATE(), GETDATE()
FROM RM_IMPORTVC.dbo.SPLETIVO p
JOIN inst_unidades_escolares u ON u.cod_unidade_escolar = p.CODFILIAL
JOIN edu_tipo_cursos tc ON tc.cod_tipo_curso = p.CODTIPOCURSO
WHERE p.CODCOLIGADA = 1
  AND NOT EXISTS (SELECT 1 FROM edu_periodos_letivos x WHERE x.cod_periodo_letivo = p.IDPERLET);



-- ===================================================================================== --
-- [3.4]                           INSERE ETAPAS
-- ===================================================================================== --

-- DEPENDENCIAS: 
--      • TABELA EDU_CURSOS

-- ===================================================================================== --

WITH origem AS (
    SELECT TRY_CAST(h.CODHABILITACAO AS bigint) AS cod_etapa, c.cod_curso, h.NOME, ROW_NUMBER() OVER (PARTITION BY c.cod_curso, TRY_CAST(h.CODHABILITACAO AS bigint) ORDER BY h.CODCOLIGADA) AS rn
    FROM RM_IMPORTVC.dbo.SHABILITACAO h
    JOIN edu_cursos c ON c.cod_curso = TRY_CAST(h.CODCURSO AS bigint)
    WHERE TRY_CAST(h.CODHABILITACAO AS bigint) IS NOT NULL
)
INSERT INTO edu_etapas (cod_etapa, cod_curso, nome, ativo, created_at, updated_at)
SELECT o.cod_etapa, o.cod_curso, LEFT(o.NOME, 150), 1, GETDATE(), GETDATE()
FROM origem o WHERE o.rn = 1
  AND NOT EXISTS (SELECT 1 FROM edu_etapas x WHERE x.cod_curso = o.cod_curso AND x.cod_etapa = o.cod_etapa);



-- ===================================================================================== --
-- [3.5]                        INSERE GRADE CURRICULAR
-- ===================================================================================== --

-- DEPENDENCIAS: 
--      • TABELA EDU_ETAPAS

-- ===================================================================================== --

INSERT INTO edu_grades_curriculares (cod_grade, cod_curso, cod_etapa, descricao, ativo, created_at, updated_at)
SELECT LEFT(g.CODGRADE, 20), et.cod_curso, et.cod_etapa,
       LEFT(ISNULL(g.DESCRICAO, g.CODGRADE), 255), 1, GETDATE(), GETDATE()
FROM RM_IMPORTVC.dbo.SGRADE g
JOIN edu_etapas et
     ON et.cod_curso = TRY_CAST(g.CODCURSO AS bigint)
    AND et.cod_etapa = TRY_CAST(g.CODHABILITACAO AS bigint)
WHERE g.CODCOLIGADA = 1
  AND NOT EXISTS (SELECT 1 FROM edu_grades_curriculares x
                  WHERE x.cod_curso = et.cod_curso
                    AND x.cod_etapa = et.cod_etapa
                    AND x.cod_grade = LEFT(g.CODGRADE, 20) COLLATE DATABASE_DEFAULT);



-- ===================================================================================== --
-- [3.6]                 INSERE GRADE CURRICULAR DISCIPLINAS
-- ===================================================================================== --

-- DEPENDENCIAS: 
--      • TABELA EDU_GRADES
--      • TABELA EDU_DISCIPLINAS

-- ===================================================================================== --
WITH origem AS (
    SELECT gc.cod_grade_curricular, gc.cod_curso, gc.cod_etapa, d.cod_disciplina, sdg.CH,
           ROW_NUMBER() OVER (PARTITION BY gc.cod_grade_curricular, d.cod_disciplina
                              ORDER BY sdg.CODCOLIGADA) AS rn
    FROM RM_IMPORTVC.dbo.SDISCGRADE sdg
    JOIN edu_grades_curriculares gc
         ON gc.cod_curso = TRY_CAST(sdg.CODCURSO AS bigint)
        AND gc.cod_etapa = TRY_CAST(sdg.CODHABILITACAO AS bigint)
        AND gc.cod_grade = LTRIM(RTRIM(sdg.CODGRADE)) COLLATE DATABASE_DEFAULT
    JOIN edu_disciplinas d
         ON d.sigla = sdg.CODDISC COLLATE DATABASE_DEFAULT
    WHERE sdg.CODCOLIGADA = 1
)
INSERT INTO edu_grade_curricular_disciplinas
    (cod_grade_curricular, cod_curso, cod_etapa, cod_disciplina, periodo_grade, aulas_semanais, carga_horaria, obrigatoria)
SELECT o.cod_grade_curricular, o.cod_curso, o.cod_etapa, o.cod_disciplina, NULL, NULL, o.CH, 1
FROM origem o
WHERE o.rn = 1
  AND NOT EXISTS (SELECT 1 FROM edu_grade_curricular_disciplinas x
                  WHERE x.cod_grade_curricular = o.cod_grade_curricular
                    AND x.cod_disciplina = o.cod_disciplina);



-- ===================================================================================== --
-- [3.7]                 INSERE APLICACOES CURRICULARES
-- ===================================================================================== --

-- DEPENDENCIAS: 
--      • TABELA INST_UNIDADES_ESCOLARES
--      • TABELA EDU_GRADES
--      • TABELA EDU_TURNOS

-- ===================================================================================== --
					
insert into edu_aplicacoes_curriculares (cod_aplicacao_curricular, cod_grade_curricular, cod_unidade_escolar, cod_etapa, cod_turno, regime_grade, periodo_grade, data_inicio, data_fim)
SELECT hf.IDHABILITACAOFILIAL,
       gc.cod_grade_curricular,
       u.cod_unidade_escolar,
       gc.cod_etapa,
       tu.cod_turno,
       NULL as regime_grade,
       NULL as periodo_grade,
       gc.data_inicio,            
       gc.data_fim             
FROM RM_IMPORTVC.dbo.SHABILITACAOFILIAL hf
JOIN edu_grades_curriculares gc
     ON gc.cod_curso = TRY_CAST(hf.CODCURSO AS bigint)
    AND gc.cod_etapa = TRY_CAST(hf.CODHABILITACAO AS bigint)
    AND gc.cod_grade = LTRIM(RTRIM(hf.CODGRADE)) COLLATE DATABASE_DEFAULT
LEFT JOIN inst_unidades_escolares u ON u.cod_unidade_escolar = hf.CODFILIAL
LEFT JOIN edu_turnos tu ON tu.cod_turno = hf.CODTURNO
WHERE hf.CODCOLIGADA = 1
  AND NOT EXISTS (SELECT 1 FROM edu_aplicacoes_curriculares x
                  WHERE x.cod_aplicacao_curricular = hf.IDHABILITACAOFILIAL);



-- ===================================================================================== --
-- [4.1]                          INSERE FUNCIONÁRIOS
-- ===================================================================================== --

-- DEPENDENCIAS: 
--      • TABELA INST_PESSOAS
--      • TABELA INST_SECOES
--      • TABELA FUN_FUNCOES
--      • TABELA FUN_SITUACAO

-- ===================================================================================== --

INSERT INTO fun_funcionarios (chapa, cod_pessoa, tipo_cadastro, cod_secao, cod_funcao, cod_fun_situacao,
                              data_admissao, data_desligamento, ativo, created_at, updated_at)
SELECT pf.CHAPA, p2.cod_pessoa, 'PESSOA', s.cod_secao, fu.cod_funcao, st.cod_fun_situacao,
       pf.DATAADMISSAO, pf.DATADEMISSAO,
       CASE WHEN pf.CODSITUACAO IN ('A', 'E', 'F', 'G', 'W', 'Y') THEN 1 ELSE 0 END,
       GETDATE(), GETDATE()
FROM  CORPORERM_HML.dbo.PFUNC pf
join CORPORERM_HML.dbo.PPESSOA p on p.CODIGO = pf.CODPESSOA
join RM_IMPORTVC.dbo.PPESSOA p1 on p1.CPF = p.CPF 
join inst_pessoas p2 ON p2.cod_pessoa = p1.CODIGO AND p2.deleted_at IS NULL
JOIN inst_secoes s ON s.cod_secao = pf.CODSECAO COLLATE DATABASE_DEFAULT
JOIN fun_funcoes fu ON fu.cod_funcao = pf.CODFUNCAO COLLATE DATABASE_DEFAULT
JOIN fun_situacao st ON st.cod_fun_situacao = pf.CODSITUACAO COLLATE DATABASE_DEFAULT
WHERE NOT EXISTS (SELECT 1 FROM fun_funcionarios x WHERE x.chapa = pf.CHAPA COLLATE DATABASE_DEFAULT) and p2.cpf is not null;



-- ===================================================================================== --
-- [4.2]                             INSERE PROFESSORES
-- ===================================================================================== --

-- DEPENDENCIAS: 
--      • TABELA INST_PESSOAS
--      • TABELA FUN_FUNCIONARIOS (FK PELA CHAPA)

-- ===================================================================================== --

insert into fun_professores ( cod_professor, cod_pessoa, chapa, ativo, created_at, updated_at )
select CODPROF, PPESSOA.CODIGO, PFUNC.CHAPA, CASE WHEN CODSITUACAO = 'A' THEN 1 ELSE 0 END, GETDATE(), GETDATE() from RM_IMPORTVC.dbo.SPROFESSOR 
join RM_IMPORTVC.dbo.PPESSOA on PPESSOA.CODIGO = CODPESSOA
join CORPORERM_HML.dbo.PPESSOA P2 on P2.CPF = PPESSOA.CPF
join CORPORERM_HML.dbo.PFUNC on PFUNC.CODPESSOA = P2.CODIGO ORDER BY CODPROF;



-- ===================================================================================== --
-- [5.1]                           INSERE TURMAS
-- ===================================================================================== --

-- DEPENDENCIAS: 
--      • TABELA INST_PESSOAS
--      • TABELA INST_UNIDADES_ESCOLARES
--			• TABELA INST_ESCOLAS
--      • TABELA EDU_PERIODOS_LETIVOS
--			• TABELA INST_ESCOLAS
--			• TABELA INST_UNIDADES_ESCOLARES
--			• TABELA EDU_TIPO_CURSOS
--      • TABELA EDU_CURSOS
--			• TABELA INST_ESCOLAS
--			• TABELA EDU_TIPO_CURSOS
--      • TABELA EDU_ETAPAS
--			• TABELA INST_ESCOLAS
--			• TABELA EDU_TIPO_CURSOS
--			• TABELA EDU_CURSOS
--      • TABELA EDU_TURNOS
--      • TABELA EDU_SALAS
--			• TABELA EDU_TIPO_SALAS
--			• TABELA INST_UNIDADES_ESCOLARES

-- ===================================================================================== --

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
    JOIN inst_pessoas p
         ON p.cod_pessoa = sp.CODPESSOA AND p.deleted_at IS NULL
    WHERE td.CODCOLIGADA = 1
      AND pt.TIPOPROF IN ('D', 'R', 'E')
    GROUP BY td.IDPERLET, td.CODTURMA
)
INSERT INTO edu_turmas (codigo_turma, cod_unidade_escolar, cod_periodo_letivo, cod_curso, cod_etapa, cod_sala, cod_turno, cod_pessoa_coordenador, cod_pessoa_orientador, cod_pessoa_professor_responsavel, nome, nome_reduzido, ativo, created_at, updated_at)
SELECT LEFT(t.CODTURMA, 20),
       u.cod_unidade_escolar,
       pl.cod_periodo_letivo,
       c.cod_curso,
       et.cod_etapa,
       es.cod_sala,
       tu.cod_turno,
       pa.coordenador,              -- TIPOPROF D
       pa.orientador,               -- TIPOPROF R
       pa.professor_responsavel,    -- TIPOPROF E (Diretor de Turma)
       LEFT(ISNULL(t.NOME, t.CODTURMA), 120),
       LEFT(ISNULL(t.NOMERED, t.CODTURMA), 12),
       CASE WHEN t.TURMAENCERRADA = 'S' THEN 0 ELSE 1 END,
       GETDATE(), GETDATE()
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
LEFT JOIN papeis pa
     ON pa.IDPERLET = t.IDPERLET
    AND pa.CODTURMA = t.CODTURMA
WHERE t.CODCOLIGADA = 1
  AND NOT EXISTS (SELECT 1 FROM edu_turmas x
                  WHERE x.cod_periodo_letivo = t.IDPERLET
                    AND x.codigo_turma = t.CODTURMA COLLATE DATABASE_DEFAULT);



-- ===================================================================================== --
-- [5.2]                      INSERE DISCIPLINAS DAS TURMAS
-- ===================================================================================== --
WITH origem AS (
    SELECT td.IDTURMADISC, t.cod_turma, d.cod_disciplina,
           ROW_NUMBER() OVER (PARTITION BY t.cod_turma, d.cod_disciplina ORDER BY td.IDTURMADISC) AS rn
    FROM RM_IMPORTVC.dbo.STURMADISC td
    JOIN edu_turmas t
         ON t.cod_periodo_letivo = td.IDPERLET
        AND t.codigo_turma = td.CODTURMA COLLATE DATABASE_DEFAULT
    JOIN edu_disciplinas d ON d.sigla = td.CODDISC COLLATE DATABASE_DEFAULT
    WHERE td.CODCOLIGADA = 1
      AND NOT EXISTS (SELECT 1 FROM edu_turma_disciplinas x
                      WHERE x.cod_turma = t.cod_turma AND x.cod_disciplina = d.cod_disciplina)
)
INSERT INTO edu_turma_disciplinas (cod_turma_disciplina, cod_turma, cod_disciplina, ativo, created_at, updated_at)
SELECT o.IDTURMADISC, o.cod_turma, o.cod_disciplina, 1, GETDATE(), GETDATE()
FROM origem o
WHERE o.rn = 1 AND NOT EXISTS (SELECT 1 FROM edu_turma_disciplinas x WHERE x.cod_turma_disciplina = o.IDTURMADISC);


  
-- ===================================================================================== --
-- [5.3]                    INSERE DISCIPLINAS DOS PROFESSORES
-- ===================================================================================== --

insert into fun_professor_disciplinas (cod_disciplina, cod_professor, cod_turma, ativo, created_at, updated_at, tipo_professor)
SELECT edu_disciplinas.cod_disciplina, fun_professores.codigo as cod_professor, edu_turmas.cod_turma, CASE WHEN STURMADISC.ATIVA = 'S' THEN 1 ELSE 0 END as ativo, GETDATE() as created_at, GETDATE() as updated_at,  UPPER(CASE SPROFESSORTURMA.TIPOPROF
        WHEN 'J' THEN 'Adjunto'
        WHEN 'I' THEN 'Assistente'
        WHEN 'X' THEN 'Auxiliar'
        WHEN 'D' THEN 'Coordenador'
        WHEN 'C' THEN 'Colaborador'
        WHEN 'P' THEN 'Conteudista'
        WHEN 'E' THEN 'Diretor de Turma'
        WHEN 'Q' THEN 'Mediador pedagógico'
        WHEN 'M' THEN 'Monitor'
        WHEN 'N' THEN 'Não docente'
        WHEN 'R' THEN 'Orientador'
        WHEN 'O' THEN 'Outros'
        WHEN 'S' THEN 'Substituto'
        WHEN 'T' THEN 'Titular'
        WHEN 'U' THEN 'Tutor'
        ELSE 'Não informado (' + ISNULL(SPROFESSORTURMA.TIPOPROF, 'vazio') + ')'
    END) AS TIPO_PROFESSOR FROM RM_IMPORTVC.dbo.STURMA
	JOIN RM_IMPORTVC.dbo.STURMADISC ON STURMADISC.IDPERLET = STURMA.IDPERLET AND STURMADISC.CODTURMA = STURMA.CODTURMA 
	JOIN RM_IMPORTVC.dbo.SDISCIPLINA ON SDISCIPLINA.CODDISC = STURMADISC.CODDISC 
	JOIN RM_IMPORTVC.dbo.SPROFESSORTURMA ON SPROFESSORTURMA.IDTURMADISC = STURMADISC.IDTURMADISC 
	JOIN RM_IMPORTVC.dbo.SPROFESSOR ON SPROFESSOR.CODPROF = SPROFESSORTURMA.CODPROF 
	JOIN RM_IMPORTVC.dbo.PPESSOA ON PPESSOA.CODIGO = SPROFESSOR.CODPESSOA
	JOIN edu_disciplinas ON SDISCIPLINA.CODDISC COLLATE DATABASE_DEFAULT = edu_disciplinas.sigla COLLATE DATABASE_DEFAULT
	JOIN fun_professores ON fun_professores.cod_professor = SPROFESSOR.CODPROF
	JOIN edu_turmas ON STURMA.CODTURMA COLLATE DATABASE_DEFAULT = edu_turmas.codigo_turma COLLATE DATABASE_DEFAULT;



-- ===================================================================================== --
-- [6.1]                          INSERE MATRICULAS
-- ===================================================================================== --

-- DEPENDENCIAS: 
--      • TABELA EDU_STATUS
--      • TABELA EDU_TURMAS
--      • TABELA EDU_REGISTROS_ACADEMICOS

-- ===================================================================================== --

WITH origem AS (
    SELECT ra.cod_pessoa, ra.cod_escola, ra.cod_registro_academico,
           t.cod_unidade_escolar, t.cod_curso, t.cod_etapa, t.cod_turma,
           st.cod_status, m.IDPERLET, m.RA,
           ROW_NUMBER() OVER (PARTITION BY ra.cod_registro_academico, t.cod_turma
                              ORDER BY m.IDHABILITACAOFILIAL) AS rn
    FROM RM_IMPORTVC.dbo.SMATRICPL m
    JOIN edu_registros_academicos ra
         ON ra.cod_escola = m.CODCOLIGADA
        AND ra.registro_academico = LTRIM(RTRIM(m.RA)) COLLATE DATABASE_DEFAULT
    JOIN edu_turmas t
         ON t.cod_periodo_letivo = m.IDPERLET
        AND t.codigo_turma = m.CODTURMA COLLATE DATABASE_DEFAULT
    JOIN edu_status st ON st.cod_status = m.CODSTATUS
    WHERE m.CODCOLIGADA = 1
      AND NOT EXISTS (SELECT 1 FROM edu_matriculas x
                      WHERE x.cod_registro_academico = ra.cod_registro_academico
                        AND x.cod_turma = t.cod_turma)
)
INSERT INTO edu_matriculas ( cod_pessoa, cod_escola, cod_unidade_escolar, cod_curso, cod_etapa,
                            cod_turma, cod_status, cod_registro_academico, ativo, created_at, updated_at)
SELECT o.cod_pessoa, o.cod_escola, o.cod_unidade_escolar, o.cod_curso, o.cod_etapa,
       o.cod_turma, o.cod_status, o.cod_registro_academico,
       1, GETDATE(), GETDATE()
FROM origem o
WHERE o.rn = 1;




-- ===================================================================================== --
-- [6.2]                          INSERE ALUNOS
-- ===================================================================================== --

-- DEPENDENCIAS: 
--      • TABELA INST_PESSOAS
--      • TABELA EDU_MATRICULAS

-- ===================================================================================== --

WITH ultima AS (
    SELECT m.cod_registro_academico, m.cod_pessoa, m.cod_matricula,
           ROW_NUMBER() OVER (PARTITION BY m.cod_registro_academico
                              ORDER BY pl.data_inicio DESC, m.cod_matricula DESC) AS rn
    FROM edu_matriculas m
    JOIN edu_turmas t ON t.cod_turma = m.cod_turma
    JOIN edu_periodos_letivos pl ON pl.cod_periodo_letivo = t.cod_periodo_letivo
)
INSERT INTO edu_alunos (cod_pessoa, cod_registro_academico, cod_matricula,cod_pessoa_pai, cod_pessoa_mae, cod_pessoa_responsavel_pedagogico, cod_pessoa_responsavel_financeiro, ativo, created_at, updated_at)
SELECT 
	u.cod_pessoa,
	u.cod_registro_academico,
	u.cod_matricula,
	PAI.CODPESSOAFILIACAO as cod_pessoa_pai,
	MAE.CODPESSOAFILIACAO as cod_pessoa_mae,
	SALUNO.CODPESSOARACA as cod_pessoa_responsavel_pedagogico,
	pr.cod_pessoa as cod_pessoa_responsavel_financeiro,
	1 as ativo,
	GETDATE() as created_at,
	GETDATE() as updated_at
FROM ultima u
join RM_IMPORTVC.dbo.VFILIACAO PAI on PAI.CODPESSOAFILHO = u.cod_pessoa and PAI.TIPORELACIONAMENTO = 'P'
join RM_IMPORTVC.dbo.VFILIACAO MAE on MAE.CODPESSOAFILHO = u.cod_pessoa and MAE.TIPORELACIONAMENTO = 'M'
join RM_IMPORTVC.dbo.PPESSOA       on MAE.CODPESSOAFILHO = PPESSOA.CODIGO
join RM_IMPORTVC.dbo.SALUNO        on SALUNO.CODPESSOA = u.cod_pessoa
join RM_IMPORTVC.dbo.FCFO          on FCFO.CODCOLIGADA = SALUNO.CODCOLCFO AND FCFO.CODCFO = SALUNO.CODCFO
JOIN inst_pessoas               pr on pr.cpf = REPLACE(REPLACE(REPLACE(FCFO.CGCCFO, '.', ''), '-', ''), '/', '') COLLATE DATABASE_DEFAULT
WHERE u.rn = 1
AND NOT EXISTS (SELECT 1 FROM edu_alunos a WHERE a.cod_registro_academico = u.cod_registro_academico);



-- =========================================================================== --
-- [6.3]                          INSERE RESPONSAVEIS
-- =========================================================================== --

-- DEPENDENCIAS: 
--      • TABELA INST_PESSOAS
--      • TABELA EDU_MATRICULAS

-- =========================================================================== --
WITH origem AS (
    SELECT al.cod_aluno, al.cod_pessoa AS cod_pessoa_aluno,
           rp.cod_responsavel_pedagogico, rf.cod_responsavel_financeiro,
           ROW_NUMBER() OVER (PARTITION BY al.cod_aluno
                              ORDER BY rp.cod_responsavel_pedagogico, rf.cod_responsavel_financeiro) AS rn
    FROM RM_IMPORTVC.dbo.SALUNO a
    -- aluno no HUB, achado pelo RA
    JOIN edu_registros_academicos ra
         ON ra.cod_escola = a.CODCOLIGADA
        AND ra.registro_academico = LTRIM(RTRIM(a.RA)) COLLATE DATABASE_DEFAULT
    JOIN edu_alunos al ON al.cod_registro_academico = ra.cod_registro_academico
                      AND al.deleted_at IS NULL
    -- responsável pedagógico atual (SALUNO.CODPESSOARACA)
    JOIN edu_responsaveis_pedagogico rp
         ON rp.cod_pessoa_aluno = al.cod_pessoa
        AND rp.cod_pessoa_responsavel = a.CODPESSOARACA
        AND rp.deleted_at IS NULL
    -- responsável financeiro atual (SALUNO.CODCFO -> FCFO -> pessoa pelo CPF)
    JOIN RM_IMPORTVC.dbo.FCFO f ON f.CODCOLIGADA = a.CODCOLCFO AND f.CODCFO = a.CODCFO
    JOIN inst_pessoas pf
         ON pf.cpf = REPLACE(REPLACE(REPLACE(f.CGCCFO, '.', ''), '-', ''), '/', '') COLLATE DATABASE_DEFAULT
    JOIN edu_responsaveis_financeiros rf
         ON rf.cod_pessoa_aluno = al.cod_pessoa
        AND rf.cod_pessoa_responsavel = pf.cod_pessoa
        AND rf.deleted_at IS NULL
    WHERE a.CODCOLIGADA = 1
      AND f.PESSOAFISOUJUR = 'F'
)
INSERT INTO edu_responsaveis (cod_aluno, cod_pessoa_aluno, cod_responsavel_pedagogico, cod_responsavel_financeiro,
                              created_at, updated_at)
SELECT o.cod_aluno, o.cod_pessoa_aluno, o.cod_responsavel_pedagogico, o.cod_responsavel_financeiro,
       GETDATE(), GETDATE()
FROM origem o
WHERE o.rn = 1
  AND NOT EXISTS (SELECT 1 FROM edu_responsaveis x
                  WHERE x.cod_aluno = o.cod_aluno
                    AND x.cod_responsavel_pedagogico = o.cod_responsavel_pedagogico
                    AND x.cod_responsavel_financeiro = o.cod_responsavel_financeiro);



					
-- =========================================================================== --
-- [6.4]                        INSERE ENTURMAS
-- =========================================================================== --

-- DEPENDENCIAS: 
--      • TABELA EDU_ALUNOS
--      • TABELA EDU_MATRICULAS
--      • TABELA EDU_TURMAS

-- =========================================================================== --

INSERT INTO edu_enturmas ( cod_aluno, cod_matricula, cod_turma, cod_status, numero_chamada,
                          ativo, created_at, updated_at)
SELECT a.cod_aluno, m.cod_matricula, m.cod_turma, m.cod_status,
       CAST(n.NUMALUNO AS varchar(120)),
       1, GETDATE(), GETDATE()
FROM edu_matriculas m
JOIN edu_alunos a ON a.cod_registro_academico = m.cod_registro_academico
JOIN edu_registros_academicos ra ON ra.cod_registro_academico = m.cod_registro_academico
JOIN edu_turmas t ON t.cod_turma = m.cod_turma
OUTER APPLY (SELECT TOP 1 sm.NUMALUNO
             FROM RM_IMPORTVC.dbo.SMATRICPL sm
             WHERE sm.CODCOLIGADA = 1
               AND sm.IDPERLET = t.cod_periodo_letivo
               AND LTRIM(RTRIM(sm.RA)) COLLATE DATABASE_DEFAULT = ra.registro_academico
               AND sm.CODTURMA COLLATE DATABASE_DEFAULT = t.codigo_turma) n
WHERE NOT EXISTS (SELECT 1 FROM edu_enturmas e WHERE e.cod_matricula = m.cod_matricula);



-- ==================================================================================== --
-- [7.1] [DEPRECATED]       ATUALIZA A MATRÍCULA ATUAL DOS ALUNOS JÁ EXISTENTES
-- ===================================================================================== --


-- WITH ultima AS (
--     SELECT m.cod_registro_academico, m.cod_matricula,
--            ROW_NUMBER() OVER (PARTITION BY m.cod_registro_academico
--                               ORDER BY pl.data_inicio DESC, m.cod_matricula DESC) AS rn
--     FROM edu_matriculas m
--     JOIN edu_turmas t ON t.cod_turma = m.cod_turma	
--     JOIN edu_periodos_letivos pl ON pl.cod_periodo_letivo = t.cod_periodo_letivo
-- )
-- UPDATE a
-- SET a.cod_matricula = u.cod_matricula, a.updated_at = GETDATE()
-- FROM edu_alunos a
-- JOIN ultima u ON u.cod_registro_academico = a.cod_registro_academico AND u.rn = 1
-- WHERE a.cod_matricula <> u.cod_matricula;



-- ===================================================================================== --
-- [7.2] [DEPRECATED] LIGA AS TURMAS ÀS SALAS (STURMA.CODSALA)
-- ===================================================================================== --

-- UPDATE t
-- SET t.cod_sala = s.cod_sala, t.updated_at = GETDATE()
-- FROM edu_turmas t
-- JOIN RM_IMPORTVC.dbo.STURMA st
--      ON st.CODCOLIGADA = 1
--     AND st.IDPERLET = t.cod_periodo_letivo
--     AND st.CODTURMA COLLATE DATABASE_DEFAULT = t.codigo_turma
-- JOIN edu_salas s
--      ON s.cod_unidade_escolar = t.cod_unidade_escolar
--     AND s.nome = LTRIM(RTRIM(st.CODSALA)) COLLATE DATABASE_DEFAULT
-- WHERE t.cod_sala IS NULL;
-- 




-- ===================================================================================== --
-- [7.3] [DEPRECATED]      CORRIGE A ETAPA DAS TURMAS JÁ CARREGADAS
-- ===================================================================================== --

-- UPDATE t
-- SET t.cod_etapa = et.cod_etapa, t.updated_at = GETDATE()
-- FROM edu_turmas t
-- JOIN RM_IMPORTVC.dbo.STURMA st
--      ON st.CODCOLIGADA = 1
--     AND st.IDPERLET = t.cod_periodo_letivo
--     AND st.CODTURMA COLLATE DATABASE_DEFAULT = t.codigo_turma
-- JOIN RM_IMPORTVC.dbo.SHABILITACAOFILIAL hf
--      ON hf.CODCOLIGADA = st.CODCOLIGADA AND hf.IDHABILITACAOFILIAL = st.IDHABILITACAOFILIAL
-- JOIN edu_etapas et
--      ON et.cod_curso = t.cod_curso
--     AND et.cod_etapa = TRY_CAST(hf.CODHABILITACAO AS bigint)
-- WHERE t.cod_etapa <> et.cod_etapa;



-- ===================================================================================== --
-- [7.4] [DEPRECATED]     CORRIGE A ETAPA DAS MATRÍCULAS (segue a turma)
-- ===================================================================================== --

-- UPDATE m
-- SET m.cod_etapa = t.cod_etapa, m.updated_at = GETDATE()
-- FROM edu_matriculas m
-- JOIN edu_turmas t ON t.cod_turma = m.cod_turma
-- WHERE m.cod_etapa <> t.cod_etapa;



-- ==================================================================================== --
-- [7.5]                         ATUALIZA ACESSOS AO APP 
-- ===================================================================================== --

UPDATE es
	SET es.acessa_vera_digital = 1, es.updated_at = GETDATE()
	FROM edu_status es
	WHERE cod_status in (2,3) ;



-- ===================================================================================== --
-- [8] COMMIT
-- ===================================================================================== --
COMMIT;
 




-- ===================================================================================== --
-- [9] CONFERÊNCIA
-- ===================================================================================== --

-- select * from inst_pessoa_enderecos; -- CHECK
-- select * from aux_parentesco; -- CHECK
-- select * from aux_rm_chaves; -- CHECK
-- select * from doc_prefixos; -- CHECK
-- select * from edu_alunos; -- CHECK
-- select * from edu_cursos;  -- CHECK
-- select * from edu_disciplinas;  -- CHECK
-- select * from edu_familias; -- CHECK
-- select * from edu_enturmas; -- CHECK
-- select * from edu_grades_curriculares; -- CHECK
-- select * from edu_matriculas; -- CHECK
-- select * from edu_periodos_letivos; -- CHECK
-- select * from edu_matriculas; -- CHECK
-- select * from edu_periodos_letivos; -- CHECK
-- select * from edu_registros_academicos; -- CHECK
-- select * from edu_responsaveis_financeiros;  -- CHECK
-- select * from edu_responsaveis_pedagogico; -- CHECK
-- select * from edu_salas; -- CHECK
-- select * from edu_status; -- CHECK
-- select * from edu_tipo_cursos; -- CHECK
-- select * from edu_tipo_salas; -- CHECK
-- select * from edu_turmas; -- CHECK
-- select * from edu_turma_disciplinas; -- CHECK
-- select * from edu_turnos; -- CHECK
-- select * from fun_funcionarios; -- CHECK
-- select * from fun_funcoes; -- CHECK
-- select * from fun_professor_disciplinas; -- CHECK
-- select * from fun_professores; -- CHECK
-- select * from fun_situacao; -- CHECK
-- select * from inst_escolas;  -- CHECK
-- select * from inst_secoes;-- CHECK
-- select * from inst_pessoas; -- CHECK
-- select * from inst_unidades_escolares; -- CHECK
-- select * from edu_responsaveis; -- CHECK
-- select * from edu_grades_curriculares; -- CHECK
-- select * from edu_grade_curricular_disciplinas; -- CHECK


-- select * from edu_turma_disciplinas; -- VERIFY
-- select * from edu_aplicacoes_curriculares; -- VERIFY 

