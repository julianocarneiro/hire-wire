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
                            <div
                                class="flex items-center gap-2 rounded-md px-2 py-2 transition-colors"
                                :class="
                                    isActiveAccount(acc.id)
                                        ? 'bg-page'
                                        : 'hover:bg-page'
                                "
                            >
                                <div class="min-w-0 flex-1">
                                    <span
                                        class="block truncate text-sm"
                                        :class="
                                            isActiveAccount(acc.id)
                                                ? 'font-medium text-text'
                                                : 'text-text-muted'
                                        "
                                    >
                                        {{ accountLabel(acc) }}
                                    </span>
                                    <span
                                        class="block text-xs tabular-nums text-text-muted"
                                    >
                                        {{ formatBalance(acc.balance) }}
                                    </span>
                                </div>
                                <div
                                    class="flex shrink-0 items-center gap-0.5 border-l border-border pl-2"
                                >
                                    <Link
                                        :href="`/bank-accounts/${acc.id}`"
                                        class="rounded-md p-1.5 text-primary hover:bg-page focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-page"
                                        title="Editar conta"
                                        aria-label="Editar conta"
                                    >
                                        <svg
                                            class="h-5 w-5"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                            aria-hidden="true"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"
                                            />
                                        </svg>
                                    </Link>
                                    <Link
                                        :href="`/bank-accounts/${acc.id}/movimentacoes`"
                                        class="rounded-md p-1.5 text-text-muted hover:bg-page hover:text-text focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-page"
                                        title="Movimentações"
                                        aria-label="Movimentações"
                                    >
                                        <svg
                                            class="h-5 w-5"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                            aria-hidden="true"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"
                                            />
                                        </svg>
                                    </Link>
                                </div>
                            </div>
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
    return n.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function isActiveAccount(id) {
    const path = page.url.split('?')[0];
    const base = `/bank-accounts/${id}`;
    return path === base || path.startsWith(`${base}/`);
}

function logout() {
    router.post('/logout');
}
</script>
