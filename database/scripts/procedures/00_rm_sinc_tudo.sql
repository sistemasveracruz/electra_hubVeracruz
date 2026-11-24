-- Sincronização completa do HUB com o TOTVS RM (RM_IMPORTVC + CORPORERM_HML).
-- Chama as procedures rm_sinc_* na ordem de dependência do índice de carga_rm_completa.sql,
-- numa única transação: se qualquer etapa falhar, nada é gravado.
-- Agendada pelo Laravel (rm:sincronizar-procedures). Para rodar à mão: EXEC dbo.rm_sinc_tudo;
-- doc_prefixos [1.11] fica fora: é uma lista fixa, carregada só pelo script de carga.
CREATE OR ALTER PROCEDURE dbo.rm_sinc_tudo
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    BEGIN TRY
        BEGIN TRAN;

        -- [1] Sem dependência
        EXEC dbo.rm_sinc_edu_status;                        -- [1.1]
        EXEC dbo.rm_sinc_edu_tipo_salas;                    -- [1.2]
        EXEC dbo.rm_sinc_edu_turnos;                        -- [1.3]
        EXEC dbo.rm_sinc_inst_pessoas;                      -- [1.4]
        EXEC dbo.rm_sinc_aux_parentesco;                    -- [1.5]
        EXEC dbo.rm_sinc_inst_escolas;                      -- [1.6]
        EXEC dbo.rm_sinc_edu_disciplinas;                   -- [1.7]
        EXEC dbo.rm_sinc_fun_situacao;                      -- [1.8]
        EXEC dbo.rm_sinc_fun_funcoes;                       -- [1.9]
        EXEC dbo.rm_sinc_inst_secoes;                       -- [1.10]

        -- [2] Base institucional e pessoas
        EXEC dbo.rm_sinc_inst_pessoa_enderecos;             -- [2.1]
        EXEC dbo.rm_sinc_inst_unidades_escolares;           -- [2.2]
        EXEC dbo.rm_sinc_edu_tipo_cursos;                   -- [2.3]
        EXEC dbo.rm_sinc_edu_registros_academicos;          -- [2.4]
        EXEC dbo.rm_sinc_edu_familias;                      -- [2.5]
        EXEC dbo.rm_sinc_edu_responsaveis_pedagogico;       -- [2.6]
        EXEC dbo.rm_sinc_edu_responsaveis_financeiros;      -- [2.7]

        -- [3] Estrutura acadêmica
        EXEC dbo.rm_sinc_edu_salas;                         -- [3.1]
        EXEC dbo.rm_sinc_edu_cursos;                        -- [3.2]
        EXEC dbo.rm_sinc_edu_periodos_letivos;              -- [3.3]
        EXEC dbo.rm_sinc_edu_etapas;                        -- [3.4]
        EXEC dbo.rm_sinc_edu_grades_curriculares;           -- [3.5]
        EXEC dbo.rm_sinc_edu_grade_curricular_disciplinas;  -- [3.6]
        EXEC dbo.rm_sinc_edu_aplicacoes_curriculares;       -- [3.7]

        -- [4] Funcionários
        EXEC dbo.rm_sinc_fun_funcionarios;                  -- [4.1]
        EXEC dbo.rm_sinc_fun_professores;                   -- [4.2]

        -- [5] Turmas
        EXEC dbo.rm_sinc_edu_turmas;                        -- [5.1]
        EXEC dbo.rm_sinc_edu_turma_disciplinas;             -- [5.2]
        EXEC dbo.rm_sinc_fun_professor_disciplinas;         -- [5.3]

        -- [6] Vida escolar
        EXEC dbo.rm_sinc_edu_matriculas;                    -- [6.1]
        EXEC dbo.rm_sinc_edu_alunos;                        -- [6.2]
        EXEC dbo.rm_sinc_edu_responsaveis;                  -- [6.3]
        EXEC dbo.rm_sinc_edu_enturmas;                      -- [6.4]

        COMMIT;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0 ROLLBACK;
        THROW;
    END CATCH
END;
