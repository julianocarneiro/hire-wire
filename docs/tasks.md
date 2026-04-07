# Tasks

https://github.com/julianocarneiro/hire-wire

## Fase 1: Infra e instalação do Laravel

✅ **Docker Desktop:** instalação da versão 4.67.0 (ou equivalente acordada).
✅ **Especificação de infra:** documento de referência para o ambiente.
✅ **Containers:** mini-infra Docker — container PHP, container PostgreSQL, `Dockerfile` e `docker-compose`.
✅ **Laravel 13:** projeto instalado e `.env` configurado.

## Fase 2: Padrões de código

✅ **Orientação a objetos:** convenções documentadas.
✅ **Camadas:** organização e responsabilidades definidas.
✅ **Design patterns:** padrões aplicáveis ao projeto documentados — [paterns](paterns/).
✅ **Checklist de revisão:** critérios para PR/review.
✅ **Testes:** especificação ou orientação de testes acordada.

## Fase 3: Domínio, tabelas e OAuth2

✅ **Camada de domínio:** especificação inicial.
✅ **Tabelas:** modelo de dados especificado.
✅ **OAuth2:** especificação da implementação (Passport ou stack definida).
✅ **Feature OAuth2:** implementação funcional.
✅ **Testes OAuth2:** cobertura de testes da feature.
✅ **Domínio:** atualização do modelo de domínio incluindo OAuth2.
✅ **Tabelas:** atualização das estruturas incluindo OAuth2.
✅ **Documentação:** registo dos testes e estado até o momento.

## Fase 4: Migrations, domínio e testes de domínio

✅ **Migrations:** criadas e alinhadas ao modelo.
✅ **Camada de domínio:** implementação coerente com a especificação.
✅ **Testes da camada de domínio:** suíte de testes da camada de domínio.

## Fase 5: Autenticação Web, sessão e dashboard

✅ **Cadastro inicial do cliente (criar conta)** — [new_user.spec.md](new_user.spec.md)
✅ Rotas `register` (GET/POST em `/register`) com middleware `guest` e throttle no POST.
✅ Controller e validação: nome, CPF único e válido, e-mail único, senha com confirmação; persistência com password hasheado.
✅ Pós-registo: `Auth::login`, `session()->regenerate()`, `LocalUserProfileProvisioner`, redirect ao dashboard.
✅ Página Inertia de registo (`GuestLayout`): campos do formulário e link _«Já tenho uma conta»_ → login.
✅ Página de login: link _«Criar conta»_ (ou equivalente) → registo.
✅ Testes de feature: registo válido + sessão + redirect; falha com e-mail duplicado; falha com CPF duplicado ou inválido; `GET /register` com utilizador autenticado redireciona; smoke de navegação login ↔ registo.
✅ **Proteger a URL inicial (`/`)**: redirecionar visitantes não autenticados para a tela de login (ou fluxo OAuth2 web, conforme stack); após autenticação bem-sucedida, redirecionar para o dashboard (ou `/` autenticado).
✅ **Fluxo de login**: integrar com o mecanismo já definido no projeto (Passport / OAuth2 / guard Laravel); garantir que uma sessão ou cookie de aplicação web válido exista após o login, não apenas token de API isolado.
✅ **Persistência do usuário local (upsert “se não existir”)**: ao primeiro login bem-sucedido, criar ou atualizar registro mínimo na base (ex.: nome, e-mail, identificador externo do provedor) **sem duplicar** usuários; documentar qual campo faz o vínculo (e-mail, `sub` do OAuth, etc.).
✅ **Autorização por escopo de dados**: todas as consultas e comandos (contas, saldos, depósitos, correção monetária) devem filtrar por **usuário autenticado** (policies, query scopes ou repositórios); impedir acesso a recursos de outro usuário (testes de negação).
✅ **Dashboard básico**: página autenticada com layout simples (boas-vindas ou resumo), link/navegação coerente com o restante do app e **botão “Sair”** que invalida sessão e redireciona para login ou home pública.
✅ **Logout**: rota `POST` (ou equivalente seguro) para destruir sessão e tokens de sessão web; proteção CSRF onde aplicável.
✅ **Testes básicos**:
✅ Feature: visitante não autenticado não acessa `/` (ou dashboard) e é redirecionado.
✅ Feature: usuário autenticado acessa dashboard e vê conteúdo esperado.
✅ Feature: logout remove autenticação e bloqueia acesso subsequent ao dashboard.
✅ Feature ou unit: usuário A não lista nem altera dados do usuário B (pelo menos um caso por recurso crítico).
✅ Feature (opcional): primeiro login cria usuário local; segundo login não duplica.

## Fase 6: Theme dark / light (Tailwind)

✅ **Especificação e convénio:** [theme.spec.md](theme.spec.md) (tokens, `localStorage`, Blade + Vue, checklist de novas páginas).
✅ **Tailwind:** variante `dark:` com **modo por classe** em `<html>` (`@custom-variant dark`); persistência explícita `light` / `dark`; sem chave em `localStorage`, usar `prefers-color-scheme` até o utilizador escolher.
✅ **`src/resources/css/app.css`:** variáveis `--hw-*` em `:root` e `.dark`; `@theme` com `page`, `surface`, `border`, `text`, `text-muted`, `primary`, `primary-fg` (utilitários `bg-page`, `text-text`, etc.).
✅ **FOUC:** script inline no `<head>` de `app.blade.php` antes do Vite, em linha com a regra documentada em `theme.spec.md`.
✅ **`useTheme`:** `src/resources/js/composables/useTheme.js` — `THEME_STORAGE_KEY`, `isDark`, `setDark`, `toggle`.
✅ **`ThemeToggle`:** `src/resources/js/Components/ThemeToggle.vue` (`role="switch"`, `aria-checked`, ícones sol/lua).
✅ **Layouts:** `GuestLayout` e `AppLayout` com toggle e superfícies base em tokens.
✅ **Páginas:** `Login.vue`, `Register.vue`, `Dashboard/Index.vue`, `BankAccountShow.vue` com tokens e erros `dark:` onde aplicável.
✅ **Nota em** [front-end_vue3.md](paterns/front-end_vue3.md) **secção 9** — tema claro/escuro e ligação à spec.

- (Opcional) **Multi-tab:** listener `storage` para alinhar o tema entre separadores.
- (Opcional) **Testes:** Vitest no composable ou E2E (Playwright) no fluxo do toggle.

## Fase 7: Crud de Contas

✅ **Domínio com herança:** entidade abstrata `BankAccount` (`App\Domain\Banking\Entities\BankAccount`) com `accountType()`; subclasses `SavingsAccount`, `CheckingAccount`, `InvestmentsAccount`; `BankAccountEntityFactory` para instanciar o tipo correto a partir do valor persistido.
✅ **Contrato do repositório:** `listForUser(UserId)` e `delete(BankAccountId, UserId)`; `EloquentBankAccountRepository` mapeia linhas para subclasses via factory; `replaceBalance` na entidade para atualização controlada do saldo.
✅ **Rotas HTTP (autenticadas):** `POST /bank-accounts` (criar, throttle), `GET /bank-accounts/{id}` (detalhe), `PATCH /bank-accounts/{id}` (atualizar saldo), `DELETE /bank-accounts/{id}` (eliminar); nomes de rota `bank-accounts.*`.
✅ **Validação:** `StoreBankAccountRequest` (tipo enumerado + `unique` por `user_id`+`type`); `UpdateBankAccountRequest` (saldo numérico ≥ 0).
✅ **Inertia — props partilhadas:** `HandleInertiaRequests` expõe `bankAccounts` (lista do utilizador) e `accountTypeOptions` (valor + etiqueta PT) em páginas autenticadas; lista da sidebar atualiza após criar (redirect `back()`).
✅ **Layout:** `AppLayout` com sidebar — ação «Nova conta» e lista de contas com `Link` para o detalhe; destaque da conta ativa pela URL.
✅ **Modal «Nova conta»:** `NewBankAccountModal.vue` — select de tipo, saldo inicial opcional, `POST` via Inertia; ao sucesso fecha o modal e renova dados partilhados.
✅ **Detalhe da conta:** `BankAccountShow.vue` — formulário de saldo (`PATCH`), secção eliminar (`DELETE` com confirmação, redirect ao dashboard).
✅ **Testes:** `BankAccountCrudTest` (props no dashboard, CRUD, duplicado de tipo, isolamento entre utilizadores); `BankAccountTest` atualizado para factory + asserções das subclasses.

## Fase 8: Movimentação da conta

✅ **Especificação:** [account_movements.spec.md](account_movements.spec.md) (regras alinhadas ao [README.md](../README.md)).
✅ **Tela de movimentações** (`/bank-accounts/{id}/movimentacoes`): interface com **três abas (tabs)** — navegação clara, estado da aba ativa acessível (teclado / ARIA), alinhada ao tema claro/escuro.
✅ **Aba «Depósito»:** formulário para registar depósito (valor, validações e submissão ao backend); feedback de sucesso/erro; atualização coerente do saldo partilhado quando aplicável.
✅ **Aba «Movimentação + saldo»:** listagem ou resumo das **movimentações** da conta **junto com** o **saldo** atual (ou evolução), de leitura prioritária para o utilizador.
✅ **Aba «Correção monetária»:** formulário ou fluxo dedicado à **correção monetária** (regra de negócio e persistência a definir em domínio/API); validação e autorização por utilizador/conta.
✅ **Backend / domínio:** modelos ou agregados para movimentos, depósitos e correções; repositórios e políticas que garantam isolamento por `user_id` / conta; rotas e testes de feature alinhados à Fase 5 (autorização).
✅ **Testes:** `BankAccountMovementsTest` + asserção `movements` em `BankAccountCrudTest`.

## Fase 9: Organização, otimização, componentização e mais testes

**Especificação:** [organization_optimization.spec.md](organization_optimization.spec.md) (objetivo, escopo, organização back/front, otimização, componentização Vue, testes e checklist de aceitação).

### Organização

✅ Rever **estrutura de pastas** (`App\Domain`, `App\Application`, `App\Infrastructure`, `Http`) e alinhar nomes de ficheiros às convenções já documentadas em [paterns](paterns/) — revista mínima; sem mudanças estruturais largas.
✅ **Rotas e controllers:** `BankAccountMovementController` (movimentações, depósito, correção); `BankAccountController` só CRUD; PHPDoc de classe nos controllers e em `HandleInertiaRequests`.
✅ **Front-end:** `Components/` (`BankAccountPageHeader`, `AccountMovementsTable`) vs `Pages/`; composables `useCurrencyFormat`, `useBankAccountLabels`, `useAccountTabsThree`.
✅ **Documentação:** `tasks.md` e [organization_optimization.spec.md](organization_optimization.spec.md) actualizados; README/infra inalterados (fluxo de dev igual).

### Otimização

✅ **Base de dados:** índice composto `account_movements (bank_account_id, created_at)`; listagem de movimentos sem N+1 por linha.
✅ **HTTP / Inertia:** mantida prop `account` nas páginas de detalhe/movimentações (saldo fresco após mutações); nota na spec.
✅ **Front (Vite):** `npm run build` com baseline registada na spec; `import.meta.glob` mantido.
✅ **Back:** sem cache de leitura (conforme spec).

### Componentização

✅ **Componentes Vue:** cabeçalho de conta e tabela de movimentos extraídos.
✅ **Composables:** `useBankAccountLabels`, `useCurrencyFormat`, `useAccountTabsThree`.
- (Opcional) **Tabs genérico** ARIA reutilizável — não implementado; lógica permanece na página + composable de três separadores.

### Mais testes

✅ **Feature:** fluxo depósito + correção + ordem na lista; guest em `POST` depósito/correção; smoke throttle nas rotas nomeadas.
✅ **Unit:** `BankAccountMovementServiceTest` com mocks dos repositórios.
- **Domínio:** casos-limite adicionais (`Money`, políticas) — apenas se surgirem novas regras (inalterado).
- **Opcional:** Playwright/Dusk ou Vitest para composables — fora do âmbito desta entrega.
