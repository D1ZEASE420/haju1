<script setup>
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
    blogs: Array,
});

const page = usePage();
const authUser  = computed(() => page.props.auth?.user);
const isAdmin   = computed(() => page.props.auth?.is_admin ?? false);

function canManage(blog) {
    if (!authUser.value) return false;
    return isAdmin.value || blog.user_id === authUser.value.id;
}

function deleteBlog(id) {
    if (!confirm('Kas soovid selle postituse kustutada?')) return;
    router.delete(`/blogs/${id}`);
}

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Blogi', href: '/blogs' },
];

function formatDate(dateStr) {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('et-EE', {
        day: 'numeric', month: 'short', year: 'numeric',
    });
}
</script>

<template>
    <Head title="Blogi" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-4xl mx-auto">

            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">Blogi</h1>
                    <p class="text-muted-foreground text-sm mt-0.5">
                        {{ blogs.length }} postitus{{ blogs.length !== 1 ? 't' : '' }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Admin badge -->
                    <span v-if="isAdmin" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-500/10 border border-amber-500/25 text-amber-600 dark:text-amber-400 text-xs font-medium">
                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9.664 1.319a.75.75 0 01.672 0 41.059 41.059 0 018.198 5.424.75.75 0 01-.254 1.285 31.372 31.372 0 00-7.86 3.83.75.75 0 01-.84 0 31.508 31.508 0 00-2.08-1.287V9.48a31.525 31.525 0 00-2.064.517.75.75 0 01-.542-.046.75.75 0 01-.288-.606V4.5c0-.404.261-.764.644-.909A41.098 41.098 0 019.664 1.32zM9 11.575a32.5 32.5 0 00-3.338 2.232 1.75 1.75 0 001.054 2.897l.004.001.13.022a17.8 17.8 0 002.15.05 17.8 17.8 0 002.15-.05l.13-.022.004-.001a1.75 1.75 0 001.053-2.897A32.5 32.5 0 009 11.575z" clip-rule="evenodd"/>
                        </svg>
                        Admin
                    </span>
                    <Link
                        href="/blogs/create"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-medium hover:bg-primary/90 active:scale-95 transition-all shadow-sm shadow-primary/20"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Uus postitus
                    </Link>
                </div>
            </div>

            <!-- Empty state -->
            <div v-if="blogs.length === 0" class="text-center py-24 rounded-2xl border border-dashed border-border">
                <div class="text-5xl mb-4">📝</div>
                <p class="font-medium text-foreground">Ühtegi postitust pole</p>
                <p class="text-muted-foreground text-sm mt-1 mb-6">Alusta oma esimese blogi postitusega</p>
                <Link href="/blogs/create" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-xl text-sm font-medium hover:bg-primary/90 transition-all">
                    Loo esimene postitus
                </Link>
            </div>

            <!-- Blog list -->
            <div v-else class="flex flex-col gap-3">
                <div
                    v-for="blog in blogs"
                    :key="blog.id"
                    class="group flex items-start justify-between rounded-2xl border border-border bg-card p-5 hover:border-primary/30 hover:shadow-md hover:shadow-primary/5 transition-all duration-200"
                >
                    <div class="flex-1 min-w-0 mr-4">
                        <Link :href="`/blogs/${blog.id}`" class="hover:text-primary transition-colors">
                            <h2 class="font-semibold text-foreground text-lg leading-snug line-clamp-1">{{ blog.title }}</h2>
                        </Link>
                        <p class="text-muted-foreground text-sm mt-1.5 line-clamp-2">{{ blog.description }}</p>

                        <div class="flex items-center gap-4 mt-3 flex-wrap">
                            <span class="inline-flex items-center gap-1.5 text-xs text-muted-foreground">
                                <span class="h-5 w-5 rounded-full bg-primary/15 flex items-center justify-center text-primary font-semibold text-[10px]">
                                    {{ blog.user?.name?.charAt(0)?.toUpperCase() ?? '?' }}
                                </span>
                                {{ blog.user?.name ?? 'Tundmatu' }}
                            </span>
                            <span class="text-xs text-muted-foreground">{{ formatDate(blog.created_at) }}</span>
                            <span class="inline-flex items-center gap-1 text-xs text-muted-foreground">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                {{ blog.comments_count ?? 0 }}
                            </span>
                        </div>
                    </div>

                    <!-- Actions — owner or admin only -->
                    <div v-if="canManage(blog)" class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                        <Link
                            :href="`/blogs/${blog.id}/edit`"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-border bg-background hover:bg-accent hover:border-primary/30 text-foreground transition-all"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Muuda
                        </Link>
                        <button
                            @click="deleteBlog(blog.id)"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-destructive/30 bg-destructive/5 text-destructive hover:bg-destructive/10 transition-all"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Kustuta
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>