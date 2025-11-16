<script setup lang="ts">
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import VideoForm from '../components/VideoForm.vue';
import { useVideosStore } from '../stores/videos';

const route = useRoute();
const videos = useVideosStore();
const submitLoading = ref(false);
const formRef = ref<any>(null);

async function onSubmit({ form }: { $event: any; form: any }) {
  submitLoading.value = true;
  try {
    await videos.update(parseInt(route.params.id as string), form);
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

    <VideoForm
      :id="parseInt(route.params.id.toString())"
      @submit="onSubmit"
      :submitLoading="submitLoading"
      ref="formRef"
    />
  </q-page>
</template>
