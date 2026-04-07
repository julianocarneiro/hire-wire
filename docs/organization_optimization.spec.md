# Especificação: Fase 9 — Organização, otimização, componentização e mais testes

Documento de referência para **revisão estrutural, performance, reutilização no front-end e expansão de testes**, sem alterar o comportamento funcional acordado nas fases anteriores. Alinha-se a [tasks.md](tasks.md) (Fase 9), a [paterns](paterns/), ao [README.md](../README.md) e, no front-end, a [theme.spec.md](theme.spec.md) e [front-end_vue3.md](paterns/front-end_vue3.md).

---

## 1. Objetivo

- **Organizar** código e pastas de forma previsível, alinhada às convenções já documentadas no projeto.
- **Otimizar** apenas onde houver evidência (consultas, payload Inertia, bundle) — evitar otimização prematura.
- **Componentizar** a UI Vue e extrair composables para reduzir duplicação entre ecrãs de contas e movimentações.
- **Aumentar a cobertura e a confiança** com testes adicionais (feature, unit/domínio e, opcionalmente, browser ou Vitest).

### 1.1 Princípios

| Princípio | Significado |
|-----------|-------------|
| **Sem regressão funcional** | Refactors não mudam regras de negócio nem contratos HTTP públicos sem decisão explícita. |
| **Mudanças incrementais** | PRs pequenos e revisáveis (ex.: primeiro extrair componente X, depois composable Y). |
| **Medir antes de cachear** | Cache só entra com critério de invalidação e prova de ganho. |

---

## 2. Escopo e fora de escopo

### 2.1 Dentro do escopo

- Reorganização de ficheiros, namespaces e documentação de responsabilidades.
- Ajustes de consultas, índices e carregamento de relações (N+1).
- Redução de payload Inertia e lazy-loading de páginas quando fizer sentido.
- Novos componentes Vue, composables e testes listados na secção 6.

### 2.2 Fora de escopo (salvo pedido explícito)

- Novas features de produto não descritas em `tasks.md` ou specs de fases anteriores.
- Troca de stack (ORM, Inertia, Vite) ou grandes migrações arquiteturais sem RFC breve no repositório.
- Otimizações que obscureçam o domínio (ex.: lógica crítica só no front-end).

---

## 3. Organização

### 3.1 Backend (PHP)

- Rever **estrutura de pastas** (`App\Domain`, `App\Application`, `App\Infrastructure`, `Http`) e **nomes de ficheiros** face a [paterns](paterns/) e ao checklist de revisão da Fase 2.
- **Rotas e controllers:** agrupar por recurso; se um controller concentrar demasiadas ações (ex.: contas + movimentações), **extrair** controller dedicado (ex.: movimentações) mantendo nomes de rota e URLs estáveis.
- **PHPDoc breve** em classes públicas de aplicação/HTTP quando a responsabilidade não for óbvia pelo nome.

### 3.2 Front-end (Vue / Inertia)

| Local | Uso |
|-------|-----|
| `resources/js/Pages/` | Ecrãs Inertia (uma rota ≈ uma página ou sub-rotas claras). |
| `resources/js/Components/` | UI reutilizável (modais, tabelas, tabs, cartões de resumo). |
| `resources/js/composables/` | Estado e lógica partilhada (formatação, labels, estado de abas). |

- Extrair **constantes partilhadas** (etiquetas PT, chaves de storage já definidas em specs, slugs de abas) para módulos únicos quando houver duplicação entre 2+ ficheiros.

### 3.3 Documentação

- Atualizar [README.md](../README.md) ou [docs/infra.md](infra.md) se o fluxo de desenvolvimento, comandos ou estrutura mudarem de forma perceptível para novos contribuidores.
- Manter [tasks.md](tasks.md) sincronizado: marcar itens concluídos e apontar para esta spec na Fase 9.

---

## 4. Otimização

### 4.1 Base de dados

- Rever **repositórios Eloquent** usados em listagens quentes (contas, movimentos): garantir `where` por `user_id` / `bank_account_id` e ordem por `created_at` quando aplicável.
- **Índices:** confirmar índices em FKs e colunas de filtro/ordenação frequentes (`user_id`, `bank_account_id`, `created_at`); adicionar migration apenas quando a query plan ou volume justificarem.
- **N+1:** usar `with()` / eager loading onde listas carreguem relações; testar com `Model::preventLazyLoading()` em ambiente de teste se o projeto já adoptar o padrão.

**Decisão (implementação Fase 9):** `EloquentAccountMovementRepository::listForAccount` não itera relações Eloquent por linha; não foi activado `Model::preventLazyLoading()` globalmente no `TestCase` (evitar ruído até surgirem listagens com lazy load real). Foi adicionada migration com índice composto `(bank_account_id, created_at)` em `account_movements` para alinhar à ordenação da listagem.

### 4.2 HTTP e Inertia

- **Props:** enviar o mínimo necessário por página; evitar duplicar no page props o que já está em **dados partilhados** (`HandleInertiaRequests`) sem necessidade.
- **Redirects:** após mutações, preferir `back()` ou redirect explícito já usado no projeto, sem payloads redundantes na sessão flash.

**Decisão (implementação Fase 9):** as páginas `bank-accounts.show` e `bank-accounts.movements` mantêm a prop Inertia `account` com saldo atualizado após `back()`, em vez de depender só de `bankAccounts` partilhado na sidebar — evita saldo desatualizado no detalhe após depósito/correção/PATCH.

### 4.3 Front (Vite)

- Executar `npm run build` e registar tamanho de chunks relevantes (baseline opcional no PR ou comentário interno).
- Se o número de páginas Inertia crescer de forma significativa, avaliar **lazy-loading** no `resolve` do Inertia (import dinâmico por página).

**Baseline (Fase 9):** `npm run build` — chunk principal `app-*.js` ~211 kB (~73 kB gzip); páginas em chunks separados via `import.meta.glob` em `resources/js/app.js`.

### 4.4 Cache no backend

- Permitido **apenas** com: (1) métrica ou cenário claro de ganho; (2) chave e TTL definidos; (3) **estratégia de invalidação** documentada no código ou nesta spec.
- Não introduzir cache de leitura “por precaução” em fluxos já rápidos e pouco frequentes.

---

## 5. Componentização (Vue)

### 5.1 Componentes candidatos (exemplos)

Extrair quando a mesma marcação ou comportamento aparecer em mais de um sítio ou quando um `.vue` exceder ~200–300 linhas sem ganho de legibilidade:

- **Cabeçalho de conta:** tipo, identificação e saldo (tokens de tema).
- **Tabela ou lista de movimentos:** colunas, empty state, alinhamento a `dark:`.
- **Tabs acessíveis:** padrão ARIA/teclado alinhado à Fase 8; opcionalmente **componente genérico** `Tabs` / `TabList` / `TabPanel` para reutilização futura.
- **Botões de ação** (primário/secundário/destrutivo) coerentes com tokens existentes.

### 5.2 Composables candidatos (exemplos)

| Composable | Responsabilidade |
|------------|-------------------|
| `useBankAccountLabels` | Mapa tipo → etiqueta PT (ou reexport de constantes do backend). |
| `useCurrencyFormat` | Formatação BRL (ou moeda única do produto) centralizada. |
| `useAccountTabs` | Estado da aba ativa, sincronização com URL se existir, teclado. |

Regras: sem efeitos colaterais escondidos; nomes alinhados aos já usados em `useTheme.js`.

---

## 6. Mais testes

### 6.1 Feature (PHPUnit / HTTP + Inertia)

- Fluxo **completo** na mesma conta: depósito(s) + correção monetária; listagem de movimentos com contagem e ordem coerentes após várias operações.
- **Não autenticado:** `POST` (e rotas mutáveis equivalentes) para depósito/correção → 401/302 conforme convenção do projeto.
- **Throttle:** pelo menos um teste smoke que confirme que o middleware de throttle está aplicado nas rotas sensíveis (sem depender de tempos frágeis, se possível).

### 6.2 Unit / integração

- Serviço ou repositório de movimentos com **dependências injetadas**; usar mock/stub do repositório de contas quando isolar regras de orquestração.
- Manter testes de domínio existentes atualizados; adicionar casos-limite para `Money`, políticas de depósito/correção, se novas regras surgirem.

### 6.3 Opcional

- **Browser:** Playwright ou Laravel Dusk — happy path: login → criar conta → depósito (e eventualmente correção).
- **Vitest:** composables puros (ex.: formatação, resolução de labels) sem montar toda a app.

---

## 7. Critérios de aceitação (checklist)

- [x] Estrutura de pastas e naming revisados; desvios documentados em comentário de PR ou nesta spec.
- [x] Controllers e rotas legíveis por recurso; extração de controller dedicado se o ficheiro principal estiver sobrecarregado.
- [x] Front: convenção `Pages/` vs `Components/` respeitada; duplicação óbvia reduzida via componentes/composables.
- [x] Consultas críticas sem N+1 evidente; índices alinhados a filtros/ordenação usados.
- [x] Build front analisado; lazy-loading apenas se justificado (`import.meta.glob` já em uso).
- [x] Novos testes (feature + unit/domínio conforme secção 6) passam no CI local (`php artisan test`, `npm run build` / `npm test` se aplicável).
- [x] README/infra/tasks atualizados quando o fluxo ou a estrutura mudarem (tasks + esta spec; README/infra inalterados — sem mudança de fluxo de desenvolvimento).

---

## 8. Referências

- [tasks.md](tasks.md) — Fase 9 (lista resumida).
- [account_movements.spec.md](account_movements.spec.md) — UI e domínio de movimentações.
- [paterns](paterns/) — camadas, revisão e front-end Vue 3.
