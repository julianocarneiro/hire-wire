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
                <Link
                    v-if="account"
                    :href="`/bank-accounts/${account.id}#editar-conta`"
                    class="shrink-0 rounded-md border border-border bg-surface px-3 py-2 text-sm font-medium text-text shadow-sm hover:bg-page focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-page"
                >
                    Editar conta
                </Link>
            </div>

            <section
                v-if="account"
                class="rounded-lg border border-border bg-surface p-6 shadow-sm dark:shadow-none"
            >
                <p class="text-sm text-text-muted">
                    Ainda não há movimentações registadas para esta conta. Esta lista será preenchida quando o
                    módulo de transações estiver disponível.
                </p>
            </section>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
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

function formatBalance(b) {
    const n = Number.parseFloat(b);
    if (Number.isNaN(n)) {
        return b;
    }
    return n.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}
</script>
