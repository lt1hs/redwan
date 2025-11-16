import { defineStore } from 'pinia';
import { ref } from 'vue';
import { api } from "@/utils/axios";
import { useQuasar } from 'quasar';
import { useHelper } from '@/composables/helper';


export const usePostsStore = defineStore('posts', () => {
  const posts = ref<any[]>([]);
  const $q = useQuasar();
  const helper = useHelper()

  async function fetch() {
    const response = await api.get('/api/admin/posts');
    posts.value = response.data;
  }

  async function create(data: any) {
    try {
      const response = await api.post('/api/admin/posts', data);
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
    const response = await api.get('/api/admin/posts/' + id);
    return response.data;
  }

  async function update(id: number, data: any) {
    try {
      await api.put('/api/admin/posts/' + id, data);
      $q.notify({
        type: 'positive',
        message: 'تم تعديل الخبر'
      });
    } catch (error) {
      helper.handleServerError(error);
    }
  }

  async function destroy(id: number) {
    await api.delete('/api/admin/posts/' + id);
    $q.notify({
      color: 'green-4',
      textColor: 'white',
      icon: 'cloud_done',
      message: 'تم حذف الخبر.'
    });
  }

  return { posts, fetch, create, fetchDetails, update, destroy };
});
