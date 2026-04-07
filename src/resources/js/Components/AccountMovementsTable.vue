<template>
    <div>
        <div
            class="mb-4 rounded-md border border-border bg-page px-4 py-3"
            aria-live="polite"
        >
            <p class="text-xs font-medium text-text-muted">Saldo atual</p>
            <p class="text-2xl font-semibold tabular-nums text-text">
                {{ formatBalance(currentBalance) }}
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
                            {{ m.balance_after != null ? formatBalance(m.balance_after) : '—' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { useBankAccountLabels } from '../composables/useBankAccountLabels';
import { useCurrencyFormat } from '../composables/useCurrencyFormat';

defineProps({
    movements: {
        type: Array,
        required: true,
    },
    currentBalance: {
        type: String,
        required: true,
    },
});

const { formatBalance, formatDate } = useCurrencyFormat();
const { movementTypeLabel } = useBankAccountLabels();
</script>
