import { defineStore } from 'pinia';
import { ref } from 'vue';
import { api } from "@/utils/axios";
import { useQuasar } from 'quasar';
import { useHelper } from '@/composables/helper';

export const useAdminsStore = defineStore('admins', () => {
  const admins = ref<any[]>([]);

  const $q = useQuasar();
  const helper = useHelper();

  async function fetch() {
    const response = await api.get('/api/admin/users');
    admins.value = response.data;
  }

  async function fetchDetails(id: number): Promise<any> {
    const response = await api.get('/api/admin/users/' + id);
    return response.data;
  }

  async function create(data: any) {
    try {
      const response = await api.post('/api/admin/users', data);
      $q.notify({
        type: 'positive',
        message: 'تم اضافة المشرف'
      });
      return response.data;
    } catch (error) {
      helper.handleServerError(error);
    }
  }

  async function update(id: number, data: any) {
    try {
      await api.put('/api/admin/users/' + id, data);
      $q.notify({
        type: 'positive',
        message: 'تم تعديل المشرف'
      });
    } catch (error) {
      helper.handleServerError(error);
    }
  }

  async function destroy(id: number) {
    await api.delete('/api/admin/users/' + id);
    $q.notify({
      color: 'green-4',
      textColor: 'white',
      icon: 'cloud_done',
      message: 'تم حذف المشرف.'
    });
  }

  return {
    admins,
    create,
    update,
    fetchDetails,
    fetch,
    destroy
  };
});
