# PHP e Laravel — Regras, padrões e camadas

Especificação de **orientação a objetos**, **design patterns** e **separação de responsabilidades** para projetos PHP/Laravel neste repositório.

---

## 1. Objetivo

- Manter código **testável**, **previsível** e **fácil de evoluir**.
- Evitar lógica de negócio esparsa em Controllers e Models “gordos”.
- Centralizar acesso a dados atrás de **interfaces** (contratos no domínio, implementação na infraestrutura) quando fizer sentido para testes e substituição de implementação.
- Adotar **domínio explícito enxuto** (`App\Domain`, `App\Application`, `App\Infrastructure`): regras de negócio fora de Models Eloquent, com crescimento estruturado por contexto (ex.: `Accounts`) sem obrigar complexidade de projetos grandes (sem Event Sourcing, sem camada intermediária desnecessária).
- **Sempre que possível**, acompanhar mudanças com **testes automatizados em PHPUnit** (via stack padrão do Laravel: `tests/`, `phpunit.xml`).

---

## 2. Orientação a objetos (regras gerais)

### 2.1 Princípios

- **SOLID**: classes pequenas, uma razão para mudar, extensível via interfaces, dependências injetadas (não instanciadas escondidas no meio do método).
- **Encapsulamento**: expor comportamento, não detalhes internos; propriedades preferencialmente `private`/`protected` com acessores apenas quando necessário.
- **Composição sobre herança**: preferir injetar dependências e compor serviços em vez de árvores profundas de herança.
- **Imutabilidade quando possível**: objetos de valor/configuração sem estado mutável evitam efeitos colaterais difíceis de rastrear.

### 2.2 Tipagem e contratos

- Usar **type hints** (parâmetros e retorno) em métodos públicos de domínio e casos de uso.
- **Contratos** (interfaces de repositório, gateways, etc.) residem no **Domain** (ex.: `App\Domain\Accounts\Repositories\AccountRepositoryInterface`) ou, se preferir pasta transversal, `app/Contracts` espelhando o mesmo namespace lógico.
- Implementações concretas ficam na **Infrastructure** (ex.: `App\Infrastructure\Persistence\Eloquent\Repositories\AccountRepository`).
- Evitar `mixed` sem necessidade; documentar exceções na docblock quando a linguagem não expressar o contrato.

### 2.3 Nomenclatura e organização

- **Classes**: substantivos (`AccountRepository`, `DepositFundsHandler`).
- **Métodos**: verbos (`deposit`, `findById`, `applyMonthlyAdjustment`).
- Um arquivo, uma classe (PSR-4).
- Namespaces alinhados à pasta. Exemplos:
  - `App\Domain\Accounts\Entities\BankAccount`
  - `App\Domain\Accounts\ValueObjects\Money`
  - `App\Application\Accounts\DepositFunds`
  - `App\Infrastructure\Persistence\Eloquent\Models\BankAccountModel`

### 2.4 Exceções

- Lançar exceções **específicas** do domínio ou da camada (ex.: `OrderNotFoundException`, `InsufficientFundsException`) quando o fluxo de negócio exige; não usar exceções genéricas para controlar fluxo normal.
- Controllers convertem exceções em **respostas HTTP** adequadas (via handler global ou mapping explícito), sem espalhar `try/catch` repetitivo.

---

## 3. Domínio explícito (DDD enxuto — modelo preferido)

**Objetivo:** separar **regra de negócio pura** da **entrega HTTP** e da **persistência Laravel**, permitindo evoluir por **contexto** (`Accounts`, `Billing`, …) sem “God services” e sem regras em Models Eloquent.

### 3.1 O que cada camada lógica faz

| Área | Namespace / pasta típica | Responsabilidade | Evitar |
|------|-------------------------|------------------|--------|
| **Domain** | `App\Domain\{Context}\...` | Entidades (agregados), **value objects**, invariantes, **domain services** pontuais quando um caso não pertence a uma única entidade, **exceções** de domínio, **interfaces** (portas) que o domínio necessita | `Request`, `JsonResponse`, Eloquent, `DB`, chamadas HTTP |
| **Application** | `App\Application\{Context}\...` | **Um caso de uso por classe** (ou classe pequena com poucos métodos coesos): orquestra domínio + portas, **transações** (`DB::transaction`) quando várias escritas dependem do mesmo resultado, disparo de filas/mail **via abstrações** quando couber | Regras que deveriam estar na entidade/VO; conhecer formato HTTP |
| **Infrastructure** | `App\Infrastructure\...` | **Models Eloquent**, **repositórios concretos** (mapear entidade ↔ model), clientes HTTP, adapters de fila, etc. | Regra de negócio (“se salário > X então…”); vazar `Query Builder` para Application |

**Presentation (HTTP):** Controllers, Form Requests, API Resources — continuam na estrutura habitual do Laravel (`app/Http/...`), apenas **finos**, chamando Application.

### 3.2 Estrutura de pastas sugerida (evolutiva)

Começar **mínimo**; criar subpastas novas quando o contexto crescer.

```text
app/
  Domain/
    Accounts/
      Entities/
      ValueObjects/
      Exceptions/
      Repositories/           # apenas interfaces (portas)
      Events/                 # opcional no início — introduzir quando houver efeitos colaterais reais
  Application/
    Accounts/
      DepositFunds.php        # exemplo: um handler / use case por fluxo
      ApplyMonthlyAdjustment.php
  Infrastructure/
    Persistence/
      Eloquent/
        Models/
        Repositories/         # implementações das interfaces do Domain
```

- Pode-se manter `app/Models` por convenção do Laravel desde que esses arquivos sejam tratados como **infraestrutura** (sem regra de negócio). Alternativa: mover para `Infrastructure\Persistence\Eloquent\Models` e ajustar PSR-4 no `composer.json`.
- **Bounded context** novo = nova área sob `Domain\{Context}` e `Application\{Context}` (não misturar agregados sem relação na mesma classe gigante).

### 3.3 Princípios de simplicidade

1. **Sem domain events até precisar:** começar com métodos na entidade e testes unitários; eventos quando surgirem efeitos colaterais estáveis (auditoria, integrações).
2. **Value objects onde o tipo importa:** ex.: dinheiro com aritmética segura; não é obrigatório VO para cada campo escalar no primeiro dia.
3. **Repositório:** interface no Domain; implementação traduz **Entity ↔ Model** (métodos privados `toDomain` / `toModel` no repositório concreto são aceitáveis).
4. **Strategy / política por variantes:** adequado a tipos de conta, gateways, etc., com implementações testáveis (podem morar em `Domain` ou ser injetadas no caso de uso).

### 3.4 Compatibilidade com nomenclatura antiga

- A antiga camada **“Service”** corresponde a **Application** (casos de uso). Nomes como `DepositFundsService` ainda são aceitáveis se a classe viver sob `Application\...` e cumprir o mesmo papel.
- O checklist e as seções abaixo usam os termos **Domain / Application / Infrastructure**; onde ler “Service”, interprete como **Application** salvo menção explícita ao contrário.

---

## 4. Camadas — visão resumida (da borda HTTP até a persistência)

| Camada | Responsabilidade principal | Não deve |
|--------|----------------------------|----------|
| **Controller** | HTTP: rota, validação de entrada (delegada), chamar **caso de uso**, formato de resposta | Regra de negócio pesada; queries diretas complexas; transações longas |
| **Application** | Orquestrar o caso de uso, transações, integrações (via portas); delegar invariantes às **entidades/VOs** | Lógica que é intrinsicamente da entidade; detalhes de request/response |
| **Domain** | Comportamento e invariantes do negócio; contratos de persistência | Framework, SQL, HTTP |
| **Infrastructure** | Eloquent, repositórios concretos, APIs externas; o “como” dos dados | Políticas de negócio |
| **Model** (Eloquent) | Mapeamento tabela/classe, casts, relacionamentos, scopes simples | Processo de negócio multi-entidade; fluxos de caso de uso |

---

## 5. Domain (entidades, value objects, eventos)

**Papel:** coração do negócio — **testável sem banco e sem HTTP**.

- **Entidades:** identidade estável (id); mutação via métodos que preservem invariantes (`deposit`, `applyInterest`, …).
- **Value objects:** imutáveis quando possível; igualdade por valor; sem identidade global.
- **Exceções:** específicas do contexto em `Exceptions\`.
- **Interfaces (portas):** o que o domínio precisa “do mundo externo” (persistir, enviar e-mail) sem definir implementação.
- **Domain events:** classes simples registrando fatos (`AccountCredited`); publicação/dispatch pode ser tratada na Application ou Infrastructure conforme o projeto amadurecer.

---

## 6. Application (casos de uso)

**Papel:** **orquestrar** um fluxo de aplicação usando o Domain e as portas.

- Um **arquivo/classe por caso de uso** é o ideal (`DepositFunds`, `RegisterUser`); se o fluxo for trivial, métodos adicionais **coesos** na mesma classe são aceitáveis.
- **`DB::transaction`** aqui quando múltiplas escritas devem ser atômicas.
- Não retornar `JsonResponse`; retornar **entidades**, **DTOs** de saída ou estruturas claras para o controller formatar com **API Resources**.
- Dependências **injetadas** (repositório via interface definida no Domain, gateways, etc.).

---

## 7. Infrastructure — Repository concreto e Model (Eloquent)

### 7.1 Repository (implementação)

**Papel:** **centralizar acesso a dados** e esconder **Eloquent/SQL** da Application.

- Implementa a **interface** definida no Domain.
- Métodos com nomes explícitos: `findByIdForUpdate`, `save`, `paginateForUser`, …
- Retornar **entidades de domínio** (ou DTOs acordados) para a Application — **não** expor `Query Builder` fora do repositório concreto.
- **Mapeamento** entity ↔ model no próprio repositório concreto mantém o modelo de domínio livre de `fillable`/ActiveRecord.

Exemplo de fronteira (conceitual):

- O **caso de uso** chama `$this->accounts->getForUpdate($id)` dentro de uma transação.
- O **repositório concreto** usa `BankAccountModel::query()->lockForUpdate()->...` e devolve `BankAccount` (entidade).

### 7.2 Model (Eloquent)

**Papel:** representar o **registro** e **relacionamentos** no banco **somente**.

- Manter **fillable/guarded**, `casts`, `hidden`, dates.
- **Relacionamentos** (`hasMany`, `belongsTo`, etc.) definidos no model.
- **Scopes** de query reutilizáveis e **pequenos** (ex.: `scopeActive`).
- Accessors/mutators apenas para **formato** ou **consistência de atributo**, não para fluxos de negócio.
- Evitar callbacks (`creating`, `saved`, …) com lógica pesada ou que chame serviços externos; preferir **comportamento explícito** na entidade de domínio + caso de uso quando o fluxo for crítico.

---

## 8. Controller

**Papel:** entrada/saída **HTTP** apenas.

- Validar com **Form Request** (`authorize` + `rules`) quando a entrada for não trivial.
- Chamar **um** caso de uso (Application) por ação na medida do possível; evitar controllers com centenas de linhas.
- Respostas: **API Resources**, arrays estruturados ou **responses** Laravel; manter consistência de envelope (`data`, `meta`, erros).
- **Autorização**: preferir **Policies** e `Gate` no controller ou Form Request, não regra de negócio espalhada.

---

## 9. Design patterns recomendados (PHP / Laravel)

| Pattern | Uso típico neste projeto |
|---------|---------------------------|
| **Repository (interface no Domain)** | Isolar persistência; testes com fake/in-memory na Application |
| **Application / caso de uso** | Orquestração e transações |
| **DTO (Data Transfer Object)** | Entrada/saída entre camadas sem acoplar ao `Request` ou ao Model inteiro |
| **Factory** | Criação complexa de agregados com invariantes |
| **Strategy** | Variar algoritmos por tipo (ex.: correção monetária, contas) |
| **Observer / Domain events** | Efeitos colaterais após fatos de domínio (introduzir quando o fluxo for claro; evitar esconder regra crítica) |
| **Decorator** | Comportamento transversal sobre repositório/cliente HTTP (cache, log) |

Laravel já oferece encaixe para **Service Provider** (registro de bindings interface → implementação), **Policies**, **Form Requests**, **Resources**, **Jobs** — usar quando reduzir acoplamento HTTP ↔ Application/Domain.

---

## 10. Testes (PHPUnit)

**Regra:** para **nova regra de negócio**, **correção de bug** ou **refatoração com risco**, incluir ou ajustar testes em **PHPUnit** sempre que for **viável** (tempo, flakiness, dependências externas). Se não der para testar na hora, registrar débito explícito (issue/task) com justificativa.

### 10.1 Stack e execução

- Framework: **PHPUnit** (configuração em `phpunit.xml` na raiz do projeto Laravel).
- Comando usual: `php artisan test` (wrapper do PHPUnit) ou `vendor/bin/phpunit`.
- Classes de teste em `tests/`, namespaces `Tests\` (PSR-4), sufixo `Test` no nome da classe.

### 10.2 Tipos de teste (quando usar cada um)

| Tipo | Foco | Exemplos neste projeto |
|------|------|------------------------|
| **Unit** (`Tests\Unit`) | Classes **isoladas**; dependências **mockadas** | Entidades, value objects, casos de uso (Application) com repositórios mockados |
| **Feature** (`Tests\Feature`) | **HTTP**, filas, e-mail fake, banco com **RefreshDatabase** | Rotas, controllers + Application integrada ao app |
| **Integration** (opcional) | Repositório real contra **banco de teste** | Queries complexas, constraints, migrations |

- Priorizar **Unit** para regras em **Domain** e orquestração em **Application** (rápidos, estáveis).
- Usar **Feature** para garantir **contrato da API** e fluxos que cruzam camadas.
- **Não** depender de serviços externos reais nos testes: usar **fakes**, **mocks** (`Mockery`), `Http::fake()`, `Mail::fake()`, etc.

### 10.3 Boas práticas com PHPUnit / Laravel

- **`RefreshDatabase`** ou `DatabaseTransactions` em testes que persistem dados; usar **banco dedicado** ou **SQLite em memória** em `phpunit.xml` / `.env.testing`.
- Um teste deve verificar **um comportamento principal**; nomes de método descritivos: `test_it_rejects_expired_orders`, ou anotação/docblock `@test` com método `it_rejects_expired_orders`.
- Assertions explícitas: `assertEquals`, `assertDatabaseHas`, `assertJson`, `assertStatus`, etc.
- Para **Application:** injetar **interfaces** do Domain e usar **mock** ou instâncias fake em testes unitários.
- Para **Form Requests** e policies: testar via **Feature** (`$this->postJson(...)`) ou testes dedicados quando o Laravel permitir.
- Cobrir **regressões**: ao corrigir um bug, adicionar teste que **falharia antes** da correção.

### 10.4 O que não precisa (exceções aceitáveis)

- Smoke manual único de integração com terceiros (mas ainda assim isolar com interface + fake na maior parte dos testes).
- Código gerado ou puramente declarativo sem lógica (ex.: só migration vazia) — sem obrigatoriedade de teste.

---

## 11. Sugestões adicionais (boas práticas)

1. **Form Requests** para validação; manter controllers finos.
2. **API Resources** ou serializers consistentes para não vazar colunas internas ou relacionamentos acidentalmente (`$hidden` no model ajuda, mas o Resource dá controle fino).
3. **Casos de uso únicos** para endpoints específicos: preferir `DepositFunds` (Application) em vez de um serviço genérico que cresce indefinidamente.
4. **Evitar “God classes”** na Application: se ultrapassar ~300–400 linhas sem motivo forte, dividir por **contexto** (`Accounts`, `Billing`).
5. **Testes**: seguir a seção **10. Testes (PHPUnit)**; manter a pirâmide (muitos unit, feature nos fluxos críticos).
6. **IDs e UoW:** operações que envolvem múltiplas tabelas devem estar na mesma transação no **caso de uso (Application)**, não no controller.
7. **Query optimization** pertence ao **repositório concreto** (eager loading, índices), não espalhada em loops no controller.

---

## 12. Checklist rápido (PR)

- [ ] Controller só coordena HTTP + chama **caso de uso (Application)**?
- [ ] Regras de negócio novas estão em **entidades/VOs (Domain)**, não no Model Eloquent?
- [ ] Consultas complexas estão no **repositório concreto (Infrastructure)**?
- [ ] **Application** concentra transação e orquestração?
- [ ] Interfaces (portas) no **Domain**; implementações na **Infrastructure**?
- [ ] Tipos/interfaces públicos claros para dependências injetadas?
- [ ] Há testes PHPUnit novos ou atualizados para a mudança (ou justificativa registrada se não for possível)?
- [ ] `php artisan test` (ou pipeline CI) passa?

---

*Documento vivo: ajustar nomes de pastas e PSR-4 no `composer.json` ao adotar `Infrastructure\Persistence\...\Models`; manter bindings de interface → implementação nos Service Providers.*
