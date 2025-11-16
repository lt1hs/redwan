<script setup lang="ts">
import SliderForm from "../components/SliderForm.vue";
import { ref } from "vue";

import { useSlidersStore } from "../stores/sliders";

const sliders = useSlidersStore();
const submitLoading = ref(false);

async function onSubmit({ $event, form }: { $event: any; form: any }) {
  submitLoading.value = true;
  try {
    await sliders.create(form);
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

    <SliderForm @submit="onSubmit" :submitLoading="submitLoading" />
  </q-page>
</template>
