<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
    order: Object,
});

const isPaid    = computed(() => props.order.payment_status === 'paid');
const isPending = computed(() => props.order.payment_status === 'pending');
const isFailed  = computed(() => props.order.payment_status === 'failed');

function formatDate(str) {
    return new Date(str).toLocaleDateString('et-EE', {
        day: 'numeric', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Pood', href: '/shop' },
    { title: 'Tellimus kinnitatud', href: '#' },
];
</script>

<template>
    <Head title="Tellimuse kinnitus" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-2xl mx-auto">

            <!-- Status banner -->
            <div
                :class="[
                    'rounded-2xl border p-8 text-center mb-6 shadow-sm',
                    isPaid    ? 'border-emerald-500/30 bg-emerald-500/8'  :
                    isPending ? 'border-amber-500/30 bg-amber-500/8'    :
                                'border-destructive/30 bg-destructive/8'
                ]"
            >
                <div class="text-5xl mb-4">
                    {{ isPaid ? '✅' : isPending ? '⏳' : '❌' }}
                </div>
                <h1 :class="[
                    'text-2xl font-semibold mb-2',
                    isPaid    ? 'text-emerald-600 dark:text-emerald-400' :
                    isPending ? 'text-amber-600 dark:text-amber-400'     :
                                'text-destructive'
                ]">
                    {{
                        isPaid    ? 'Makse õnnestus!' :
                        isPending ? 'Makse töötlemisel' :
                                   'Makse ebaõnnestus'
                    }}
                </h1>
                <p class="text-muted-foreground text-sm">
                    {{
                        isPaid    ? 'Tellimus on vastu võetud. Kinnitusmeil on saadetud aadressile ' + order.email :
                        isPending ? 'Makse kinnitatakse peagi. Saad teavituse e-postile.' :
                                   'Makse ei õnnestunud. Tooted on ostukorvis alles — proovi uuesti.'
                    }}
                </p>

                <!-- Retry button for failed payment -->
                <Link
                    v-if="isFailed"
                    href="/checkout"
                    class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 rounded-xl bg-primary text-primary-foreground text-sm font-medium hover:bg-primary/90 transition-all"
                >
                    Proovi uuesti
                </Link>
            </div>

            <!-- Order details -->
            <div class="rounded-2xl border border-border bg-card p-6 shadow-sm space-y-6">

                <!-- Order meta -->
                <div>
                    <h2 class="font-semibold text-foreground mb-4">Tellimuse andmed</h2>
                    <div class="grid sm:grid-cols-2 gap-3 text-sm">
                        <div class="flex flex-col gap-0.5">
                            <span class="text-muted-foreground">Tellimuse nr</span>
                            <span class="font-medium text-foreground">#{{ order.id }}</span>
                        </div>
                        <div class="flex flex-col gap-0.5">
                            <span class="text-muted-foreground">Kuupäev</span>
                            <span class="font-medium text-foreground">{{ formatDate(order.created_at) }}</span>
                        </div>
                        <div class="flex flex-col gap-0.5">
                            <span class="text-muted-foreground">Nimi</span>
                            <span class="font-medium text-foreground">{{ order.first_name }} {{ order.last_name }}</span>
                        </div>
                        <div class="flex flex-col gap-0.5">
                            <span class="text-muted-foreground">E-post</span>
                            <span class="font-medium text-foreground">{{ order.email }}</span>
                        </div>
                        <div class="flex flex-col gap-0.5">
                            <span class="text-muted-foreground">Telefon</span>
                            <span class="font-medium text-foreground">{{ order.phone }}</span>
                        </div>
                        <div class="flex flex-col gap-0.5">
                            <span class="text-muted-foreground">Makse staatus</span>
                            <span :class="[
                                'inline-flex items-center gap-1.5 font-medium',
                                isPaid    ? 'text-emerald-600 dark:text-emerald-400' :
                                isPending ? 'text-amber-600 dark:text-amber-400'     :
                                            'text-destructive'
                            ]">
                                <span class="h-2 w-2 rounded-full" :class="isPaid ? 'bg-emerald-500' : isPending ? 'bg-amber-500' : 'bg-destructive'"></span>
                                {{ isPaid ? 'Makstud' : isPending ? 'Ootel' : 'Ebaõnnestunud' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="h-px bg-border"></div>

                <!-- Ordered items -->
                <div>
                    <h2 class="font-semibold text-foreground mb-4">Tellitud tooted</h2>
                    <div class="flex flex-col gap-3">
                        <div
                            v-for="item in order.items"
                            :key="item.id"
                            class="flex items-center gap-3 p-3 rounded-xl bg-muted/40 border border-border"
                        >
                            <img
                                :src="item.image_url"
                                :alt="item.name"
                                class="h-12 w-12 rounded-lg object-cover shrink-0"
                            />
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-foreground line-clamp-1">{{ item.name }}</p>
                                <p class="text-xs text-muted-foreground">{{ item.quantity }} × {{ item.price.toFixed(2) }} €</p>
                            </div>
                            <p class="text-sm font-semibold text-foreground shrink-0">
                                {{ (item.price * item.quantity).toFixed(2) }} €
                            </p>
                        </div>
                    </div>
                </div>

                <div class="h-px bg-border"></div>

                <!-- Total -->
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-foreground">Kokku</span>
                    <span class="text-2xl font-bold text-foreground">{{ order.total.toFixed(2) }} €</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3 mt-6">
                <Link
                    href="/shop"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-primary-foreground text-sm font-medium hover:bg-primary/90 transition-all shadow-sm shadow-primary/20"
                >
                    ← Tagasi poodi
                </Link>
                <Link
                    href="/dashboard"
                    class="px-4 py-2.5 text-sm text-muted-foreground hover:text-foreground transition-colors"
                >
                    Dashboard
                </Link>
            </div>

        </div>
    </AppLayout>
</template>