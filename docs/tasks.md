# Tasks

https://github.com/julianocarneiro/hire-wire

## Fase 1: Infra e Instalação do Laravel

✅ Instalar Docker Desktop 4.67.0
✅ Criar uma especificação para infra
✅ Organizar mini infra com docker, sendo um container para php e outro para postgres, criar dockerfile e docker-compose
✅ Instalação do Laravel 13 e configuração do .env

## Fase 2: Especificar padrões de código

✅ Especificar Orientação a Objetos
✅ Camadas
✅ Design Paterns
✅ Checklist para Revisão
✅ Especificação de testes

## Fase 3: Definição de dominio e tabelas, Implementar OAuth2

✅ Especificar camada de dominio
✅ Especificar tabelas
✅ Especificar implementação do OAuth2
✅ Implementar a feature OAuth2
✅ Implementar Testes do OAuth2
✅ Atualizar camada de dominio, add OAuth2
✅ Atualizar estruturas de tabelas, add OAuth2
✅ Criada documentação dos testes até o momento

## Fase 4

✅ Criar migrations
✅ Criar camada de dominio
✅ Criar testes da camada de dominio

## Fase 5: Autenticação Web, sessão e dashboard

✅ **Cadastro inicial do cliente (criar conta)** — [new_user.spec.md](new_user.spec.md)
  ✅ Rotas `register` (GET/POST em `/register`) com middleware `guest` e throttle no POST.
  ✅ Controller e validação: nome, CPF único e válido, e-mail único, senha com confirmação; persistência com password hasheado.
  ✅ Pós-registo: `Auth::login`, `session()->regenerate()`, `LocalUserProfileProvisioner`, redirect ao dashboard.
  ✅ Página Inertia de registo (`GuestLayout`): campos do formulário e link *«Já tenho uma conta»* → login.
  ✅ Página de login: link *«Criar conta»* (ou equivalente) → registo.
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

