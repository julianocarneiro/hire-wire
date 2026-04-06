<template>
    <Teleport to="body">
        <div
            v-if="open"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="presentation"
            @click.self="close"
        >
            <div class="fixed inset-0 bg-black/50 dark:bg-black/70" aria-hidden="true" />
            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="new-account-title"
                class="relative z-10 w-full max-w-md rounded-lg border border-border bg-surface p-6 shadow-lg"
                @keydown.escape.prevent="close"
            >
                <h2 id="new-account-title" class="text-lg font-semibold text-text">Nova conta</h2>
                <p class="mt-1 text-sm text-text-muted">
                    Escolha o tipo e, se quiser, o saldo inicial (predefinição: 0,00).
                </p>
                <form class="mt-4 space-y-4" @submit.prevent="submit">
                    <div>
                        <label for="account-type" class="block text-sm font-medium text-text">Tipo</label>
                        <select
                            id="account-type"
                            v-model="form.type"
                            class="mt-1 w-full rounded-md border border-border bg-page px-3 py-2 text-sm text-text shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            required
                        >
                            <option v-for="opt in accountTypeOptions" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </select>
                        <p v-if="form.errors.type" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.type }}
                        </p>
                    </div>
                    <div>
                        <label for="account-balance" class="block text-sm font-medium text-text"
                            >Saldo inicial</label
                        >
                        <input
                            id="account-balance"
                            v-model="form.balance"
                            type="text"
                            inputmode="decimal"
                            class="mt-1 w-full rounded-md border border-border bg-page px-3 py-2 text-sm text-text shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            autocomplete="off"
                        />
                        <p v-if="form.errors.balance" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.balance }}
                        </p>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button
                            type="button"
                            class="rounded-md border border-border px-3 py-1.5 text-sm font-medium text-text hover:bg-page"
                            @click="close"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="rounded-md bg-primary px-3 py-1.5 text-sm font-medium text-primary-fg hover:opacity-90 disabled:opacity-60"
                            :disabled="form.processing"
                        >
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:open']);

const page = usePage();

const accountTypeOptions = computed(() => page.props.accountTypeOptions ?? []);

const form = useForm({
    type: 'savings',
    balance: '0',
});

watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen) {
            return;
        }
        form.clearErrors();
        const first = accountTypeOptions.value[0]?.value ?? 'savings';
        form.reset();
        form.type = first;
        form.balance = '0';
    },
);

function close() {
    emit('update:open', false);
}

function submit() {
    form.post('/bank-accounts', {
        preserveScroll: true,
        onSuccess: () => {
            close();
        },
    });
}
</script>
