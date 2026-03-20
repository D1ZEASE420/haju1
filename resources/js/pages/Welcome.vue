<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{ canRegister: boolean }>(),
    { canRegister: true }
);
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet" />
    </Head>

    <div class="relative min-h-screen bg-[#0a0c10] text-white overflow-hidden flex flex-col" style="font-family: 'DM Sans', sans-serif;">

        <!-- Ambient background orbs -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -left-40 h-[600px] w-[600px] rounded-full bg-blue-600/10 blur-[120px]"></div>
            <div class="absolute top-1/2 -right-60 h-[500px] w-[500px] rounded-full bg-indigo-500/8 blur-[100px]"></div>
            <div class="absolute -bottom-40 left-1/3 h-[400px] w-[400px] rounded-full bg-violet-600/8 blur-[100px]"></div>
        </div>

        <!-- Subtle grid overlay -->
        <div class="pointer-events-none absolute inset-0" style="background-image: radial-gradient(circle, rgba(255,255,255,0.03) 1px, transparent 1px); background-size: 40px 40px;"></div>

        <!-- Nav -->
        <header class="relative z-10 flex items-center justify-between px-8 py-6 max-w-6xl mx-auto w-full">
            <div class="flex items-center gap-2.5">
                <div class="h-8 w-8 rounded-lg bg-blue-500/20 border border-blue-500/30 flex items-center justify-center">
                    <svg class="h-4 w-4 text-blue-400" viewBox="0 0 40 42" fill="currentColor">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M17.2 5.633 8.6.855 0 5.633v26.51l16.2 9 16.2-9v-8.442l7.6-4.223V9.856l-8.6-4.777-8.6 4.777V18.3l-5.6 3.111V5.633ZM38 18.301l-5.6 3.11v-6.157l5.6-3.11V18.3Zm-1.06-7.856-5.54 3.078-5.54-3.079 5.54-3.078 5.54 3.079ZM24.8 18.3v-6.157l5.6 3.111v6.158L24.8 18.3Zm-1 1.732 5.54 3.078-13.14 7.302-5.54-3.078 13.14-7.3v-.002Zm-16.2 7.89 7.6 4.222V38.3L2 30.966V7.92l5.6 3.111v16.892ZM8.6 9.3 3.06 6.222 8.6 3.143l5.54 3.08L8.6 9.3Zm21.8 15.51-13.2 7.334V38.3l13.2-7.334v-6.156ZM9.6 11.034l5.6-3.11v14.6l-5.6 3.11v-14.6Z"/>
                    </svg>
                </div>
                <span class="font-semibold text-white/90 text-sm">Haju</span>
            </div>

            <nav class="flex items-center gap-2">
                <template v-if="$page.props.auth.user">
                    <Link
                        :href="dashboard()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white/10 border border-white/10 text-sm font-medium text-white/90 hover:bg-white/15 hover:border-white/20 transition-all"
                    >
                        Dashboard
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </Link>
                </template>
                <template v-else>
                    <Link
                        :href="login()"
                        class="px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white transition-colors"
                    >
                        Log in
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-500 text-sm font-medium text-white hover:bg-blue-400 transition-all shadow-lg shadow-blue-500/25"
                    >
                        Get started
                    </Link>
                </template>
            </nav>
        </header>

        <!-- Hero -->
        <main class="relative z-10 flex-1 flex flex-col items-center justify-center px-6 py-20 text-center max-w-4xl mx-auto w-full">

            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-blue-500/30 bg-blue-500/10 text-blue-300 text-xs font-medium mb-8">
                <span class="h-1.5 w-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                Laravel + Vue · Inertia
            </div>

            <h1 class="text-5xl sm:text-6xl md:text-7xl font-light tracking-tight mb-6 leading-[1.1]">
                Your personal<br>
                <span class="font-semibold bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">dashboard</span>
            </h1>

            <p class="text-lg text-white/50 max-w-xl mb-10 font-light leading-relaxed">
                Weather, maps, and blog — all in one clean, modern interface.
            </p>

            <div class="flex items-center gap-4 flex-wrap justify-center">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="inline-flex items-center gap-2.5 px-6 py-3 rounded-xl bg-blue-500 text-white font-medium hover:bg-blue-400 transition-all shadow-xl shadow-blue-500/30 text-sm"
                >
                    Open Dashboard
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </Link>
                <template v-else>
                    <Link
                        :href="register()"
                        class="inline-flex items-center gap-2.5 px-6 py-3 rounded-xl bg-blue-500 text-white font-medium hover:bg-blue-400 transition-all shadow-xl shadow-blue-500/30 text-sm"
                    >
                        Create account
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </Link>
                    <Link
                        :href="login()"
                        class="inline-flex items-center gap-2.5 px-6 py-3 rounded-xl border border-white/10 bg-white/5 text-white/80 font-medium hover:bg-white/10 hover:text-white transition-all text-sm"
                    >
                        Sign in
                    </Link>
                </template>
            </div>

            <!-- Feature pills -->
            <div class="flex flex-wrap justify-center gap-3 mt-16">
                <div v-for="f in ['🌤 Weather', '🗺️ Interactive Map', '📝 Blog', '🔒 Auth']" :key="f"
                    class="px-4 py-2 rounded-full border border-white/8 bg-white/4 text-white/50 text-sm">
                    {{ f }}
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="relative z-10 pb-8 text-center text-xs text-white/25">
            Built with Laravel 12 &amp; Vue 3
        </footer>
    </div>
</template>
