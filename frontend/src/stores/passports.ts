import { defineStore } from 'pinia';
import { ref } from 'vue';
import { api } from '@/boot/axios'; // Import the configured API instance
import { useQuasar } from 'quasar';
import { useHelper } from '@/composables/helper';
import moment from 'moment';

interface Passport {
  id: number;
  full_name: string;
  nationality: string;
  passport_number: string;
  date_of_birth: string;
  residence_expiry_date: string;
  phone_number: string;
  mobile_number: string;
  passport_status: string;
  passport_delivery_date: string;
  transaction_type: string;
  payment_status: string;
  recipient_name: string;
  address: string;
  zipcode: string;
  delivered_by: string;
  personal_photo: string | null;
  passport_photo: string | null;
  unique_code: string;
  [key: string]: any;
}

export const usePassportsStore = defineStore('passports', () => {
  const passports = ref<Passport[]>([]);
  const helper = useHelper();
  const $q = useQuasar();

  // REMOVED: Redundant API_URL definition and getAuthHeaders
  // The 'api' instance from '@/boot/axios' already handles base URL,
  // Authorization header (via interceptor), and withCredentials.

  // Create a new passport - simple implementation
  async function create(data: any) {
    try {
      console.log(
        'Starting passport creation with data:',
        data instanceof FormData ? 'FormData object' : data
      );

      // Log the complete FormData if it's a FormData object
      if (data instanceof FormData) {
        console.log('FormData contents:');
        for (const [key, value] of data.entries()) {
          console.log(`${key}: ${value instanceof File ? `File: ${value.name}` : value}`);
        }
      }

      // Ensure payment_status is a string value (only if it's not FormData already handled)
      if (!(data instanceof FormData)) {
        if (data.payment_status && typeof data.payment_status !== 'string') {
          data.payment_status = (data.payment_status as any).value || 'pending';
          console.log('Fixed payment_status to:', data.payment_status);
        } else if (!data.payment_status) {
          data.payment_status = 'pending';
          console.log('Set default payment_status to: pending');
        }
      } else {
         const paymentStatus = data.get('payment_status');
         if (paymentStatus && typeof paymentStatus !== 'string') {
             data.delete('payment_status');
             if (typeof paymentStatus === 'object' && paymentStatus !== null) {
                 const value = (paymentStatus as any).value || 'pending';
                 data.append('payment_status', value);
                 console.log('Fixed payment_status in FormData to:', value);
             } else {
                 data.append('payment_status', 'pending');
                 console.log('Set default payment_status in FormData to: pending');
             }
         }
      }


      const url = `/admin/passports`;

      console.log('Sending passport data to:', api.defaults.baseURL + url);

      const response = await api.post(url, data);

      console.log('Response received:', response.status, response.statusText);
      console.log('Response data:', response.data);

      if (response.status === 201) {
        await fetch(); // Re-fetch to update the list, ensuring correct structure
        // Or, if your API returns the created passport directly, you can push it:
        // if (response.data && response.data.data) {
        //   passports.value.push(response.data.data);
        // } else if (response.data) { // If API returns the object directly without 'data' key
        //   passports.value.push(response.data);
        // }


        $q.notify({
          type: 'positive',
          message: 'تم اضافة الجواز بنجاح',
          position: 'top'
        });

        return response.data;
      } else {
        throw new Error(`Unexpected response status: ${response.status}`);
      }
    } catch (error: any) {
      console.error('Create passport error:', error);
      console.error('Error response:', error.response?.data);
      helper.handleServerError(error);
      throw error;
    }
  }

  // Fetch all passports, with optional filtering parameters
  async function fetch(params: Record<string, any> = {}) {
    try {
      const response = await api.get(`/admin/passports`, {
        params,
      });

      // *** THIS IS THE KEY CHANGE ***
      // Safely access data, assuming it's either directly in response.data (simple array)
      // or nested under response.data.data (common with Laravel Resource Collections)
      // Or an empty array if both are null/undefined.
      passports.value = response.data.data || response.data || [];
      
      console.log('Fetched passports data:', passports.value); // Added for debug


      return response;
    } catch (error) {
      console.error('Fetch passports error:', error); // More specific error log
      helper.handleServerError(error);
      throw error;
    }
  }

  // Fetch details of a specific passport by ID
  async function fetchDetails(id: number) {
    try {
      const response = await api.get(`/admin/passports/${id}`);
      // Safely access data, assuming it's either directly in response.data (single object)
      // or nested under response.data.data
      return response.data.data || response.data;
    } catch (error) {
      helper.handleServerError(error);
      throw error;
    }
  }

  // Fetch passport by ID
  async function fetchById(id: number) {
    try {
      const response = await api.get(`/admin/passports/${id}`);
      // Safely access data, assuming it's either directly in response.data (single object)
      // or nested under response.data.data
      return response.data.data || response.data;
    } catch (error) {
      helper.handleServerError(error);
      throw error;
    }
  }

  // Update passport
  async function update(id: number, data: any) {
    try {
      let formData: FormData;
      if (!(data instanceof FormData)) {
        formData = new FormData();
        for (const key in data) {
          if (data[key] !== null && data[key] !== undefined) {
            formData.append(key, data[key]);
          }
        }
      } else {
        formData = data;
      }
      
      formData.set('_method', 'PUT'); // Or 'PATCH' depending on your Laravel route

      console.log('Updating passport with data:',
        Array.from(formData.entries()).reduce((obj, [key, value]) => {
          obj[key] = value instanceof File ? `File: ${value.name}` : value;
          return obj;
        }, {} as Record<string, any>)
      );

      const response = await api.post(`/admin/passports/${id}`, formData);

      console.log('Update successful:', response.status);
      
      // Refresh passport list
      await fetch();
      
      $q.notify({
        type: 'positive',
        message: 'تم تحديث الجواز بنجاح',
        position: 'top'
      });
      return response.data;
    } catch (error) {
      helper.handleServerError(error);
      throw error;
    }
  }

  // Add destroy function
  async function destroy(id: number) {
    try {
      console.log(`Attempting to delete passport with ID: ${id}`);
      const response = await api.delete(`/admin/passports/${id}`);
      console.log(`Delete response status: ${response.status}`);
      if (response.status === 200 || response.status === 204) {
        console.log('Passport deleted successfully from backend. Updating frontend state.');
        const initialLength = passports.value.length;
        passports.value = passports.value.filter((passport) => passport.id !== id);
        console.log(`Passports array length changed from ${initialLength} to ${passports.value.length}`);
        return true;
      }
      console.log('Delete operation did not return status 200 or 204.');
      return false;
    } catch (error) {
      console.error('Error during passport deletion:', error);
      helper.handleServerError(error);
      throw error;
    }
  }

  // Add these methods inside the store definition
  async function generateReport(params: any) {
    try {
      const response = await api.get(`/reports/generate`, {
        params,
      });
      return response.data;
    } catch (error) {
      console.error('Error generating report:', error);
      throw error;
    }
  }

  async function exportReport(params: any) {
    try {
      const response = await api.get(`/reports/export`, {
        params,
        responseType: 'blob',
      });

      const blob = new Blob([response.data], { type: 'application/vnd.ms-excel' });
      const link = document.createElement('a');
      link.href = window.URL.createObjectURL(blob);
      link.download = `report-${params.type}-${new Date().toISOString().split('T')[0]}.xlsx`;
      link.click();
      window.URL.revokeObjectURL(link.href);
    } catch (error) {
      console.error('Error exporting report:', error);
      throw error;
    }
  }

  // Test function for file uploads
  async function testFileUpload(data: FormData) {
    try {
      console.log('Testing file upload with FormData');

      console.log('FormData entries for debug:');
      for (const [key, value] of data.entries()) {
        console.log(`${key}: ${value instanceof File ? `File: ${value.name}` : value}`);
      }

      const url = `/test-form-upload`;

      console.log('Sending test request to:', api.defaults.baseURL + url);

      const response = await api.post(url, data);

      console.log('Test response:', response.data);
      return response.data;
    } catch (error) {
      console.error('Test upload error:', error);
      throw error;
    }
  }

  // Simple test function to diagnose CSRF issues
  async function testSimplePost() {
    try {
      console.log('Testing GET request...');
      const getResponse = await api.get(`/test`);
      console.log('GET response:', getResponse.data);

      console.log('Testing POST request with JSON data...');
      const postResponse = await api.post(
        `/test`,
        {
          test: 'data',
          time: new Date().toISOString()
        }
      );
      console.log('POST response:', postResponse.data);

      return {
        getResponse: getResponse.data,
        postResponse: postResponse.data
      };
    } catch (error) {
      console.error('Simple test error:', error);
      throw error;
    }
  }

  // Direct upload without Laravel middleware (This function seems to duplicate 'create' for some reason)
  async function directUpload(data: FormData) {
    try {
      console.log('Starting direct upload with FormData');

      const paymentStatus = data.get('payment_status');
      if (paymentStatus && typeof paymentStatus !== 'string') {
        data.delete('payment_status');
        if (typeof paymentStatus === 'object' && paymentStatus !== null) {
          const value = (paymentStatus as any).value || 'pending';
          data.append('payment_status', value);
          console.log('Fixed payment_status in FormData to:', value);
        } else {
          data.append('payment_status', 'pending');
          console.log('Set default payment_status in FormData to: pending');
        }
      }

      console.log('FormData entries for debug:');
      for (const [key, value] of data.entries()) {
        console.log(`- ${key}: ${value instanceof File ? `File: ${value.name}` : value}`);
      }

      const url = `/admin/passports`;

      console.log('Sending data to direct passport API:', api.defaults.baseURL + url);

      const response = await api.post(url, data);

      console.log('Direct passport API response:', response.data);

      if (response.data.success) { // Assuming success property in response
        $q.notify({
          type: 'positive',
          message: 'تم اضافة الجواز بنجاح',
          position: 'top'
        });
      }

      return response.data;
    } catch (error) {
      console.error('Direct upload error:', error);
      throw error;
    }
  }

  return {
    passports,
    create,
    fetch,
    fetchDetails,
    fetchById,
    update,
    destroy,
    generateReport,
    exportReport,
    testFileUpload,
    testSimplePost,
    directUpload
  };
});
