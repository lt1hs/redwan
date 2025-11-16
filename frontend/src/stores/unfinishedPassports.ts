import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export interface UnfinishedPassport {
  id: number;
  full_name?: string;
  nationality?: string;
  passport_number?: string;
  date_of_birth?: string;
  residence_expiry_date?: string;
  phone_number?: string;
  mobile_number?: string;
  transaction_type?: string;
  address?: string;
  zipcode?: string;
  personal_photo?: string;
  passport_photo?: string;
  notes?: string;
  completion_status: 'مسودة' | 'قيد المراجعة' | 'جاهز للنقل';
  created_at: string;
  updated_at: string;
}

export const useUnfinishedPassportsStore = defineStore('unfinishedPassports', () => {
  const unfinishedPassports = ref<UnfinishedPassport[]>([]);

  async function fetch() {
    try {
      const response = await axios.get('/api/unfinished-passports-public');
      unfinishedPassports.value = Array.isArray(response.data) ? response.data : [];
      return response.data;
    } catch (error) {
      unfinishedPassports.value = [];
      throw error;
    }
  }

  async function create(data: FormData) {
    const response = await axios.post('/api/unfinished-passports-public', data);
    await fetch();
    return response.data;
  }

  async function update(id: number, data: FormData) {
    const response = await axios.post(`/api/unfinished-passports-public/${id}`, data);
    await fetch();
    return response.data;
  }

  async function destroy(id: number) {
    await axios.delete(`/api/unfinished-passports-public/${id}`);
    await fetch();
    return true;
  }

  async function convertToPassport(id: number) {
    const response = await axios.post(`/api/unfinished-passports-public/${id}/convert`);
    await fetch();
    return response.data;
  }

  async function importExcel(file: File) {
    try {
      const formData = new FormData();
      formData.append('file', file);
      console.log('Sending import request with file:', file.name);
      const response = await axios.post('/api/unfinished-passports-import', formData);
      console.log('Upload response:', response.data);
      await fetch();
      return response.data;
    } catch (error) {
      console.error('Store import error:', error);
      throw error;
    }
  }

  async function fetchById(id: number) {
    const response = await axios.get(`/api/unfinished-passports-public/${id}`);
    return response.data;
  }

  return {
    unfinishedPassports,
    fetch,
    fetchById,
    create,
    update,
    destroy,
    convertToPassport,
    importExcel
  };
});
