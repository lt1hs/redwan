<template>
  <q-form @submit="onSubmit" class="q-gutter-md">
    <!-- Personal Information -->
    <div class="row q-col-gutter-md">
      <!-- Full Name (Persian) -->
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.full_name_fa"
          label="نام و نام خانوادگی (فارسی)"
          :rules="[val => !!val || 'نام و نام خانوادگی به فارسی الزامی است']"
          outlined
          dense
        />
      </div>
      <!-- Full Name (English) -->
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.full_name_en"
          label="Full Name (English)"
          :rules="[val => !!val || 'Full name in English is required']"
          outlined
          dense
        />
      </div>
    </div>

    <!-- Father's Name -->
    <div class="row q-col-gutter-md">
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.father_name_fa"
          label="نام پدر (فارسی)"
          :rules="[val => !!val || 'نام پدر به فارسی الزامی است']"
          outlined
          dense
        />
      </div>
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.father_name_en"
          label="Father's Name (English)"
          :rules="[val => !!val || 'Father\'s name in English is required']"
          outlined
          dense
        />
      </div>
    </div>

    <!-- Nationality -->
    <div class="row q-col-gutter-md">
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.nationality_fa"
          label="ملیت (فارسی)"
          :rules="[val => !!val || 'ملیت به فارسی الزامی است']"
          outlined
          dense
        />
      </div>
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.nationality_en"
          label="Nationality (English)"
          :rules="[val => !!val || 'Nationality in English is required']"
          outlined
          dense
        />
      </div>
    </div>

    <!-- Citizenship -->
    <div class="row q-col-gutter-md">
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.citizenship_fa"
          label="تابعیت (فارسی)"
          :rules="[val => !!val || 'تابعیت به فارسی الزامی است']"
          outlined
          dense
        />
      </div>
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.citizenship_en"
          label="Citizenship (English)"
          :rules="[val => !!val || 'Citizenship in English is required']"
          outlined
          dense
        />
      </div>
    </div>

    <!-- Other Fields -->
    <div class="row q-col-gutter-md">
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.passport_number"
          label="شماره گذرنامه"
          :rules="[val => !!val || 'شماره گذرنامه الزامی است']"
          outlined
          dense
        />
      </div>
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.national_id"
          label="کد فراگیر"
          :rules="[val => !!val || 'کد فراگیر الزامی است']"
          outlined
          dense
        />
      </div>
    </div>

    <div class="row q-col-gutter-md">
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.police_code"
          label="کد پلیس"
          :rules="[val => !!val || 'کد پلیس الزامی است']"
          outlined
          dense
        />
      </div>
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.bank_code"
          label="کد بانک"
          :rules="[val => !!val || 'کد بانک الزامی است']"
          outlined
          dense
        />
      </div>
    </div>

    <!-- Photo Upload -->
    <div class="row q-col-gutter-md">
      <div class="col-12">
        <q-file
          v-model="form.personal_photo"
          label="عکس پرسنلی"
          outlined
          dense
          accept=".jpg,.jpeg,.png"
        >
          <template v-slot:prepend>
            <q-icon name="attach_file" />
          </template>
        </q-file>
      </div>
    </div>

    <!-- Status -->
    <div class="row q-col-gutter-md">
      <div class="col-12">
        <q-select
          v-model="form.status"
          :options="['active', 'expired', 'pending', 'suspended']"
          label="وضعیت"
          outlined
          dense
          :rules="[val => !!val || 'وضعیت الزامی است']"
        />
      </div>
    </div>

    <!-- Submit Button -->
    <div class="row justify-end">
      <q-btn
        type="submit"
        color="primary"
        :loading="loading"
      >
        {{ submitButtonText }}
      </q-btn>
    </div>
  </q-form>
</template>

<script setup>
import { ref, defineProps, defineEmits } from 'vue';

const props = defineProps({
  initialData: {
    type: Object,
    default: () => ({})
  },
  submitButtonText: {
    type: String,
    default: 'ذخیره'
  }
});

const emit = defineEmits(['submit']);
const loading = ref(false);

const form = ref({
  full_name_fa: '',
  full_name_en: '',
  father_name_fa: '',
  father_name_en: '',
  nationality_fa: '',
  nationality_en: '',
  citizenship_fa: '',
  citizenship_en: '',
  passport_number: '',
  national_id: '',
  police_code: '',
  bank_code: '',
  personal_photo: null,
  status: 'active',
  ...props.initialData
});

const onSubmit = async () => {
  loading.value = true;
  try {
    emit('submit', form.value);
  } finally {
    loading.value = false;
  }
};
</script> 