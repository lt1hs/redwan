import { defineStore } from 'pinia';
import { ref } from 'vue';
import { api } from "@/utils/axios";
import { useQuasar } from 'quasar';
import { useHelper } from '@/composables/helper';

export const usePagesStore = defineStore('pages', () => {
  const pages = ref<any[]>([]);
  const $q = useQuasar();
  const helper = useHelper()

  async function fetch() {
    const response = await api.get('/api/admin/pages');
    pages.value = response.data;
  }

  async function create(data: any) {
    try {
      const response = await api.post('/api/admin/pages', data);
      $q.notify({
        type: 'positive',
        message: 'تم اضافة الصفحة'
      });

      return response.data;
    } catch (error) {
      helper.handleServerError(error);
    }
  }

  async function fetchDetails(id: number): Promise<any> {
    const response = await api.get('/api/admin/pages/' + id);
    return response.data as any;
  }

  async function update(id: number, data: any) {
    try {
      await api.put('/api/admin/pages/' + id, data);
      $q.notify({
        type: 'positive',
        message: 'تم تعديل الصفحة'
      });
    } catch (error) {
      helper.handleServerError(error);
    }
  }

  async function destroy(id: number) {
    await api.delete('/api/admin/pages/' + id);
    $q.notify({
      color: 'green-4',
      textColor: 'white',
      icon: 'cloud_done',
      message: 'تم حذف الصفحة.'
    });
  }

  return { pages, fetch, create, fetchDetails, update, destroy };
});
