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
