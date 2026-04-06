# Lista de testes

Referência rápida dos testes PHPUnit no projeto (`src/tests/`). Atualizar este ficheiro quando acrescentar ou remover testes.

---

## Feature — OAuth

**Ficheiro:** `tests/Feature/OAuth/PassportProtectedApiTest.php`

| Método | O que verifica |
|--------|----------------|
| `test_me_requires_authentication` | `GET /api/me` sem token → 401 |
| `test_me_rejects_invalid_bearer_token` | `GET /api/me` com Bearer inválido → 401 |
| `test_me_returns_profile_with_valid_token_and_scope` | Token válido com escopo `read:profile` → 200 e dados do utilizador |
| `test_me_forbids_token_without_required_scope` | Token sem `read:profile` (ex.: só `read:accounts`) → 403 |

---

## Feature — exemplo (template Laravel)

**Ficheiro:** `tests/Feature/ExampleTest.php`

| Método | O que verifica |
|--------|----------------|
| `test_the_application_returns_a_successful_response` | `GET /` → 200 |

---

## Unit — exemplo (template Laravel)

**Ficheiro:** `tests/Unit/ExampleTest.php`

| Método | O que verifica |
|--------|----------------|
| `test_that_true_is_true` | Placeholder (`assertTrue(true)`) |

---

*Executar: `php artisan test` (ou `composer test`, conforme o [README](../README.md)) a partir da pasta `src/`.*
