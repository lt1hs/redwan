<script setup lang="ts">
import AdminForm from '../components/AdminForm.vue';
import { ref } from 'vue';

import { useAdminsStore } from '../stores/admins';

const admins = useAdminsStore();
const submitLoading = ref(false);

async function onSubmit({ $event, form }: { $event: any; form: any }) {
  submitLoading.value = true;
  try {
    await admins.create(form);
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

    <AdminForm @submit="onSubmit" :submitLoading="submitLoading" />
  </q-page>
</template>
