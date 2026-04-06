<template>
    <div class="min-h-screen bg-page text-text">
        <div class="mx-auto flex min-h-screen max-w-6xl flex-col px-4 py-10 sm:px-6 lg:px-8">
            <div class="-mt-2 mb-6 flex shrink-0 justify-end">
                <ThemeToggle />
            </div>
            <slot />
        </div>
    </div>
</template>

<script setup>
import ThemeToggle from '../Components/ThemeToggle.vue';
</script>
