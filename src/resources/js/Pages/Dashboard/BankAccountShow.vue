<template>
    <AppLayout>
        <div class="space-y-6">
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold text-text">Conta bancária</h1>
                <p v-if="account" class="text-sm text-text-muted">
                    <span class="font-medium text-text">{{ typeLabel }}</span>
                    · saldo atual:
                    <span class="tabular-nums text-text">{{ formatBalance(account.balance) }}</span>
                </p>
            </div>

            <section
                v-if="account"
                id="editar-conta"
                class="scroll-mt-24 rounded-lg border border-border bg-surface p-4 shadow-sm dark:shadow-none"
            >
                <h2 class="text-sm font-semibold text-text">Atualizar saldo</h2>
                <p class="mt-1 text-xs text-text-muted">
                    Altera apenas o saldo registado nesta conta (valor não negativo).
                </p>
                <form class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end" @submit.prevent="submitBalance">
                    <div class="flex-1">
                        <label for="edit-balance" class="block text-xs font-medium text-text-muted"
                            >Novo saldo</label
                        >
                        <input
                            id="edit-balance"
                            v-model="balanceForm.balance"
                            type="text"
                            inputmode="decimal"
                            class="mt-1 w-full rounded-md border border-border bg-page px-3 py-2 text-sm text-text shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        />
                        <p
                            v-if="balanceForm.errors.balance"
                            class="mt-1 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ balanceForm.errors.balance }}
                        </p>
                    </div>
                    <button
                        type="submit"
                        class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-fg hover:opacity-90 disabled:opacity-60"
                        :disabled="balanceForm.processing"
                    >
                        Atualizar saldo
                    </button>
                </form>
            </section>

            <section v-if="account" class="rounded-lg border border-red-200 bg-page p-4 dark:border-red-900/50">
                <h2 class="text-sm font-semibold text-red-800 dark:text-red-300">Deletar conta</h2>
                <p class="mt-1 text-xs text-text-muted">
                    Desenja realmente remover esta conta. Esta ação não pode ser desfeita.
                </p>
                <button
                    type="button"
                    class="mt-3 rounded-md border border-red-300 px-3 py-1.5 text-sm font-medium text-red-700 hover:bg-red-50 dark:border-red-800 dark:text-red-300 dark:hover:bg-red-950/40"
                    @click="confirmDestroy"
                >
                    Deletar conta
                </button>
            </section>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    account: {
        type: Object,
        required: true,
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

const balanceForm = useForm({
    balance: props.account.balance,
});

watch(
    () => props.account.balance,
    (b) => {
        balanceForm.balance = b;
    },
);

function formatBalance(b) {
    const n = Number.parseFloat(b);
    if (Number.isNaN(n)) {
        return b;
    }
    return n.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function submitBalance() {
    balanceForm.patch(`/bank-accounts/${props.account.id}`, { preserveScroll: true });
}

function confirmDestroy() {
    if (
        !confirm(
            'Tem a certeza de que quer eliminar esta conta? O registo será apagado permanentemente.',
        )
    ) {
        return;
    }
    router.delete(`/bank-accounts/${props.account.id}`);
}
</script>
