<template>
  <q-page class="q-pa-md">
    <div class="row justify-center">
      <div class="col-12 col-md-10 col-lg-8">
        <q-card class="q-mb-md">
          <q-card-section class="bg-primary text-white">
            <div class="text-h5">
              <q-icon name="o_person" class="q-mr-sm" />
              الملف الشخصي
            </div>
          </q-card-section>

          <q-card-section>
            <div class="row q-col-gutter-md">
              <!-- Profile Photo -->
              <div class="col-12 text-center q-mb-md">
                <q-avatar size="120px" class="q-mb-md">
                  <img
                    v-if="form.profile_photo_url"
                    :src="form.profile_photo_url"
                    alt="Profile Photo"
                  />
                  <q-icon v-else name="o_person" size="80px" />
                </q-avatar>
                <div>
                  <q-btn
                    color="primary"
                    outline
                    label="تغيير الصورة"
                    icon="o_photo_camera"
                    @click="$refs.photoInput.click()"
                  />
                  <input
                    ref="photoInput"
                    type="file"
                    accept="image/*"
                    style="display: none"
                    @change="onPhotoChange"
                  />
                </div>
              </div>

              <!-- Name -->
              <div class="col-12 col-md-6">
                <q-input
                  v-model="form.name"
                  label="الاسم *"
                  outlined
                  :error="!!errors.name"
                  :error-message="errors.name"
                />
              </div>

              <!-- Email -->
              <div class="col-12 col-md-6">
                <q-input
                  v-model="form.email"
                  label="البريد الإلكتروني *"
                  type="email"
                  outlined
                  :error="!!errors.email"
                  :error-message="errors.email"
                />
              </div>
            </div>
          </q-card-section>

          <q-separator />

          <q-card-actions align="right" class="q-pa-md">
            <q-btn
              color="primary"
              label="حفظ التغييرات"
              icon="o_save"
              :loading="loading"
              @click="updateProfile"
            />
          </q-card-actions>
        </q-card>

        <!-- Change Password Card -->
        <q-card>
          <q-card-section class="bg-secondary text-white">
            <div class="text-h6">
              <q-icon name="o_lock" class="q-mr-sm" />
              تغيير كلمة المرور
            </div>
          </q-card-section>

          <q-card-section>
            <div class="row q-col-gutter-md">
              <!-- Current Password -->
              <div class="col-12">
                <q-input
                  v-model="passwordForm.current_password"
                  label="كلمة المرور الحالية *"
                  :type="showCurrentPassword ? 'text' : 'password'"
                  outlined
                  :error="!!passwordErrors.current_password"
                  :error-message="passwordErrors.current_password"
                >
                  <template v-slot:append>
                    <q-icon
                      :name="showCurrentPassword ? 'o_visibility_off' : 'o_visibility'"
                      class="cursor-pointer"
                      @click="showCurrentPassword = !showCurrentPassword"
                    />
                  </template>
                </q-input>
              </div>

              <!-- New Password -->
              <div class="col-12 col-md-6">
                <q-input
                  v-model="passwordForm.password"
                  label="كلمة المرور الجديدة *"
                  :type="showNewPassword ? 'text' : 'password'"
                  outlined
                  :error="!!passwordErrors.password"
                  :error-message="passwordErrors.password"
                >
                  <template v-slot:append>
                    <q-icon
                      :name="showNewPassword ? 'o_visibility_off' : 'o_visibility'"
                      class="cursor-pointer"
                      @click="showNewPassword = !showNewPassword"
                    />
                  </template>
                </q-input>
              </div>

              <!-- Confirm Password -->
              <div class="col-12 col-md-6">
                <q-input
                  v-model="passwordForm.password_confirmation"
                  label="تأكيد كلمة المرور *"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  outlined
                  :error="!!passwordErrors.password_confirmation"
                  :error-message="passwordErrors.password_confirmation"
                >
                  <template v-slot:append>
                    <q-icon
                      :name="showConfirmPassword ? 'o_visibility_off' : 'o_visibility'"
                      class="cursor-pointer"
                      @click="showConfirmPassword = !showConfirmPassword"
                    />
                  </template>
                </q-input>
              </div>
            </div>
          </q-card-section>

          <q-separator />

          <q-card-actions align="right" class="q-pa-md">
            <q-btn
              color="secondary"
              label="تغيير كلمة المرور"
              icon="o_lock"
              :loading="passwordLoading"
              @click="updatePassword"
            />
          </q-card-actions>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import { api } from '@/utils/axios';
import { useAuthStore } from '@/stores/auth';

const $q = useQuasar();
const authStore = useAuthStore();

const loading = ref(false);
const passwordLoading = ref(false);
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const form = ref({
  name: '',
  email: '',
  profile_photo_url: '',
  profile_photo: null as File | null
});

const errors = ref({
  name: '',
  email: '',
  profile_photo: ''
});

const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: ''
});

const passwordErrors = ref({
  current_password: '',
  password: '',
  password_confirmation: ''
});

const photoInput = ref<HTMLInputElement | null>(null);

onMounted(() => {
  loadProfile();
});

const loadProfile = () => {
  if (authStore.user) {
    form.value.name = authStore.user.name;
    form.value.email = authStore.user.email;
    form.value.profile_photo_url = authStore.user.profile_photo_url || '';
  }
};

const onPhotoChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    form.value.profile_photo = target.files[0];
    
    // Preview the image
    const reader = new FileReader();
    reader.onload = (e) => {
      form.value.profile_photo_url = e.target?.result as string;
    };
    reader.readAsDataURL(target.files[0]);
  }
};

const updateProfile = async () => {
  loading.value = true;
  errors.value = { name: '', email: '', profile_photo: '' };

  try {
    const formData = new FormData();
    formData.append('name', form.value.name);
    formData.append('email', form.value.email);
    
    if (form.value.profile_photo) {
      formData.append('profile_photo', form.value.profile_photo);
    }

    // Don't set Content-Type header - let the browser set it with boundary
    const response = await api.post('/profile/update', formData);

    // Update auth store with new user data
    await authStore.fetchUser();

    $q.notify({
      type: 'positive',
      message: 'تم تحديث الملف الشخصي بنجاح',
      position: 'top'
    });
  } catch (error: any) {
    console.error('Profile update error:', error);
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    }
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'حدث خطأ أثناء تحديث الملف الشخصي',
      position: 'top'
    });
  } finally {
    loading.value = false;
  }
};

const updatePassword = async () => {
  passwordLoading.value = true;
  passwordErrors.value = {
    current_password: '',
    password: '',
    password_confirmation: ''
  };

  try {
    await api.post('/profile/update-password', passwordForm.value);

    $q.notify({
      type: 'positive',
      message: 'تم تغيير كلمة المرور بنجاح',
      position: 'top'
    });

    // Clear password form
    passwordForm.value = {
      current_password: '',
      password: '',
      password_confirmation: ''
    };
  } catch (error: any) {
    if (error.response?.data?.errors) {
      passwordErrors.value = error.response.data.errors;
    }
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'حدث خطأ أثناء تغيير كلمة المرور',
      position: 'top'
    });
  } finally {
    passwordLoading.value = false;
  }
};
</script>
