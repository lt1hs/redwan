<script setup lang="ts">
import PassForm from '../components/PassForm.vue';
import { ref, onMounted, nextTick, reactive, watch } from 'vue';
import { usePassportsStore } from '../stores/passports';
import { useRoute, useRouter } from 'vue-router';
import { useQuasar } from 'quasar';
import { useHelper } from '@/composables/helper';
import PassportSmsNotification from '@/components/PassportSmsNotification.vue';

const route = useRoute();
const router = useRouter();
const $q = useQuasar();
const passports = usePassportsStore();
const submitLoading = ref(false);
const formLoading = ref(true);
const formRef = ref<any>(null);
const smsNotificationRef = ref<any>(null);
const passportData = reactive({
  id: '',
  name: '',
  phone: '',
  status: 'pending'
});

let initialStatus = '';

onMounted(async () => {
  const id = route.params.id;
  console.log('Route params:', route.params);

  if (!id) {
    $q.notify({
      type: 'negative',
      message: 'معرف الجواز غير صحيح',
      position: 'top'
    });
    router.push({ name: 'PassIndex' });
    return;
  }

  try {
    formLoading.value = true;
    const id = Number(route.params.id);
    const response = await passports.fetchById(id);
    console.log('Raw response:', response);

    const data = response;
    console.log('Passport data to set:', data);
    
    // Store the key passport data for SMS notifications
    passportData.id = String(data.id);
    passportData.name = data.name || '';
    passportData.phone = data.phone || '';
    passportData.status = data.status || 'pending';
    initialStatus = passportData.status;

    // Wait for the form component to be mounted
    await nextTick();

    // Update the form with fetched data
    if (formRef.value) {
      // Add photo URLs to the data
      const dataWithPhotos = {
        ...data,
        personal_photo_url: data.personal_photo,
        passport_photo_url: data.passport_photo
      };
      formRef.value.setFormData(dataWithPhotos);
      console.log('Form data set completed'); // Debug log
    } else {
      console.error('Form ref not found'); // Debug log
    }
  } catch (error) {
    console.error('Error fetching passport:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء تحميل بيانات الجواز',
      position: 'top'
    });
  } finally {
    formLoading.value = false;
  }
});

watch(() => passportData.status, (newStatus, oldStatus) => {
  if (oldStatus && newStatus !== oldStatus) {
    if (smsNotificationRef.value) {
      smsNotificationRef.value.sendNotification();
    }
  }
});

async function onSubmit({ $event, form }: { $event: any; form: any }) {
  const helper = useHelper();

  try {
    submitLoading.value = true;
    const id = Number(route.params.id);
    const result = await passports.update(id, form);

    if (result) {
      // Update passport data for SMS notifications
      passportData.name = form.name || '';
      passportData.phone = form.phone || '';
      passportData.status = form.status || 'pending';
      
      $q.notify({
        type: 'positive',
        message: 'تم تحديث الجواز بنجاح!',
        position: 'top'
      });
      
      router.push({ name: 'PassIndex' });
    }
  } catch (error: any) {
    helper.handleServerError(error);
  } finally {
    submitLoading.value = false;
  }
}
</script>

<template>
  <q-page padding>
    <base-breadcrumbs />
    <q-card flat bordered class="q-pa-md">
      <q-card-section>
        <div class="text-h6 q-mb-md">تعديل بيانات الجواز</div>
        <template v-if="formLoading">
          <div class="flex flex-center q-pa-lg">
            <q-spinner color="primary" size="3em" />
          </div>
        </template>
        <template v-else>
          <PassForm
            ref="formRef"
            @submit="onSubmit"
            :submitLoading="submitLoading"
            :id="Number(route.params.id)"
          />
        </template>
      </q-card-section>
    </q-card>
    
    <!-- Add SMS Notification Component -->
    <PassportSmsNotification 
      ref="smsNotificationRef"
      v-if="!formLoading"
      :passport-id="passportData.id" 
      :passport-status="passportData.status" 
      :recipient-name="passportData.name" 
      :recipient-phone="passportData.phone" 
      class="q-mt-md"
    />
    
  </q-page>
</template>

<style lang="scss" scoped>
.q-card {
  border-radius: 8px;

  &:deep(.q-card__section) {
    padding: 24px;
  }
}

.text-h6 {
  color: var(--q-primary);
  font-weight: 500;
}
</style>
