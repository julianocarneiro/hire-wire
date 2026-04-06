<template>
    <AppLayout>
        <div class="space-y-2">
            <h1 class="text-xl font-semibold text-slate-900">Conta bancária</h1>
            <p v-if="account" class="text-sm text-slate-600">
                Tipo: {{ account.type }} · Saldo: {{ account.balance }}
            </p>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({
    account: {
        type: Object,
        required: true,
    },
});
</script>
