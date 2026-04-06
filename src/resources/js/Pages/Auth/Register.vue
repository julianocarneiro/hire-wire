<template>
    <GuestLayout>
        <div class="mx-auto w-full max-w-md space-y-8">
            <div>
                <h1
                    class="text-center text-2xl font-semibold tracking-tight text-text"
                >
                    Criar conta
                </h1>
                <p class="mt-2 text-center text-sm text-text-muted">
                    Preencha os dados para se registar.
                </p>
            </div>

            <form
                class="space-y-6 rounded-xl border border-border bg-surface p-6 shadow-sm"
                @submit.prevent="submit"
            >
                <div>
                    <label
                        for="name"
                        class="block text-sm font-medium text-text"
                        >Nome</label
                    >
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        autocomplete="name"
                        required
                        class="mt-1 block w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    />
                    <p
                        v-if="form.errors.name"
                        class="mt-1 text-sm text-red-600 dark:text-red-400"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>

                <div>
                    <label
                        for="cpf"
                        class="block text-sm font-medium text-text"
                        >CPF</label
                    >
                    <input
                        id="cpf"
                        v-model="form.cpf"
                        type="text"
                        autocomplete="off"
                        required
                        class="mt-1 block w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    />
                    <p
                        v-if="form.errors.cpf"
                        class="mt-1 text-sm text-red-600 dark:text-red-400"
                    >
                        {{ form.errors.cpf }}
                    </p>
                </div>

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
                        autocomplete="email"
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
                        autocomplete="new-password"
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

                <div>
                    <label
                        for="password_confirmation"
                        class="block text-sm font-medium text-text"
                        >Confirmar senha</label
                    >
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="mt-1 block w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    />
                </div>

                <button
                    type="submit"
                    class="flex w-full justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-primary-fg shadow hover:opacity-90 disabled:opacity-50"
                    :disabled="form.processing"
                >
                    Criar Conta
                </button>
            </form>

            <p class="text-center text-sm text-text-muted">
                <Link
                    href="/login"
                    class="font-medium text-text underline decoration-border underline-offset-2 hover:opacity-80"
                >
                    Já tenho uma conta
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>

<script setup>
import { Link, useForm } from "@inertiajs/vue3";
import GuestLayout from "../../Layouts/GuestLayout.vue";

const form = useForm({
    name: "",
    cpf: "",
    email: "",
    password: "",
    password_confirmation: "",
});

function submit() {
    form.post("/register", {
        preserveScroll: true,
    });
}
</script>
