<script setup lang="ts">
import PostForm from '../components/PostForm.vue';

import { ref } from 'vue';

import { usePostsStore } from '../stores/posts';

const posts = usePostsStore();
const submitLoading = ref(false);

async function onSubmit({ $event, form }: { $event: any; form: any }) {
  submitLoading.value = true;
  try {
    await posts.create(form);
    $event.target.reset();
  } catch {
    /* empty */
  }
  submitLoading.value = false;
}
</script>
<template>
  <q-page>
    <base-breadcrumbs />

    <PostForm @submit="onSubmit" :submitLoading="submitLoading" />
  </q-page>
</template>
