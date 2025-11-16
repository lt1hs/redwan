import axios from 'axios';
import type { AxiosRequestConfig } from 'axios'; // Import AxiosRequestConfig for type hinting

// Define the base URL based on environment
const BASE_URL =
  import.meta.env.NODE_ENV === 'production'
    ? 'https://api.reality360d.com'
    : 'http://91.109.114.156:8000/api'; // Ensure this has '/api'

console.log('Using API base URL:', BASE_URL);

// Function to get CSRF token from cookies
// This helper function is used internally by the interceptor.
const getXsrfTokenFromCookie = (): string | null => {
  const cookies = document.cookie.split(';');
  for (const cookie of cookies) {
    const [name, value] = cookie.trim().split('=');
    if (name === 'XSRF-TOKEN') {
      return decodeURIComponent(value);
    }
  }
  return null;
};

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
    'Content-Type': 'application/json',
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  withCredentials: true // Enable sending cookies with requests (essential for Sanctum)
});

// Add request interceptor to add the auth token and CSRF token
api.interceptors.request.use(
  async (config) => {
    // Add Authorization header with JWT token if available in localStorage
    const token = localStorage.getItem('auth_token');
    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    // CSRF Token handling for non-GET requests
    // Laravel Sanctum expects X-XSRF-TOKEN header for POST/PUT/DELETE
    if (['post', 'put', 'patch', 'delete'].includes(config.method?.toLowerCase() || '')) {
      let xsrfToken = getXsrfTokenFromCookie();

      if (!xsrfToken) {
        // If no XSRF-TOKEN cookie is found, attempt to fetch it from Laravel
        console.log('No XSRF-TOKEN cookie found, fetching from /sanctum/csrf-cookie...');
        try {
          // This call also sends the 'withCredentials: true' to ensure the cookie is set
          const csrfResponse = await axios.get(`${BASE_URL}/sanctum/csrf-cookie`, {
            withCredentials: true,
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
          });

          if (csrfResponse.status === 204) {
            xsrfToken = getXsrfTokenFromCookie(); // Try to get it again after the request
            if (xsrfToken) {
              console.log('Successfully fetched new XSRF-TOKEN cookie.');
            } else {
              console.warn('CSRF cookie was not set after /sanctum/csrf-cookie request.');
            }
          }
        } catch (csrfError) {
          console.error('Failed to fetch CSRF cookie from /sanctum/csrf-cookie:', csrfError);
          // If CSRF token cannot be obtained, the request might fail, but let it proceed to get a 419
        }
      }

      // Add the X-XSRF-TOKEN header if a token is available
      if (xsrfToken && config.headers) {
        config.headers['X-XSRF-TOKEN'] = xsrfToken;
        console.log('X-XSRF-TOKEN header set for request:', xsrfToken.substring(0, 10) + '...');
      } else {
        console.warn('X-XSRF-TOKEN header could not be set for non-GET request.');
      }
    }

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

    // Handle 419 CSRF Token Mismatch
    // This part should be triggered only if other CSRF bypasses fail or for web routes.
    // Given previous issues, if this is still happening after kernel/route config, it's problematic.
    if (error.response?.status === 419) {
      console.error('419 CSRF Token Mismatch detected. Attempting to refresh token and retry request.');

      // Prevent infinite loops if retry fails
      if (originalRequest._retry) {
        console.error('Already retried 419 request, not retrying again.');
        return Promise.reject(error);
      }
      originalRequest._retry = true;

      try {
        // Fetch a new CSRF cookie
        console.log('Attempting to fetch new CSRF cookie after 419.');
        await axios.get(`${BASE_URL}/sanctum/csrf-cookie`, { withCredentials: true });

        // Retrieve the new token from cookies
        const newXsrfToken = getXsrfTokenFromCookie();
        if (newXsrfToken) {
          // Update the header for the original request and retry
          originalRequest.headers['X-XSRF-TOKEN'] = newXsrfToken;
          console.log('Retrying original request with new X-XSRF-TOKEN.');
          return api(originalRequest); // Retry the request
        } else {
          console.error('Failed to get new XSRF-TOKEN from cookie after refresh.');
          throw new Error('CSRF token refresh failed.');
        }
      } catch (tokenRefreshError) {
        console.error('Error refreshing CSRF token after 419:', tokenRefreshError);
        // If token refresh fails, clear auth data and redirect to login
        localStorage.removeItem('auth_token');
        delete api.defaults.headers.common['Authorization'];
        window.location.href = '/login';
        return Promise.reject(tokenRefreshError);
      }
    }

    // For other errors (e.g., 400, 403, 422, 500)
    return Promise.reject(error);
  }
);

export { api };