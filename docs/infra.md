# Infraestrutura local (Docker)

Stack mínima com **dois** serviços: container da aplicação PHP/Laravel (`app`) e container PostgreSQL (`db`). Não há Nginx nem serviço Node; o backend sobe com `php artisan serve` na porta **8000**.

## Pré-requisitos

- [Docker Engine](https://docs.docker.com/engine/install/) (no Windows, em geral via Docker Desktop)
- Docker Compose v2 (`docker compose`)

## Serviços e portas

| Serviço | Imagem / build | Descrição |
|---------|----------------|-----------|
| `db` | `postgres:16-alpine` | PostgreSQL; dados em **`./docker-data/postgres`** no host (bind mount, persistente) |
| `app` | build a partir do `Dockerfile` na raiz | PHP 8.3 CLI + Composer; comando padrão `php artisan serve --host=0.0.0.0 --port=8000` |

| Onde | Porta |
|------|--------|
| API (Laravel) | [http://localhost:8000](http://localhost:8000) |
| Postgres (host, ex.: DBeaver, `psql` no SO) | `localhost:5433` |

Na rede interna do Compose, o Laravel no container usa **`db`** na porta **5432** (porta do processo Postgres dentro do container). Só no host você usa **`localhost:5433`** (porta publicada no `docker-compose.yml`).

Se você rodar o Laravel **fora** do Docker e apontar para o Postgres do Compose, use `DB_HOST=127.0.0.1` e `DB_PORT=5433` no `src/.env`.

O serviço `app` usa `depends_on` com **`condition: service_healthy`** no `db`, para o Postgres aceitar conexões antes do container da aplicação subir. O `db` expõe um *healthcheck* com `pg_isready`.

### Persistência do PostgreSQL

- Os ficheiros do cluster ficam na pasta **`docker-data/postgres/`** na raiz do repositório (criada automaticamente no primeiro `docker compose up`).
- Essa pasta está no **`.gitignore`** (não entra no Git).
- `docker compose down` **não** apaga esses ficheiros; ao subir de novo, o mesmo banco é reutilizado.
- `docker compose down -v` remove **volumes declarados** no Compose; como o Postgres já **não** usa volume nomeado, os dados **continuam** em `docker-data/postgres/`. Para **zerar o banco de propósito**, pare os containers e apague a pasta `docker-data/postgres` no host (ou só o seu conteúdo), depois suba de novo e rode `migrate`.
- Se antes usava o volume Docker `postgres_data` e mudou para este layout, o Compose passa a usar só `docker-data/` — o volume antigo pode ficar órfão no Docker até `docker volume prune`. Os dados antigos continuam nesse volume até o remover manualmente; para migrar, use `pg_dump` / `pg_restore` ou aceite uma base nova em `docker-data/`.

## Variáveis no `docker-compose.yml`

O Postgres é criado com:

- `POSTGRES_DB=hire_wire`
- `POSTGRES_USER=hire_wire`
- `POSTGRES_PASSWORD=hire_wire_secret`

O serviço `app` também define variáveis `DB_*` equivalentes ( `DB_HOST=db`, etc.). O Laravel continua lendo o `.env` do projeto quando presente; **mantenha `DB_*` no `.env` alinhado** a esses valores (ou altere `POSTGRES_*` e `environment` do `app` em um único lugar).

## Variáveis no `.env` do Laravel

Arquivo: **`src/.env`** (projeto Laravel dentro de `src/`).

```env
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=hire_wire
DB_USERNAME=hire_wire
DB_PASSWORD=hire_wire_secret
```

Copie a partir de `src/.env.example` e ajuste conforme necessário.

## Primeiro uso: código Laravel

A pasta **`src/`** no repositório é montada em **`/var/www/html`** no serviço `app` (veja o volume em `docker-compose.yml`).

1. Se `src` ainda não tiver o Laravel, crie-o **a partir da raiz do repositório** ( `src` deve estar vazio):

   ```bash
   docker compose build
   docker compose run --rm --no-deps -e COMPOSER_PROCESS_TIMEOUT=0 app composer create-project laravel/laravel .
   ```

   `--no-deps` evita subir o Postgres só para rodar o Composer. `COMPOSER_PROCESS_TIMEOUT=0` desativa o tempo máximo padrão do Composer (útil em rede lenta; extrair pacotes grandes pode passar de 300 segundos).

   Se a instalação falhar no meio, conclua dependências com: `docker compose run --rm --no-deps -e COMPOSER_PROCESS_TIMEOUT=0 app composer install --no-interaction`.

2. Em **`src/.env`**, configure o bloco `DB_*` com `DB_HOST=db` (valores alinhados ao Compose), conforme a seção anterior.

## Imagem `app` (`Dockerfile`)

- Base: `php:8.3-cli-bookworm`
- Extensões PHP instaladas: `pdo`, `pdo_pgsql`, `mbstring`, `bcmath`, `exif`, `pcntl`, `zip`, `curl`
- [Composer 2](https://getcomposer.org/) copiado da imagem oficial `composer:2`
- `WORKDIR`: `/var/www/html`

## `.dockerignore`

Na raiz há um `.dockerignore` que reduz o contexto de build (ex.: `.git`, `vendor`, `node_modules`, `.env`, caches de `storage` / `bootstrap`). O código da aplicação entra pelo **volume** em tempo de execução, não por `COPY` no `Dockerfile`.

## Comandos

Execute na raiz do repositório (onde estão `docker-compose.yml` e `Dockerfile`):

1. **Build e subir em segundo plano**

   ```bash
   docker compose build
   docker compose up -d
   ```

2. **Instalar dependências PHP**

   ```bash
   docker compose exec app composer install
   ```

3. **Chave da aplicação** (se ainda não gerada)

   ```bash
   docker compose exec app php artisan key:generate
   ```

4. **Migrações**

   ```bash
   docker compose exec app php artisan migrate
   ```

5. **Laravel Passport** (OAuth2 — usado neste projeto)

   ```bash
   docker compose exec app php artisan passport:install
   ```

### Logs e ciclo de vida

```bash
docker compose logs -f
docker compose down
```

Se o serviço **`app`** aparecer como **`Created`** em `docker compose ps -a` mas não **`Up`**, o Laravel não está a servir na porta 8000. Suba (ou recrie) o serviço:

```bash
docker compose up -d app
```

`docker compose down -v` remove volumes **nomeados** ainda definidos no Compose (neste projeto, tipicamente nenhum associado ao Postgres). Os dados do Postgres ficam em **`docker-data/postgres/`**; para **apagar o banco por completo**, remova essa pasta no explorador de ficheiros ou, na raiz do repositório:

```bash
docker compose down
rm -rf docker-data/postgres
```

(No PowerShell no Windows: `Remove-Item -Recurse -Force docker-data\postgres`.)

## Frontend (Vue.js)

O Compose deste repositório não inclui serviço para o frontend. Suba o Vue separadamente e aponte a URL da API para o backend (ex.: `http://localhost:8000`).

## Backend em outra pasta

O backend padrão é **`src/`**. Para usar outro caminho, altere o volume do serviço `app` em `docker-compose.yml` (ex.: `./backend:/var/www/html`).

## Arquivos na raiz vs `src/`

`Dockerfile`, `docker-compose.yml` e `.dockerignore` ficam na **raiz** do repositório. O código Laravel vive em **`src/`** e é montado em `/var/www/html` no container.

Instruções resumidas para candidatos também aparecem em [README.md](../README.md) (seção *Como Usar*).
