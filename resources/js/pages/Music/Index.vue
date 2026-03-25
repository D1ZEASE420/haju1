<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
    albums:   Array,
    genres:   Array,
    filters:  Object,
    hasToken: Boolean,
    newToken: String,
});

const page     = usePage();
const authUser = computed(() => page.props.auth?.user);
const isAdmin  = computed(() => page.props.auth?.is_admin ?? false);

function canManage(album) {
    if (!authUser.value) return false;
    return isAdmin.value || album.user_id === authUser.value.id;
}

// ---- Filter state ----
const search = ref(props.filters?.search ?? '');
const genre  = ref(props.filters?.genre  ?? '');
const rating = ref(props.filters?.rating ?? '');
const sort   = ref(props.filters?.sort   ?? 'created_at');
const dir    = ref(props.filters?.dir    ?? 'desc');

function applyFilters() {
    router.get('/music', {
        search: search.value || undefined,
        genre:  genre.value  || undefined,
        rating: rating.value || undefined,
        sort:   sort.value,
        dir:    dir.value,
    }, { preserveState: true, replace: true });
}

function clearFilters() {
    search.value = '';
    genre.value  = '';
    rating.value = '';
    sort.value   = 'created_at';
    dir.value    = 'desc';
    router.get('/music');
}

function toggleSort(field) {
    if (sort.value === field) {
        dir.value = dir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sort.value = field;
        dir.value  = 'desc';
    }
    applyFilters();
}

function deleteAlbum(id) {
    if (!confirm('Kas soovid selle albumi kustutada?')) return;
    router.delete(`/music/${id}`);
}

// ---- API Token ----
const tokenVisible   = ref(false);
const tokenCopied    = ref(false);
const revokePending  = ref(false);

// Kui server saadab uue tokeni, ava automaatselt reveal
watch(() => props.newToken, (val) => {
    if (val) tokenVisible.value = true;
}, { immediate: true });

function generateToken() {
    router.post('/settings/api-token');
}

function revokeToken() {
    if (!confirm('Kas oled kindel? Praegune võti lakkab töötamast kohe.')) return;
    revokePending.value = true;
    router.delete('/settings/api-token', {}, {
        onFinish: () => { revokePending.value = false; },
    });
}

function copyToken() {
    if (!props.newToken) return;
    navigator.clipboard.writeText(props.newToken).then(() => {
        tokenCopied.value = true;
        setTimeout(() => { tokenCopied.value = false; }, 2000);
    });
}

// ----

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Muusika', href: '/music' },
];

function starColor(r) {
    if (r >= 4) return 'text-amber-400';
    if (r >= 3) return 'text-amber-300';
    return 'text-muted-foreground';
}
</script>

<template>
    <Head title="Muusika" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-7xl mx-auto">

            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">Lemmikalbumid</h1>
                    <p class="text-muted-foreground text-sm mt-0.5">{{ albums.length }} albumit</p>
                </div>
                <div class="flex items-center gap-2">
                    <a
                        href="/api/albums"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-border bg-card text-xs font-medium text-muted-foreground hover:text-foreground hover:border-primary/30 transition-all"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                        JSON API
                    </a>
                    <Link
                        href="/music/create"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-medium hover:bg-primary/90 active:scale-95 transition-all shadow-sm shadow-primary/20"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Lisa album
                    </Link>
                </div>
            </div>

            <!-- ======================== API TOKEN CARD ======================== -->
            <div class="rounded-2xl border border-border bg-card p-5 mb-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <!-- Icon -->
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-foreground">API võti</p>
                            <p class="text-xs text-muted-foreground mt-0.5">
                                <span v-if="hasToken">Sul on aktiivne võti. Kasuta seda <code class="font-mono bg-muted px-1 rounded">Authorization: Bearer &lt;token&gt;</code> päisega.</span>
                                <span v-else>Genereeri võti, et pääseda ligi muusika API-le.</span>
                            </p>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center gap-2 shrink-0">
                        <button
                            v-if="hasToken"
                            @click="revokeToken"
                            :disabled="revokePending"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-destructive/40 text-destructive text-xs font-medium hover:bg-destructive/10 transition-all disabled:opacity-50"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Tühista võti
                        </button>
                        <button
                            @click="generateToken"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-primary text-primary-foreground text-xs font-medium hover:bg-primary/90 active:scale-95 transition-all"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            {{ hasToken ? 'Genereeri uus' : 'Genereeri võti' }}
                        </button>
                    </div>
                </div>

                <!-- New token reveal (shown once after generation) -->
                <div v-if="newToken" class="mt-4">
                    <div class="rounded-xl border border-amber-500/30 bg-amber-500/5 p-4">
                        <div class="flex items-start gap-2 mb-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <p class="text-xs font-medium text-amber-600 dark:text-amber-400">
                                Salvesta see võti kohe — seda ei kuvata uuesti!
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1 min-w-0">
                                <input
                                    :type="tokenVisible ? 'text' : 'password'"
                                    :value="newToken"
                                    readonly
                                    class="w-full font-mono text-xs bg-background border border-border rounded-lg px-3 py-2.5 text-foreground pr-10 focus:outline-none"
                                />
                                <button
                                    @click="tokenVisible = !tokenVisible"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                                    :title="tokenVisible ? 'Peida' : 'Näita'"
                                >
                                    <svg v-if="tokenVisible" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                    <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            <button
                                @click="copyToken"
                                class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-lg border border-border bg-background text-xs font-medium text-foreground hover:bg-accent transition-all shrink-0"
                            >
                                <svg v-if="!tokenCopied" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                <svg v-else class="h-3.5 w-3.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ tokenCopied ? 'Kopeeritud!' : 'Kopeeri' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ======================== / API TOKEN CARD ======================== -->

            <!-- Filters -->
            <div class="rounded-2xl border border-border bg-card p-4 mb-6 shadow-sm">
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <!-- Search -->
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            v-model="search"
                            placeholder="Otsi pealkirja või artisti…"
                            @keydown.enter="applyFilters"
                            class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-border bg-background text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all"
                        />
                    </div>

                    <!-- Genre filter -->
                    <select
                        v-model="genre"
                        @change="applyFilters"
                        class="px-3 py-2.5 rounded-xl border border-border bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all"
                    >
                        <option value="">Kõik žanrid</option>
                        <option v-for="g in genres" :key="g" :value="g">{{ g }}</option>
                    </select>

                    <!-- Rating filter -->
                    <select
                        v-model="rating"
                        @change="applyFilters"
                        class="px-3 py-2.5 rounded-xl border border-border bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all"
                    >
                        <option value="">Kõik hinnangud</option>
                        <option value="5">⭐⭐⭐⭐⭐ (5)</option>
                        <option value="4">⭐⭐⭐⭐ (4)</option>
                        <option value="3">⭐⭐⭐ (3)</option>
                        <option value="2">⭐⭐ (2)</option>
                        <option value="1">⭐ (1)</option>
                    </select>

                    <!-- Actions -->
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
                <div class="flex items-center gap-2 mt-3 flex-wrap">
                    <span class="text-xs text-muted-foreground">Sorteeri:</span>
                    <button
                        v-for="s in [{ key: 'created_at', label: 'Lisatud' }, { key: 'title', label: 'Pealkiri' }, { key: 'artist', label: 'Artist' }, { key: 'release_year', label: 'Aasta' }, { key: 'rating', label: 'Hinnang' }]"
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
            <div v-if="albums.length === 0" class="text-center py-24 rounded-2xl border border-dashed border-border">
                <div class="text-5xl mb-4">🎵</div>
                <p class="font-medium text-foreground">Albumeid ei leitud</p>
                <p class="text-muted-foreground text-sm mt-1 mb-6">Muuda otsingut või lisa uus album</p>
                <Link href="/music/create" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-xl text-sm font-medium hover:bg-primary/90 transition-all">
                    Lisa esimene album
                </Link>
            </div>

            <!-- Album grid -->
            <div v-else class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div
                    v-for="album in albums"
                    :key="album.id"
                    class="group flex flex-col rounded-2xl border border-border bg-card overflow-hidden hover:border-primary/30 hover:shadow-lg hover:shadow-primary/5 transition-all duration-200"
                >
                    <!-- Cover image -->
                    <div class="relative aspect-square overflow-hidden bg-muted">
                        <img
                            v-if="album.image"
                            :src="album.image"
                            :alt="album.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            loading="lazy"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center text-6xl">🎵</div>

                        <!-- Genre badge -->
                        <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full bg-background/90 backdrop-blur-sm text-xs font-medium text-foreground border border-border/50">
                            {{ album.genre }}
                        </span>

                        <!-- Owner/admin actions on hover -->
                        <div
                            v-if="canManage(album)"
                            class="absolute top-3 right-3 flex gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity"
                        >
                            <Link
                                :href="`/music/${album.id}/edit`"
                                class="h-7 w-7 flex items-center justify-center rounded-lg bg-background/90 backdrop-blur-sm border border-border/50 text-muted-foreground hover:text-foreground transition-colors"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </Link>
                            <button
                                @click="deleteAlbum(album.id)"
                                class="h-7 w-7 flex items-center justify-center rounded-lg bg-background/90 backdrop-blur-sm border border-destructive/40 text-destructive hover:bg-destructive/10 transition-colors"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="flex flex-col flex-1 p-4 gap-2">
                        <div>
                            <h2 class="font-semibold text-foreground text-[15px] leading-snug line-clamp-1">{{ album.title }}</h2>
                            <p class="text-sm text-muted-foreground">{{ album.artist }} · {{ album.release_year }}</p>
                        </div>
                        <p class="text-xs text-muted-foreground line-clamp-2 leading-relaxed flex-1">{{ album.description }}</p>

                        <div class="flex items-center justify-between pt-2 border-t border-border mt-auto">
                            <!-- Stars -->
                            <div class="flex items-center gap-0.5">
                                <span
                                    v-for="i in 5"
                                    :key="i"
                                    :class="['text-sm', i <= album.rating ? 'text-amber-400' : 'text-muted/30 opacity-30']"
                                >★</span>
                            </div>
                            <!-- Added by -->
                            <span class="text-xs text-muted-foreground">{{ album.user?.name ?? 'Tundmatu' }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>