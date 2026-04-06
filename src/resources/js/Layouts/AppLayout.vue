<template>
    <div class="min-h-screen bg-slate-50 text-slate-900">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <div class="text-sm font-semibold text-slate-800">Hire Wire</div>
                <div class="flex items-center gap-3">
                    <span v-if="user" class="hidden text-sm text-slate-600 sm:inline">
                        {{ user.name }}
                    </span>
                    <button
                        type="button"
                        class="rounded-md bg-slate-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-800"
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

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);

function logout() {
    router.post('/logout');
}
</script>
