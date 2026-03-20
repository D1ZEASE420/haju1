<script setup>
import { ref, computed } from 'vue'
import { router, usePage, Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const city = ref('')
const page = usePage()

const weather = computed(() => page.props.weather)
const hasWeather = computed(() => weather.value && weather.value.cod === 200)
const hasError = computed(() => weather.value && weather.value.cod !== 200)

const search = () => {
    if (!city.value.trim()) return
    router.get('/weather/search', { city: city.value }, { replace: true })
}

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Weather', href: '/weather' },
]

// Map weather condition codes to gradient themes
const weatherTheme = computed(() => {
    if (!hasWeather.value) return { from: 'from-slate-500/20', to: 'to-slate-600/10', icon: '🌡️' }
    const id = weather.value?.weather?.[0]?.id
    if (id >= 200 && id < 300) return { from: 'from-slate-600/30', to: 'to-gray-700/20', icon: '⛈️' }
    if (id >= 300 && id < 500) return { from: 'from-blue-400/20', to: 'to-slate-500/10', icon: '🌧️' }
    if (id >= 500 && id < 600) return { from: 'from-blue-600/25', to: 'to-indigo-700/15', icon: '🌧️' }
    if (id >= 600 && id < 700) return { from: 'from-sky-200/30', to: 'to-blue-300/20', icon: '❄️' }
    if (id === 800) return { from: 'from-amber-400/20', to: 'to-sky-400/15', icon: '☀️' }
    if (id > 800) return { from: 'from-sky-400/15', to: 'to-slate-400/10', icon: '⛅' }
    return { from: 'from-sky-500/15', to: 'to-blue-600/10', icon: '🌤' }
})
</script>

<template>
    <Head title="Weather" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col items-center min-h-full p-6 gap-8">

            <!-- Header -->
            <div class="w-full max-w-2xl text-center">
                <h1 class="text-3xl font-semibold tracking-tight text-foreground">Weather</h1>
                <p class="text-muted-foreground mt-1">Search any city for current conditions</p>
            </div>

            <!-- Search bar -->
            <div class="w-full max-w-md">
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            v-model="city"
                            placeholder="City name…"
                            @keydown.enter="search"
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-border bg-card text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary/50 transition-all"
                        />
                    </div>
                    <button
                        @click="search"
                        class="px-5 py-2.5 bg-primary text-primary-foreground rounded-xl font-medium hover:bg-primary/90 active:scale-95 transition-all focus:outline-none focus:ring-2 focus:ring-primary/50 focus:ring-offset-2 focus:ring-offset-background"
                    >
                        Search
                    </button>
                </div>
            </div>

            <!-- Error state -->
            <div v-if="hasError" class="w-full max-w-md rounded-2xl border border-destructive/30 bg-destructive/10 p-5 text-center">
                <div class="text-2xl mb-2">🔍</div>
                <p class="font-medium text-foreground">City not found</p>
                <p class="text-sm text-muted-foreground mt-1">{{ weather.message || 'Please try a different city name' }}</p>
            </div>

            <!-- Weather Card -->
            <div v-if="hasWeather" class="w-full max-w-md">
                <div :class="`relative overflow-hidden rounded-2xl border border-border bg-card shadow-sm`">
                    <!-- Gradient background -->
                    <div :class="`absolute inset-0 bg-gradient-to-br ${weatherTheme.from} ${weatherTheme.to}`"></div>

                    <div class="relative p-8">
                        <!-- City & country -->
                        <div class="flex items-start justify-between mb-6">
                            <div>
                                <h2 class="text-2xl font-semibold text-foreground">{{ weather.name }}</h2>
                                <p class="text-muted-foreground text-sm mt-0.5">{{ weather.sys?.country }}</p>
                            </div>
                            <img
                                v-if="weather.weather?.[0]?.icon"
                                :src="`https://openweathermap.org/img/wn/${weather.weather[0].icon}@2x.png`"
                                class="h-16 w-16 -mt-1 -mr-1 drop-shadow-sm"
                                :alt="weather.weather[0].description"
                            />
                        </div>

                        <!-- Temperature -->
                        <div class="mb-6">
                            <div class="flex items-end gap-2">
                                <span class="text-7xl font-light tracking-tight text-foreground">{{ Math.round(weather.main.temp) }}</span>
                                <span class="text-3xl text-muted-foreground mb-3">°C</span>
                            </div>
                            <p class="text-muted-foreground capitalize mt-1">{{ weather.weather[0].description }}</p>
                            <p class="text-sm text-muted-foreground mt-0.5">
                                Feels like {{ Math.round(weather.main.feels_like) }}°C
                            </p>
                        </div>

                        <!-- Stats grid -->
                        <div class="grid grid-cols-3 gap-3">
                            <div class="rounded-xl bg-background/60 backdrop-blur-sm border border-border/60 p-3 text-center">
                                <p class="text-xs text-muted-foreground uppercase tracking-wide font-medium">Humidity</p>
                                <p class="text-lg font-semibold text-foreground mt-1">{{ weather.main.humidity }}%</p>
                            </div>
                            <div class="rounded-xl bg-background/60 backdrop-blur-sm border border-border/60 p-3 text-center">
                                <p class="text-xs text-muted-foreground uppercase tracking-wide font-medium">Wind</p>
                                <p class="text-lg font-semibold text-foreground mt-1">{{ weather.wind.speed }}<span class="text-sm font-normal text-muted-foreground"> m/s</span></p>
                            </div>
                            <div class="rounded-xl bg-background/60 backdrop-blur-sm border border-border/60 p-3 text-center">
                                <p class="text-xs text-muted-foreground uppercase tracking-wide font-medium">Pressure</p>
                                <p class="text-lg font-semibold text-foreground mt-1">{{ weather.main.pressure }}<span class="text-sm font-normal text-muted-foreground"> hPa</span></p>
                            </div>
                        </div>

                        <!-- High/Low -->
                        <div class="flex justify-center gap-6 mt-4 pt-4 border-t border-border/50">
                            <div class="text-center">
                                <p class="text-xs text-muted-foreground">High</p>
                                <p class="font-semibold text-foreground">{{ Math.round(weather.main.temp_max) }}°</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-muted-foreground">Low</p>
                                <p class="font-semibold text-foreground">{{ Math.round(weather.main.temp_min) }}°</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-if="!weather" class="text-center text-muted-foreground">
                <div class="text-5xl mb-3">🌍</div>
                <p>Search for a city to see the weather</p>
            </div>

        </div>
    </AppLayout>
</template>
