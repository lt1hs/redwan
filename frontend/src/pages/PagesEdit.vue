<script setup lang="ts">
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import PageForm from '../components/PageForm.vue';
import { usePagesStore } from '@/stores/pages';

const route = useRoute();
const pages = usePagesStore();
const submitLoading = ref(false);
const formRef = ref<any>(null);

async function onSubmit({ form }: { $event: any; form: any }) {
  submitLoading.value = true;
  try {
    await pages.update(parseInt(route.params.id as string), form);
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

    <PageForm
      :id="parseInt(route.params.id.toString())"
      @submit="onSubmit"
      :submitLoading="submitLoading"
      ref="formRef"
    />
  </q-page>
</template>
