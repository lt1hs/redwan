import { defineStore } from 'pinia';
import { ref } from 'vue';
import { api } from "@/utils/axios";

export const useOptionsStore = defineStore('options', () => {
  const options = ref<{
    [key: string]: any;
  }>({});

  async function fetch() {
    const response = await api.get('/api/admin/stores/options');
    options.value = response.data;
  }

  fetch();

  return { options, fetch };
});
