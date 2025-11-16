<script setup lang="ts">
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import RoleForm from '../components/RoleForm.vue';
import { useRolesStore } from '@/stores/roles';

const route = useRoute();
const roles = useRolesStore();
const submitLoading = ref(false);
const formRef = ref<any>(null);

async function onSubmit({ form }: { $event: any; form: any }) {
  submitLoading.value = true;
  try {
    await roles.update(parseInt(route.params.id as string), form);
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

    <RoleForm
      :id="parseInt(route.params.id.toString())"
      @submit="onSubmit"
      :submitLoading="submitLoading"
      ref="formRef"
    />
  </q-page>
</template>
