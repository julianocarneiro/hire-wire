<template>
    <AppLayout>
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-text">Dashboard</h1>
            <p v-if="user" class="text-text-muted">
                Olá, <span class="font-medium text-text">{{ user.name }}</span>
                ({{ user.email }}).
            </p>
            <p class="text-sm text-text-muted">
                Esta área está protegida por sessão web. Utilize «Sair» para terminar a sessão com segurança.
            </p>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({
    user: {
        type: Object,
        required: true,
    },
});
</script>
