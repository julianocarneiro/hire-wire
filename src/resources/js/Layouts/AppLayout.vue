<template>
    <div class="flex min-h-screen flex-col bg-page text-text">
        <header class="border-b border-border bg-surface">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:max-w-none lg:px-8">
                <div class="text-sm font-semibold text-text">Hire Wire</div>
                <div class="flex items-center gap-3">
                    <ThemeToggle />
                    <span v-if="user" class="hidden text-sm text-text-muted sm:inline">
                        {{ user.name }}
                    </span>
                    <button
                        type="button"
                        class="rounded-md bg-primary px-3 py-1.5 text-sm font-medium text-primary-fg hover:opacity-90"
                        @click="logout"
                    >
                        Sair
                    </button>
                </div>
            </div>
        </header>
        <div class="flex flex-1 min-h-0">
            <aside
                v-if="user"
                class="flex w-64 shrink-0 flex-col border-r border-border bg-surface/90 lg:w-72"
            >
                <div class="border-b border-border p-4">
                    <button
                        type="button"
                        class="w-full rounded-md bg-primary px-3 py-2 text-left text-sm font-medium text-primary-fg hover:opacity-90"
                        @click="newAccountOpen = true"
                    >
                        Nova conta
                    </button>
                </div>
                <nav class="flex-1 overflow-y-auto p-2" aria-label="Contas">
                    <p v-if="bankAccounts.length === 0" class="px-2 py-3 text-xs text-text-muted">
                        Ainda não tem contas. Utilize «Nova conta» para começar.
                    </p>
                    <ul v-else class="space-y-1">
                        <li v-for="acc in bankAccounts" :key="acc.id">
                            <Link
                                :href="`/bank-accounts/${acc.id}`"
                                class="block rounded-md px-3 py-2 text-sm transition-colors hover:bg-page"
                                :class="
                                    isActiveAccount(acc.id)
                                        ? 'bg-page font-medium text-text'
                                        : 'text-text-muted'
                                "
                            >
                                <span class="block truncate">{{ accountLabel(acc) }}</span>
                                <span class="block text-xs text-text-muted">{{ formatBalance(acc.balance) }}</span>
                            </Link>
                        </li>
                    </ul>
                </nav>
            </aside>
            <main class="min-w-0 flex-1 overflow-y-auto">
                <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
                    <slot />
                </div>
            </main>
        </div>
        <NewBankAccountModal v-model:open="newAccountOpen" />
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import ThemeToggle from '../Components/ThemeToggle.vue';
import NewBankAccountModal from '../Components/NewBankAccountModal.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const bankAccounts = computed(() => page.props.bankAccounts ?? []);
const newAccountOpen = ref(false);

const accountTypeOptions = computed(() => page.props.accountTypeOptions ?? []);

function accountLabel(acc) {
    return accountTypeOptions.value.find((o) => o.value === acc.type)?.label ?? acc.type;
}

function formatBalance(b) {
    const n = Number.parseFloat(b);
    if (Number.isNaN(n)) {
        return b;
    }
    return n.toLocaleString('pt-PT', { style: 'currency', currency: 'EUR' });
}

function isActiveAccount(id) {
    return page.url.startsWith(`/bank-accounts/${id}`);
}

function logout() {
    router.post('/logout');
}
</script>
