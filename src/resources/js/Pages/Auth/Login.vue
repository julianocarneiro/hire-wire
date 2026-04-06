<template>
    <GuestLayout>
        <div class="mx-auto w-full max-w-md space-y-8">
            <div>
                <h1
                    class="text-center text-2xl font-semibold tracking-tight text-text"
                >
                    Entrar
                </h1>
                <p class="mt-2 text-center text-sm text-text-muted">
                    Utilize o seu e-mail e senha para acessar.
                </p>
            </div>

            <form
                class="space-y-6 rounded-xl border border-border bg-surface p-6 shadow-sm"
                @submit.prevent="submit"
            >
                <div>
                    <label
                        for="email"
                        class="block text-sm font-medium text-text"
                        >E-mail</label
                    >
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        autocomplete="username"
                        required
                        class="mt-1 block w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    />
                    <p
                        v-if="form.errors.email"
                        class="mt-1 text-sm text-red-600 dark:text-red-400"
                    >
                        {{ form.errors.email }}
                    </p>
                </div>

                <div>
                    <label
                        for="password"
                        class="block text-sm font-medium text-text"
                        >Senha</label
                    >
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="mt-1 block w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    />
                    <p
                        v-if="form.errors.password"
                        class="mt-1 text-sm text-red-600 dark:text-red-400"
                    >
                        {{ form.errors.password }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <input
                        id="remember"
                        v-model="form.remember"
                        type="checkbox"
                        class="rounded border-border text-primary focus:ring-primary"
                    />
                    <label for="remember" class="text-sm text-text"
                        >Lembrar-me</label
                    >
                </div>

                <button
                    type="submit"
                    class="flex w-full justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-primary-fg shadow hover:opacity-90 disabled:opacity-50"
                    :disabled="form.processing"
                >
                    Entrar
                </button>
            </form>

            <p class="text-center text-sm text-text-muted">
                Ainda não tem conta?<br />
                <Link
                    href="/register"
                    class="mt-1 inline-block font-medium text-text underline decoration-border underline-offset-2 hover:opacity-80"
                >
                    Criar conta
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>

<script setup>
import { Link, useForm } from "@inertiajs/vue3";
import GuestLayout from "../../Layouts/GuestLayout.vue";

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

function submit() {
    form.post("/login", {
        preserveScroll: true,
    });
}
</script>
