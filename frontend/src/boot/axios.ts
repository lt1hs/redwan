import { boot } from 'quasar/wrappers';
import axios from 'axios';
import type { AxiosInstance, AxiosRequestConfig } from 'axios';
import type { App } from 'vue';
import { Notify } from 'quasar';

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $axios: AxiosInstance;
    $api: AxiosInstance;
  }
}

// Define the API Base URL consistently
// This should be http://91.109.114.156:8000/api for development
// And https://api.reality360d.com/api for production
const API_BASE_URL = 'http://91.109.114.156:8000/api';
console.log('[axios.ts - Global] API_BASE_URL:', API_BASE_URL);

// Define the CSRF Base URL consistently
// This should be http://91.109.114.156:8000 for development
// And https://api.reality360d.com for production
const CSRF_BASE_URL =
  import.meta.env.MODE === 'production'
    ? 'https://api.reality360d.com'
    : 'http://91.109.114.156:8000';
console.log('[axios.ts - Global] CSRF_BASE_URL for sanctum/csrf-cookie:', CSRF_BASE_URL); // ADDED MORE CONTEXT TO LOG

// Create a custom axios instance
const api = axios.create({
  baseURL: API_BASE_URL, // Use the consistent API_BASE_URL
  withCredentials: true, // Essential for sending/receiving cookies (like XSRF-TOKEN)
  headers: {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest', // Important for Laravel's AJAX detection
    'Content-Type': 'application/json'
  }
});

console.log('[axios.ts - Global] Axios API instance baseURL set to:', api.defaults.baseURL); // ADDED MORE CONTEXT TO LOG

// Function to get CSRF token from cookies
const getXsrfTokenFromCookie = () => {
  const cookies = document.cookie.split(';');
  for (const cookie of cookies) {
    const [name, value] = cookie.trim().split('=');
    if (name === 'XSRF-TOKEN') {
      return decodeURIComponent(value);
    }
  }
  return null;
};


// REVISED: Function to get CSRF token.
const getCsrfToken = async () => {
  try {
    console.log('[axios.ts - getCsrfToken] Attempting to fetch /sanctum/csrf-cookie...');

    const response = await axios.get(`${CSRF_BASE_URL}/sanctum/csrf-cookie`, {
      withCredentials: true,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    if (response.status === 204) {
      console.log('[axios.ts - getCsrfToken] CSRF cookie request successful (204 No Content)');
      const xsrfToken = getXsrfTokenFromCookie(); // Use your helper to read the cookie
      console.log('[axios.ts - getCsrfToken] XSRF cookie found and value:', xsrfToken ? xsrfToken.substring(0, 10) + '...' : 'not found');
      return xsrfToken;
    }
    return null;
  } catch (error) {
    console.error('[axios.ts - getCsrfToken] Failed to get CSRF token from /sanctum/csrf-cookie:', error);
    Notify.create({
      type: 'negative',
      message: 'Failed to retrieve CSRF token. Please check backend API URL.',
      position: 'top'
    });
    return null;
  }
};


// Add request interceptor to add the auth token and CSRF token
api.interceptors.request.use(
  async (config) => {
    // 1. Add Authorization Header
    const token = localStorage.getItem('auth_token');
    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    // 2. Handle CSRF Token for state-modifying requests
    if (['post', 'put', 'patch', 'delete'].includes(config.method?.toLowerCase() || '')) {
      let xsrfToken = getXsrfTokenFromCookie();

      if (!xsrfToken) {
        console.log('[axios.ts - Interceptor] No CSRF token cookie found for request. Fetching new one...');
        await getCsrfToken(); // This will fetch and set the cookie
        xsrfToken = getXsrfTokenFromCookie(); // Try to get it again after fetch
      }

      if (xsrfToken && config.headers) {
        config.headers['X-XSRF-TOKEN'] = xsrfToken;
        console.log('[axios.ts - Interceptor] Using CSRF token for request:', xsrfToken.substring(0, 10) + '...');
      } else {
        console.warn('[axios.ts - Interceptor] CSRF token is still missing after fetch. Request might fail.');
      }
    }

    // 3. Set appropriate Content-Type header
    if (config.data instanceof FormData) {
      delete config.headers['Content-Type'];
    } else {
      if (!config.headers['Content-Type']) {
        config.headers['Content-Type'] = 'application/json';
      }
    }

    // Log the request configuration for debugging
    console.log('[axios.ts - Interceptor] Request Config (before sending):', {
      url: config.url, // This is the relative path, not the full URL yet
      method: config.method,
      xsrfTokenHeader: config.headers['X-XSRF-TOKEN'] ? 'present' : 'missing',
      contentTypeHeader: config.headers['Content-Type'] || 'auto-set (FormData)'
    });
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Add response interceptor to handle errors
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    console.error('[axios.ts - Interceptor] API Error:', {
      status: error.response?.status,
      data: error.response?.data,
      message: error.response?.data?.message || error.message,
      config: error.config
    });

    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token');
      Notify.create({
        type: 'negative',
        message: 'Your session has expired. Please log in again.',
        position: 'top'
      });
      window.location.href = '/login';
    } else if (error.response?.status === 419) {
      console.error('[axios.ts - Interceptor] CSRF token mismatch or expired (419 error)');

      try {
        console.log('[axios.ts - Interceptor] Getting new CSRF token after 419 error');

        const response = await axios.get(`${CSRF_BASE_URL}/sanctum/csrf-cookie`, {
          withCredentials: true,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        if (response.status === 204) {
          const newXsrfToken = getXsrfTokenFromCookie();
          if (newXsrfToken && error.config.headers) {
            error.config.headers['X-XSRF-TOKEN'] = newXsrfToken;
            console.log('[axios.ts - Interceptor] New CSRF token obtained after 419 and applied to retry:', newXsrfToken.substring(0, 10) + '...');
            console.log('[axios.ts - Interceptor] Retrying the original request with new token and config');
            return api(error.config);
          } else {
            console.error('[axios.ts - Interceptor] Failed to get new CSRF token from cookie after 419');
            Notify.create({
              type: 'negative',
              message: 'Session expired. Failed to refresh security token. Please refresh the page and try again.',
              position: 'top'
            });
          }
        }
      } catch (tokenError) {
        console.error('[axios.ts - Interceptor] Failed to get new CSRF token after 419:', tokenError);
        Notify.create({
          type: 'negative',
          message: 'Session expired. Failed to refresh security token. Please refresh the page and try again.',
          position: 'top'
        });
      }
      return Promise.reject(error);
    } else if (error.response?.status === 422) {
      const errors = error.response.data.errors;
      if (errors) {
        Object.values(errors).forEach((errorMessages: any) => {
          if (Array.isArray(errorMessages)) {
            errorMessages.forEach((message: string) => {
              Notify.create({
                type: 'negative',
                message,
                position: 'top',
                timeout: 5000 // Display for 5 seconds
              });
            });
          } else {
            Notify.create({
              type: 'negative',
              message: errorMessages,
              position: 'top',
              timeout: 5000 // Display for 5 seconds
            });
          }
        });
      }
    } else {
      Notify.create({
        type: 'negative',
        message: error.response?.data?.message || 'An unexpected error occurred. Please try again.',
        position: 'top',
        timeout: 5000 // Display for 5 seconds
      });
    }

    return Promise.reject(error);
  }
);

// Mock SMS API responses for development mode
if (import.meta.env.MODE !== 'production') {
  const mockSmsHandlers: Record<string, (config?: AxiosRequestConfig) => [number, any]> = {
    '/api/admin/sms/credit': () => {
      console.log('[axios.ts - Mock] Using mock SMS credit API');
      return [
        200,
        {
          balance: 1000,
          currency: 'IRR',
          expiry_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString()
        }
      ];
    },
    '/api/admin/sms/logs': (config?: AxiosRequestConfig) => {
      console.log('[axios.ts - Mock] Using mock SMS logs API', config);
      const logs = Array(10)
        .fill(0)
        .map((_, i) => ({
          id: i + 1,
          phone: `+98505${i}${i}${i}${i}${i}`,
          message: `This is a test message ${i + 1}`,
          status: ['sent', 'failed', 'pending'][i % 3],
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
          type: 'test',
          recipient_name: `User ${i + 1}`,
          related_id: i
        }));
      return [200, { data: logs, total: 10 }];
    },
    '/api/admin/sms/logs/recent': () => {
      console.log('[axios.ts - Mock] Using mock recent SMS logs API');
      const logs = Array(5)
        .fill(0)
        .map((_, i) => ({
          id: i + 1,
          phone: `+98505${i}${i}${i}${i}${i}`,
          message: `This is a recent test message ${i + 1}`,
          status: ['sent', 'failed', 'pending'][i % 3],
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
          type: 'test',
          recipient_name: `User ${i + 1}`,
          related_id: i
        }));
      return [200, logs];
    },
    '/api/admin/sms/retry': () => {
      console.log('[axios.ts - Mock] Using mock SMS retry API');
      return [
        200,
        {
          id: 1,
          phone: '+985051234',
          message: 'This is a retried message',
          status: 'sent',
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
          type: 'test'
        }
      ];
    },
    '/api/admin/sms/statistics': () => {
      console.log('[axios.ts - Mock] Using mock SMS statistics API');
      return [
        200,
        {
          total: 100,
          sent: 75,
          failed: 15,
          pending: 10,
          today: 25
        }
      ];
    },
    '/api/admin/sms/send': (config?: AxiosRequestConfig) => {
      console.log('[axios.ts - Mock] Using mock SMS send API', config);
      if (!config || !config.data) {
        return [400, { error: 'No data provided' }];
      }

      const data = typeof config.data === 'string' ? JSON.parse(config.data) : config.data;
      return [
        200,
        {
          id: Math.floor(Math.random() * 1000),
          phone: data.phone || '98500000000',
          message: data.message || 'Mock message',
          status: 'sent',
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
          type: data.type,
          recipient_name: data.recipient_name,
          related_id: data.related_id
        }
      ];
    },
    '/api/admin/sms/status': (config?: AxiosRequestConfig) => {
      console.log('[axios.ts - Mock] Using mock SMS status API', config);
      return [
        200,
        {
          messageId: config?.url?.split('/').pop() || '12345',
          status: 'delivered',
          deliveryTime: new Date().toISOString()
        }
      ];
    },
    '/api/admin/sms/messages': () => {
      console.log('[axios.ts - Mock] Using mock SMS messages API');
      const messages = Array(5)
        .fill(0)
        .map((_, i) => ({
          id: i + 1,
          from: `+98505${i}${i}${i}${i}${i}`,
          to: '5000xxx',
          message: `This is an incoming message ${i + 1}`,
          receivedAt: new Date().toISOString()
        }));
      return [200, messages];
    }
  };

  // Intercept 404 responses for SMS API routes
  api.interceptors.response.use(
    (response) => response,
    (error) => {
      if (error.response && error.response.status === 404) {
        const url = error.config.url;
        console.log('[axios.ts - Mock Interceptor] Intercepting 404 for URL:', url); // Added log

        for (const [route, handler] of Object.entries(mockSmsHandlers)) {
          if (url.includes(route)) {
            console.warn(`[axios.ts - Mock Interceptor] Using mock response for ${url}`);
            const [status, data] = handler(error.config);
            return Promise.resolve({ data, status });
          }
        }

        if (url.includes('/api/admin/sms/retry/') && url.endsWith('/retry')) {
            console.warn(`[axios.ts - Mock Interceptor] Using mock response for retry URL: ${url}`); // Added log
            const [status, data] = mockSmsHandlers['/api/admin/sms/retry'](error.config);
            return Promise.resolve({ data, status });
        }
      }
      return Promise.reject(error);
    }
  );
}

export default boot((app: App) => {
  app.config.globalProperties.$axios = axios;
  app.config.globalProperties.$api = api;
});

export { api, getCsrfToken };