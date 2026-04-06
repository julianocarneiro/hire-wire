<template>
    <div class="min-h-screen bg-page text-text">
        <header class="border-b border-border bg-surface">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
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
        <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import ThemeToggle from '../Components/ThemeToggle.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);

function logout() {
    router.post('/logout');
}
</script>
