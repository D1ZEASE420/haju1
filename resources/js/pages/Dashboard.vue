<script setup>
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Map from '@/pages/Map.vue';

const props = defineProps({
    markers: Array
});

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

            <!-- Ülemine grid kaardid -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">

                <!-- Weather kaart -->
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

            <!-- Alumine suur kaart → Map -->
            <div class="relative min-h-[80vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border overflow-hidden">
                <Map :markers="markers" />
            </div>

        </div>
    </AppLayout>
</template>