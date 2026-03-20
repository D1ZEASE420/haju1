<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({ album: Object });

const form = useForm({
    title:        props.album.title,
    artist:       props.album.artist,
    release_year: props.album.release_year,
    genre:        props.album.genre,
    rating:       props.album.rating,
    description:  props.album.description,
    image:        props.album.image ?? '',
});

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Muusika', href: '/music' },
    { title: 'Muuda albumit', href: `/music/${props.album.id}/edit` },
];

const GENRES = ['Pop', 'Rock', 'Hard Rock', 'Progressive Rock', 'Electronic', 'Jazz', 'Hip-Hop', 'Soul', 'Classical', 'Country', 'R&B', 'Metal', 'Indie', 'Folk'];

function submit() {
    form.put(`/music/${props.album.id}`);
}
</script>

<template>
    <Head title="Muuda albumit" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-2xl mx-auto">

            <div class="mb-8">
                <h1 class="text-2xl font-semibold tracking-tight text-foreground">Muuda albumit</h1>
                <p class="text-muted-foreground text-sm mt-1">Uuenda albumi andmeid</p>
            </div>

            <div class="rounded-2xl border border-border bg-card p-6 shadow-sm">
                <form @submit.prevent="submit" class="flex flex-col gap-5">

                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Albumi pealkiri <span class="text-destructive">*</span></label>
                        <input v-model="form.title" type="text" :class="['px-4 py-3 rounded-xl border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all', form.errors.title ? 'border-destructive' : 'border-border']" />
                        <p v-if="form.errors.title" class="text-destructive text-xs">{{ form.errors.title }}</p>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Artist / Bänd <span class="text-destructive">*</span></label>
                        <input v-model="form.artist" type="text" :class="['px-4 py-3 rounded-xl border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all', form.errors.artist ? 'border-destructive' : 'border-border']" />
                        <p v-if="form.errors.artist" class="text-destructive text-xs">{{ form.errors.artist }}</p>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Väljalaskeaasta <span class="text-destructive">*</span></label>
                            <input v-model="form.release_year" type="number" min="1900" :max="new Date().getFullYear() + 1" :class="['px-4 py-3 rounded-xl border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all', form.errors.release_year ? 'border-destructive' : 'border-border']" />
                            <p v-if="form.errors.release_year" class="text-destructive text-xs">{{ form.errors.release_year }}</p>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Žanr <span class="text-destructive">*</span></label>
                            <select v-model="form.genre" :class="['px-4 py-3 rounded-xl border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all', form.errors.genre ? 'border-destructive' : 'border-border']">
                                <option v-for="g in GENRES" :key="g" :value="g">{{ g }}</option>
                            </select>
                            <p v-if="form.errors.genre" class="text-destructive text-xs">{{ form.errors.genre }}</p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Hinnang <span class="text-destructive">*</span></label>
                        <div class="flex items-center gap-2">
                            <button v-for="i in 5" :key="i" type="button" @click="form.rating = i" :class="['text-3xl transition-transform hover:scale-110', i <= form.rating ? 'text-amber-400' : 'text-muted-foreground opacity-30']">★</button>
                            <span class="text-sm text-muted-foreground ml-2">{{ form.rating }}/5</span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Kirjeldus <span class="text-destructive">*</span></label>
                        <textarea v-model="form.description" rows="4" :class="['px-4 py-3 rounded-xl border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all resize-none', form.errors.description ? 'border-destructive' : 'border-border']"></textarea>
                        <p v-if="form.errors.description" class="text-destructive text-xs">{{ form.errors.description }}</p>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Kaanepildi URL <span class="text-muted-foreground font-normal">(vabatahtlik)</span></label>
                        <input v-model="form.image" type="url" placeholder="https://…" :class="['px-4 py-3 rounded-xl border bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all', form.errors.image ? 'border-destructive' : 'border-border']" />
                        <div v-if="form.image" class="mt-2">
                            <img :src="form.image" alt="Eelvaade" class="h-24 w-24 rounded-xl object-cover border border-border" @error="$event.target.style.display='none'" />
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2 border-t border-border">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-medium hover:bg-primary/90 disabled:opacity-50 active:scale-95 transition-all shadow-sm shadow-primary/20"
                        >
                            <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            {{ form.processing ? 'Salvestamine…' : 'Salvesta muudatused' }}
                        </button>
                        <Link href="/music" class="px-4 py-2.5 text-sm text-muted-foreground hover:text-foreground transition-colors">Tühista</Link>
                    </div>

                </form>
            </div>
        </div>
    </AppLayout>
</template>