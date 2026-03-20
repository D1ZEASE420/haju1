<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
    cart: Array,
    total: Number,
    stripePublicKey: String,
});

// ---- Always send CSRF token with Axios ----
axios.defaults.headers.common['X-CSRF-TOKEN'] =
    document.head.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

// ---- Form fields ----
const form = ref({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
});
const errors = ref({});

// ---- Stripe state ----
let stripe = null;
let elements = null;
let paymentElement = null;
const stripeReady = ref(false);
const stripeError = ref(null);
const clientSecret = ref(null);

// ---- UI state ----
const step = ref(1);
const processing = ref(false);
const paymentError = ref(null);

const cartTotal = computed(() => props.total ?? 0);

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Pood', href: '/shop' },
    { title: 'Kassa', href: '/checkout' },
];

// ---- Step 1: validate customer details ----
function validateDetails() {
    errors.value = {};
    if (!form.value.first_name.trim()) errors.value.first_name = 'Eesnimi on kohustuslik.';
    if (!form.value.last_name.trim())  errors.value.last_name  = 'Perenimi on kohustuslik.';
    if (!form.value.email.trim())      errors.value.email      = 'E-post on kohustuslik.';
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email))
        errors.value.email = 'Sisesta korrektne e-posti aadress.';
    if (!form.value.phone.trim()) errors.value.phone = 'Telefon on kohustuslik.';

    if (Object.keys(errors.value).length > 0) return;

    step.value = 2;
    initStripe();
}

// ---- Step 2: initialise Stripe ----
async function initStripe() {
    if (!props.stripePublicKey) {
        stripeError.value = 'Stripe on konfigureerimata. Kontrolli .env seadeid.';
        return;
    }

    stripeError.value = null;
    stripeReady.value = false;

    try {
        // 1. Create PaymentIntent on backend
        const { data } = await axios.post('/orders/payment-intent', {}, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (data.error) {
            stripeError.value = data.error;
            return;
        }

        clientSecret.value = data.clientSecret;

        // 2. Load Stripe.js dynamically
        await loadStripeJs();

        // 3. Mount Payment Element
        stripe = window.Stripe(props.stripePublicKey);
        elements = stripe.elements({
            clientSecret: clientSecret.value,
            appearance: {
                theme: document.documentElement.classList.contains('dark') ? 'night' : 'stripe',
                variables: {
                    colorPrimary:    '#3b82f6',
                    colorBackground: document.documentElement.classList.contains('dark') ? '#0f1117' : '#ffffff',
                    borderRadius:    '12px',
                    fontFamily:      'DM Sans, sans-serif',
                },
            },
        });

        paymentElement = elements.create('payment');

        // Small delay to ensure the DOM element is rendered
        await new Promise(resolve => setTimeout(resolve, 100));
        paymentElement.mount('#stripe-payment-element');
        paymentElement.on('ready', () => (stripeReady.value = true));

    } catch (e) {
        console.error('Stripe init error:', e);
        stripeError.value =
            e.response?.data?.error ??
            e.response?.data?.message ??
            e.message ??
            'Makse algatamine ebaõnnestus.';
    }
}

function loadStripeJs() {
    return new Promise((resolve, reject) => {
        if (window.Stripe) { resolve(); return; }
        const script = document.createElement('script');
        script.src = 'https://js.stripe.com/v3/';
        script.onload = resolve;
        script.onerror = () => reject(new Error('Stripe.js laadimine ebaõnnestus.'));
        document.head.appendChild(script);
    });
}

// ---- Step 2: submit payment ----
async function submitPayment() {
    if (!stripe || !elements || processing.value) return;

    processing.value = true;
    paymentError.value = null;

    const { error: stripeConfirmError, paymentIntent } = await stripe.confirmPayment({
        elements,
        redirect: 'if_required',
    });

    if (stripeConfirmError) {
        paymentError.value = stripeConfirmError.message;
        processing.value = false;
        return;
    }

    try {
        const { data } = await axios.post('/orders', {
            ...form.value,
            payment_intent_id: paymentIntent.id,
        }, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (data.payment_status === 'paid') {
            router.visit(`/orders/${data.order_id}/confirmation`);
        } else if (data.payment_status === 'pending') {
            paymentError.value = 'Makse on töötlemisel. Saad kinnituse e-postile.';
        } else {
            paymentError.value = 'Makse ebaõnnestus. Proovi uuesti.';
        }
    } catch (e) {
        console.error('Order store error:', e);
        paymentError.value =
            e.response?.data?.error ??
            e.response?.data?.message ??
            'Viga tellimuse salvestamisel.';
    } finally {
        processing.value = false;
    }
}
</script>

<template>
    <Head title="Kassa" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-5xl mx-auto">

            <div class="mb-8">
                <h1 class="text-2xl font-semibold tracking-tight text-foreground">Kassa</h1>
                <p class="text-muted-foreground text-sm mt-1">Täida andmed ja tee makse</p>
            </div>

            <div class="grid lg:grid-cols-5 gap-8">

                <!-- Left: form -->
                <div class="lg:col-span-3 space-y-5">

                    <!-- Step indicators -->
                    <div class="flex items-center gap-3 mb-6">
                        <div :class="['flex items-center gap-2 text-sm font-medium', step >= 1 ? 'text-primary' : 'text-muted-foreground']">
                            <span :class="['h-7 w-7 rounded-full flex items-center justify-center text-xs font-bold border-2 transition-colors', step >= 1 ? 'border-primary bg-primary text-primary-foreground' : 'border-border text-muted-foreground']">1</span>
                            Kontaktandmed
                        </div>
                        <div class="flex-1 h-px bg-border"></div>
                        <div :class="['flex items-center gap-2 text-sm font-medium', step >= 2 ? 'text-primary' : 'text-muted-foreground']">
                            <span :class="['h-7 w-7 rounded-full flex items-center justify-center text-xs font-bold border-2 transition-colors', step >= 2 ? 'border-primary bg-primary text-primary-foreground' : 'border-border text-muted-foreground']">2</span>
                            Makse
                        </div>
                    </div>

                    <!-- Step 1: Customer details -->
                    <div v-show="step === 1" class="rounded-2xl border border-border bg-card p-6 shadow-sm space-y-5">
                        <h2 class="font-semibold text-foreground">Kontaktandmed</h2>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Eesnimi <span class="text-destructive">*</span></label>
                                <input
                                    v-model="form.first_name"
                                    type="text"
                                    placeholder="Mari"
                                    :class="['px-4 py-3 rounded-xl border bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all', errors.first_name ? 'border-destructive' : 'border-border focus:border-primary/50']"
                                />
                                <p v-if="errors.first_name" class="text-destructive text-xs">{{ errors.first_name }}</p>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Perenimi <span class="text-destructive">*</span></label>
                                <input
                                    v-model="form.last_name"
                                    type="text"
                                    placeholder="Maasikas"
                                    :class="['px-4 py-3 rounded-xl border bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all', errors.last_name ? 'border-destructive' : 'border-border focus:border-primary/50']"
                                />
                                <p v-if="errors.last_name" class="text-destructive text-xs">{{ errors.last_name }}</p>
                            </div>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">E-post <span class="text-destructive">*</span></label>
                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="mari@näide.ee"
                                :class="['px-4 py-3 rounded-xl border bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all', errors.email ? 'border-destructive' : 'border-border focus:border-primary/50']"
                            />
                            <p v-if="errors.email" class="text-destructive text-xs">{{ errors.email }}</p>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Telefon <span class="text-destructive">*</span></label>
                            <input
                                v-model="form.phone"
                                type="tel"
                                placeholder="+372 5123 4567"
                                :class="['px-4 py-3 rounded-xl border bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all', errors.phone ? 'border-destructive' : 'border-border focus:border-primary/50']"
                            />
                            <p v-if="errors.phone" class="text-destructive text-xs">{{ errors.phone }}</p>
                        </div>

                        <button
                            @click="validateDetails"
                            class="w-full py-3 rounded-xl bg-primary text-primary-foreground text-sm font-medium hover:bg-primary/90 active:scale-[0.99] transition-all shadow-sm shadow-primary/20"
                        >
                            Jätka maksele →
                        </button>
                    </div>

                    <!-- Step 2: Payment -->
                    <div v-show="step === 2" class="rounded-2xl border border-border bg-card p-6 shadow-sm space-y-5">
                        <div class="flex items-center justify-between">
                            <h2 class="font-semibold text-foreground">Makseinfo</h2>
                            <button
                                @click="step = 1"
                                class="text-xs text-muted-foreground hover:text-foreground transition-colors"
                            >
                                ← Muuda andmeid
                            </button>
                        </div>

                        <!-- Customer summary -->
                        <div class="p-4 rounded-xl bg-muted/50 border border-border text-sm text-muted-foreground">
                            {{ form.first_name }} {{ form.last_name }} · {{ form.email }} · {{ form.phone }}
                        </div>

                        <!-- Stripe error -->
                        <div v-if="stripeError" class="p-4 rounded-xl border border-destructive/30 bg-destructive/10 text-destructive text-sm flex items-start gap-2">
                            <svg class="h-4 w-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p>{{ stripeError }}</p>
                                <button @click="initStripe" class="text-xs underline mt-1 hover:no-underline">Proovi uuesti</button>
                            </div>
                        </div>

                        <!-- Stripe Payment Element -->
                        <div v-if="!stripeError">
                            <div v-if="!stripeReady" class="flex items-center gap-3 py-6 text-muted-foreground text-sm">
                                <svg class="h-4 w-4 animate-spin shrink-0" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                Laaditakse maksevormi…
                            </div>
                            <div id="stripe-payment-element" class="min-h-[120px]"></div>
                        </div>

                        <!-- Payment error -->
                        <div v-if="paymentError" class="p-4 rounded-xl border border-destructive/30 bg-destructive/10 text-destructive text-sm flex items-start gap-2">
                            <svg class="h-4 w-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ paymentError }}
                        </div>

                        <!-- Pay button -->
                        <button
                            v-if="!stripeError"
                            @click="submitPayment"
                            :disabled="!stripeReady || processing"
                            class="w-full py-3 rounded-xl bg-primary text-primary-foreground text-sm font-medium hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.99] transition-all flex items-center justify-center gap-2 shadow-sm shadow-primary/20"
                        >
                            <svg v-if="processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            {{ processing ? 'Töötlemine…' : `Maksa ${cartTotal.toFixed(2)} €` }}
                        </button>

                        <p class="text-xs text-muted-foreground text-center flex items-center justify-center gap-1.5">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Turvaline makse Stripe kaudu — kaardi andmeid ei salvestata
                        </p>
                    </div>
                </div>

                <!-- Right: order summary -->
                <div class="lg:col-span-2">
                    <div class="rounded-2xl border border-border bg-card p-6 shadow-sm sticky top-6">
                        <h2 class="font-semibold text-foreground mb-4">Tellimuse kokkuvõte</h2>

                        <div class="flex flex-col gap-3 max-h-80 overflow-y-auto">
                            <div v-for="item in cart" :key="item.id" class="flex items-start gap-3">
                                <img
                                    :src="item.image_url"
                                    :alt="item.name"
                                    class="h-12 w-12 rounded-lg object-cover shrink-0"
                                />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-foreground line-clamp-1">{{ item.name }}</p>
                                    <p class="text-xs text-muted-foreground">Kogus: {{ item.quantity }}</p>
                                </div>
                                <p class="text-sm font-medium text-foreground shrink-0">
                                    {{ (item.price * item.quantity).toFixed(2) }} €
                                </p>
                            </div>
                        </div>

                        <div class="h-px bg-border my-4"></div>

                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground text-sm">Kokku</span>
                            <span class="text-2xl font-bold text-foreground">{{ cartTotal.toFixed(2) }} €</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>