<script setup lang="ts">
import RoleForm from '../components/RoleForm.vue';
import { ref } from 'vue';

import { useRolesStore } from '@/stores/roles';

const roles = useRolesStore();
const submitLoading = ref(false);

async function onSubmit({ $event, form }: { $event: any; form: any }) {
  submitLoading.value = true;
  try {
    await roles.create(form);
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

    <RoleForm @submit="onSubmit" :submitLoading="submitLoading" />
  </q-page>
</template>
