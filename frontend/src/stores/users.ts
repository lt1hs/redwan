import { defineStore } from 'pinia';
import { api } from '@/utils/axios';

export const useUsersStore = defineStore('users', {
  state: () => ({
    users: [] as any[],
    roles: [] as any[],
    permissions: [] as any[],
    loading: false,
    error: null as any,
  }),

  actions: {
    async fetchUsers() {
      this.loading = true;
      try {
        const response = await api.get('/admin/users');
        this.users = response.data.data;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async createUser(userData: any) {
      this.loading = true;
      try {
        const response = await api.post('/admin/users', userData);
        this.users.push(response.data.data);
        return response.data.data;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateUser(id: number, userData: any) {
      this.loading = true;
      try {
        const response = await api.put(`/admin/users/${id}`, userData);
        const index = this.users.findIndex(u => u.id === id);
        if (index !== -1) {
          this.users[index] = response.data.data;
        }
        return response.data.data;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deleteUser(id: number) {
      this.loading = true;
      try {
        await api.delete(`/admin/users/${id}`);
        this.users = this.users.filter(u => u.id !== id);
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async assignRole(userId: number, role: string) {
      this.loading = true;
      try {
        const response = await api.post(`/admin/users/${userId}/assign-role`, { role });
        const index = this.users.findIndex(u => u.id === userId);
        if (index !== -1) {
          this.users[index] = response.data.data;
        }
        return response.data.data;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchRoles() {
      try {
        const response = await api.get('/admin/roles');
        this.roles = response.data.data;
      } catch (error) {
        this.error = error;
        throw error;
      }
    },

    async fetchPermissions() {
      try {
        const response = await api.get('/admin/roles/permissions/all');
        this.permissions = response.data.data;
      } catch (error) {
        this.error = error;
        throw error;
      }
    },

    async createRole(roleData: any) {
      this.loading = true;
      try {
        const response = await api.post('/admin/roles', roleData);
        this.roles.push(response.data.data);
        return response.data.data;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateRole(id: number, roleData: any) {
      this.loading = true;
      try {
        const response = await api.put(`/admin/roles/${id}`, roleData);
        const index = this.roles.findIndex(r => r.id === id);
        if (index !== -1) {
          this.roles[index] = response.data.data;
        }
        return response.data.data;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deleteRole(id: number) {
      this.loading = true;
      try {
        await api.delete(`/admin/roles/${id}`);
        this.roles = this.roles.filter(r => r.id !== id);
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    }
  }
});
