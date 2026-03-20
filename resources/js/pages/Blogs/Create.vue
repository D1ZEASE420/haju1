<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const form = useForm({
    title: '',
    description: '',
});

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Blogi', href: '/blogs' },
    { title: 'Uus postitus', href: '/blogs/create' },
];

function submit() {
    form.post('/blogs', { onSuccess: () => form.reset() });
}
</script>

<template>
    <Head title="Uus postitus" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-2xl mx-auto">

            <div class="mb-8">
                <h1 class="text-2xl font-semibold tracking-tight text-foreground">Uus postitus</h1>
                <p class="text-muted-foreground text-sm mt-1">Kirjuta ja avalda blogi postitus</p>
            </div>

            <div class="rounded-2xl border border-border bg-card p-6 shadow-sm">
                <form @submit.prevent="submit" class="flex flex-col gap-5">

                    <!-- Title -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">
                            Pealkiri <span class="text-destructive">*</span>
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            placeholder="Postituse pealkiri…"
                            class="px-4 py-3 rounded-xl border border-border bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary/50 transition-all"
                        />
                        <p v-if="form.errors.title" class="text-destructive text-xs flex items-center gap-1">
                            <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ form.errors.title }}
                        </p>
                    </div>

                    <!-- Description / Content -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">
                            Sisu <span class="text-destructive">*</span>
                        </label>
                        <textarea
                            v-model="form.description"
                            placeholder="Kirjuta siia oma postituse sisu…"
                            rows="12"
                            class="px-4 py-3 rounded-xl border border-border bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary/50 transition-all resize-none leading-relaxed"
                        ></textarea>
                        <p v-if="form.errors.description" class="text-destructive text-xs flex items-center gap-1">
                            <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-3 pt-2 border-t border-border">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-medium hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95 transition-all shadow-sm shadow-primary/20"
                        >
                            <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            {{ form.processing ? 'Avaldamine…' : 'Avalda postitus' }}
                        </button>
                        <Link
                            href="/blogs"
                            class="px-4 py-2.5 text-sm text-muted-foreground hover:text-foreground transition-colors"
                        >
                            Tühista
                        </Link>
                    </div>

                </form>
            </div>

        </div>
    </AppLayout>
</template>