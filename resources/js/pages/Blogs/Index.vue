<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
    blogs: Array
});

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Blogs', href: '/blogs' }
];
</script>

<template>
    <Head title="Blogs" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <!-- Header -->
            <div class="flex justify-between mb-4">
                <h1 class="text-2xl font-bold">Blogs</h1>
                <Link href="/blogs/create" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Add Blog
                </Link>
            </div>

            <!-- No blogs -->
            <div v-if="blogs.length === 0" class="text-gray-500">
                No blogs yet.
            </div>

            <!-- Blog list -->
            <ul>
                <li v-for="blog in blogs" :key="blog.id" class="border p-4 rounded mb-2 flex justify-between items-center">
                    
                    <!-- Blog info with clickable title -->
                    <div>
                        <Link :href="`/blogs/${blog.id}`" class="hover:underline">
                            <h2 class="font-semibold text-lg">{{ blog.title }}</h2>
                        </Link>
                        <p class="text-gray-600">{{ blog.description }}</p>
                        <p class="text-sm text-gray-400">Comments: {{ blog.comments_count }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2">
                        <Link :href="`/blogs/${blog.id}/edit`" class="px-2 py-1 bg-yellow-400 text-white rounded hover:bg-yellow-500">
                            Edit
                        </Link>
                        <form :action="`/blogs/${blog.id}`" method="POST" @submit.prevent="$inertia.delete($event.target.action)">
                            <button type="submit" class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">
                                Delete
                            </button>
                        </form>
                    </div>

                </li>
            </ul>
        </div>
    </AppLayout>
</template>