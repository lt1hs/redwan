<script setup lang="ts">
import PassForm from '../components/PassForm.vue';
import { ref } from 'vue';
import { usePassportsStore } from '../stores/passports';

const passports = usePassportsStore();
const submitLoading = ref(false);

async function onSubmit({ $event, form }: { $event: any; form: any }) {
  submitLoading.value = true;
  try {
    await passports.create(form);
    $event.target.reset();
    alert('تمت إضافة الجواز بنجاح!');
  } catch (error) {
    console.error(error);
    alert('حدث خطأ أثناء إضافة الجواز.');
  }
  submitLoading.value = false;
}
</script>

<template>
  <q-page>
    <base-breadcrumbs />
    <PassForm @submit="onSubmit" :submitLoading="submitLoading" />
  </q-page>
</template>
