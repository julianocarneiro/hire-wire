<template>
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold text-text">{{ title }}</h1>
            <p v-if="account" class="text-sm text-text-muted">
                <span class="font-medium text-text">{{ typeLabel }}</span>
                · saldo atual:
                <span class="tabular-nums text-text">{{ formatBalance(account.balance) }}</span>
            </p>
        </div>
        <div v-if="$slots.actions" class="flex shrink-0 flex-wrap gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useBankAccountLabels } from '../composables/useBankAccountLabels';
import { useCurrencyFormat } from '../composables/useCurrencyFormat';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    account: {
        type: Object,
        required: true,
    },
});

const { typeLabelFor } = useBankAccountLabels();
const { formatBalance } = useCurrencyFormat();

const typeLabel = computed(() => typeLabelFor(props.account?.type));
</script>
