<script setup>
import { computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
    products: Array,
    cart: Array,
    total: Number,
});

// ---- Local cart state (reactive, synced with server) ----
const cart = ref(props.cart ?? []);
const cartTotal = ref(props.total ?? 0);
const cartOpen = ref(false);
const loading = ref({}); // per-product loading state
const toast = ref(null); // { message, type }

const cartCount = computed(() =>
    cart.value.reduce((sum, item) => sum + item.quantity, 0)
);

// ---- Quantity selectors per product ----
const quantities = ref(
    Object.fromEntries((props.products ?? []).map(p => [p.id, 1]))
);

// ---- Toast helper ----
function showToast(message, type = 'success') {
    toast.value = { message, type };
    setTimeout(() => (toast.value = null), 3000);
}

// ---- API helpers ----
async function addToCart(product) {
    loading.value[product.id] = true;
    try {
        const { data } = await axios.post('/cart/add', {
            product_id: product.id,
            quantity: quantities.value[product.id] ?? 1,
        });
        cart.value = data.cart;
        cartTotal.value = data.total;
        cartOpen.value = true;
        showToast(`${product.name} lisatud ostukorvi!`);
    } catch {
        showToast('Viga toote lisamisel.', 'error');
    } finally {
        loading.value[product.id] = false;
    }
}

async function updateQty(productId, quantity) {
    const { data } = await axios.patch(`/cart/${productId}`, { quantity });
    cart.value = data.cart;
    cartTotal.value = data.total;
}

async function removeItem(productId) {
    const { data } = await axios.delete(`/cart/${productId}`);
    cart.value = data.cart;
    cartTotal.value = data.total;
}

async function clearCart() {
    const { data } = await axios.delete('/cart');
    cart.value = data.cart;
    cartTotal.value = data.total;
}

// ---- Categories for filter ----
const categories = computed(() => {
    const cats = [...new Set((props.products ?? []).map(p => p.category))];
    return ['kõik', ...cats.sort()];
});
const activeCategory = ref('kõik');

const filteredProducts = computed(() =>
    activeCategory.value === 'kõik'
        ? props.products
        : props.products.filter(p => p.category === activeCategory.value)
);

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Pood', href: '/shop' },
];
</script>

<template>
    <Head title="Pood" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-7xl mx-auto">

            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">Pood</h1>
                    <p class="text-muted-foreground text-sm mt-0.5">{{ filteredProducts.length }} toodet</p>
                </div>

                <!-- Cart button -->
                <button
                    @click="cartOpen = true"
                    class="relative inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-border bg-card hover:bg-accent hover:border-primary/30 transition-all text-sm font-medium text-foreground"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Ostukorv
                    <span
                        v-if="cartCount > 0"
                        class="absolute -top-1.5 -right-1.5 h-5 w-5 flex items-center justify-center rounded-full bg-primary text-primary-foreground text-[10px] font-bold"
                    >{{ cartCount }}</span>
                </button>
            </div>

            <!-- Category filter -->
            <div class="flex gap-2 flex-wrap mb-7">
                <button
                    v-for="cat in categories"
                    :key="cat"
                    @click="activeCategory = cat"
                    :class="[
                        'px-3.5 py-1.5 rounded-full text-sm font-medium transition-all capitalize',
                        activeCategory === cat
                            ? 'bg-primary text-primary-foreground shadow-sm shadow-primary/25'
                            : 'border border-border bg-card text-muted-foreground hover:text-foreground hover:border-primary/30'
                    ]"
                >
                    {{ cat }}
                </button>
            </div>

            <!-- Product grid -->
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="product in filteredProducts"
                    :key="product.id"
                    class="group flex flex-col rounded-2xl border border-border bg-card overflow-hidden hover:border-primary/30 hover:shadow-lg hover:shadow-primary/5 transition-all duration-200"
                >
                    <!-- Product image -->
                    <div class="relative aspect-[4/3] overflow-hidden bg-muted">
                        <img
                            :src="product.image_url"
                            :alt="product.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            loading="lazy"
                        />
                        <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full bg-background/90 backdrop-blur-sm text-xs font-medium text-foreground capitalize border border-border/50">
                            {{ product.category }}
                        </span>
                    </div>

                    <!-- Product info -->
                    <div class="flex flex-col flex-1 p-5 gap-3">
                        <div class="flex-1">
                            <h2 class="font-semibold text-foreground text-[15px] leading-snug">{{ product.name }}</h2>
                            <p class="text-muted-foreground text-sm mt-1.5 line-clamp-2 leading-relaxed">
                                {{ product.description }}
                            </p>
                        </div>

                        <!-- Price + qty + add to cart -->
                        <div class="pt-3 border-t border-border">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xl font-bold text-foreground">
                                    {{ product.price.toFixed(2) }} €
                                </span>
                                <!-- Quantity selector -->
                                <div class="flex items-center gap-1 border border-border rounded-lg overflow-hidden">
                                    <button
                                        @click="quantities[product.id] = Math.max(1, (quantities[product.id] || 1) - 1)"
                                        class="w-8 h-8 flex items-center justify-center text-muted-foreground hover:text-foreground hover:bg-accent transition-colors"
                                    >−</button>
                                    <span class="w-8 text-center text-sm font-medium text-foreground">
                                        {{ quantities[product.id] || 1 }}
                                    </span>
                                    <button
                                        @click="quantities[product.id] = Math.min(99, (quantities[product.id] || 1) + 1)"
                                        class="w-8 h-8 flex items-center justify-center text-muted-foreground hover:text-foreground hover:bg-accent transition-colors"
                                    >+</button>
                                </div>
                            </div>

                            <button
                                @click="addToCart(product)"
                                :disabled="loading[product.id]"
                                class="w-full py-2.5 rounded-xl bg-primary text-primary-foreground text-sm font-medium hover:bg-primary/90 disabled:opacity-60 disabled:cursor-not-allowed active:scale-[0.98] transition-all flex items-center justify-center gap-2"
                            >
                                <svg v-if="loading[product.id]" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ loading[product.id] ? 'Lisamine…' : 'Lisa ostukorvi' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Cart drawer ─────────────────────────────── -->
        <Teleport to="body">
            <!-- Overlay -->
            <Transition name="fade">
                <div
                    v-if="cartOpen"
                    @click="cartOpen = false"
                    class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40"
                ></div>
            </Transition>

            <!-- Drawer panel -->
            <Transition name="slide-right">
                <div
                    v-if="cartOpen"
                    class="fixed right-0 top-0 bottom-0 w-full max-w-sm bg-card border-l border-border shadow-2xl z-50 flex flex-col"
                >
                    <!-- Drawer header -->
                    <div class="flex items-center justify-between px-6 py-5 border-b border-border shrink-0">
                        <h2 class="font-semibold text-foreground">
                            Ostukorv
                            <span v-if="cartCount > 0" class="ml-1.5 text-sm font-normal text-muted-foreground">({{ cartCount }})</span>
                        </h2>
                        <div class="flex items-center gap-2">
                            <button
                                v-if="cart.length > 0"
                                @click="clearCart"
                                class="text-xs text-muted-foreground hover:text-destructive transition-colors"
                            >
                                Tühjenda
                            </button>
                            <button
                                @click="cartOpen = false"
                                class="p-1.5 rounded-lg hover:bg-accent text-muted-foreground hover:text-foreground transition-colors"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Cart items -->
                    <div class="flex-1 overflow-y-auto px-6 py-4">
                        <div v-if="cart.length === 0" class="flex flex-col items-center justify-center h-full text-center gap-3">
                            <div class="text-5xl">🛒</div>
                            <p class="font-medium text-foreground">Ostukorv on tühi</p>
                            <p class="text-sm text-muted-foreground">Lisa tooted poest ostukorvi</p>
                        </div>

                        <div v-else class="flex flex-col gap-4">
                            <div
                                v-for="item in cart"
                                :key="item.id"
                                class="flex gap-3 p-3 rounded-xl border border-border bg-background"
                            >
                                <img
                                    :src="item.image_url"
                                    :alt="item.name"
                                    class="h-16 w-16 rounded-lg object-cover shrink-0"
                                />
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-foreground text-sm line-clamp-1">{{ item.name }}</p>
                                    <p class="text-primary font-semibold text-sm mt-0.5">{{ item.price.toFixed(2) }} €</p>

                                    <!-- Qty controls -->
                                    <div class="flex items-center gap-2 mt-2">
                                        <div class="flex items-center gap-1 border border-border rounded-lg overflow-hidden">
                                            <button
                                                @click="updateQty(item.id, item.quantity - 1)"
                                                class="w-7 h-7 flex items-center justify-center text-muted-foreground hover:text-foreground hover:bg-accent text-sm transition-colors"
                                            >−</button>
                                            <span class="w-7 text-center text-sm font-medium text-foreground">{{ item.quantity }}</span>
                                            <button
                                                @click="updateQty(item.id, item.quantity + 1)"
                                                class="w-7 h-7 flex items-center justify-center text-muted-foreground hover:text-foreground hover:bg-accent text-sm transition-colors"
                                            >+</button>
                                        </div>
                                        <span class="text-xs text-muted-foreground ml-auto">
                                            {{ (item.price * item.quantity).toFixed(2) }} €
                                        </span>
                                        <button
                                            @click="removeItem(item.id)"
                                            class="p-1 rounded hover:bg-destructive/10 text-muted-foreground hover:text-destructive transition-colors"
                                        >
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Drawer footer -->
                    <div v-if="cart.length > 0" class="px-6 py-5 border-t border-border shrink-0 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground text-sm">Kokku</span>
                            <span class="text-xl font-bold text-foreground">{{ cartTotal.toFixed(2) }} €</span>
                        </div>
                        <Link
                            href="/checkout"
                            class="block w-full py-3 rounded-xl bg-primary text-primary-foreground text-sm font-medium text-center hover:bg-primary/90 active:scale-[0.98] transition-all shadow-sm shadow-primary/25"
                        >
                            Jätka kassasse →
                        </Link>
                    </div>
                </div>
            </Transition>

            <!-- Toast notification -->
            <Transition name="toast">
                <div
                    v-if="toast"
                    :class="[
                        'fixed bottom-6 left-1/2 -translate-x-1/2 z-[60] px-5 py-3 rounded-xl shadow-xl text-sm font-medium border flex items-center gap-2',
                        toast.type === 'error'
                            ? 'bg-destructive/10 border-destructive/30 text-destructive'
                            : 'bg-card border-border text-foreground'
                    ]"
                >
                    <span v-if="toast.type !== 'error'">✓</span>
                    {{ toast.message }}
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-right-enter-active, .slide-right-leave-active { transition: transform 0.3s cubic-bezier(0.4,0,0.2,1); }
.slide-right-enter-from, .slide-right-leave-to { transform: translateX(100%); }

.toast-enter-active, .toast-leave-active { transition: all 0.25s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(-50%) translateY(12px); }
</style>