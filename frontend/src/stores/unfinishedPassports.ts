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

  async function fetch(params: Record<string, any> = {}) {
    try {
      const response = await axios.get('/api/unfinished-passports-public', {
        params: {
          ...params,
          per_page: params.per_page || 10,
          page: params.page || 1
        }
      });
      
      if (params.per_page === 'all') {
        unfinishedPassports.value = Array.isArray(response.data.data) ? response.data.data : Array.isArray(response.data) ? response.data : [];
        return { 
          data: unfinishedPassports.value, 
          total: unfinishedPassports.value.length,
          current_page: 1,
          last_page: 1,
          per_page: 'all'
        };
      } else {
        unfinishedPassports.value = Array.isArray(response.data.data) ? response.data.data : Array.isArray(response.data) ? response.data : [];
        return {
          data: unfinishedPassports.value,
          total: response.data.total || unfinishedPassports.value.length,
          current_page: response.data.current_page || 1,
          last_page: response.data.last_page || 1,
          per_page: response.data.per_page || params.per_page || 10
        };
      }
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
    const token = localStorage.getItem('auth_token');
    
    // Debug: Log what we're sending
    console.log('Sending FormData entries:');
    for (let [key, value] of data.entries()) {
      console.log(`${key}:`, value);
    }
    
    const response = await window.fetch(`http://91.109.114.156:8000/api/admin/unfinished-passports/${id}`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`
      },
      body: data
    });
    
    if (!response.ok) {
      const errorText = await response.text();
      console.error('Backend error response:', errorText);
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const result = await response.json();
    console.log('Backend response:', result);
    return result;
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
    const token = localStorage.getItem('auth_token');
    const response = await window.fetch(`http://91.109.114.156:8000/api/admin/unfinished-passports/${id}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    return await response.json();
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
