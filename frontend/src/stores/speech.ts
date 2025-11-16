import { defineStore } from 'pinia';
import axios from '@/plugins/axios';

interface Speech {
  id: number;
  title: string;
  recipient: string;
  content: string;
  paper_size: 'A4' | 'A3';
  header_image_url?: string;
  footer_image_url?: string;
  signature_image_url?: string;
  created_at: string;
  updated_at: string;
}

interface State {
  speeches: Speech[];
  speech: Speech | null;
  loading: boolean;
  error: any;
}

export const useSpeechStore = defineStore('speech', {
  state: (): State => ({
    speeches: [],
    speech: null,
    loading: false,
    error: null
  }),

  actions: {
    async fetch(params = {}) {
      this.loading = true;
      try {
        const response = await axios.get<{ speeches: Speech[] }>('/api/speeches', { params });
        this.speeches = response.data.speeches;
        return response.data;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async get(id: number) {
      this.loading = true;
      try {
        const response = await axios.get<Speech>(`/api/speeches/${id}`);
        this.speech = response.data;
        return response.data;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async create(
      data: Partial<Speech> & Record<string, File | string | number | null | undefined>
    ) {
      this.loading = true;
      try {
        const formData = new FormData();
        Object.entries(data).forEach(([key, value]) => {
          if (value instanceof File) {
            formData.append(key, value);
          } else if (value !== undefined && value !== null) {
            formData.append(key, String(value));
          }
        });

        const response = await axios.post<Speech>('/api/speeches', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        return response.data;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async update(
      id: number,
      data: Partial<Speech> & Record<string, File | string | number | null | undefined>
    ) {
      this.loading = true;
      try {
        const formData = new FormData();
        Object.entries(data).forEach(([key, value]) => {
          if (value instanceof File) {
            formData.append(key, value);
          } else if (value !== undefined && value !== null) {
            formData.append(key, String(value));
          }
        });

        const response = await axios.post<Speech>(`/api/speeches/${id}`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        return response.data;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async delete(id: number) {
      this.loading = true;
      try {
        await axios.delete(`/api/speeches/${id}`);
        this.speeches = this.speeches.filter((speech) => speech.id !== id);
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    }
  }
});
