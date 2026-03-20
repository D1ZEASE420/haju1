<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

const page = usePage();
const name = page.props.name;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="relative min-h-dvh grid lg:grid-cols-2">

        <!-- Left panel -->
        <div class="relative hidden lg:flex flex-col bg-[#0a0c10] p-12 overflow-hidden">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -top-20 -left-20 h-[400px] w-[400px] rounded-full bg-blue-600/15 blur-[100px]"></div>
                <div class="absolute bottom-0 right-0 h-[300px] w-[300px] rounded-full bg-indigo-500/10 blur-[80px]"></div>
                <div class="absolute inset-0" style="background-image: radial-gradient(circle, rgba(255,255,255,0.02) 1px, transparent 1px); background-size: 32px 32px;"></div>
            </div>
            <Link :href="home()" class="relative z-10 flex items-center gap-2.5">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/20 border border-blue-500/30">
                    <AppLogoIcon class="size-4 fill-current text-blue-400" />
                </div>
                <span class="font-semibold text-white/90 text-sm">{{ name }}</span>
            </Link>
            <div class="relative z-10 mt-auto">
                <blockquote class="text-white/60 text-sm font-light leading-relaxed max-w-xs">
                    "A clean, modern dashboard for weather, maps, and blogging."
                </blockquote>
            </div>
        </div>

        <!-- Right panel -->
        <div class="relative flex flex-col items-center justify-center px-6 py-12 bg-background">
            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                <div class="absolute top-0 right-0 h-[400px] w-[400px] rounded-full bg-primary/4 blur-[100px]"></div>
            </div>
            <div class="relative w-full max-w-sm">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground" v-if="title">{{ title }}</h1>
                    <p class="text-sm text-muted-foreground mt-2" v-if="description">{{ description }}</p>
                </div>
                <div class="rounded-2xl border border-border bg-card shadow-sm p-8">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
