<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import { useRouter, useRoute } from 'vue-router';
import { useUnfinishedPassportsStore } from '@/stores/unfinishedPassports';

const $q = useQuasar();
const router = useRouter();
const route = useRoute();
const store = useUnfinishedPassportsStore();

const loading = ref(false);
const isEdit = ref(false);

const form = ref({
  full_name: '',
  nationality: '',
  passport_number: '',
  date_of_birth: '',
  residence_expiry_date: '',
  phone_number: '',
  mobile_number: '',
  transaction_type: '',
  address: '',
  zipcode: '',
  notes: '',
  completion_status: 'مسودة'
});

const transactionTypes = [
  'تجديد جواز',
  'جواز جديد',
  'تعديل بيانات',
  'استبدال جواز تالف',
  'أخرى'
];

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
      form.value.full_name = data.full_name || '';
      form.value.nationality = data.nationality || '';
      form.value.passport_number = data.passport_number || '';
      form.value.date_of_birth = data.date_of_birth ? data.date_of_birth.split('T')[0] : '';
      form.value.residence_expiry_date = data.residence_expiry_date ? data.residence_expiry_date.split('T')[0] : '';
      form.value.phone_number = data.phone_number || '';
      form.value.mobile_number = data.mobile_number || '';
      form.value.transaction_type = data.transaction_type || '';
      form.value.address = data.address || '';
      form.value.zipcode = data.zipcode || '';
      form.value.notes = data.notes || '';
      form.value.completion_status = data.completion_status || 'مسودة';
      
      console.log('Form after update:', form.value);
      console.log('Zipcode in form:', form.value.zipcode);
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
  <q-page>
    <base-breadcrumbs />
    <q-card>
      <q-card-section>
        <div class="text-h6">{{ isEdit ? 'تعديل جواز غير مكتمل' : 'إضافة جواز غير مكتمل' }}</div>
      </q-card-section>

      <q-card-section>
        <q-form @submit="handleSubmit" class="q-gutter-md">
          <div class="row q-col-gutter-md">
            <div class="col-md-6 col-12">
              <q-input
                v-model="form.full_name"
                label="الاسم الكامل"
                outlined
              />
            </div>
            <div class="col-md-6 col-12">
              <q-input
                v-model="form.nationality"
                label="الجنسية"
                outlined
              />
            </div>
          </div>

          <div class="row q-col-gutter-md">
            <div class="col-md-6 col-12">
              <q-input
                v-model="form.passport_number"
                label="رقم الجواز"
                outlined
              />
            </div>
            <div class="col-md-6 col-12">
              <q-input
                v-model="form.date_of_birth"
                label="تاريخ الميلاد"
                type="date"
                outlined
              />
            </div>
          </div>

          <div class="row q-col-gutter-md">
            <div class="col-md-6 col-12">
              <q-input
                v-model="form.residence_expiry_date"
                label="تاريخ انتهاء الإقامة"
                type="date"
                outlined
              />
            </div>
            <div class="col-md-6 col-12">
              <q-input
                v-model="form.mobile_number"
                label="رقم الهاتف المحمول"
                outlined
              />
            </div>
          </div>

          <div class="row q-col-gutter-md">
            <div class="col-md-6 col-12">
              <q-input
                v-model="form.phone_number"
                label="رقم الهاتف الثابت"
                outlined
              />
            </div>
            <div class="col-md-6 col-12">
              <q-select
                v-model="form.transaction_type"
                :options="transactionTypes"
                label="نوع المعاملة"
                outlined
              />
            </div>
          </div>

          <div class="row q-col-gutter-md">
            <div class="col-md-6 col-12">
              <q-input
                v-model="form.zipcode"
                label="كد ناجا"
                outlined
              />
            </div>
            <div class="col-md-6 col-12">
              <q-select
                v-model="form.completion_status"
                :options="['مسودة', 'قيد المراجعة', 'جاهز للنقل']"
                label="حالة الإكمال"
                outlined
              />
            </div>
          </div>

          <q-input
            v-model="form.address"
            label="العنوان"
            type="textarea"
            outlined
            rows="3"
          />

          <q-input
            v-model="form.notes"
            label="ملاحظات"
            type="textarea"
            outlined
            rows="3"
          />

          <div class="row q-gutter-sm">
            <q-btn
              type="submit"
              color="primary"
              :loading="loading"
              :label="isEdit ? 'تحديث' : 'حفظ'"
            />
            <q-btn
              color="grey"
              flat
              label="إلغاء"
              :to="{ name: 'UnfinishedPassportsIndex' }"
            />
          </div>
        </q-form>
      </q-card-section>
    </q-card>
  </q-page>
</template>
