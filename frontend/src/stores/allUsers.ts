import { defineStore } from 'pinia';
import { ref } from 'vue';
import { api } from '@/utils/axios';

export const useAllUsersStore = defineStore('allUsers', () => {
  const users = ref([]);
  const loading = ref(false);
  const pagination = ref({
    page: 1,
    rowsPerPage: 10,
    rowsNumber: 0,
  });

  const fetchUsers = async (params = {}) => {
    loading.value = true;
    try {
      const response = await api.get('/admin/all-users', { params });
      users.value = response.data.data;
      pagination.value = {
        page: response.data.current_page,
        rowsPerPage: response.data.per_page === 'all' ? 0 : response.data.per_page,
        rowsNumber: response.data.total,
      };
      return response.data;
    } catch (error) {
      console.error('Error fetching all users:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  };

  return {
    users,
    loading,
    pagination,
    fetchUsers,
  };
});
