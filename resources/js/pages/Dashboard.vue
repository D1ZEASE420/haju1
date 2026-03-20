<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Map from '@/pages/Map.vue';
import axios from 'axios';

const markers = ref([]);

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
];

onMounted(async () => {
    try {
        const res = await axios.get('/map/markers');
        markers.value = res.data;
    } catch (err) {
        console.error('Failed to load markers', err);
    }
});
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-5 p-5 overflow-x-auto">

            <!-- Quick Access Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">

                <!-- Weather Card -->
                <Link
                    href="/weather/search?city=tallinn"
                    class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 transition-all duration-300 hover:shadow-lg hover:shadow-primary/10 hover:-translate-y-0.5 hover:border-primary/30"
                >
                    <div class="absolute inset-0 bg-gradient-to-br from-sky-500/8 to-blue-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-sky-500/12 text-2xl border border-sky-500/20">🌤</span>
                            <svg class="h-4 w-4 text-muted-foreground group-hover:text-primary transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-foreground">Weather</h3>
                            <p class="text-sm text-muted-foreground mt-0.5">Check current conditions</p>
                        </div>
                    </div>
                </Link>

                <!-- Blog Card -->
                <Link
                    href="/blogs"
                    class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 transition-all duration-300 hover:shadow-lg hover:shadow-primary/10 hover:-translate-y-0.5 hover:border-primary/30"
                >
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/8 to-teal-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-500/12 text-2xl border border-emerald-500/20">📝</span>
                            <svg class="h-4 w-4 text-muted-foreground group-hover:text-primary transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-foreground">Blog</h3>
                            <p class="text-sm text-muted-foreground mt-0.5">Write and share posts</p>
                        </div>
                    </div>
                </Link>

                <!-- Shop Card -->
                <Link
                    href="/shop"
                    class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 transition-all duration-300 hover:shadow-lg hover:shadow-primary/10 hover:-translate-y-0.5 hover:border-primary/30"
                >
                    <div class="absolute inset-0 bg-gradient-to-br from-orange-500/8 to-amber-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-orange-500/12 text-2xl border border-orange-500/20">🛍️</span>
                            <svg class="h-4 w-4 text-muted-foreground group-hover:text-primary transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-foreground">Pood</h3>
                            <p class="text-sm text-muted-foreground mt-0.5">Sirvi ja osta tooteid</p>
                        </div>
                    </div>
                </Link>

                <!-- Map stats card -->
                <div class="relative overflow-hidden rounded-2xl border border-border bg-card p-6">
                    <div class="absolute inset-0 bg-gradient-to-br from-violet-500/5 to-purple-600/3"></div>
                    <div class="relative flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-500/12 text-2xl border border-violet-500/20">🗺️</span>
                            <span class="text-xs font-medium text-muted-foreground bg-muted px-2 py-1 rounded-md">
                                {{ markers.length }} pins
                            </span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-foreground">Map</h3>
                            <p class="text-sm text-muted-foreground mt-0.5">Click the map to add markers</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Map Section -->
            <div class="flex-1 min-h-[70vh] rounded-2xl border border-border overflow-hidden shadow-sm">
                <Map :markers="markers" />
            </div>

        </div>
    </AppLayout>
</template>