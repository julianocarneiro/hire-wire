# Sistema de Contas Bancárias - Code Test

Este sistema foi desenvolvido para avaliar as habilidades de desenvolvimento dos candidatos por meio de um desafio prático. A aplicação permite a criação e o gerenciamento de três tipos de contas bancárias: **Conta Poupança**, **Conta Corrente** e **Conta Investimentos**. O sistema inclui um backend em **Laravel** e um frontend em **Vue.js**, com autenticação implementada via **Laravel Passport**.

---

## 🚀 Funcionalidades Gerais

### 1. Cadastro de Usuário
- Os usuários podem se cadastrar fornecendo:
  - **Nome**
  - **CPF** (único)
  - **E-mail** (único)
  - **Senha**
- O sistema valida para evitar duplicidade de **CPF** e **e-mail**.

### 2. Tipos de Contas
- **Conta Poupança**
- **Conta Corrente**
- **Conta Investimentos**

### 3. Operações Disponíveis
- **Depósito**: Adicionar valores ao saldo de uma conta específica.
- **Consulta de Saldo**: Visualizar o saldo atual de qualquer conta do usuário.
- **Correção Monetária**: Aplicada mensalmente conforme as regras de cada tipo de conta.

### 4. Autenticação e Controle de Acesso
- Apenas usuários autenticados podem acessar as operações de suas contas.
- A autenticação é realizada via **Laravel Passport** com tokens de acesso OAuth2.

---

## 📜 Regras de Negócio

### 1. Conta Poupança
- **Depósito**:
  - O valor depositado é adicionado diretamente ao saldo da conta.
- **Correção Monetária**:
  - 0,001% do saldo é aplicado no final de cada mês.

### 2. Conta Corrente
- **Depósito**:
  - O valor depositado recebe um incremento de **R$0,50** antes de ser adicionado ao saldo.
  - **Exemplo**: Um depósito de **R$100,00** resulta em **R$100,50** adicionados ao saldo.
- **Correção Monetária**:
  - 0,1% do saldo é aplicado no final de cada mês.

### 3. Conta Investimentos
- **Depósito**:
  - O valor depositado recebe um incremento de **R$0,50** antes de ser adicionado ao saldo.
  - **Exemplo**: Um depósito de **R$200,00** resulta em **R$200,50** adicionados ao saldo.
- **Correção Monetária**:
  - 0,1% do saldo é aplicado no final de cada mês.

---

## 🛠️ Tecnologias Utilizadas
- **Backend**: Laravel
- **Frontend**: Vue.js
- **Autenticação**: Laravel Passport (OAuth2)

---

## 😁 Sobre suas habilidades
Este code test não foi feito para avaliar o quão bem você conhece PHP, mas sim para explorar suas habilidades com Laravel e, acima de tudo, seu domínio em programação orientada a objetos no **modo hard**. Prepare-se para brilhar! 😎

## 🏁 Como Usar

1. Clone este repositório.

**Rodar a aplicação:** na raiz do repositório, suba os serviços com Docker Compose (PHP + PostgreSQL) e execute migrations/Passport dentro do container — a interface web fica em **http://localhost:8000** (detalhes abaixo).

**Build do front-end:** na pasta **[src/](src/)**, com Node.js instalado no host, use `npm install` e depois **`npm run dev`** (desenvolvimento, HMR) ou **`npm run build`** (gera `public/build/` para produção, sem servidor Vite).

### Backend com Docker (Laravel + PostgreSQL)

O diretório **[src/](src/)** do repositório é montado em `/var/www/html` no serviço `app`. É necessário ter **Docker** e **Docker Compose v2** instalados.

1. Na primeira vez, gere o Laravel dentro de `src` (diretório vazio), a partir da raiz do repositório:

   ```bash
   docker compose build
   docker compose run --rm --no-deps -e COMPOSER_PROCESS_TIMEOUT=0 app composer create-project laravel/laravel .
   ```

   Em conexões lentas, o `COMPOSER_PROCESS_TIMEOUT=0` evita falha por limite de tempo (300s) do Composer. Se a criação parar pela metade, rode: `docker compose run --rm --no-deps -e COMPOSER_PROCESS_TIMEOUT=0 app composer install --no-interaction`.

   Se `src` já tiver o projeto, pule este passo.

2. No arquivo **`src/.env`**, use PostgreSQL com host **`db`** (nome do serviço no Compose), por exemplo:

   ```env
   DB_CONNECTION=pgsql
   DB_HOST=db
   DB_PORT=5432
   DB_DATABASE=hire_wire
   DB_USERNAME=hire_wire
   DB_PASSWORD=hire_wire_secret
   ```

   Os valores acima coincidem com as variáveis `POSTGRES_*` definidas no `docker-compose.yml`. Altere em um só lugar se quiser credenciais diferentes.

3. **Pasta de dados do PostgreSQL (obrigatória no host):** o `docker-compose.yml` monta **`docker-data/postgres/`** na raiz do repositório como volume do Postgres. Essa pasta **não vem no clone** (está no `.gitignore`). **Crie-a antes do primeiro `docker compose up`**, mesmo que fique vazia — em vários ambientes o bind mount falha se o caminho no host não existir.

   ```bash
   mkdir -p docker-data/postgres
   ```

   No PowerShell: `New-Item -ItemType Directory -Force -Path docker-data/postgres`

4. Suba os containers:

   ```bash
   docker compose build
   docker compose up -d
   ```

5. Dentro do container da aplicação:

   ```bash
   docker compose exec app composer install
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan migrate
   docker compose exec app php artisan passport:install
   ```

6. API em **http://localhost:8000** (`php artisan serve` no serviço `app`). O Postgres fica exposto em **localhost:5433** no host (mapeamento no `docker-compose.yml`; evita conflito com outro Postgres na 5432).

Comandos úteis: `docker compose logs -f`, `docker compose down`. Os dados do PostgreSQL persistem na pasta **`docker-data/postgres/`** no repositório (bind mount; ver [docs/infra.md](docs/infra.md)). Para zerar o banco, apague essa pasta com os containers parados — `docker compose down -v` já não remove esses ficheiros.

Mais detalhes em [docs/infra.md](docs/infra.md).

### Frontend (Vite + Vue 3 + Inertia)

O código do front-end fica em **[src/](src/)** (mesmo diretório do Laravel). O **Docker Compose só sobe o PHP**; o **Vite** (compilação e recarga em dev) roda na sua máquina com **Node.js** (recomenda-se LTS atual).

#### Desenvolvimento (`npm run dev`)

1. Com o backend acessível (por exemplo `docker compose up` e API em **http://localhost:8000**).
2. Na pasta do Laravel:

   ```bash
   cd src
   npm install
   npm run dev
   ```

3. Deixe o processo do Vite **em execução**. Ele costuma usar a porta **5173** e o Laravel injeta os scripts via `@vite` em `resources/views/app.blade.php`.
4. Abra a aplicação em **http://localhost:8000**. Alterações em `.vue` e em `resources/js` são recompiladas (HMR/recarga).

Se a página não carregar os assets ou aparecer erro de Vite, confira se `npm run dev` está rodando e se não há bloqueio de firewall na porta do Vite.

#### Build de produção (`npm run build`)

Para gerar os ficheiros estáticos em `public/build/` (sem precisar do servidor Vite em runtime):

```bash
cd src
npm install
npm run build
```

Depois do build, o PHP pode servir só o que está em `public/build/` — útil em deploy ou quando **não** quiser manter `npm run dev` ligado. Volte a executar `npm run build` sempre que alterar o front antes de publicar.

#### Fluxo geral

- **Dev:** `docker compose up` + `npm run dev` em `src/`.
- **API/base URL:** o dashboard Inertia usa as mesmas rotas web do Laravel em **http://localhost:8000**; a API OAuth2 (Passport) segue as rotas configuradas em `routes/api.php` na mesma base, se o cliente for externo.

---

## 📢 Observações
Este sistema é uma simulação para o teste técnico de contratação e visa avaliar suas habilidades em backend, frontend, e lógica de negócios.
