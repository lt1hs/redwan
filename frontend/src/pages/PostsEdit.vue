<script setup lang="ts">
import { ref } from 'vue';
import { useRoute } from 'vue-router';

import PostForm from '../components/PostForm.vue';
import { usePostsStore } from '../stores/posts';

const route = useRoute();
const posts = usePostsStore();
const submitLoading = ref(false);
const formRef = ref<any>(null);

async function onSubmit({ form }: { $event: any; form: any }) {
  submitLoading.value = true;
  try {
    await posts.update(parseInt(route.params.id as string), form);
    formRef.value!.fetch();
  } catch {
    /* empty */
  }
  submitLoading.value = false;
}
</script>
<template>
  <q-page>
    <base-breadcrumbs />

    <PostForm
      :id="parseInt(route.params.id.toString())"
      @submit="onSubmit"
      :submitLoading="submitLoading"
      ref="formRef"
    />
  </q-page>
</template>
