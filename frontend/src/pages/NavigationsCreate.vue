<script setup lang="ts">
import NavigationForm from '../components/NavigationForm.vue';
import { ref } from 'vue';

import { useNavigationsStore } from '@/stores/navigations';

const navigations = useNavigationsStore();
const submitLoading = ref(false);

async function onSubmit({ $event, form }: { $event: any; form: any }) {
  submitLoading.value = true;
  try {
    await navigations.create(form);
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

    <NavigationForm @submit="onSubmit" :submitLoading="submitLoading" />
  </q-page>
</template>
