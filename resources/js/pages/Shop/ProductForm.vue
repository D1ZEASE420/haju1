<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
    product: Object, // null = create mode, object = edit mode
});

const isEdit = !!props.product;

const form = useForm({
    name:        props.product?.name        ?? '',
    description: props.product?.description ?? '',
    price:       props.product?.price       ?? '',
    image_url:   props.product?.image_url   ?? '',
    category:    props.product?.category    ?? '',
    in_stock:    props.product?.in_stock    ?? true,
});

const CATEGORIES = [
    'elektroonika', 'kotid', 'jalanõud', 'mööbel', 'köök',
    'ilu', 'raamatud', 'sport', 'riided', 'muu'
];

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Pood', href: '/shop' },
    { title: isEdit ? 'Muuda toodet' : 'Lisa toode', href: '#' },
];

function submit() {
    if (isEdit) {
        form.put(`/shop/products/${props.product.id}`);
    } else {
        form.post('/shop/products');
    }
}
</script>

<template>
    <Head :title="isEdit ? 'Muuda toodet' : 'Lisa toode'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-2xl mx-auto">

            <div class="mb-8">
                <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                    {{ isEdit ? 'Muuda toodet' : 'Lisa uus toode' }}
                </h1>
                <p class="text-muted-foreground text-sm mt-1">
                    {{ isEdit ? 'Uuenda toote andmeid' : 'Lisa uus toode poodi' }}
                </p>
            </div>

            <div class="rounded-2xl border border-border bg-card p-6 shadow-sm">
                <form @submit.prevent="submit" class="flex flex-col gap-5">

                    <!-- Name -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Toote nimi <span class="text-destructive">*</span></label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="nt. Nahast seljakott"
                            :class="['px-4 py-3 rounded-xl border bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all', form.errors.name ? 'border-destructive' : 'border-border']"
                        />
                        <p v-if="form.errors.name" class="text-destructive text-xs">{{ form.errors.name }}</p>
                    </div>

                    <!-- Price + Category -->
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Hind (€) <span class="text-destructive">*</span></label>
                            <input
                                v-model="form.price"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                :class="['px-4 py-3 rounded-xl border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all', form.errors.price ? 'border-destructive' : 'border-border']"
                            />
                            <p v-if="form.errors.price" class="text-destructive text-xs">{{ form.errors.price }}</p>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Kategooria <span class="text-destructive">*</span></label>
                            <select
                                v-model="form.category"
                                :class="['px-4 py-3 rounded-xl border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all', form.errors.category ? 'border-destructive' : 'border-border']"
                            >
                                <option value="" disabled>Vali kategooria…</option>
                                <option v-for="cat in CATEGORIES" :key="cat" :value="cat">{{ cat }}</option>
                            </select>
                            <p v-if="form.errors.category" class="text-destructive text-xs">{{ form.errors.category }}</p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Kirjeldus <span class="text-destructive">*</span></label>
                        <textarea
                            v-model="form.description"
                            placeholder="Kirjelda toodet…"
                            rows="4"
                            :class="['px-4 py-3 rounded-xl border bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all resize-none', form.errors.description ? 'border-destructive' : 'border-border']"
                        ></textarea>
                        <p v-if="form.errors.description" class="text-destructive text-xs">{{ form.errors.description }}</p>
                    </div>

                    <!-- Image URL -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Pildi URL <span class="text-muted-foreground font-normal">(vabatahtlik)</span></label>
                        <input
                            v-model="form.image_url"
                            type="url"
                            placeholder="https://…"
                            :class="['px-4 py-3 rounded-xl border bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all', form.errors.image_url ? 'border-destructive' : 'border-border']"
                        />
                        <p v-if="form.errors.image_url" class="text-destructive text-xs">{{ form.errors.image_url }}</p>
                        <div v-if="form.image_url" class="mt-2">
                            <img :src="form.image_url" alt="Eelvaade" class="h-24 w-24 rounded-xl object-cover border border-border" @error="$event.target.style.display='none'" />
                        </div>
                    </div>

                    <!-- In stock toggle -->
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            @click="form.in_stock = !form.in_stock"
                            :class="[
                                'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                form.in_stock ? 'bg-primary' : 'bg-muted'
                            ]"
                        >
                            <span :class="['inline-block h-4 w-4 transform rounded-full bg-white transition-transform', form.in_stock ? 'translate-x-6' : 'translate-x-1']"></span>
                        </button>
                        <label class="text-sm font-medium text-foreground">
                            {{ form.in_stock ? 'Laos saadaval' : 'Laost otsas' }}
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-3 pt-2 border-t border-border">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-medium hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95 transition-all shadow-sm shadow-primary/20"
                        >
                            <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            {{ form.processing ? 'Salvestamine…' : (isEdit ? 'Salvesta muudatused' : 'Lisa toode') }}
                        </button>
                        <Link href="/shop" class="px-4 py-2.5 text-sm text-muted-foreground hover:text-foreground transition-colors">
                            Tühista
                        </Link>
                    </div>

                </form>
            </div>
        </div>
    </AppLayout>
</template>