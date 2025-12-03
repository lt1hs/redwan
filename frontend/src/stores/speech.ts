import { defineStore } from 'pinia';
import { api } from '@/boot/axios';

interface Speech {
  id: number;
  title: string;
  recipient: string;
  content: string;
  paper_size: 'A4' | 'A3';
  template_type?: string;
  header_image_url?: string;
  footer_image_url?: string;
  signature_image_url?: string;
  created_at: string;
  updated_at: string;
}

interface Template {
  name: string;
  header: string;
  content_template: string;
  footer: string;
}

interface State {
  speeches: Speech[];
  speech: Speech | null;
  templates: Record<string, Template>;
  loading: boolean;
  error: any;
}

export const useSpeechStore = defineStore('speech', {
  state: (): State => ({
    speeches: [],
    speech: null,
    templates: {},
    loading: false,
    error: null
  }),

  actions: {
    async fetchTemplates() {
      try {
        const response = await api.get('/speech-templates');
        this.templates = response.data;
        return response.data;
      } catch (error) {
        this.error = error;
        throw error;
      }
    },

    async fetch(params = {}) {
      this.loading = true;
      try {
        const response = await api.get('/speeches', { params });
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
        const response = await api.get(`/speeches/${id}`);
        this.speech = response.data;
        return response.data;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async create(data: Partial<Speech>) {
      this.loading = true;
      try {
        const response = await api.post('/speeches', data);
        return response.data;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async update(id: number, data: Partial<Speech>) {
      this.loading = true;
      try {
        const response = await api.put(`/speeches/${id}`, data);
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
        await api.delete(`/speeches/${id}`);
        this.speeches = this.speeches.filter((speech) => speech.id !== id);
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async duplicate(id: number) {
      this.loading = true;
      try {
        const response = await api.post(`/speeches/${id}/duplicate`);
        return response.data;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    }
  }
});
