# Git Patern

Modelo **simples, profissional e pronto pra usar** (baseado em _Conventional Commits_ + boas práticas de PR).

---

# ✅ 1. Padrão de commits (Conventional Commits simplificado)

Formato:

```
tipo(escopo): descrição curta
```

### 📌 Tipos mais usados

- `feat` → nova funcionalidade
- `fix` → correção de bug
- `refactor` → melhoria interna (sem mudar comportamento)
- `style` → formatação (indentação, lint, etc)
- `docs` → documentação
- `test` → testes
- `chore` → configs, deps, scripts

---

### 📌 Exemplos reais (Laravel)

```bash
feat(auth): adiciona login com JWT

fix(user): corrige erro ao atualizar perfil

refactor(order): melhora lógica de cálculo de total

test(api): adiciona testes para rota de criação de pedidos

chore(deps): atualiza versão do laravel para 11.x

docs(readme): adiciona instruções de setup
```

---

### 📌 Boas práticas

- sempre em **inglês**
- usar verbo no **presente**
- descrição curta (até ~70 chars)
- detalhar no corpo se necessário:

```bash
feat(payment): adiciona integração com stripe

- cria service PaymentService
- adiciona webhook handler
- adiciona testes básicos
```

---

# ✅ 2. Padrão de Pull Request (PR)

Aqui é onde você se destaca mesmo.

---

## 📄 Template de PR (copiar e usar)

```md
## 🧠 Descrição

Explique o que foi feito e por quê.

## 🔧 Tipo de mudança

- [ ] feat (nova feature)
- [ ] fix (bug fix)
- [ ] refactor
- [ ] docs
- [ ] test
- [ ] chore

## 🚀 O que foi implementado

-
-
-

## 🧪 Como testar

1. Rode `php artisan migrate`
2. Acesse `/api/...`
3. Execute...

## 📸 Evidências (opcional)

Prints, logs ou exemplos de request/response

## ⚠️ Observações

Algo importante que o revisor deve saber

## ✅ Checklist

- [ ] Código segue padrões do projeto
- [ ] Testes adicionados/atualizados
- [ ] Sem erros de lint
- [ ] Documentação atualizada (se necessário)
```

---

# ✅ 3. Nome de branch (importante também)

Padrão:

```
tipo/descricao-curta
```

### Exemplos:

```bash
feat/login-jwt
fix/user-update-error
refactor/payment-service
chore/update-laravel
```

---

# ✅ 4. Fluxo ideal (profissional)

```bash
main
  └── develop
        └── feat/minha-feature
```

Se quiser simplificar (pro teste):

👉 pode usar só:

```
main
 └── feat/*
```
