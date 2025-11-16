import { defineStore } from 'pinia';
import { api } from '@/utils/axios';

export const useCardsStore = defineStore('cards', {
  state: () => ({
    lastCreatedId: null as number | null,
    loading: false,
    error: null as any,
    cards: [] as any[],
    total: 0,
    currentPage: 1,
    lastPage: 1
  }),

  actions: {
    async list(page = 1, perPage = 10) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get('/admin/cards', {
          params: {
            page,
            per_page: perPage
          }
        });
        
        // Log the response for debugging
        console.log('Cards API Response:', response.data);
        
        // Handle different possible response structures
        if (response.data && Array.isArray(response.data)) {
          // If the response is directly an array
          this.cards = response.data;
          this.total = response.data.length;
        } else if (response.data && response.data.data) {
          // If the response has a data property
          this.cards = response.data.data;
          this.total = response.data.total || response.data.data.length;
          this.currentPage = response.data.current_page || page;
          this.lastPage = response.data.last_page || 1;
        } else {
          // If the response structure is unexpected
          console.error('Unexpected API response structure:', response.data);
          this.cards = [];
          this.total = 0;
        }
        
        return response.data;
      } catch (error) {
        console.error('Error fetching cards:', error);
        this.error = error;
        this.cards = [];
        this.total = 0;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async listAll() {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get('/admin/cards/all');
        
        // Log the response for debugging
        console.log('All Cards API Response:', response.data);
        
        // Handle the response
        if (response.data && Array.isArray(response.data)) {
          this.cards = response.data;
          this.total = response.data.length;
        } else {
          console.error('Unexpected API response structure:', response.data);
          this.cards = [];
          this.total = 0;
        }
        
        return response.data;
      } catch (error) {
        console.error('Error fetching all cards:', error);
        this.error = error;
        this.cards = [];
        this.total = 0;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async create(formData: FormData) {
      this.loading = true;
      this.error = null;
      try {
        // Log the FormData contents for debugging
        console.log('FormData contents:');
        const formDataObj: Record<string, string> = {};
        for (const [key, value] of formData.entries()) {
          console.log(`${key}:`, value);
          formDataObj[key] = value.toString();
        }
        console.log('FormData as object:', formDataObj);

        // Remove unique_code field if it exists
        if (formData.has('unique_code')) {
          formData.delete('unique_code');
        }

        // Try with timeout to handle potential server issues
        const response = await api.post('/admin/cards', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            Accept: 'application/json'
          },
          timeout: 30000 // Extend timeout to 30 seconds
        });
        
        console.log('API Response:', response.data);
        this.lastCreatedId = response.data.id || response.data.data?.id;
        // Refresh the list after creating a new card
        await this.list(this.currentPage);
        return response.data;
      } catch (error: any) {
        console.error('Card creation error:', error);
        
        // Check if the error is related to timeout
        if (error.code === 'ECONNABORTED') {
          console.error('Request timed out. This might be due to a large image file or server processing time.');
        }
        
        if (error.response) {
          console.error('Error response:', {
            status: error.response.status,
            statusText: error.response.statusText,
            headers: error.response.headers,
            data: error.response.data
          });
          
          // Try to log server error details if available
          if (error.response.status === 500 && error.response.data) {
            console.error('Server error details:', {
              message: error.response.data.message,
              exception: error.response.data.exception,
              file: error.response.data.file,
              line: error.response.data.line
            });
          }
          
          // If there are validation errors, throw them
          if (error.response.status === 422 && error.response.data.errors) {
            throw error;
          }
        }
        
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async get(id: number) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get(`/admin/cards/${id}`);
        console.log('GET Card Response:', {
          fullResponse: response,
          data: response.data,
          citizenship: {
            fa: response.data.citizenship_fa,
            en: response.data.citizenship_en
          },
          photo: response.data.personal_photo
        });
        
        // Enhanced logging for debugging photo URLs
        if (response.data) {
          if (response.data.personal_photo) {
            console.log('------- PHOTO URL DEBUGGING -------');
            console.log('Raw photo URL from API:', response.data.personal_photo);
            
            // Check if it's already an absolute URL
            const isAbsoluteUrl = response.data.personal_photo.startsWith('http');
            console.log('Is absolute URL?', isAbsoluteUrl);
            
            // Check if it contains storage/ path
            const hasStoragePath = response.data.personal_photo.includes('/storage/');
            console.log('Contains /storage/ path?', hasStoragePath);
            
            // Check for other patterns
            console.log('URL components:', {
              includes_photos_cards: response.data.personal_photo.includes('photos/cards'),
              includes_api: response.data.personal_photo.includes('/api/'),
              last_part: response.data.personal_photo.split('/').pop()
            });
            
            // For full resolution of image URL issues, log the base URL + relative URL combinations
            const baseUrl = window.location.origin;
            const filename = response.data.personal_photo.split('/').pop();
            
            console.log('Possible absolute URLs:');
            console.log(`1. ${baseUrl}/storage/app/public/photos/cards/${filename}`);
            console.log(`2. ${baseUrl}/storage/app/public/photos/cards/${filename}`);
            console.log(`3. http://localhost:8000/storage/app/public/photos/cards/${filename}`);
            console.log(`4. http://localhost:8000/storage/app/public/photos/cards/${filename}`);
            console.log(`5. http://localhost:8000/storege/app/public/photos/cards/${filename}`);
            
            // Test fetch on URLs to find accessible ones
            this.testPhotoUrls(filename);
            
            console.log('------- END PHOTO URL DEBUGGING -------');
          } else {
            console.warn('No photo found for card:', id);
          }
        }
        
        return response.data;
      } catch (error) {
        console.error('Error fetching card:', error);
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Helper method to test if photo URLs are accessible
    async testPhotoUrls(filename: string) {
      const backendUrl = "http://localhost:8000";
      const urls = [
        `${backendUrl}/storage/app/public/photos/cards/${filename}`,
        `${backendUrl}/storage/app/public/photos/cards/${filename}`,
        `${backendUrl}/public/storage/photos/cards/${filename}`,
        `${backendUrl}/images/${filename}`,
        `${backendUrl}/storage/${filename}`
      ];
      
      console.log('Testing photo URLs for accessibility...');
      
      for (const url of urls) {
        try {
          const response = await fetch(url, { method: 'HEAD' });
          console.log(`URL ${url}: ${response.ok ? 'ACCESSIBLE' : 'NOT FOUND'} (${response.status})`);
        } catch (error: any) {
          console.log(`URL ${url}: ERROR (${error.message})`);
        }
      }
    },

    async update(id: number, formData: FormData) {
      this.loading = true;
      this.error = null;
      try {
        // Add _method field for Laravel to correctly interpret PUT request with FormData
        formData.append('_method', 'PUT');
        const response = await api.post(`/admin/cards/${id}`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            Accept: 'application/json'
          }
        });
        await this.list(this.currentPage);
        return response.data;
      } catch (error) {
        console.error('Error updating card:', error);
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async delete(id: number) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.delete(`/admin/cards/${id}`);
        await this.list(this.currentPage);
        return response.data;
      } catch (error) {
        console.error('Error deleting card:', error);
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    }
  }
});
