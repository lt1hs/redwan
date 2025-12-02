import axios from 'axios';
import type { AxiosRequestConfig } from 'axios'; // Import AxiosRequestConfig for type hinting

// Define the base URL based on environment
const BASE_URL =
  import.meta.env.NODE_ENV === 'production'
    ? 'https://api.reality360d.com'
    : 'http://91.109.114.156:8000/api'; // Ensure this has '/api'

console.log('Using API base URL:', BASE_URL);

// Note: We're using API token authentication (Bearer tokens) with Sanctum
// CSRF tokens are not needed for API routes - only for cookie-based session auth

// Function to extract JSON from response that might contain PHP warnings
function extractJSONFromResponse(responseData: any) {
  if (typeof responseData !== 'string') {
    return responseData;
  }

  // If the response contains a JSON object embedded in PHP warnings/notices
  if (responseData.includes('{') && responseData.includes('}')) {
    try {
      const jsonStartIndex = responseData.indexOf('{');
      const jsonString = responseData.substring(jsonStartIndex);
      return JSON.parse(jsonString);
    } catch (error) {
      console.error('Failed to extract JSON from malformed response:', error);
      return responseData;
    }
  }

  return responseData;
}

const api = axios.create({
  baseURL: BASE_URL,
  headers: {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  withCredentials: false // We're using API tokens, not cookies
});

// Add request interceptor to add the auth token and CSRF token
api.interceptors.request.use(
  async (config) => {
    // Set Content-Type to application/json only if not FormData
    if (config.headers && !(config.data instanceof FormData)) {
      config.headers['Content-Type'] = 'application/json';
    }

    // Add Authorization header with JWT token if available in localStorage
    const token = localStorage.getItem('auth_token');
    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    // CSRF Token handling is NOT needed for API token authentication
    // We're using Bearer tokens (Sanctum API tokens), not session-based auth
    // CSRF tokens are only needed for cookie-based authentication
    // Skip CSRF token handling entirely for API routes

    // Log the request configuration for debugging
    if (import.meta.env.NODE_ENV === 'development') {
      console.log(`[API Request] ${config.method?.toUpperCase()} ${config.url}`, {
        url: config.url,
        method: config.method,
        headers: config.headers, // Log all headers for full context
        data: config.data,
        baseURL: config.baseURL // Confirm baseURL in config
      });
    }
    return config;
  },
  (error) => {
    console.error('Request error (Axios interceptor):', error);
    return Promise.reject(error);
  }
);

// Add response interceptor to handle errors (401, 419) and malformed JSON
api.interceptors.response.use(
  (response) => {
    // Log successful responses in development mode
    if (import.meta.env.NODE_ENV === 'development') {
      console.log(`[API Response] ${response.status} ${response.config.url}`, response);
    }

    // Check if response contains PHP warnings before JSON
    if (typeof response.data === 'string' && response.data.includes('<br />')) {
      console.warn('Response contains PHP warnings, attempting to extract JSON');
      response.data = extractJSONFromResponse(response.data);
    }

    return response;
  },
  async (error) => {
    console.error('API Error (Response Interceptor):', error);

    // Get the original request config to retry if needed
    const originalRequest = error.config;

    // Handle 401 Unauthorized (Token expired/invalid)
    if (error.response?.status === 401) {
      console.warn('401 Unauthorized response received, clearing token and redirecting.');
      localStorage.removeItem('auth_token');
      delete api.defaults.headers.common['Authorization'];
      window.location.href = '/login'; // Redirect to login page
      return Promise.reject(error); // Reject the original error
    }

    // Handle 419 CSRF Token Mismatch (shouldn't happen with API token auth)
    if (error.response?.status === 419) {
      console.error('419 CSRF Token Mismatch - This should not happen with API token authentication');
      // Clear auth and redirect to login
      localStorage.removeItem('auth_token');
      delete api.defaults.headers.common['Authorization'];
      window.location.href = '/login';
      return Promise.reject(error);
    }

    // For other errors (e.g., 400, 403, 422, 500)
    return Promise.reject(error);
  }
);

export { api };