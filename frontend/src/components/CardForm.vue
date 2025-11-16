<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import { useCardsStore } from '@/stores/cards';

const props = defineProps<{
  id: number;
  submitLoading?: boolean;
}>();

const emit = defineEmits<{
  (e: 'submit', payload: { form: any }): void;
}>();

const $q = useQuasar();
const cardsStore = useCardsStore();

const form = ref({
  full_name_fa: '',
  father_name_fa: '',
  full_name_en: '',
  father_name_en: '',
  passport_number: '',
  national_id: '',
  police_code: '',
  card_expiry_date: '',
  citizenship_fa: '',
  citizenship_en: '',
  personal_photo: null as File | null,
  card_type: 'personal',
  status: 'active'
});

const photoPreview = ref<string | null>(null);

async function fetch() {
  try {
    const data = await cardsStore.get(props.id);
    console.log('Fetched card data:', data);

    // If the data is nested under a 'data' property, use that
    const card = data.data ? data.data : data;

    // Only assign known fields to avoid breaking reactivity
    form.value.full_name_fa = card.full_name_fa || '';
    form.value.father_name_fa = card.father_name_fa || '';
    form.value.full_name_en = card.full_name_en || '';
    form.value.father_name_en = card.father_name_en || '';
    form.value.passport_number = card.passport_number || '';
    form.value.national_id = card.national_id || '';
    form.value.police_code = card.police_code || '';
    form.value.citizenship_fa = card.citizenship_fa || '';
    form.value.citizenship_en = card.citizenship_en || '';
    form.value.card_expiry_date = card.card_expiry_date
      ? new Date(card.card_expiry_date).toISOString().split('T')[0]
      : '';
    form.value.card_type = card.card_type || 'personal';
    form.value.status = card.status || 'active';
    form.value.personal_photo = null; // Reset the file input

    if (card.personal_photo) {
      photoPreview.value = `http://localhost:8000/storage/${card.personal_photo}`;
    }
  } catch (error) {
    console.error('Error fetching card:', error);
    $q.notify({
      type: 'negative',
      message: 'خطا في جلب بيانات البطاقة'
    });
  }
}

function handlePhotoUpload(event: Event) {
  const input = event.target as HTMLInputElement;
  if (input.files && input.files[0]) {
    form.value.personal_photo = input.files[0];
    photoPreview.value = URL.createObjectURL(input.files[0]);
  }
}

function submit() {
  const formData = new FormData();
  Object.entries(form.value).forEach(([key, value]) => {
    // Special handling for File objects
    if (value instanceof File) {
      formData.append(key, value, value.name);
    } else if (value !== null && value !== undefined) { // Ensure undefined is also handled
      formData.append(key, String(value)); // Convert all values to string explicitly
    }
  });

  // Log FormData contents for debugging
  console.log('FormData being sent for update:');
  for (const [key, value] of formData.entries()) {
    console.log(`${key}:`, value);
  }
  emit('submit', { form: formData });
}

onMounted(() => {
  fetch();
});
</script>

<template>
  <q-form @submit.prevent="submit" id="card-edit-form">
    <div class="row q-col-gutter-md">
      <!-- Persian Name -->
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.full_name_fa"
          label="نام و نام خانوادگی (فارسی)"
          outlined
          :rules="[val => !!val || 'لطفا نام و نام خانوادگی را وارد کنید']"
        />
      </div>

      <!-- Father's Name (Persian) -->
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.father_name_fa"
          label="نام پدر (فارسی)"
          outlined
          :rules="[val => !!val || 'لطفا نام پدر را وارد کنید']"
        />
      </div>

      <!-- English Name -->
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.full_name_en"
          label="نام و نام خانوادگی (انگلیسی)"
          outlined
          :rules="[val => !!val || 'لطفا نام و نام خانوادگی را وارد کنید']"
        />
      </div>

      <!-- Father's Name (English) -->
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.father_name_en"
          label="نام پدر (انگلیسی)"
          outlined
          :rules="[val => !!val || 'لطفا نام پدر را وارد کنید']"
        />
      </div>

      <!-- Passport Number -->
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.passport_number"
          label="شماره گذرنامه"
          outlined
          :rules="[val => !!val || 'لطفا شماره گذرنامه را وارد کنید']"
        />
      </div>

      <!-- National ID -->
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.national_id"
          label="کد فراگیر"
          outlined
          :rules="[val => !!val || 'لطفا کد فراگیر را وارد کنید']"
        />
      </div>

      <!-- Police Code -->
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.police_code"
          label="کد پلیس"
          outlined
          :rules="[val => !!val || 'لطفا کد پلیس را وارد کنید']"
        />
      </div>

      <!-- Citizenship (Persian) -->
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.citizenship_fa"
          label="تابعیت (فارسی)"
          outlined
          :rules="[val => !!val || 'لطفا تابعیت را وارد کنید']"
        />
      </div>

      <!-- Citizenship (English) -->
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.citizenship_en"
          label="تابعیت (انگلیسی)"
          outlined
          :rules="[val => !!val || 'لطفا تابعیت را وارد کنید']"
        />
      </div>

      <!-- Card Expiry Date -->
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.card_expiry_date"
          label="تاریخ انقضای کارت"
          outlined
          type="date"
          :rules="[val => !!val || 'لطفا تاریخ انقضای کارت را وارد کنید']"
        />
      </div>

      <!-- Card Type -->
      <div class="col-12 col-md-6">
        <q-select
          v-model="form.card_type"
          label="نوع کارت"
          outlined
          :options="[
            { label: 'شخصی', value: 'personal' },
            { label: 'همسر', value: 'wife' },
            { label: 'پسر', value: 'son' },
            { label: 'دختر', value: 'daughter' }
          ]"
          emit-value
          map-options
        />
      </div>

      <!-- Status -->
      <div class="col-12 col-md-6">
        <q-select
          v-model="form.status"
          label="وضعیت"
          outlined
          :options="[
            { label: 'فعال', value: 'active' },
            { label: 'منقضی', value: 'expired' },
            { label: 'لغو شده', value: 'cancelled' }
          ]"
          emit-value
          map-options
        />
      </div>

      <!-- Photo Upload -->
      <div class="col-12">
        <q-file
          v-model="form.personal_photo"
          label="تصویر شخصی"
          outlined
          accept="image/*"
          @update:model-value="handlePhotoUpload"
        >
          <template v-slot:prepend>
            <q-icon name="o_photo" />
          </template>
        </q-file>

        <div v-if="photoPreview" class="q-mt-sm">
          <q-img
            :src="photoPreview"
            style="max-width: 200px; max-height: 200px"
            class="rounded-borders"
          />
          <div style="word-break: break-all; font-size: 12px; color: #888;">
            {{ photoPreview }}
          </div>
        </div>
      </div>
    </div>
  </q-form>
</template>
