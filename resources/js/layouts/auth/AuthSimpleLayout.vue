<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="relative min-h-svh flex flex-col items-center justify-center bg-background px-4 py-12">

        <!-- Ambient glow -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[500px] w-[500px] rounded-full bg-primary/6 blur-[100px]"></div>
        </div>

        <div class="relative w-full max-w-sm">

            <!-- Logo -->
            <div class="flex justify-center mb-8">
                <Link :href="home()" class="flex items-center gap-2.5 group">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 border border-primary/20 group-hover:border-primary/40 transition-colors">
                        <AppLogoIcon class="size-5 fill-current text-primary" />
                    </div>
                    <span class="font-semibold text-foreground">Haju</span>
                </Link>
            </div>

            <!-- Heading -->
            <div class="text-center mb-8">
                <h1 class="text-2xl font-semibold tracking-tight text-foreground">{{ title }}</h1>
                <p class="text-sm text-muted-foreground mt-2">{{ description }}</p>
            </div>

            <!-- Card -->
            <div class="rounded-2xl border border-border bg-card shadow-sm p-8">
                <slot />
            </div>

        </div>
    </div>
</template>
