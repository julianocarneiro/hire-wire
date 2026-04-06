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

3. Suba os containers:

   ```bash
   docker compose build
   docker compose up -d
   ```

4. Dentro do container da aplicação:

   ```bash
   docker compose exec app composer install
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan migrate
   docker compose exec app php artisan passport:install
   ```

5. API em **http://localhost:8000** (`php artisan serve` no serviço `app`). O Postgres fica exposto em **localhost:5432** para ferramentas como DBeaver.

Comandos úteis: `docker compose logs -f`, `docker compose down`. Para apagar também os dados do banco: `docker compose down -v`.

Mais detalhes em [docs/infra.md](docs/infra.md).

### Frontend e fluxo geral

1. Configure o frontend em Vue.js (fora do Compose deste repositório, salvo se você adicionar um serviço Node).
2. Ajuste URLs de API do frontend para o backend (por exemplo `http://localhost:8000`).
3. Com backend e frontend em execução, acesse o sistema pelo frontend.

---

## 📢 Observações
Este sistema é uma simulação para o teste técnico de contratação e visa avaliar suas habilidades em backend, frontend, e lógica de negócios.
