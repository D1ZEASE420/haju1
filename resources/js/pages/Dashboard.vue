<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import Map from '@/pages/Map.vue';
import axios from 'axios';

const markers = ref([]); // will store markers from backend

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
];

// Load markers from backend
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
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

            <!-- Top grid cards -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">

                <!-- Weather card -->
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border cursor-pointer hover:shadow-lg transition-transform transform hover:scale-105"
                >
                    <a
                        href="/weather/search?city=tallinn"
                        class="flex h-full w-full flex-col items-center justify-center gap-2 bg-blue-50 dark:bg-blue-900 text-gray-900 dark:text-white"
                    >
                        <span class="text-4xl">🌤</span>
                        <span class="text-lg font-semibold">Weather</span>
                    </a>
                </div>

                <!-- Placeholder 1 -->
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>

                <!-- Placeholder 2 -->
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>

            </div>

            <!-- Map Section -->
            <div class="relative min-h-[80vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border overflow-hidden mt-4">
                <Map :markers="markers" />
            </div>

        </div>
    </AppLayout>
</template>