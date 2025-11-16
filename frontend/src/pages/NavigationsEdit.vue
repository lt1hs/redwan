<script setup lang="ts">
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import NavigationForm from '../components/NavigationForm.vue';
import { useNavigationsStore } from '@/stores/navigations';

const route = useRoute();
const navigations = useNavigationsStore();
const submitLoading = ref(false);
const formRef = ref<any>(null);

async function onSubmit({ form }: { $event: any; form: any }) {
  submitLoading.value = true;
  try {
    await navigations.update(parseInt(route.params.id as string), form);
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

    <NavigationForm
      :id="parseInt(route.params.id.toString())"
      @submit="onSubmit"
      :submitLoading="submitLoading"
      ref="formRef"
    />
  </q-page>
</template>
