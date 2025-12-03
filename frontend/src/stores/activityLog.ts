import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { ActivityLog, ActivityLogResponse, ActivityLogFilter, User } from '@/types/activity';
import { api } from '@/boot/axios';
import { useQuasar } from 'quasar';
import { useHelper } from '@/composables/helper';

export const useActivityLogStore = defineStore('activityLog', () => {
  const loading = ref(false);
  const activities = ref<ActivityLog[]>([]);
  const $q = useQuasar();
  const helper = useHelper();

  async function fetch(
    params: ActivityLogFilter & { page: number; per_page: number }
  ): Promise<ActivityLogResponse> {
    loading.value = true;
    try {
      const response = await api.get<ActivityLogResponse>('/admin/activity-logs', { params });
      activities.value = response.data.data;
      return response.data;
    } catch (error) {
      helper.handleServerError(error);
      throw error;
    } finally {
      loading.value = false;
    }
  }

  async function fetchUsers(): Promise<User[]> {
    const response = await api.get<{ data: User[] }>('/admin/users');
    return response.data.data;
  }

  async function exportLogs(filter: ActivityLogFilter): Promise<void> {
    const response = await api.get('/admin/activity-logs/export', {
      params: filter,
      responseType: 'blob'
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `activity-log-${new Date().toISOString()}.xlsx`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  async function logActivity(data: {
    action: ActivityLog['action'];
    module: string;
    description: string;
    details?: any;
  }): Promise<void> {
    await api.post('/admin/activity-logs', data);
  }

  return {
    loading,
    activities,
    fetch,
    fetchUsers,
    exportLogs,
    logActivity
  };
});
