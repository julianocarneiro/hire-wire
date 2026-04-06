# Especificação — camada de domínio (Domain)

Documento de referência para modelar o **núcleo de negócio** do sistema de contas bancárias descrito no [README.md](../README.md), em conformidade com [php-laravel.patern.md](paterns/php-laravel.patern.md).

---

## 1. Objetivo

- Expressar **regras de negócio puras** (cadastro de usuário, contas, depósito, correção monetária) sem dependência de HTTP, Eloquent ou Laravel.
- Permitir **testes unitários** de entidades, value objects e políticas sem banco.
- Definir **portas** (interfaces de persistência) que a infraestrutura implementará.

---

## 2. Contextos delimitados (bounded contexts)

### 2.1 `Users` (identidade e cadastro)

**Responsabilidade:** garantir que um **usuário** possa ser concebido no domínio com dados válidos e invariantes de unicidade acordadas pelo produto.

| Artefato | Descrição |
|----------|-----------|
| **Entidade** `User` | Identidade estável (`id`); atributos: nome, CPF, e-mail, credencial de autenticação (referência opaca ao segredo, não o hash em si na entidade se preferir isolar — ou armazenar hash apenas na persistência via VO/adapters). |
| **Value objects** | `Cpf`, `Email` (validação e normalização onde couber); opcionalmente `PersonName` se houver regras além de “não vazio”. |
| **Exceções** | `DuplicateCpfException`, `DuplicateEmailException`, `InvalidCpfException`, etc. |
| **Porta** | `UserRepositoryInterface`: `findByCpf`, `findByEmail`, `save`, `existsWithCpf`, `existsWithEmail`, … conforme necessidade dos casos de uso. |

**Nota:** tokens OAuth2, clients Passport e sessões **não** pertencem ao domínio; ficam em infraestrutura e HTTP.

### 2.2 `Banking` (contas e movimentação de saldo)

**Responsabilidade:** **contas bancárias** por usuário, com **tipos** distintos e regras de **depósito** e **correção monetária mensal** conforme o [README.md](../README.md).

| Artefato | Descrição |
|----------|-----------|
| **Entidade** `BankAccount` (agregado) | Identidade estável (`id`); dono (`UserId` ou referência); **tipo** de conta; **saldo** como value object monetário; métodos que preservam invariantes: `deposit(Money $amount)`, `applyMonthlyAdjustment()`, etc. |
| **Value objects** | `Money` (valor em centavos ou decimal encapsulado + operações seguras); `AccountType` (enum ou VO fechado: poupança, corrente, investimentos); `UserId` / `BankAccountId` como identificadores tipados (opcional, recomendado). |
| **Política / strategy** | Regras que variam por tipo: **incremento no depósito** (+R$ 0,50 para corrente e investimentos; nenhum para poupança); **percentual de correção mensal** (0,001% poupança; 0,1% corrente e investimentos). Implementar como: `AccountDepositPolicy` + `MonthlyAdjustmentPolicy` por tipo, ou **strategy** injetada na entidade/factory conforme `AccountType`. |
| **Domain services** | Somente se uma operação não couber numa única entidade; preferir manter lógica no agregado + políticas. |
| **Exceções** | `BankAccountNotFoundException`, `UnauthorizedAccountAccessException`, `InvalidDepositAmountException`, … |
| **Porta** | `BankAccountRepositoryInterface`: `findByIdForUser`, `findByIdForUpdate`, `save`, listagens necessárias, etc. |

**Invariantes de negócio (resumo):**

- Depósito em **Poupança:** crédito integral do valor informado ao saldo.
- Depósito em **Corrente** e **Investimentos:** crédito de `valor + R$ 0,50`.
- Correção mensal: multiplicar saldo por `(1 + taxa)`, com taxas fixas por tipo conforme README.

### 2.3 Identidade autenticada (OAuth2) e modelo de domínio

- O **Domain** mantém a entidade `User` como agregado de **cadastro** e regras de unicidade (CPF, e-mail, etc.). **Não** modelar aqui `OAuthClient`, access/refresh tokens, nem tabelas `oauth_*` ([detalhe no esquema](tables.spac.md)).
- Depois da validação do **Bearer token** (Passport, guard `api`), a **camada Application / HTTP** resolve o utilizador autenticado — p.ex. um `users.id` estável que o Domain já trata como identidade do `User`. **OAuth2 é o mecanismo de entrada** na API; **não** é o núcleo dos casos de uso de contas ou depósito.
- **Escopos** (ex.: `read:accounts`, `write:accounts` — ver [especificação OAuth2 / Passport](laravel_passport_oauth2.md)) são **autorização na fronteira**: middleware Passport, policies ou equivalente. **Não** incorporar listas de escopos ou invariantes OAuth dentro das entidades `User` ou `BankAccount`.

---

## 3. Namespace e pastas (alvo)

Alinhado ao padrão do repositório:

```text
app/Domain/
  Users/
    Entities/
    ValueObjects/
    Exceptions/
    Repositories/          # UserRepositoryInterface
  Banking/
    Entities/
    ValueObjects/
    Exceptions/
    Repositories/          # BankAccountRepositoryInterface
    Policies/              # ou Strategies/ — políticas de depósito e correção por tipo
```

Novos contextos = novas árvores sob `Domain\{Context}`; não misturar agregados sem relação na mesma classe.

---

## 4. O que não entra no Domain

| Proibido no Domain | Onde tratar |
|--------------------|-------------|
| `Request`, `JsonResponse`, rotas | `app/Http` |
| Eloquent `Model`, `DB::`, Query Builder | `Infrastructure` (repositórios concretos) |
| `DB::transaction`, jobs, mail | `Application` (casos de uso) |
| Clients HTTP, filas, Passport | `Infrastructure` (ver [laravel_passport_oauth2.md](laravel_passport_oauth2.md)) |

---

## 5. Relação com Application e Infrastructure

- **Application** orquestra: `RegisterUser`, `OpenBankAccount` (se aplicável), `DepositFunds`, `GetAccountBalance`, `ApplyMonthlyAdjustment` (para uma conta ou lote, conforme produto), sempre com transação quando houver múltiplas escritas.
- **Infrastructure** implementa repositórios: mapeamento **entidade ↔ modelo Eloquent** (`toDomain` / `toModel` no repositório concreto).
- **Controllers** apenas validam entrada (Form Requests), autorizam (Policies) e chamam um caso de uso por ação.

Fluxos OAuth2, escopos, clientes e critérios de aceitação da API protegida estão especificados em [laravel_passport_oauth2.md](laravel_passport_oauth2.md); evitar duplicar essa lista neste documento.

---

## 6. Testes

Para cada nova regra ou política no domínio, preferir **testes unitários** (PHPUnit) sem banco, conforme seção 10 de [php-laravel.patern.md](paterns/php-laravel.patern.md).

---

## 7. Checklist de revisão (domínio)

- [ ] Entidades expõem comportamento por métodos; saldo/tipos não são alterados diretamente por fora sem passar pelas regras?
- [ ] Políticas de depósito e correção estão centralizadas e cobertas por tipo de conta?
- [ ] Interfaces de repositório estão no `Domain`; sem imports de Laravel/Eloquent?
- [ ] Exceções de domínio são específicas e sem referência a HTTP?
- [ ] Casos de uso que exigem utilizador assumem identidade já autenticada na fronteira; **sem** lógica OAuth ou de tokens dentro de entidades de domínio?

---

*Documento vivo: ajustar nomes de contexto (`Banking` vs `Accounts`) conforme o time padronizar no código.*
