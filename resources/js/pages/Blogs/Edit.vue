<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
    blog: Object
});

const form = useForm({
    title: props.blog.title,
    description: props.blog.description
});

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Blogs', href: '/blogs' },
    { title: 'Edit Blog', href: `/blogs/${props.blog.id}/edit` }
];

function submit() {
    form.put(`/blogs/${props.blog.id}`);
}
</script>

<template>
    <Head title="Edit Blog" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 max-w-2xl">
            <h1 class="text-2xl font-bold mb-4">Edit Blog</h1>

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

                <button type="submit" class="px-4 py-2 bg-yellow-400 text-white rounded hover:bg-yellow-500">
                    Update
                </button>
                <Link href="/blogs" class="text-gray-500 underline">Cancel</Link>
            </form>
        </div>
    </AppLayout>
</template>