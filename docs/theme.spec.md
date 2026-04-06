# Especificação: tema claro / escuro (Tailwind v4)

Documento de referência para **geração e revisão de código** do tema da aplicação (Fase 6). Alinha-se a [front-end_vue3.md](paterns/front-end_vue3.md) e à [documentação oficial do Tailwind — dark mode](https://tailwindcss.com/docs/dark-mode).

---

## 1. Objetivo

- Oferecer **dois modos visuais** coerentes (claro e escuro), com **alternância manual** persistida.
- Centralizar cores em **tokens** (CSS variables + `@theme`) para evitar paletas fixas (`slate-900`, `white`) espalhadas nas vistas.
- Evitar **flash de tema errado** no primeiro paint (FOUC), aplicando o modo antes do JavaScript do bundle.

---

## 2. Requisitos técnicos fixos

| Item | Valor / regra |
|------|----------------|
| **Tailwind** | v4 com `@import 'tailwindcss';` e plugin Vite `@tailwindcss/vite` |
| **Variante `dark`** | **Modo por classe** no ancestral: `@custom-variant dark (&:where(.dark, .dark *));` em `src/resources/css/app.css` |
| **Elemento raiz do tema** | Classe `dark` em **`<html>`** (`document.documentElement`), não só no `body` |
| **Chave `localStorage`** | `hire-wire-theme` |
| **Valores persistidos** | `light` ou `dark` (string). Ausência da chave = respeitar **preferência do sistema** até o utilizador escolher |

### 2.1 Regra de resolução inicial (obrigatória alinhar em dois sítios)

A lógica abaixo deve ser **idêntica** em:

1. O **script inline** no `<head>` do layout Blade principal (`src/resources/views/app.blade.php`), executado antes do Vite.
2. Qualquer teste ou documentação que descreva o “primeiro paint”.

```text
Se localStorage['hire-wire-theme'] === 'dark'  →  modo escuro
Senão, se localStorage['hire-wire-theme'] === 'light'  →  modo claro
Senão  →  modo escuro se matchMedia('(prefers-color-scheme: dark)'), senão claro
```

Em seguida: `document.documentElement.classList.toggle('dark', modoEscuro)`.

Após escolha explícita do utilizador, gravar sempre `light` ou `dark` (o script deixa de depender só do sistema).

---

## 3. Tokens de cor (`app.css`)

### 3.1 Variáveis `--hw-*`

- Definir em **`:root`** valores para modo claro e em **`.dark`** valores para modo escuro (sobreposição das mesmas variáveis `--hw-*`).
- Dentro de `@theme`, mapear utilitários Tailwind com prefixo `--color-*`:

| Token `@theme` | Uso típico |
|----------------|------------|
| `page` | Fundo da app (`bg-page`, `text-page` se necessário) |
| `surface` | Cartões, header, painéis (`bg-surface`) |
| `border` | Bordas (`border-border`, `decoration-border`) |
| `text` | Texto principal (`text-text`) |
| `text-muted` | Texto secundário (`text-text-muted`) |
| `primary` | Botão primário / foco (`bg-primary`, `focus:ring-primary`, …) |
| `primary-fg` | Texto sobre primário (`text-primary-fg`) |

**Proibição:** novas telas não devem introduzir `bg-slate-*` / `text-slate-*` para fundo ou texto principal quando existir token adequado; exceções pontuais (gráficos, badges) devem ser justificadas e, se possível, usar `dark:`.

### 3.2 Estados de erro e aviso

- Mensagens de validação: `text-red-600 dark:text-red-400` (ou token `--color-danger` no futuro, se adicionado ao `@theme`).

---

## 4. Front-end Vue / Inertia

### 4.1 Composable `useTheme`

- **Ficheiro:** `src/resources/js/composables/useTheme.js`
- **Exportar:** `THEME_STORAGE_KEY` e função `useTheme()`
- **`useTheme()`** deve expor pelo menos:
  - `isDark` — reativo, sincronizado com `document.documentElement.classList.contains('dark')` após `onMounted`
  - `setDark(boolean)` — aplica classe, atualiza `localStorage`
  - `toggle()` — alterna o modo

### 4.2 Componente `ThemeToggle`

- **Ficheiro:** `src/resources/js/Components/ThemeToggle.vue`
- **Papel:** botão de alternância única (não é três estados: claro / sistema / escuro nesta fase, salvo evolução futura documentada aqui).
- **Acessibilidade:** `role="switch"`, `aria-checked` ligado a `isDark`, `aria-label` e `title` em português (ex.: “Ativar tema claro” / “Ativar tema escuro”).
- **Ícones:** SVG inline `aria-hidden="true"`; texto visível apenas para leitores de ecrã via `.sr-only` quando aplicável.

### 4.3 Onde colocar o toggle

- **`GuestLayout`:** área superior alinhada à direita (visitantes vêem o controlo em login/registo).
- **`AppLayout`:** barra superior, junto ao utilizador e ao botão “Sair”.

### 4.4 Corpo da página

- Em `src/resources/views/app.blade.php`, o `<body>` pode usar `class="font-sans antialiased bg-page text-text"` para fundo coerente atrás do mount Inertia.

---

## 5. Checklist para novas páginas / componentes

1. Fundo da área: `bg-page`, texto: `text-text` / `text-text-muted`.
2. Cartões ou formulários: `bg-surface`, `border-border`.
3. Inputs: `bg-surface`, `text-text`, `border-border`, foco `focus:border-primary focus:ring-1 focus:ring-primary`.
4. Botão primário: `bg-primary text-primary-fg hover:opacity-90` (ou hover tokenizado se for introduzido).
5. Necessita de cor que não exista no token? — estender primeiro `:root` / `.dark` e `@theme`, depois usar o utilitário novo.

---

## 6. Testes manuais mínimos

- Primeira visita sem `localStorage`: comportamento segue o SO; sem flash incorreto após reload.
- Clicar no toggle: classe `dark` no `<html>`, valor gravado, reload mantém a escolha.
- Login, registo, dashboard legíveis nos dois modos; foco por teclado visível no toggle.

---

## 7. Ficheiros de referência neste repositório

- `src/resources/css/app.css` — variant dark, `--hw-*`, `@theme`
- `src/resources/views/app.blade.php` — script inicial + classes do `body`
- `src/resources/js/composables/useTheme.js`
- `src/resources/js/Components/ThemeToggle.vue`
- `src/resources/js/Layouts/GuestLayout.vue`, `AppLayout.vue`
