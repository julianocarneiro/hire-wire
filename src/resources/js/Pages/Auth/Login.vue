<template>
    <GuestLayout>
        <div class="mx-auto w-full max-w-md space-y-8">
            <div>
                <h1
                    class="text-center text-2xl font-semibold tracking-tight text-slate-900"
                >
                    Entrar
                </h1>
                <p class="mt-2 text-center text-sm text-slate-600">
                    Utilize o seu e-mail e senha para acessar.
                </p>
            </div>

            <form
                class="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                @submit.prevent="submit"
            >
                <div>
                    <label
                        for="email"
                        class="block text-sm font-medium text-slate-700"
                        >E-mail</label
                    >
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        autocomplete="username"
                        required
                        class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900"
                    />
                    <p
                        v-if="form.errors.email"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ form.errors.email }}
                    </p>
                </div>

                <div>
                    <label
                        for="password"
                        class="block text-sm font-medium text-slate-700"
                        >Senha</label
                    >
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900"
                    />
                    <p
                        v-if="form.errors.password"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ form.errors.password }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <input
                        id="remember"
                        v-model="form.remember"
                        type="checkbox"
                        class="rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                    />
                    <label for="remember" class="text-sm text-slate-700"
                        >Lembrar-me</label
                    >
                </div>

                <button
                    type="submit"
                    class="flex w-full justify-center rounded-md bg-slate-900 px-3 py-2 text-sm font-semibold text-white shadow hover:bg-slate-800 disabled:opacity-50"
                    :disabled="form.processing"
                >
                    Entrar
                </button>
            </form>

            <p class="text-center text-sm text-slate-600">
                Ainda não tem conta?<br />
                <Link
                    href="/register"
                    class="mt-1 inline-block font-medium text-slate-900 underline decoration-slate-400 underline-offset-2 hover:text-slate-700"
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
