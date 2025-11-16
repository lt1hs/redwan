import { defineStore } from 'pinia';
import { api } from '@/utils/axios'; // Assuming '@/utils/axios' now correctly points to 'http://localhost:8000/api'
import { computed, ref } from 'vue';
import { useQuasar } from 'quasar';
import { useHelper } from '@/composables/helper';

type User = {
  id: number;
  name: string;
  email: string;
  permissions: Array<string>;
  roles: Array<string>;
  profile_photo_url: string;
};

type Credentials = {
  email: string;
  password: string;
  remember: boolean;
};

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null);
  const token = ref<string | null>(localStorage.getItem('auth_token'));
  const returnUrl = ref<string | null>(null);
  const isLoggedIn = computed(() => !!user.value && !!token.value);
  const $q = useQuasar();
  const helper = useHelper();

  // Set token in axios headers when it changes
  if (token.value) {
    api.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
  }

  async function fetchUser() {
    try {
      if (!token.value) {
        console.warn('No token available, cannot fetch user');
        // If there's no token, ensure user is null and redirect to login if not already there
        user.value = null;
        if (window.location.pathname !== '/login') {
            window.location.href = '/login';
        }
        return;
      }

      console.log('Fetching user data...');
      // *** CRITICAL CHANGE: Use the correct Laravel API route for fetching user info ***
      // Based on your routes/api.php, it's either '/user' or '/auth/me'.
      // I'll use '/user' as it's typically for authenticated user details.
      const response = await api.get('/user', { // Changed from '/api_user.php' to '/user'
        headers: {
          Authorization: `Bearer ${token.value}`,
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });
      console.log('User data response:', response);

      // Laravel's /user route often returns the user object directly at the top level
      // Your Laravel /user route returns user data and permissions/roles at the top level
      if (response.data && response.data.id) { // Check for 'id' as a reliable indicator of user object
        user.value = response.data; // Assign the entire response.data as the user object
        console.log('User authenticated successfully:', user.value!.name);
      } else {
        console.error('Invalid user data structure received or user not found:', response.data);
        throw new Error('Invalid user data structure');
      }
    } catch (error: any) {
      console.error('Error fetching user:', error);

      // Log detailed error information
      if (error.response) {
        console.error('Response error data:', error.response.data);
        console.error('Response status:', error.response.status);
        console.error('Response headers:', error.response.headers);
      } else if (error.request) {
        console.error('No response received. Request details:', error.request);
      } else {
        console.error('Error message:', error.message);
      }

      // If we get a 401, the token might be invalid
      if (error.response?.status === 401) {
        console.warn('Token invalid or expired, clearing auth data');
        localStorage.removeItem('auth_token');
        token.value = null;
        user.value = null;
        delete api.defaults.headers.common['Authorization'];

        // Show notification
        $q.notify({
          type: 'negative',
          message: 'Your session has expired. Please log in again.'
        });
        window.location.href = '/login'; // Redirect to login page
      }

      throw error; // Re-throw to allow handling by caller
    }
  }

  async function login(credentials: Credentials) {
    try {
      console.log('Attempting login...', credentials.email);

      // *** CRITICAL CHANGE: Use the correct Laravel API route for login ***
      // Change from '/api_login.php' to '/auth/login' as per your routes/api.php
      const response = await api.post('/auth/login', credentials, { // Changed from '/api_login.php' to '/auth/login'
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      console.log('Login response:', response);

      // Your Laravel AuthController@login returns a JSON with 'token' and 'user' directly.
      // So, the `extractJSONFromResponse` logic might not be needed here if Laravel always returns clean JSON.
      // However, if you are still getting PHP warnings in the response, keep that helper in `axios.ts`.
      const responseData = response.data; // Assuming response.data is already parsed JSON

      // Store the token
      const authToken = responseData.token;
      if (!authToken) {
        console.error('No token received in response');
        throw new Error('Authentication failed - no token received');
      }

      token.value = authToken;
      localStorage.setItem('auth_token', authToken);

      // Set token in axios headers
      api.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;

      // Set user from response
      if (responseData.user) {
        user.value = responseData.user;
        console.log('Login successful for user:', user.value!.name);
      } else {
        console.error('No user data in response');
        throw new Error('User data missing in response');
      }

      $q.notify({
        type: 'positive',
        message: 'Welcome!'
      });
    } catch (error: any) {
      console.error('Login error:', error);

      // Log detailed error information
      if (error.response) {
        console.error('Response error data:', error.response.data);
        console.error('Response status:', error.response.status);
      }

      helper.handleServerError(error);
      throw error; // Re-throw to allow handling by caller
    }
  }

  async function logout() {
    try {
      console.log('Attempting logout...');
      if (token.value) {
        // *** CRITICAL CHANGE: Use the correct Laravel API route for logout ***
        // Change from '/api_logout.php' to '/auth/logout' as per your routes/api.php
        await api.post(
          '/auth/logout', // Changed from '/api_logout.php' to '/auth/logout'
          {},
          {
            headers: {
              Authorization: `Bearer ${token.value}`,
              Accept: 'application/json',
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            }
          }
        );
        console.log('Logout API call successful');
      }
    } catch (error: any) {
      console.error('Logout error:', error);
      // Even if API call fails, clear local auth data to log user out
    } finally {
      // Clear auth data regardless of API response
      console.log('Clearing auth data');
      localStorage.removeItem('auth_token');
      token.value = null;
      user.value = null;
      delete api.defaults.headers.common['Authorization'];

      // Show notification
      $q.notify({
        type: 'positive',
        message: 'You have been logged out successfully.'
      });
      // Redirect to login page after logout
      window.location.href = '/login';
    }
  }

  const can = computed(() => (permission: string) => {
    if (!user.value) return false;
    if (user.value.roles.includes('Super-Admin')) return true;
    return user.value.permissions.includes(permission);
  });

  const canany = computed(() => (permissions: Array<string>) => {
    if (!user.value) return false;
    if (user.value.roles.includes('Super-Admin')) return true;
    return permissions.some((e) => user.value!.permissions.includes(e));
  });

  return { user, token, returnUrl, isLoggedIn, fetchUser, login, logout, can, canany };
});