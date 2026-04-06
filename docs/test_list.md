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

## Unit — Domain — Users (`Cpf`)

**Ficheiro:** `tests/Unit/Domain/Users/CpfTest.php`

| Método | O que verifica |
|--------|----------------|
| `test_it_accepts_normalised_valid_cpf` | CPF formatado é aceite e normalizado para 11 dígitos |
| `test_it_rejects_invalid_length` | Menos ou mais de 11 dígitos → exceção de domínio |
| `test_it_rejects_repeated_digits` | CPF com todos os dígitos iguais → exceção |
| `test_it_rejects_invalid_check_digits` | Dígitos verificadores incorretos → exceção |
| `test_equals_compares_digits` | Igualdade entre instâncias com formatos diferentes |

---

## Unit — Domain — Users (`Email`)

**Ficheiro:** `tests/Unit/Domain/Users/EmailTest.php`

| Método | O que verifica |
|--------|----------------|
| `test_it_normalises_case` | E-mail com espaços e maiúsculas → minúsculas e sem espaços laterais |
| `test_it_rejects_empty_string` | String vazia/apenas espaços → exceção |
| `test_it_rejects_invalid_format` | Formato inválido → exceção |
| `test_equals_is_case_insensitive` | `equals` ignora diferença de capitalização |

---

## Unit — Domain — Banking (`Money`)

**Ficheiro:** `tests/Unit/Domain/Banking/MoneyTest.php`

| Método | O que verifica |
|--------|----------------|
| `test_add_rounds_to_two_decimals` | Soma de valores monetários com duas casas decimais |
| `test_monthly_adjustment_savings_rate` | `balance × (1 + 0,00001)` alinhado à poupança |
| `test_monthly_adjustment_checking_rate` | `balance × (1 + 0,001)` alinhado à corrente |
| `test_zero_is_not_greater_than_zero` | `Money::zero()` não é considerado positivo |

---

## Unit — Domain — Banking (`AccountDepositPolicy`)

**Ficheiro:** `tests/Unit/Domain/Banking/AccountDepositPolicyTest.php`

| Método | O que verifica |
|--------|----------------|
| `test_savings_credits_stated_amount_only` | Poupança: valor creditado = valor declarado |
| `test_checking_adds_fifty_cents_bonus` | Corrente: valor creditado inclui bónus de R$ 0,50 |
| `test_investments_adds_fifty_cents_bonus` | Investimentos: idem corrente (+R$ 0,50) |

---

## Unit — Domain — Banking (`MonthlyAdjustmentPolicy`)

**Ficheiro:** `tests/Unit/Domain/Banking/MonthlyAdjustmentPolicyTest.php`

| Método | O que verifica |
|--------|----------------|
| `test_savings_applies_zero_point_zero_zero_one_percent` | Poupança: 0,001 % sobre o saldo (arredondamento a duas casas) |
| `test_checking_applies_zero_point_one_percent` | Corrente: 0,1 % |
| `test_investments_applies_zero_point_one_percent` | Investimentos: 0,1 % |

---

## Unit — Domain — Banking (`BankAccount`)

**Ficheiro:** `tests/Unit/Domain/Banking/BankAccountTest.php`

| Método | O que verifica |
|--------|----------------|
| `test_deposit_on_checking_applies_bonus_and_returns_balance` | Depósito em corrente atualiza saldo com bónus |
| `test_deposit_on_savings_does_not_apply_bonus` | Depósito em poupança sem bónus |
| `test_it_rejects_non_positive_deposit` | Valor zero ou não positivo → `InvalidDepositAmountException` |
| `test_monthly_adjustment_on_savings` | `applyMonthlyAdjustment` na poupança altera o saldo conforme a taxa |

---

## Unit — exemplo (template Laravel)

**Ficheiro:** `tests/Unit/ExampleTest.php`

| Método | O que verifica |
|--------|----------------|
| `test_that_true_is_true` | Placeholder (`assertTrue(true)`) |

---

*Executar: `php artisan test` ou `composer test` a partir da pasta `src/`, ou via Docker: `docker compose run --rm --no-deps app php artisan test` (na raiz do repositório), conforme o [README](../README.md).*
