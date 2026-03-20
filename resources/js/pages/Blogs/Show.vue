<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
    blog: Object,
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user);

// Admin = id 1 (mirror backend logic)
const isAdmin = computed(() => authUser.value?.id === 1);

// Can the current user manage (edit/delete) the blog post itself?
const canManageBlog = computed(() =>
    authUser.value && (isAdmin.value || props.blog.user_id === authUser.value.id)
);

// Can the current user delete a given comment?
function canDeleteComment(comment) {
    if (!authUser.value) return false;
    return (
        isAdmin.value ||
        comment.user_id === authUser.value.id ||
        props.blog.user_id === authUser.value.id   // blog owner can moderate
    );
}

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Blogi', href: '/blogs' },
    { title: props.blog.title, href: `/blogs/${props.blog.id}` },
];

// ---- Comment form ----
const newComment = ref('');
const submitting = ref(false);

async function addComment() {
    if (!newComment.value.trim() || submitting.value) return;
    submitting.value = true;
    try {
        await axios.post(`/blogs/${props.blog.id}/comments`, { content: newComment.value });
        newComment.value = '';
        // Reload page to get fresh comments from server
        router.reload({ only: ['blog'] });
    } finally {
        submitting.value = false;
    }
}

function deleteComment(id) {
    if (!confirm('Kas soovid selle kommentaari kustutada?')) return;
    router.delete(`/comments/${id}`, {
        preserveScroll: true,
        onSuccess: () => router.reload({ only: ['blog'] }),
    });
}

function deleteBlog() {
    if (!confirm('Kas soovid selle postituse kustutada? Kõik kommentaarid kustutatakse samuti.')) return;
    router.delete(`/blogs/${props.blog.id}`);
}

// Format date
function formatDate(dateStr) {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('et-EE', {
        day: 'numeric', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}
function formatDateShort(dateStr) {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('et-EE', {
        day: 'numeric', month: 'short', year: 'numeric',
    });
}
</script>

<template>
    <Head :title="blog.title" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-3xl mx-auto">

            <!-- ── Blog Post ─────────────────────────────────────── -->
            <article class="rounded-2xl border border-border bg-card p-8 mb-6 shadow-sm">

                <!-- Post header -->
                <div class="flex items-start justify-between gap-4 mb-6">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-3xl font-semibold tracking-tight text-foreground leading-tight">
                            {{ blog.title }}
                        </h1>
                        <!-- Author & date -->
                        <div class="flex items-center gap-3 mt-3">
                            <span class="h-7 w-7 rounded-full bg-primary/15 flex items-center justify-center text-primary font-semibold text-xs shrink-0">
                                {{ blog.user?.name?.charAt(0)?.toUpperCase() ?? '?' }}
                            </span>
                            <span class="text-sm text-muted-foreground">
                                <span class="font-medium text-foreground">{{ blog.user?.name ?? 'Tundmatu' }}</span>
                                · {{ formatDateShort(blog.created_at) }}
                            </span>
                        </div>
                    </div>

                    <!-- Owner/admin actions -->
                    <div v-if="canManageBlog" class="flex items-center gap-2 shrink-0">
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
                            @click="deleteBlog"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-destructive/30 bg-destructive/5 text-destructive hover:bg-destructive/10 transition-all"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Kustuta
                        </button>
                    </div>
                </div>

                <!-- Divider -->
                <div class="h-px bg-border mb-6"></div>

                <!-- Post body -->
                <div class="text-foreground/80 leading-relaxed whitespace-pre-line text-[15px]">
                    {{ blog.description }}
                </div>
            </article>

            <!-- ── Comments ──────────────────────────────────────── -->
            <section>
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-semibold text-foreground">
                        Kommentaarid
                        <span class="ml-1.5 text-sm font-normal text-muted-foreground">
                            ({{ blog.comments?.length ?? 0 }})
                        </span>
                    </h2>
                    <!-- Admin badge -->
                    <span v-if="isAdmin" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-500/10 border border-amber-500/25 text-amber-600 dark:text-amber-400 text-xs font-medium">
                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9.664 1.319a.75.75 0 01.672 0 41.059 41.059 0 018.198 5.424.75.75 0 01-.254 1.285 31.372 31.372 0 00-7.86 3.83.75.75 0 01-.84 0 31.508 31.508 0 00-2.08-1.287V9.48a31.525 31.525 0 00-2.064.517.75.75 0 01-.542-.046.75.75 0 01-.288-.606V4.5c0-.404.261-.764.644-.909A41.098 41.098 0 019.664 1.32zM9 11.575a32.5 32.5 0 00-3.338 2.232 1.75 1.75 0 001.054 2.897l.004.001.13.022a17.8 17.8 0 002.15.05 17.8 17.8 0 002.15-.05l.13-.022.004-.001a1.75 1.75 0 001.053-2.897A32.5 32.5 0 009 11.575z" clip-rule="evenodd"/>
                        </svg>
                        Admin
                    </span>
                </div>

                <!-- Add comment form -->
                <div class="rounded-2xl border border-border bg-card p-5 mb-5 shadow-sm">
                    <div class="flex items-start gap-3">
                        <!-- Current user avatar -->
                        <span class="h-8 w-8 rounded-full bg-primary/15 flex items-center justify-center text-primary font-semibold text-sm shrink-0 mt-0.5">
                            {{ authUser?.name?.charAt(0)?.toUpperCase() ?? '?' }}
                        </span>
                        <div class="flex-1">
                            <textarea
                                v-model="newComment"
                                placeholder="Lisa kommentaar…"
                                rows="3"
                                @keydown.ctrl.enter="addComment"
                                class="w-full resize-none bg-transparent text-foreground placeholder:text-muted-foreground focus:outline-none text-sm leading-relaxed"
                            ></textarea>
                            <div class="flex items-center justify-between pt-3 mt-2 border-t border-border">
                                <p class="text-xs text-muted-foreground">Ctrl + Enter saatmiseks</p>
                                <button
                                    @click="addComment"
                                    :disabled="!newComment.trim() || submitting"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm font-medium hover:bg-primary/90 disabled:opacity-40 disabled:cursor-not-allowed active:scale-95 transition-all"
                                >
                                    <svg v-if="submitting" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                    </svg>
                                    {{ submitting ? 'Saatmine…' : 'Postita' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comment list -->
                <div v-if="blog.comments?.length" class="flex flex-col gap-2.5">
                    <div
                        v-for="comment in blog.comments"
                        :key="comment.id"
                        class="group flex items-start gap-3 rounded-xl border border-border bg-card px-5 py-4 transition-all hover:border-border/80"
                    >
                        <!-- Avatar -->
                        <span class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-semibold text-sm shrink-0 mt-0.5">
                            {{ comment.user?.name?.charAt(0)?.toUpperCase() ?? '?' }}
                        </span>

                        <!-- Comment body -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-baseline gap-2 flex-wrap">
                                <span class="text-sm font-medium text-foreground">
                                    {{ comment.user?.name ?? 'Tundmatu' }}
                                </span>
                                <!-- Admin indicator on comment author -->
                                <span v-if="comment.user_id === 1" class="text-[10px] px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400 font-medium">
                                    Admin
                                </span>
                                <span class="text-xs text-muted-foreground">
                                    {{ formatDate(comment.created_at) }}
                                </span>
                            </div>
                            <p class="text-sm text-muted-foreground mt-1 whitespace-pre-line leading-relaxed">
                                {{ comment.content }}
                            </p>
                        </div>

                        <!-- Delete button — visible on hover if allowed -->
                        <button
                            v-if="canDeleteComment(comment)"
                            @click="deleteComment(comment.id)"
                            class="opacity-0 group-hover:opacity-100 transition-opacity p-1.5 rounded-lg hover:bg-destructive/10 text-muted-foreground hover:text-destructive shrink-0"
                            title="Kustuta kommentaar"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- No comments -->
                <div v-else class="text-center py-12 rounded-xl border border-dashed border-border text-muted-foreground text-sm">
                    Kommentaare pole veel. Ole esimene!
                </div>
            </section>

        </div>
    </AppLayout>
</template>