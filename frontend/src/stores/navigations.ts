import { defineStore } from 'pinia';
import { ref } from 'vue';
import { api } from "@/utils/axios";
import { useQuasar } from 'quasar';
import { useHelper } from '@/composables/helper';


export const useNavigationsStore = defineStore('navigations', () => {
  const navigations = ref<any[]>([]);
  const $q = useQuasar();
  const helper = useHelper()

  async function fetch() {
    const response = await api.get('/api/admin/navigations');
    navigations.value = response.data;
  }

  async function create(data: any) {
    try {
      const response = await api.post('/api/admin/navigations', data);
      $q.notify({
        type: 'positive',
        message: 'تم اضافة القائمة'
      });

      return response.data;
    } catch (error) {
      helper.handleServerError(error);
    }
  }

  async function fetchDetails(id: number): Promise<any> {
    const response = await api.get('/api/admin/navigations/' + id);
    return response.data as any;
  }

  async function update(id: number, data: any) {
    try {
      await api.put('/api/admin/navigations/' + id, data);
      $q.notify({
        type: 'positive',
        message: 'تم تعديل القائمة'
      });
    } catch (error) {
      helper.handleServerError(error);
    }
  }

  async function destroy(id: number) {
    await api.delete('/api/admin/navigations/' + id);
    $q.notify({
      color: 'green-4',
      textColor: 'white',
      icon: 'cloud_done',
      message: 'تم حذف القائمة.'
    });
  }

  return { navigations, fetch, create, fetchDetails, update, destroy };
});
