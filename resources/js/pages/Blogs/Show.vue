<template>
  <div>
    <h1>{{ blog.title }}</h1>
    <p>{{ blog.description }}</p>

    <h2>Comments</h2>
    <ul>
      <li v-for="comment in blog.comments" :key="comment.id">
        {{ comment.content }}
        <button v-if="comment.user_id === user.id" @click="deleteComment(comment.id)">Delete</button>
      </li>
    </ul>

    <form @submit.prevent="addComment">
      <textarea v-model="newComment" placeholder="Add a comment"></textarea>
      <button type="submit">Post Comment</button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  blog: Object
});

const { props: pageProps } = usePage();
const user = pageProps.auth.user;

const newComment = ref('');

const addComment = async () => {
  if (!newComment.value.trim()) return;

  await axios.post(`/blogs/${props.blog.id}/comments`, {
    content: newComment.value
  });

  window.location.reload();
};

const deleteComment = async (id) => {
  await axios.delete(`/comments/${id}`);
  window.location.reload();
};
</script>