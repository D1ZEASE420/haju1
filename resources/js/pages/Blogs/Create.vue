<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const form = useForm({
    title: '',
    description: ''
});

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Blogs', href: '/blogs' },
    { title: 'Add Blog', href: '/blogs/create' }
];

function submit() {
    form.post('/blogs', {
        onSuccess: () => form.reset()
    });
}
</script>

<template>
    <Head title="Add Blog" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 max-w-2xl">
            <h1 class="text-2xl font-bold mb-4">Add Blog</h1>

            <form @submit.prevent="submit" class="flex flex-col gap-4">
                <input
                    v-model="form.title"
                    type="text"
                    placeholder="Title"
                    class="border p-2 rounded"
                />

                <textarea
                    v-model="form.description"
                    placeholder="Description"
                    class="border p-2 rounded"
                    rows="5"
                ></textarea>

                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Save
                </button>
                <Link href="/blogs" class="text-gray-500 underline">Cancel</Link>
            </form>
        </div>
    </AppLayout>
</template>