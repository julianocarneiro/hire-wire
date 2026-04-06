# Especificação: movimentações da conta (Fase 8)

Documento de referência para **geração e revisão de código** da funcionalidade de movimentações, depósitos e correção monetária. Alinha-se a [tasks.md](tasks.md) (Fase 8), ao [README.md](../README.md) (regras de negócio) e à [theme.spec.md](theme.spec.md) (tema claro/escuro).

---

## 1. Objetivo

- Disponibilizar a rota **`/bank-accounts/{id}/movimentacoes`** com interface de **três abas**: Depósito, Movimentação + saldo, Correção monetária.
- **Persistir** depósitos e correções monetárias na tabela `account_movements`, mantendo o saldo da conta coerente com as regras já definidas no domínio.
- Garantir **isolamento por utilizador** (Fase 5): consultas e comandos filtrados por `user_id` / posse da conta; testes de negação entre utilizadores.
- Garantir **acessibilidade** das abas (ARIA, teclado) e **coerência visual** com os tokens de tema (`bg-page`, `bg-surface`, `text-text`, etc.).

---

## 2. Alinhamento ao README (regras de negócio)

As regras **não** devem ser reimplementadas no controller ou na vista. Devem reutilizar-se:

| Conceito | Onde está no código |
|----------|---------------------|
| Crédito de depósito (bónus R$0,50 em corrente e investimentos) | `App\Domain\Banking\Policies\AccountDepositPolicy` |
| Correção monetária mensal (percentuais por tipo de conta) | `App\Domain\Banking\Policies\MonthlyAdjustmentPolicy` |
| Operações sobre o saldo | `App\Domain\Banking\Entities\BankAccount::deposit()` e `::applyMonthlyAdjustment()` |

Referência textual das regras: secção **«Regras de Negócio»** do [README.md](../README.md).

---

## 3. Modelo de dados (`account_movements`)

A estrutura prevista na migration `create_account_movements_table` (campos principais):

| Campo | Uso |
|-------|-----|
| `bank_account_id` | FK para `bank_accounts` (cascade on delete) |
| `type` | Discriminador do movimento (ver secção 4) |
| `amount` | Valor principal do movimento para exibição e auditoria (ver semântica abaixo) |
| `balance_after` | Saldo da conta **após** a operação (obrigatório após conclusão bem-sucedida) |
| `metadata` | JSON opcional (ex.: valor declarado no depósito antes do bónus) |
| `created_at` | Momento do registo |

### 3.1 Semântica recomendada de `amount`

- **Depósito:** valor **creditado** ao saldo (já inclui o bónus de R$0,50 quando aplicável pela política).
- **Correção monetária:** **delta** positivo creditado (juros = saldo novo − saldo anterior), para leitura clara na listagem.

### 3.2 Valores de `type`

Definir constantes ou enumeração única no domínio/infra, por exemplo:

- `deposit` — depósito registado pelo utilizador.
- `monthly_adjustment` — aplicação da correção monetária (simulação da regra mensal).

Documentar qualquer alteração destes identificadores neste ficheiro.

---

## 4. Domínio, infraestrutura e transações

### 4.1 Repositório de movimentos

- Contrato (ex.: `AccountMovementRepositoryInterface`) com operações mínimas:
  - **Registar** um movimento após operação bem-sucedida na conta.
  - **Listar** movimentos de uma conta **apenas** se a conta pertencer ao utilizador indicado (via join/`whereHas` em `bank_accounts.user_id` ou validação prévia consistente).

### 4.2 Modelo Eloquent

- Modelo `AccountMovement` com relação `belongsTo` `BankAccount`.
- Não expor movimentos de contas de outros utilizadores em nenhum caminho de leitura pública.

### 4.3 Consistência e concorrência

Operações que alterem saldo **e** gravem movimento devem:

1. Executar dentro de **`DB::transaction`**.
2. Carregar a conta com **`findByIdForUpdate`** (bloqueio pessimista) antes de aplicar `deposit` ou `applyMonthlyAdjustment`.
3. Persistir o saldo atualizado via repositório de contas existente.
4. Inserir a linha em `account_movements` com `balance_after` correto.

### 4.4 Serviço de aplicação (opcional mas recomendado)

Orquestrar transação + domínio + append do movimento num serviço dedicado mantém o HTTP fino e favorece testes (espírito OOP descrito no README).

### 4.5 Registo de dependências

Registrar a implementação do repositório de movimentos no service container (ex.: `AppServiceProvider`).

---

## 5. HTTP e Inertia

### 5.1 Rotas (grupo `auth`)

| Método | Caminho sugerido | Nome sugerido | Notas |
|--------|------------------|---------------|--------|
| `GET` | `bank-accounts/{id}/movimentacoes` | `bank-accounts.movements` | Já existente; enriquecer props |
| `POST` | `bank-accounts/{id}/deposito` | `bank-accounts.deposit` | `whereNumber('id')`, throttle alinhado ao CRUD (ex.: `throttle:20,1`) |
| `POST` | `bank-accounts/{id}/correcao-monetaria` | `bank-accounts.monthly-adjustment` | Corpo vazio ou só CSRF; throttle semelhante |

### 5.2 Validação

- **Depósito:** campo `amount` obrigatório, numérico, estritamente **> 0**. Mensagens de validação coerentes com o resto da app (PT quando aplicável).
- **Correção:** sem parâmetros obrigatórios; a lógica é a do domínio.

### 5.3 Respostas

- Sucesso: **`back()`** (redirect Inertia) para recarregar a página e atualizar `bankAccounts` partilhados via `HandleInertiaRequests`.
- Conta inexistente ou de outro utilizador: **404**.
- Exceções de domínio (ex.: depósito não positivo): mapear para **422** ou redirect com erros, conforme padrão já usado no projeto.

### 5.4 Props Inertia na página de movimentações

Além de `account` (`id`, `type`, `balance`):

- **`movements`:** lista ordenada por `created_at` descendente, com campos suficientes para a tabela (ex.: `id`, `type`, `amount`, `balance_after`, `created_at`). Etiquetas legíveis podem ser resolvidas no front a partir de `type`.

---

## 6. Front-end (`Dashboard/BankAccountMovements`)

### 6.1 Três abas

1. **Depósito** — formulário com valor; submissão Inertia (`useForm`); feedback de erro/sucesso; texto breve sobre bónus nas contas corrente e investimentos (README).
2. **Movimentação + saldo** — saldo atual em destaque; listagem cronológica dos movimentos; opcional coluna «saldo após» usando `balance_after`.
3. **Correção monetária** — explicação de que se trata de **simulação** da aplicação mensal; ação com confirmação antes do POST.

### 6.2 Tema

Seguir [theme.spec.md](theme.spec.md): tokens (`bg-page`, `bg-surface`, `border-border`, `text-text`, `text-text-muted`, `primary`, erros `text-red-600 dark:text-red-400`). Evitar paletas soltas para superfície/texto principal.

### 6.3 Navegação

O layout lateral já pode incluir atalho para movimentações; link adicional na página de detalhe da conta é opcional mas melhora a descoberta.

---

## 7. Acessibilidade (abas)

- **Tablist:** `role="tablist"` no contentor das abas.
- **Separadores:** cada aba com `role="tab"`, `aria-selected`, `aria-controls` apontando ao `id` do painel.
- **Painéis:** `role="tabpanel"` com `id` correspondente e `aria-labelledby` (ou associação equivalente conforme práticas WAI-ARIA).
- **Teclado:** navegação entre abas com **setas Esquerda/Direita** (e opcionalmente Home/End) quando o foco está no tablist, alinhado às WAI-ARIA Authoring Practices.
- **Foco visível:** anéis de foco com `focus:ring-primary` / `focus:ring-offset-page` como nas outras páginas.

---

## 8. Autorização e testes

### 8.1 Autorização

Qualquer `POST` ou listagem de movimentos deve verificar posse da conta pelo utilizador autenticado (mesmo padrão que `show`, `update`, `destroy`, `movements`).

### 8.2 Testes de feature (mínimo sugerido)

- Depósito em **poupança:** saldo aumenta exatamente o valor declarado; linha em `account_movements` coerente.
- Depósito em **corrente** ou **investimentos:** saldo aumenta valor + R$0,50; `metadata` com valor declarado se implementado.
- **Correção monetária:** saldo atualizado conforme `MonthlyAdjustmentPolicy`; movimento registado.
- Utilizador **B** não consegue depósito nem correção na conta do utilizador **A** (404).
- Depósito inválido (zero/negativo): erro de validação.
- Opcional: asserções Inertia na GET com contagem/`movements` após operações.

Os **testes unitários** de domínio (`BankAccountTest`, `MonthlyAdjustmentPolicyTest`) continuam a definir as fórmulas; os feature tests validam integração HTTP + base de dados.

---

## 9. Decisão de produto: correção repetida

O README descreve correção **no final de cada mês**. Neste projeto, a correção via UI é uma **simulação manual** («Aplicar correção») que executa **uma instância** da lógica mensal **sem** obrigar idempotência por mês civil no MVP.

Se no futuro for exigida **no máximo uma correção por (conta, mês)**, pode acrescentar-se coluna ou convenção em `metadata` e restrição única — documentar alteração neste ficheiro.

---

## 10. Referências de ficheiros

| Área | Caminhos típicos |
|------|------------------|
| Migration | `src/database/migrations/*_create_account_movements_table.php` |
| Domínio | `src/app/Domain/Banking/Entities/BankAccount.php`, políticas em `Policies/` |
| Repositório de contas | `src/app/Infrastructure/Banking/EloquentBankAccountRepository.php` |
| Rotas web | `src/routes/web.php` |
| Middleware Inertia | `src/app/Http/Middleware/HandleInertiaRequests.php` |
| Vista | `src/resources/js/Pages/Dashboard/BankAccountMovements.vue` |
| Testes de exemplo CRUD / movimentos | `src/tests/Feature/BankAccountCrudTest.php` (ou ficheiro dedicado) |

---

## 11. Checklist de revisão

- [ ] Depósito e correção usam apenas políticas/entidades de domínio existentes para alterar saldo.
- [ ] Transação + `lockForUpdate` na conta antes de mutar saldo.
- [ ] Movimentos gravados com `balance_after` e `type` consistentes.
- [ ] Nenhum vazamento de dados entre utilizadores (403/404 conforme padrão do projeto).
- [ ] Abas com ARIA e teclado; tema com tokens.
- [ ] Testes de feature cobrem casos felizes, validação e isolamento.
