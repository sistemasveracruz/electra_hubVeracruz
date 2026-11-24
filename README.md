# HUB Veracruz

Aplicação Laravel que centraliza os dados acadêmicos e institucionais da escola, carregados a partir do TOTVS RM.

## Stack

- PHP 8.2+ e Laravel 12
- SQL Server

## Estrutura do banco

As tabelas são agrupadas por domínio, indicado pelo prefixo do nome:

| Prefixo | Domínio | Exemplos |
|---------|---------|----------|
| `inst_` | Institucional | escolas, unidades escolares, pessoas, endereços, seções |
| `edu_` | Educacional    | cursos, etapas, grades, turmas, matrículas, alunos |
| `fun_` | Funcionários   | funcionários, funções, situações, professores |
| `aux_` | Auxiliares     | parentesco, chaves do RM |
| `doc_` | Documentação   | prefixos de domínio |

As chaves primárias usam o padrão `cod_<entidade>`. Sempre que a tabela tem origem no RM, a chave é o próprio código do RM.

### Diagrama

- [database/schemas/hub_veracruz.dbml](database/schemas/hub_veracruz.dbml): código-fonte do diagrama no formato do [dbdiagram.io](https://dbdiagram.io). Para visualizar, cole o conteúdo num diagrama novo do site.
- [database/schemas/18112026_hub_veracruz.pdf](database/schemas/18112026_hub_veracruz.pdf): o diagrama exportado em PDF, de dd/mm/AAAA.

Quem cria as tabelas são as migrations em [database/migrations/](database/migrations/). O DBML serve só como documentação: quando uma migration mudar, atualize o DBML e exporte o PDF de novo.

## Integração com o TOTVS RM

Os dados vêm da leitura direta das bases do RM:

| Base | Conteúdo |
|---|---|
| `RM_IMPORTVC` | Educacional: pessoas, cursos, turmas, matrículas, professores |
| `CORPORERM_HML` | Folha: funcionários (`PFUNC`), funções, seções, situações |

As pessoas que estão em bases diferentes são ligadas pelo CPF, porque o `CODPESSOA` de uma base não corresponde ao da outra.

### Carga inicial por script SQL

O script [database/scripts/carga_rm_completa.sql](database/scripts/carga_rm_completa.sql) preenche o banco inteiro na ordem das dependências. Para usá-lo:

1. Rode as migrations no banco de destino (`php artisan migrate:fresh`). Esse comando **apaga todos os dados** do banco.
2. Abra o script no SSMS e confira, no bloco `[0]`, qual banco o `USE` seleciona.
3. Execute o script. Cada INSERT tem um `NOT EXISTS`, então o script pode rodar de novo sem duplicar linhas.

O índice no topo do script lista os blocos numerados (`[1.1]`, `[5.1]`...). Para ir até um bloco, busque o código dele com Ctrl+F.

### Sincronização diária por procedures

As procedures em [database/scripts/procedures/](database/scripts/procedures/) mantêm o HUB atualizado com o RM. Há uma procedure por bloco do script de carga, com o mesmo número no nome do arquivo (ex.: `5.1_rm_sinc_edu_turmas.sql`). Cada uma:

1. **atualiza** as linhas que mudaram no RM e reativa as que voltaram a aparecer;
2. **insere** as linhas novas;
3. faz **soft delete** (`ativo = 0` e `deleted_at`) das linhas que sumiram do RM. As tabelas sem `deleted_at`, como `edu_grade_curricular_disciplinas`, `edu_aplicacoes_curriculares` e `edu_responsaveis`, recebem um `DELETE` de verdade. `edu_turnos` não tem remoção.

As colunas preenchidas só no HUB, como `edu_status.acessa_vera_digital`, não são alteradas.

A procedure `dbo.rm_sinc_tudo` ([00_rm_sinc_tudo.sql](database/scripts/procedures/00_rm_sinc_tudo.sql)) chama todas na ordem do índice, numa única transação: se uma etapa falhar, nada é gravado. Num banco vazio ela faz a carga completa, exceto `doc_prefixos`, que continua no script de carga.

**Criação:** a migration `2026_11_19_000000_create_rm_sinc_procedures` cria as procedures no `php artisan migrate`. Depois de alterar um `.sql`, rode o arquivo no SSMS, porque a migration não roda de novo sozinha.

**Execução:**

```bash
php artisan rm:sincronizar-procedures            # sincroniza
php artisan rm:sincronizar-procedures --dry-run  # executa e desfaz no final
```

No SSMS, o equivalente é `EXEC dbo.rm_sinc_tudo;`.

**Agendamento:** [routes/console.php](routes/console.php) agenda o comando todo dia às 02:00, horário de Brasília. Para o agendamento funcionar, o servidor precisa desta linha no cron:

```
* * * * * cd /caminho/do/projeto && php artisan schedule:run >> /dev/null 2>&1
```

**Permissão:** o usuário do banco configurado no `.env` (`DB_USERNAME`) precisa de leitura (SELECT) nas bases `RM_IMPORTVC` e `CORPORERM_HML`.

## View de login (`vw_login_pessoas`)

A view [database/scripts/views/vw_login_pessoas.sql](database/scripts/views/vw_login_pessoas.sql) traz uma linha por CPF com os dados que o login do appapi usa:

- **identificação:** `cpf`, `nome`, `cod_pessoa`;
- **alunos e cursos:** `cod_alunos` (`[1,2]`) e `cod_cursos` (`{"2":[1,2],"3":[6]}`), dos alunos enturmados no ano;
- **vínculos:** `estudante`, `ingressante`, `funcionario`, `pai_ou_mae`, `responsavel_financeiro`, `responsavel_pedagogico`, `ex_aluno`, `parcelas_em_aberto`;
- **`habilita_login`:** vale 1 para aluno, funcionário, pai/mãe, responsável financeiro ou pedagógico, ou ex-aluno com parcelas em aberto;
- **permissões:** as colunas `habilita_*` (menu, calendário, rematrícula, transporte...).

```sql
SELECT * FROM dbo.vw_login_pessoas WHERE cpf = '12345678901';
```

A view foi feita para ser consultada por CPF. A partir do CPF, cada etapa usa um índice, e esses índices são criados pela migration `2026_11_20_000000_create_vw_login_pessoas`.

A view lê duas bases que precisam estar no mesmo servidor do HUB: `ExAlunos` (ex-alunos) e `Senior_VeraCruz` (títulos em aberto). Sem elas, a criação da view falha.

Depois de alterar o `.sql`, rode o arquivo no SSMS.

## View de alunos (`vw_alunos`)

A view [database/scripts/views/vw_alunos.sql](database/scripts/views/vw_alunos.sql) traz uma linha por aluno (RA), com a matrícula mais recente, a turma, a situação e os dados de pai, mãe e responsáveis, incluindo o parentesco.

```sql
SELECT * FROM dbo.vw_alunos WHERE ano = YEAR(GETDATE());
```

Quando o aluno não tem pai, mãe ou algum responsável no RM, ele continua na view e as colunas dessa pessoa ficam `NULL`. A view é criada pela migration `2026_11_23_000000_create_vw_alunos`.
