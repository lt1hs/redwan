<script setup lang="ts">
import { ref, onMounted, computed, nextTick } from 'vue';
import { useQuasar } from 'quasar';
import { useRouter, useRoute } from 'vue-router';
import { useUnfinishedPassportsStore } from '@/stores/unfinishedPassports';

const $q = useQuasar();
const router = useRouter();
const route = useRoute();
const store = useUnfinishedPassportsStore();

const loading = ref(false);
const isEdit = ref(false);
const formRef = ref();

const form = ref({
  gender: '',
  full_name: '',
  nationality: '',
  passport_number: '',
  passport_id: '',
  date_of_birth: '',
  residence_expiry_date: '',
  expiration_date: '',
  phone_number: '',
  mobile_number: '',
  transaction_type: '',
  residence_authority: '',
  address: '',
  governorate: '',
  zipcode: '',
  najacode: '',
  personal_photo: null,
  passport_photo: null,
  residence_photo: null,
  passport_extension_photo: null,
  notes: '',
  completion_status: 'قيد المراجعة'
});

const imagePreviews = ref({
  personal_photo: null,
  passport_photo: null,
  residence_photo: null,
  passport_extension_photo: null
});

const getImagePreview = (file: File | null, existingUrl: string | null) => {
  if (file && file instanceof File) {
    return URL.createObjectURL(file);
  }
  
  if (existingUrl) {
    let fullUrl = existingUrl;
    if (existingUrl.startsWith('/storage/') || existingUrl.startsWith('/tmp/')) {
      fullUrl = `http://91.109.114.156:8000${existingUrl}`;
    }
    return fullUrl;
  }
  
  return null;
};

const transactionTypes = [
  'تجديد جواز',
  'جواز جديد',
  'تعديل بيانات',
  'استبدال جواز تالف',
  'أخرى'
];

const formRules = {
  full_name: [(val: string) => !!val || 'الاسم الكامل مطلوب'],
  phone_number: [(val: string) => !!val || 'رقم الهاتف مطلوب'],
  personal_photo: [(val: any) => !!val || !!imagePreviews.value.personal_photo || 'الصورة الشخصية مطلوبة']
};

const isFormValid = computed(() => {
  return form.value.full_name && form.value.phone_number && (form.value.personal_photo || imagePreviews.value.personal_photo);
});

onMounted(async () => {
  if (route.params.id) {
    isEdit.value = true;
    console.log('Loading passport with ID:', route.params.id);
    try {
      loading.value = true;
      const data = await store.fetchById(Number(route.params.id));
      console.log('Loaded passport data:', data);
      console.log('Zipcode from API:', data.zipcode);
      
      // Update form fields individually to ensure reactivity
      form.value.gender = data.gender || '';
      form.value.full_name = data.full_name || '';
      form.value.nationality = data.nationality || '';
      form.value.passport_number = data.passport_number || '';
      form.value.passport_id = data.passport_id || '';
      form.value.date_of_birth = data.date_of_birth ? data.date_of_birth.split('T')[0] : '';
      form.value.residence_expiry_date = data.residence_expiry_date ? data.residence_expiry_date.split('T')[0] : '';
      form.value.expiration_date = data.expiration_date ? data.expiration_date.split('T')[0] : '';
      form.value.phone_number = data.phone_number || '';
      form.value.mobile_number = data.mobile_number || '';
      form.value.transaction_type = data.transaction_type || '';
      form.value.residence_authority = data.residence_authority || '';
      form.value.zipcode = data.zipcode || '';
      form.value.najacode = data.najacode || '';
      form.value.address = data.address || '';
      form.value.governorate = data.governorate || '';
      form.value.personal_photo = null;
      form.value.passport_photo = null;
      form.value.residence_photo = null;
      form.value.passport_extension_photo = null;
      form.value.notes = data.notes || '';
      form.value.completion_status = data.completion_status || 'قيد المراجعة';
      
      // Set existing image previews
      imagePreviews.value.personal_photo = data.personal_photo || null;
      imagePreviews.value.passport_photo = data.passport_photo || null;
      imagePreviews.value.residence_photo = data.residence_photo || null;
      imagePreviews.value.passport_extension_photo = data.passport_extension_photo || null;
      
      await nextTick();
      
      console.log('Form after update:', form.value);
      console.log('Address from API:', data.address);
      console.log('Address in form:', form.value.address);
    } catch (error) {
      console.error('Error loading passport data:', error);
      $q.notify({
        type: 'negative',
        message: 'حدث خطأ أثناء تحميل بيانات الجواز'
      });
    } finally {
      loading.value = false;
    }
  }
});

const handleSubmit = async () => {
  if (!formRef.value?.validate()) {
    $q.notify({
      type: 'negative',
      message: 'يرجى تعبئة جميع الحقول المطلوبة'
    });
    return;
  }

  loading.value = true;
  try {
    const formData = new FormData();
    Object.entries(form.value).forEach(([key, value]) => {
      if (value) formData.append(key, value);
    });

    if (isEdit.value) {
      await store.update(Number(route.params.id), formData);
      $q.notify({
        type: 'positive',
        message: 'تم تحديث الجواز غير المكتمل بنجاح'
      });
    } else {
      await store.create(formData);
      $q.notify({
        type: 'positive',
        message: 'تم إنشاء الجواز غير المكتمل بنجاح'
      });
    }

    router.push({ name: 'UnfinishedPassportsIndex' });
  } catch (error) {
    console.error('Error saving unfinished passport:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء حفظ البيانات'
    });
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <q-page class="q-pa-md">
    <base-breadcrumbs />
    
    <q-card class="q-card--bordered q-card--flat no-shadow q-mb-md">
      <q-card-section class="bg-primary text-white q-pa-md">
        <div class="text-h6 text-center">
          <q-icon name="description" class="q-mr-sm" />
          {{ isEdit ? 'تعديل جواز غير مكتمل' : 'إضافة جواز غير مكتمل' }}
        </div>
      </q-card-section>

      <q-card-section class="q-pa-md">
        <q-form ref="formRef" @submit="handleSubmit" class="q-gutter-md">
          
          <div class="row q-col-gutter-sm">
            <div class="col-md-4 col-12">
              <q-select
                v-model="form.gender"
                :options="['ذكر', 'أنثى']"
                label="الجنس"
                outlined
                dense
              />
            </div>
            <div class="col-md-8 col-12">
              <q-input
                v-model="form.full_name"
                label="الاسم الكامل"
                outlined
                dense
                :rules="formRules.full_name"
                lazy-rules
                class="required-field"
              />
            </div>
          </div>

          <div class="row q-col-gutter-sm">
            <div class="col-md-6 col-12">
              <q-input
                v-model="form.nationality"
                label="الجنسية"
                outlined
                dense
              />
            </div>
            <div class="col-md-6 col-12">
              <q-input
                v-model="form.passport_id"
                label="رقم الهوية/الجواز"
                outlined
                dense
              />
            </div>
          </div>

          <div class="row q-col-gutter-sm">
            <div class="col-md-6 col-12">
              <q-input
                v-model="form.date_of_birth"
                label="تاريخ الميلاد"
                type="date"
                outlined
                dense
              />
            </div>
            <div class="col-md-6 col-12">
              <q-input
                v-model="form.residence_expiry_date"
                label="تاريخ انتهاء الإقامة"
                type="date"
                outlined
                dense
              />
            </div>
          </div>

          <div class="row q-col-gutter-sm">
            <div class="col-md-4 col-12">
              <q-input
                v-model="form.phone_number"
                label="رقم الهاتف الثابت"
                outlined
                dense
                :rules="formRules.phone_number"
                lazy-rules
                class="required-field"
              />
            </div>
            <div class="col-md-4 col-12">
              <q-select
                v-model="form.transaction_type"
                :options="transactionTypes"
                label="نوع المعاملة"
                outlined
                dense
              />
            </div>
            <div class="col-md-4 col-12">
              <q-select
                v-model="form.residence_authority"
                :options="['مجمع جهان اهل بيت', 'سپاه', 'اطلاعات']"
                label="جهة الاقامة"
                outlined
                dense
              />
            </div>
          </div>

          <div class="row q-col-gutter-sm">
            <div class="col-md-6 col-12">
              <q-input
                v-model="form.zipcode"
                label="كد ناجا"
                outlined
                dense
              />
            </div>
            <div class="col-md-6 col-12">
              <q-input
                v-model="form.governorate"
                label="المحافظة"
                outlined
                dense
              />
            </div>
          </div>

          <q-input
            v-model="form.address"
            label="العنوان"
            type="textarea"
            outlined
            dense
            rows="2"
            :key="`address-${route.params.id || 'new'}`"
          />

          <!-- Photo Upload Section -->
          <div class="row q-col-gutter-sm">
            <div class="col-md-6 col-12">
              <q-file
                v-model="form.personal_photo"
                label="صورة شخصية"
                outlined
                dense
                accept="image/*"
                :rules="formRules.personal_photo"
                lazy-rules
                class="required-field"
              >
                <template v-slot:prepend>
                  <q-icon name="person" />
                </template>
              </q-file>
              <div v-if="getImagePreview(form.personal_photo, imagePreviews.personal_photo)" class="q-mt-sm">
                <img :src="getImagePreview(form.personal_photo, imagePreviews.personal_photo)" 
                     style="max-width: 100px; max-height: 100px; border-radius: 4px;"
                     @error="imagePreviews.personal_photo = null" />
              </div>
            </div>
            <div class="col-md-6 col-12">
              <q-file
                v-model="form.passport_photo"
                label="صورة الجواز"
                outlined
                dense
                accept="image/*"
              >
                <template v-slot:prepend>
                  <q-icon name="credit_card" />
                </template>
              </q-file>
              <div v-if="getImagePreview(form.passport_photo, imagePreviews.passport_photo)" class="q-mt-sm">
                <img :src="getImagePreview(form.passport_photo, imagePreviews.passport_photo)" 
                     style="max-width: 100px; max-height: 100px; border-radius: 4px;"
                     @error="imagePreviews.passport_photo = null" />
              </div>
            </div>
          </div>

          <div class="row q-col-gutter-sm">
            <div class="col-md-6 col-12">
              <q-file
                v-model="form.residence_photo"
                label="اخر صورة للاقامة"
                outlined
                dense
                accept="image/*"
              >
                <template v-slot:prepend>
                  <q-icon name="home" />
                </template>
              </q-file>
              <div v-if="getImagePreview(form.residence_photo, imagePreviews.residence_photo)" class="q-mt-sm">
                <img :src="getImagePreview(form.residence_photo, imagePreviews.residence_photo)" 
                     style="max-width: 100px; max-height: 100px; border-radius: 4px;"
                     @error="imagePreviews.residence_photo = null" />
              </div>
            </div>
            <div class="col-md-6 col-12">
              <q-file
                v-model="form.passport_extension_photo"
                label="صورة تمديد الجواز (ان وجدت)"
                outlined
                dense
                accept="image/*"
              >
                <template v-slot:prepend>
                  <q-icon name="extension" />
                </template>
              </q-file>
              <div v-if="getImagePreview(form.passport_extension_photo, imagePreviews.passport_extension_photo)" class="q-mt-sm">
                <img :src="getImagePreview(form.passport_extension_photo, imagePreviews.passport_extension_photo)" 
                     style="max-width: 100px; max-height: 100px; border-radius: 4px;"
                     @error="imagePreviews.passport_extension_photo = null" />
              </div>
            </div>
          </div>

          <div class="row q-col-gutter-sm">
            <div class="col-md-6 col-12">
              <q-input
                v-model="form.notes"
                label="ملاحظات"
                type="textarea"
                outlined
                dense
                rows="2"
              />
            </div>
            <div class="col-md-6 col-12">
              <q-select
                v-model="form.completion_status"
                :options="['قيد المراجعة', 'جاهز للنقل', 'حذف']"
                label="حالة الإكمال"
                outlined
                dense
              />
            </div>
          </div>

          <div class="row justify-center q-gutter-sm q-mt-md">
            <q-btn
              type="submit"
              color="primary"
              :loading="loading"
              :disable="!isFormValid"
              size="md"
            >
              <q-icon :name="isEdit ? 'edit' : 'save'" class="q-mr-xs" />
              {{ isEdit ? 'تحديث' : 'حفظ' }}
            </q-btn>
            
            <q-btn
              color="grey-7"
              flat
              :to="{ name: 'UnfinishedPassportsIndex' }"
              size="md"
            >
              <q-icon name="cancel" class="q-mr-xs" />
              إلغاء
            </q-btn>
          </div>
        </q-form>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<style scoped>
.required-field :deep(.q-field__label):after {
  content: ' *';
  color: #f44336;
}
</style>
