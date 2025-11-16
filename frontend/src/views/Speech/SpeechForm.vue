<script setup lang="ts">
import { ref, defineComponent } from 'vue';
import { useQuasar } from 'quasar';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

defineComponent({
  name: 'SpeechForm'
});

const $q = useQuasar();
const loading = ref(false);

const formData = ref({
  title: '',
  content: '',
  date: ''
  // Add other form fields as needed
});

const rules = {
  title: [(val: string) => !!val || 'العنوان مطلوب'],
  content: [(val: string) => !!val || 'المحتوى مطلوب'],
  date: [(val: string) => !!val || 'التاريخ مطلوب']
};

async function onSubmit() {
  try {
    loading.value = true;

    // Make your API call here
    // await api.createSpeech(formData.value)

    $q.notify({
      type: 'positive',
      message: 'تم إنشاء الخطاب بنجاح'
    });

    // Optional: Reset form after success
    formData.value = {
      title: '',
      content: '',
      date: ''
    };
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء إنشاء الخطاب'
    });
    console.error(error);
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <AuthenticatedLayout>
    <div class="q-pa-md">
      <h5 class="q-mt-none q-mb-md">إنشاء خطاب جديد</h5>

      <q-form @submit.prevent="onSubmit" class="q-gutter-md">
        <q-input v-model="formData.title" label="عنوان الخطاب" :rules="rules.title" outlined />

        <q-input
          v-model="formData.content"
          label="محتوى الخطاب"
          type="textarea"
          :rules="rules.content"
          outlined
        />

        <q-input v-model="formData.date" label="التاريخ" type="date" :rules="rules.date" outlined />

        <div>
          <q-btn label="حفظ" type="submit" color="primary" :loading="loading" />

          <q-btn label="إلغاء" flat class="q-ml-sm" :disable="loading" to="/speeches" />
        </div>
      </q-form>
    </div>
  </AuthenticatedLayout>
</template>
