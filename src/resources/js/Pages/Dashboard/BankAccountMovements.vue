<template>
    <AppLayout>
        <div class="space-y-6">
            <BankAccountPageHeader title="Movimentações" :account="account">
                <template #actions>
                    <Link
                        v-if="account"
                        :href="`/bank-accounts/${account.id}`"
                        class="rounded-md border border-border bg-surface px-3 py-2 text-sm font-medium text-text shadow-sm hover:bg-page focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-page"
                    >
                        Detalhe da conta
                    </Link>
                </template>
            </BankAccountPageHeader>

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
                        <AccountMovementsTable :movements="movements" :current-balance="account.balance" />
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
import { Link, useForm } from '@inertiajs/vue3';
import AccountMovementsTable from '../../Components/AccountMovementsTable.vue';
import BankAccountPageHeader from '../../Components/BankAccountPageHeader.vue';
import { useAccountTabsThree } from '../../composables/useAccountTabs';
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

const { activeTab, tabDepositRef, tabListRef, tabCorrecaoRef, onTabKeydown } = useAccountTabsThree();

const depositForm = useForm({
    amount: '',
});

const adjustmentForm = useForm({});

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
