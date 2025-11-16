import axios, { AxiosInstance, AxiosRequestConfig, AxiosResponse, InternalAxiosRequestConfig } from 'axios';

// Create axios instance with base URL
const axiosInstance: AxiosInstance = axios.create({
  baseURL: '/api', // Use relative URL for proxy
  headers: {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  withCredentials: true
});

// Add request interceptor
axiosInstance.interceptors.request.use(
  async (config: InternalAxiosRequestConfig): Promise<InternalAxiosRequestConfig> => {
    // Get token from localStorage
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    // For multipart/form-data, don't set Content-Type
    if (config.data instanceof FormData) {
      delete config.headers['Content-Type'];
    }

    // Get CSRF token if not already set
    if (!document.cookie.includes('XSRF-TOKEN')) {
      await axios.get('/sanctum/csrf-cookie');
    }

    return config;
  },
  (error: any) => {
    return Promise.reject(error);
  }
);

// Add response interceptor
axiosInstance.interceptors.response.use(
  (response: AxiosResponse) => response,
  async (error: any) => {
    if (error.response?.status === 401) {
      // Handle unauthorized access
      localStorage.removeItem('token');
      // Only redirect to login if we're not already on the login page
      if (!window.location.pathname.includes('/login')) {
        window.location.href = '/login';
      }
    } else if (error.response?.status === 419) {
      // CSRF token mismatch, try to refresh and retry the request
      await axios.get('/sanctum/csrf-cookie');
      return axiosInstance(error.config);
    }
    return Promise.reject(error);
  }
);

export default axiosInstance; 