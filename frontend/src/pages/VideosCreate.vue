<script setup lang="ts">
import VideoForm from '../components/VideoForm.vue';
import { ref } from 'vue';

import { useVideosStore } from '../stores/videos';

const videos = useVideosStore();
const submitLoading = ref(false);

async function onSubmit({ $event, form }: { $event: any; form: any }) {
  submitLoading.value = true;
  try {
    await videos.create(form);
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

    <VideoForm @submit="onSubmit" :submitLoading="submitLoading" />
  </q-page>
</template>
