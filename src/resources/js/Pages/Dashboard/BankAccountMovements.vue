<template>
    <AppLayout>
        <div class="space-y-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="space-y-1">
                    <h1 class="text-2xl font-semibold text-text">Movimentações</h1>
                    <p v-if="account" class="text-sm text-text-muted">
                        <span class="font-medium text-text">{{ typeLabel }}</span>
                        · saldo atual:
                        <span class="tabular-nums text-text">{{ formatBalance(account.balance) }}</span>
                    </p>
                </div>
                <div class="flex shrink-0 flex-wrap gap-2">
                    <Link
                        v-if="account"
                        :href="`/bank-accounts/${account.id}`"
                        class="rounded-md border border-border bg-surface px-3 py-2 text-sm font-medium text-text shadow-sm hover:bg-page focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-page"
                    >
                        Detalhe da conta
                    </Link>
                </div>
            </div>

            <div
                v-if="account"
                class="rounded-lg border border-border bg-surface shadow-sm dark:shadow-none"
            >
                <div
                    class="flex flex-wrap gap-1 border-b border-border p-2"
                    role="tablist"
                    aria-label="Secções de movimentações"
                    @keydown="onTabKeydown"
                >
                    <button
                        id="tab-deposito"
                        ref="tabDepositRef"
                        type="button"
                        role="tab"
                        :tabindex="activeTab === 0 ? 0 : -1"
                        :aria-selected="activeTab === 0"
                        aria-controls="panel-deposito"
                        class="rounded-md px-3 py-2 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-surface"
                        :class="
                            activeTab === 0
                                ? 'bg-page text-text shadow-sm'
                                : 'text-text-muted hover:bg-page hover:text-text'
                        "
                        @click="activeTab = 0"
                    >
                        Depósito
                    </button>
                    <button
                        id="tab-lista"
                        ref="tabListRef"
                        type="button"
                        role="tab"
                        :tabindex="activeTab === 1 ? 0 : -1"
                        :aria-selected="activeTab === 1"
                        aria-controls="panel-lista"
                        class="rounded-md px-3 py-2 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-surface"
                        :class="
                            activeTab === 1
                                ? 'bg-page text-text shadow-sm'
                                : 'text-text-muted hover:bg-page hover:text-text'
                        "
                        @click="activeTab = 1"
                    >
                        Movimentação + saldo
                    </button>
                    <button
                        id="tab-correcao"
                        ref="tabCorrecaoRef"
                        type="button"
                        role="tab"
                        :tabindex="activeTab === 2 ? 0 : -1"
                        :aria-selected="activeTab === 2"
                        aria-controls="panel-correcao"
                        class="rounded-md px-3 py-2 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-surface"
                        :class="
                            activeTab === 2
                                ? 'bg-page text-text shadow-sm'
                                : 'text-text-muted hover:bg-page hover:text-text'
                        "
                        @click="activeTab = 2"
                    >
                        Correção monetária
                    </button>
                </div>

                <div class="p-6">
                    <div
                        v-show="activeTab === 0"
                        id="panel-deposito"
                        role="tabpanel"
                        aria-labelledby="tab-deposito"
                        tabindex="0"
                    >
                        <h2 class="text-sm font-semibold text-text">Registar depósito</h2>
                        <p class="mt-1 text-xs text-text-muted">
                            O valor indicado é creditado na conta. Contas
                            <span class="font-medium text-text">corrente</span> e
                            <span class="font-medium text-text">investimentos</span> recebem um bónus de R$&nbsp;0,50
                            sobre o valor depositado (simulação descrita nas regras do sistema).
                        </p>
                        <form class="mt-4 max-w-md space-y-3" @submit.prevent="submitDeposit">
                            <div>
                                <label for="deposit-amount" class="block text-xs font-medium text-text-muted"
                                    >Valor (R$)</label
                                >
                                <input
                                    id="deposit-amount"
                                    v-model="depositForm.amount"
                                    type="text"
                                    inputmode="decimal"
                                    autocomplete="off"
                                    class="mt-1 w-full rounded-md border border-border bg-page px-3 py-2 text-sm text-text shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                />
                                <p
                                    v-if="depositForm.errors.amount"
                                    class="mt-1 text-sm text-red-600 dark:text-red-400"
                                >
                                    {{ depositForm.errors.amount }}
                                </p>
                            </div>
                            <button
                                type="submit"
                                class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-fg hover:opacity-90 disabled:opacity-60"
                                :disabled="depositForm.processing"
                            >
                                Registar depósito
                            </button>
                        </form>
                    </div>

                    <div
                        v-show="activeTab === 1"
                        id="panel-lista"
                        role="tabpanel"
                        aria-labelledby="tab-lista"
                        tabindex="0"
                    >
                        <div
                            class="mb-4 rounded-md border border-border bg-page px-4 py-3"
                            aria-live="polite"
                        >
                            <p class="text-xs font-medium text-text-muted">Saldo atual</p>
                            <p class="text-2xl font-semibold tabular-nums text-text">
                                {{ formatBalance(account.balance) }}
                            </p>
                        </div>
                        <h2 class="text-sm font-semibold text-text">Histórico</h2>
                        <p v-if="movements.length === 0" class="mt-2 text-sm text-text-muted">
                            Ainda não há movimentações registadas para esta conta.
                        </p>
                        <div v-else class="mt-3 overflow-x-auto">
                            <table class="w-full min-w-[28rem] border-collapse text-left text-sm">
                                <thead>
                                    <tr class="border-b border-border text-xs text-text-muted">
                                        <th class="py-2 pr-4 font-medium">Data</th>
                                        <th class="py-2 pr-4 font-medium">Tipo</th>
                                        <th class="py-2 pr-4 font-medium text-right">Valor</th>
                                        <th class="py-2 font-medium text-right">Saldo após</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="m in movements"
                                        :key="m.id"
                                        class="border-b border-border/80 text-text"
                                    >
                                        <td class="py-2 pr-4 tabular-nums text-text-muted">
                                            {{ formatDate(m.created_at) }}
                                        </td>
                                        <td class="py-2 pr-4">{{ movementTypeLabel(m.type) }}</td>
                                        <td class="py-2 pr-4 text-right tabular-nums">
                                            {{ formatBalance(m.amount) }}
                                        </td>
                                        <td class="py-2 text-right tabular-nums text-text-muted">
                                            {{
                                                m.balance_after != null
                                                    ? formatBalance(m.balance_after)
                                                    : '—'
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div
                        v-show="activeTab === 2"
                        id="panel-correcao"
                        role="tabpanel"
                        aria-labelledby="tab-correcao"
                        tabindex="0"
                    >
                        <h2 class="text-sm font-semibold text-text">Correção monetária</h2>
                        <p class="mt-2 text-sm text-text-muted">
                            Simula a aplicação da <strong class="font-medium text-text">correção mensal</strong> definida
                            para o tipo desta conta (percentual sobre o saldo atual). Pode utilizar este fluxo várias
                            vezes como exercício; num cenário real a correção seria agendada ao fim de cada mês.
                        </p>
                        <button
                            type="button"
                            class="mt-4 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-fg hover:opacity-90 disabled:opacity-60"
                            :disabled="adjustmentForm.processing"
                            @click="confirmAdjustment"
                        >
                            Aplicar correção monetária
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, nextTick, ref } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    account: {
        type: Object,
        required: true,
    },
    movements: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const accountTypeOptions = computed(() => page.props.accountTypeOptions ?? []);

const typeLabel = computed(() => {
    const t = props.account?.type;
    if (!t) {
        return '';
    }
    return accountTypeOptions.value.find((o) => o.value === t)?.label ?? t;
});

const activeTab = ref(0);
const tabDepositRef = ref(null);
const tabListRef = ref(null);
const tabCorrecaoRef = ref(null);

const tabRefs = [tabDepositRef, tabListRef, tabCorrecaoRef];

function onTabKeydown(e) {
    const key = e.key;
    if (key !== 'ArrowRight' && key !== 'ArrowLeft' && key !== 'Home' && key !== 'End') {
        return;
    }
    e.preventDefault();
    const len = 3;
    let next = activeTab.value;
    if (key === 'ArrowRight') {
        next = (activeTab.value + 1) % len;
    } else if (key === 'ArrowLeft') {
        next = (activeTab.value + len - 1) % len;
    } else if (key === 'Home') {
        next = 0;
    } else {
        next = len - 1;
    }
    activeTab.value = next;
    nextTick(() => {
        tabRefs[next]?.value?.focus();
    });
}

const depositForm = useForm({
    amount: '',
});

const adjustmentForm = useForm({});

function formatBalance(b) {
    const n = Number.parseFloat(b);
    if (Number.isNaN(n)) {
        return b;
    }
    return n.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function formatDate(iso) {
    if (!iso) {
        return '';
    }
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) {
        return iso;
    }
    return d.toLocaleString('pt-BR', {
        dateStyle: 'short',
        timeStyle: 'short',
    });
}

function movementTypeLabel(type) {
    if (type === 'deposit') {
        return 'Depósito';
    }
    if (type === 'monthly_adjustment') {
        return 'Correção monetária';
    }
    return type;
}

function submitDeposit() {
    depositForm.post(`/bank-accounts/${props.account.id}/deposito`, {
        preserveScroll: true,
        onSuccess: () => {
            depositForm.reset('amount');
        },
    });
}

function confirmAdjustment() {
    if (
        !confirm(
            'Aplicar a correção monetária ao saldo atual desta conta? Esta operação será registada no histórico.',
        )
    ) {
        return;
    }
    adjustmentForm.post(`/bank-accounts/${props.account.id}/correcao-monetaria`, { preserveScroll: true });
}
</script>
