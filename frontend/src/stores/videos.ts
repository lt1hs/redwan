import { defineStore } from 'pinia';
import { ref } from 'vue';
import { api } from "@/utils/axios";
import { useQuasar } from 'quasar';
import { useHelper } from '@/composables/helper';


export const useVideosStore = defineStore('videos', () => {
  const videos = ref<any[]>([]);
  const $q = useQuasar();
  const helper = useHelper()

  async function fetch() {
    const response = await api.get('/api/admin/videos');
    videos.value = response.data;
  }

  async function create(data: any) {
    try {
      const response = await api.post('/api/admin/videos', data);
      $q.notify({
        type: 'positive',
        message: 'تم اضافة الخبر'
      });

      return response.data;
    } catch (error) {
      helper.handleServerError(error);
    }
  }

  async function fetchDetails(id: number): Promise<any> {
    const response = await api.get('/api/admin/videos/' + id);
    return response.data;
  }

  async function update(id: number, data: any) {
    try {
      await api.put('/api/admin/videos/' + id, data);
      $q.notify({
        type: 'positive',
        message: 'تم تعديل الخبر'
      });
    } catch (error) {
      helper.handleServerError(error);
    }
  }

  async function destroy(id: number) {
    await api.delete('/api/admin/videos/' + id);
    $q.notify({
      color: 'green-4',
      textColor: 'white',
      icon: 'cloud_done',
      message: 'تم حذف الخبر.'
    });
  }

  return { videos, fetch, create, fetchDetails, update, destroy };
});
