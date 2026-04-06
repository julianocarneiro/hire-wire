# Frontend — Vue 3 e Inertia.js

Especificação de **padrões**, **organização** e **boas práticas** para a camada front-end com **Vue 3** e **Inertia** integrada ao **Laravel** neste repositório. **Estilo:** **Tailwind CSS v4** (via Vite).

---

## 1. Objetivo

- Manter páginas **previsíveis**, fáceis de revisar e alinhadas ao **contrato de dados** enviado pelo backend (props Inertia).
- Reduzir duplicação entre telas sem acoplá-las ao detalhe HTTP (rotas Laravel + `router`/`Link` do Inertia).
- Priorizar **Composition API** com **`<script setup>`** para legibilidade e tree-shaking.
- Conviver com o modelo **MPA híbrido**: navegação via Inertia (sem SPA clássica com Vue Router para páginas servidas pelo Laravel).

---

## 2. Stack assumida

| Peça | Papel |
|------|--------|
| **Laravel** | Rotas, controllers, validação, autorização; respostas `Inertia::render(...)` |
| **Inertia.js** | Ponte servidor ↔ cliente; troca de página sem recarregar layout inteiro quando configurado assim |
| **Vue 3** | Componentes, reatividade, templates |
| **Vite** | Build e HMR (`laravel-vite-plugin`) |
| **Tailwind CSS v4** | Estilo utilitário; integração recomendada com **`@tailwindcss/vite`** no Vite; tema e tokens via CSS (variáveis/custom properties) conforme a [documentação da v4](https://tailwindcss.com/docs) |

**Tailwind v4** é a versão padrão deste projeto; versões exatas de pacotes ficam no `package.json`. Esta especificação descreve **comportamento e convenções**, não substitui o changelog do Tailwind.

---

## 3. Princípios gerais

### 3.1 Onde vive a “verdade”

- **Rotas e nomes de rota:** Laravel (`routes/web.php` ou equivalente). O front **não** define rotas de página em arquivo paralelo.
- **Dados da tela:** props Inertia vindas do controller/resource. Evitar “buscar de novo” no mount o que o servidor já enviou, salvo necessidade real (ex.: autocomplete paginado).
- **Validação de formulário:** preferir **Form Request** no Laravel; no cliente, exibir erros com `useForm` ou objeto de erros compartilhado pelo Inertia.

### 3.2 Separação de responsabilidades

| Camada / artefato | Responsabilidade | Evitar |
|-------------------|------------------|--------|
| **Página Inertia** (`Pages/...`) | Compor a tela, wiring de dados, delegar UI repetível a componentes | Lógica de negócio pesada duplicando regras do backend |
| **Componentes** (`Components/...`) | UI reutilizável, eventos, slots, pequena lógica de apresentação | Chamadas diretas ao axios para mutar estado global sem contrato |
| **Composables** (`composables/...`) | Lógica reutilizável entre páginas (formatadores, uso de teclado, listeners) | Acesso direto a `document`/`window` sem guard para SSR se o projeto evoluir |
| **Layouts** | Shell (nav, footer, alerts globais) | Props de negócio específicas de uma única página (preferir na página) |

---

## 4. Estrutura de pastas sugerida

Ajustar ao que o `vite.config` e o `resources/js` (ou `src/resources/js`) do projeto adotarem; modelo típico **Laravel + Inertia**:

```text
resources/js/
  app.js                 # bootstrap Inertia + Vue
  Pages/                 # uma pasta por domínio ou recurso
    Auth/
    Dashboard/
  Layouts/
    AppLayout.vue
    GuestLayout.vue
  Components/
    ui/                  # primitivas: botão, input, modal
    domain/              # blocos de negócio reutilizáveis
  composables/
  types/                 # se usar TypeScript: tipos das props Inertia
```

- **Páginas** espelham rotas e agrupamentos do backend quando possível (facilita onboarding).
- **Componentes `ui/`** não conhecem regras de negócio; recebem props e emitem eventos.

---

## 5. Vue 3 — Composition API e `<script setup>`

### 5.1 Padrão de script

- Usar **`<script setup lang="ts">`** quando o projeto tiver TypeScript; caso contrário, **`<script setup>`** sem `lang`.
- Preferir **`ref` / `computed` / `watch`** explícitos; usar `reactive` só quando o objeto for estável e local.
- **Props:** definir com tipos claros (`defineProps`) e, quando necessário, **`withDefaults`**.
- **Emits:** `defineEmits` com eventos nomeados (`submit`, `update:modelValue`, etc.).

### 5.2 Ordem sugerida no SFC

1. `<script setup>`
2. `<template>`
3. `<style scoped>` (ou estilo global só quando inevitável)

### 5.3 Nomenclatura

- **Componentes:** `PascalCase` em arquivo e em template.
- **Composables:** prefixo `use` (`useMoneyFormat.ts`).
- **Eventos em template:** kebab-case no pai (`@update:model-value`).

---

## 6. Inertia — páginas, layouts e navegação

### 6.1 Página

- Cada rota Inertia mapeia para **um** componente principal em `Pages/`.
- Resolver layout via **`defineOptions({ layout: ... })`**, **`layout` callback na resposta** do servidor ou wrapper — **um padrão por projeto**, não misturar três estilos.

### 6.2 Links e método HTTP

- Navegação GET: **`<Link href="...">`** com `route()` do Ziggy **se** o projeto expuser nomes de rota ao JS; caso contrário, URLs relativas estáveis.
- Mutations: **formulários Inertia** (`router.post`, `router.put`, …) ou **`useForm`** para estado + erros + `processing`.
- Evitar `axios` para ações que deveriam ser **visit Inertia** (mantém histórico, flash messages e validação alinhadas).

### 6.3 Props comuns

- Tratar **`errors`** e mensagens flash como **fonte única** de feedback pós-request.
- Dados compartilhados (`auth`, `flash`, config) definidos no **middleware HandleInertiaRequests** do Laravel — não duplicar no front sem motivo.

---

## 7. Formulários e validação

| Tarefa | Recomendação |
|--------|----------------|
| Envio com loading | `useForm` do Inertia (`processing`, `transform`, `resetOnSuccess`) |
| Erros por campo | Chaves alinhadas aos `attributes` do Laravel (`form.errors.email`) |
| CSRF / sessão | Confiar no stack Laravel + cookie/session conforme configuração do projeto |
| Upload de arquivo | `forceFormData` / `multipart` conforme doc Inertia; limites no backend |

- **Não** confiar apenas na validação do cliente; o servidor é autoritativo.

---

## 8. Estado global

- **Padrão:** estado mínimo; dados da página vêm das **props Inertia**.
- Introduzir **Pinia** quando houver estado **transversal** real (carrinho, tema, preferências) compartilhado por muitas páginas **sem** passar via props em cascata.
- Evitar store grande “espelhando” o backend; preferir **refetch** via visit ou endpoint API só quando necessário.

---

## 9. Estilo — Tailwind CSS v4

- Entrada CSS típica na v4: **`@import "tailwindcss";`** no arquivo principal de estilos (ex.: `app.css`), com **plugin Vite** `@tailwindcss/vite` — alinhar ao `vite.config` do repositório.
- Preferir **classes utilitárias**; extrair padrões repetidos para **componentes Vue**; usar **`@apply`** só quando reduzir ruído real (a v4 incentiva composição utilitária no markup).
- **Tema / design tokens:** usar **variáveis CSS** e recursos da v4 (`@theme`, etc.) para cores, fontes e raios; evitar “valores mágicos” espalhados sem nome semântico quando o time definir tokens.
- **Tema claro / escuro (Fase 6):** modo por classe `dark` no `<html>`; chave `hire-wire-theme` no `localStorage`; toggles em `GuestLayout` e `AppLayout` via **`Components/ThemeToggle.vue`** e composable **`composables/useTheme.js`**. Preferir tokens globais (`bg-page`, `bg-surface`, `text-text`, `border-border`, `bg-primary`, …) em vez de paletas fixas (`slate-*`) para fundos e texto principal. Detalhe completo, checklist de novas telas e regra do script anti-FOUC: **[theme.spec.md](../theme.spec.md)** (na raiz de `docs/`).
- **Acessibilidade:** foco visível, `aria-*` em interativos customizados, rótulos em inputs.
- **Responsivo:** mobile-first (`sm:`, `md:`) coerente com o layout principal.
- Ao consultar exemplos na internet, **verificar se são para v3 ou v4** (há diferenças de configuração e de features).

---

## 10. Performance e bundles

- **Lazy load** de páginas pesadas com `defineAsyncComponent` ou equivalente na resolução Inertia, quando o ganho for mensurável.
- Listas longas: virtualização ou paginação **definida no backend**; evitar renderizar milhares de nós sem necessidade.
- Imagens: dimensões e formatos adequados; preferir assets via Vite.

---

## 11. Integração com API (Passport / JSON)

- Quando uma tela precisar **JSON puro** (ex.: cliente autenticado com token), isolar em **módulo de API** (axios com interceptors) em `services/` ou `api/`.
- **Não misturar** em um mesmo componente chamadas Inertia e REST sem organização — extrair serviço ou composable.

---

## 12. Testes (recomendado quando o front crescer)

- **Vitest** + **Vue Test Utils** para componentes e composables.
- Testes focados em **comportamento** (eventos, exibição condicional, erros de formulário simulados), não em snapshots frágeis de markup inteiro.
- E2E (Playwright/Cypress) para fluxos críticos opcionalmente na CI.

---

## 13. Checklist rápido (PR)

- [ ] Página Inertia correspondente à rota Laravel está no lugar acordado (`Pages/`)?
- [ ] Dados necessários vêm do **controller** como props (sem segunda fonte redundante)?
- [ ] Formulários usam **`useForm` / router** com tratamento de `errors`?
- [ ] Componentes reutilizáveis estão **desacoplados** de uma rota específica?
- [ ] Não há lógica de negócio duplicando invariantes que já existem no **Domain/Application** do backend?
- [ ] **Acessibilidade** básica atendida (labels, foco, botões reais)?
- [ ] Novas superfícies usam **tokens de tema** (`bg-page`, `text-text`, …) onde aplicável — ver [theme.spec.md](../theme.spec.md)?
- [ ] Build/local: `npm run build` / `npm run dev` sem erros relevantes?

---

*Documento vivo: atualizar caminhos (`resources/js` vs `src/`) e escolhas (TypeScript, Ziggy, Pinia) conforme o repositório evoluir. **Tailwind:** manter referência à **v4** e ao setup Vite do projeto.*
