import { defineStore } from 'pinia';
import { ref } from 'vue';
import { api } from "@/utils/axios";
import { useQuasar } from 'quasar';
import { useHelper } from '@/composables/helper';

export const useSlidersStore = defineStore('sliders', () => {
  const sliders = ref<any[]>([]);

  const $q = useQuasar();
  const helper = useHelper();

  async function fetch() {
    const response = await api.get('/api/admin/sliders');
    sliders.value = response.data;
  }

  async function fetchDetails(id: number): Promise<any> {
    const response = await api.get('/api/admin/sliders/' + id);
    return response.data;
  }

  async function create(data: any) {
    try {
      const response = await api.post('/api/admin/sliders', data);
      $q.notify({
        type: 'positive',
        message: 'تم اضافة السلايدر'
      });
      return response.data;
    } catch (error) {
      helper.handleServerError(error);
    }
  }

  async function update(id: number, data: any) {
    try {
      await api.put('/api/admin/sliders/' + id, data);
      $q.notify({
        type: 'positive',
        message: 'تم تعديل السلايدر'
      });
    } catch (error) {
      helper.handleServerError(error);
    }
  }

  async function destroy(id: number) {
    await api.delete('/api/admin/sliders/' + id);
    $q.notify({
      color: 'green-4',
      textColor: 'white',
      icon: 'cloud_done',
      message: 'تم حذف السلايدر.'
    });
  }

  return {
    sliders,
    create,
    update,
    fetchDetails,
    fetch,
    destroy
  };
});
