<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
    movies:  Array,
    error:   String,
    filters: Object,
});

const search = ref(props.filters?.search ?? '');
const sort   = ref(props.filters?.sort   ?? 'title');
const dir    = ref(props.filters?.dir    ?? 'asc');

function applyFilters() {
    router.get('/movies', {
        search: search.value || undefined,
        sort:   sort.value,
        dir:    dir.value,
    }, { preserveState: true, replace: true });
}

function clearFilters() {
    search.value = '';
    sort.value   = 'title';
    dir.value    = 'asc';
    router.get('/movies');
}

function toggleSort(field) {
    if (sort.value === field) {
        dir.value = dir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sort.value = field;
        dir.value  = 'asc';
    }
    applyFilters();
}

// Try to find a poster/image field — APIs use different field names
function getPoster(movie) {
    return movie.poster
        || movie.image
        || movie.image_url
        || movie.thumbnail
        || movie.cover
        || movie.photo
        || null;
}

function getYear(movie) {
    return movie.year
        || movie.release_year
        || movie.released
        || movie.release_date?.substring(0, 4)
        || null;
}

function getGenre(movie) {
    return movie.genre
        || movie.category
        || movie.type
        || null;
}

function getDirector(movie) {
    return movie.director
        || movie.directed_by
        || movie.filmmaker
        || null;
}

function getRating(movie) {
    return movie.rating
        || movie.score
        || movie.imdb_rating
        || null;
}

// Detect what sort fields are available from first movie
const availableSortFields = computed(() => {
    if (!props.movies?.length) return [];
    const m = props.movies[0];
    if (!m || typeof m !== 'object') return [];
    const fields = [];
    if ('title' in m)                         fields.push({ key: 'title',    label: 'Pealkiri' });
    if (getYear(m) !== null)                  fields.push({ key: 'year',     label: 'Aasta' });
    if (getRating(m) !== null)                fields.push({ key: 'rating',   label: 'Hinnang' });
    if (getGenre(m) !== null)                 fields.push({ key: 'genre',    label: 'Žanr' });
    if (getDirector(m) !== null)              fields.push({ key: 'director', label: 'Režissöör' });
    return fields;
});

// Get all unique genres for filter
const genres = computed(() => {
    if (!props.movies?.length) return [];
    const gs = props.movies.map(m => getGenre(m)).filter(Boolean);
    return [...new Set(gs)].sort();
});

const selectedGenre = ref('');

const displayedMovies = computed(() => {
    if (!selectedGenre.value) return props.movies;
    return props.movies.filter(m => getGenre(m) === selectedGenre.value);
});

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Filmid', href: '/movies' },
];
</script>

<template>
    <Head title="Filmid" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-7xl mx-auto">

            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">Filmid</h1>
                    <p class="text-muted-foreground text-sm mt-0.5">
                        Andmed: <a href="https://ralfiharjutus.ta24siim.itmajakas.ee/api/movies" target="_blank" rel="noopener" class="text-primary hover:underline">väline API</a>
                        <span v-if="!error"> · {{ displayedMovies?.length ?? 0 }} filmi</span>
                    </p>
                </div>
            </div>

            <!-- Error state -->
            <div v-if="error" class="rounded-2xl border border-destructive/30 bg-destructive/8 p-8 text-center mb-6">
                <div class="text-4xl mb-3">🎬</div>
                <p class="font-medium text-foreground">API ei ole kättesaadav</p>
                <p class="text-sm text-muted-foreground mt-1">{{ error }}</p>
                <button
                    @click="router.reload()"
                    class="mt-4 px-4 py-2 rounded-xl bg-primary text-primary-foreground text-sm font-medium hover:bg-primary/90 transition-all"
                >
                    Proovi uuesti
                </button>
            </div>

            <template v-else>
                <!-- Filters -->
                <div class="rounded-2xl border border-border bg-card p-4 mb-6 shadow-sm">
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <!-- Search -->
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input
                                v-model="search"
                                placeholder="Otsi filmi…"
                                @keydown.enter="applyFilters"
                                class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-border bg-background text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all"
                            />
                        </div>

                        <!-- Genre filter -->
                        <select
                            v-if="genres.length"
                            v-model="selectedGenre"
                            class="px-3 py-2.5 rounded-xl border border-border bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all"
                        >
                            <option value="">Kõik žanrid</option>
                            <option v-for="g in genres" :key="g" :value="g">{{ g }}</option>
                        </select>

                        <!-- Search + clear buttons -->
                        <div class="flex gap-2">
                            <button
                                @click="applyFilters"
                                class="flex-1 py-2.5 rounded-xl bg-primary text-primary-foreground text-sm font-medium hover:bg-primary/90 transition-all"
                            >
                                Otsi
                            </button>
                            <button
                                @click="clearFilters"
                                class="px-4 py-2.5 rounded-xl border border-border bg-background text-sm text-muted-foreground hover:text-foreground hover:bg-accent transition-all"
                            >
                                Tühjenda
                            </button>
                        </div>
                    </div>

                    <!-- Sort buttons -->
                    <div v-if="availableSortFields.length" class="flex items-center gap-2 mt-3 flex-wrap">
                        <span class="text-xs text-muted-foreground">Sorteeri:</span>
                        <button
                            v-for="s in availableSortFields"
                            :key="s.key"
                            @click="toggleSort(s.key)"
                            :class="[
                                'inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium transition-all',
                                sort === s.key
                                    ? 'bg-primary/10 text-primary border border-primary/20'
                                    : 'border border-border text-muted-foreground hover:text-foreground hover:bg-accent'
                            ]"
                        >
                            {{ s.label }}
                            <span v-if="sort === s.key">{{ dir === 'asc' ? '↑' : '↓' }}</span>
                        </button>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-if="!displayedMovies?.length" class="text-center py-20 rounded-2xl border border-dashed border-border">
                    <div class="text-5xl mb-3">🎬</div>
                    <p class="font-medium text-foreground">Filme ei leitud</p>
                    <p class="text-muted-foreground text-sm mt-1">Muuda otsingut</p>
                </div>

                <!-- Movie grid -->
                <div v-else class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <div
                        v-for="(movie, index) in displayedMovies"
                        :key="movie.id ?? index"
                        class="group flex flex-col rounded-2xl border border-border bg-card overflow-hidden hover:border-primary/30 hover:shadow-lg hover:shadow-primary/5 transition-all duration-200"
                    >
                        <!-- Poster -->
                        <div class="relative aspect-[2/3] overflow-hidden bg-muted">
                            <img
                                v-if="getPoster(movie)"
                                :src="getPoster(movie)"
                                :alt="movie.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                loading="lazy"
                                @error="$event.target.style.display='none'"
                            />
                            <!-- Fallback if no poster -->
                            <div v-else class="w-full h-full flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-slate-800 to-slate-900">
                                <span class="text-5xl">🎬</span>
                                <span class="text-xs text-slate-400 text-center px-3">Poster puudub</span>
                            </div>

                            <!-- Genre badge -->
                            <span v-if="getGenre(movie)" class="absolute top-3 left-3 px-2.5 py-1 rounded-full bg-black/70 backdrop-blur-sm text-xs font-medium text-white border border-white/10">
                                {{ getGenre(movie) }}
                            </span>

                            <!-- Rating badge -->
                            <span v-if="getRating(movie)" class="absolute top-3 right-3 inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-500/90 backdrop-blur-sm text-xs font-bold text-white">
                                ★ {{ getRating(movie) }}
                            </span>
                        </div>

                        <!-- Info -->
                        <div class="flex flex-col flex-1 p-4 gap-2">
                            <div>
                                <h2 class="font-semibold text-foreground text-[15px] leading-snug line-clamp-2">
                                    {{ movie.title }}
                                </h2>
                                <div class="flex items-center gap-2 mt-1 flex-wrap">
                                    <span v-if="getYear(movie)" class="text-xs text-muted-foreground">{{ getYear(movie) }}</span>
                                    <span v-if="getYear(movie) && getDirector(movie)" class="text-muted-foreground/30 text-xs">·</span>
                                    <span v-if="getDirector(movie)" class="text-xs text-muted-foreground">{{ getDirector(movie) }}</span>
                                </div>
                            </div>

                            <p v-if="movie.description" class="text-xs text-muted-foreground line-clamp-3 leading-relaxed flex-1">
                                {{ movie.description }}
                            </p>

                            <!-- Extra fields — show any remaining data -->
                            <div class="pt-2 border-t border-border mt-auto space-y-1">
                                <template v-for="(value, key) in movie" :key="key">
                                    <div
                                        v-if="!['id','title','description','poster','image','image_url','thumbnail','cover','photo','genre','category','type','director','directed_by','filmmaker','year','release_year','released','release_date','rating','score','imdb_rating','created_at','updated_at'].includes(String(key)) && value && typeof value !== 'object'"
                                        class="flex items-baseline gap-2 text-xs"
                                    >
                                        <span class="text-muted-foreground capitalize shrink-0">{{ String(key).replace(/_/g, ' ') }}:</span>
                                        <span class="text-foreground truncate">{{ value }}</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

        </div>
    </AppLayout>
</template>