<template>
  <q-page class="q-pa-md">
    <div class="q-mx-auto" style="max-width: 1000px;">
      <base-breadcrumbs />
      <q-card flat bordered class="creation-card q-pa-lg">
        <q-card-section class="q-pb-none">
          <div class="text-h5 text-weight-bold q-mb-md row items-center">
            <q-icon name="o_description" color="primary" size="32px" class="q-mr-sm" />
            طباعة خطاب رسمي / Print Official Letter
          </div>
        </q-card-section>

        <q-card-section class="q-pa-lg text-right" dir="rtl">
          <div class="text-h6 q-mb-md">
            <p>اسم المستخدم: {{ passportData?.full_name }}</p>
            <p>رقم الجواز: {{ passportData?.passport_number }}</p>
            <p>كود المكتب (الكود الفريد): {{ passportData?.unique_code }}</p>
            <p>صورة الجواز:</p>
            <img :src="displayPassportPhotoUrl" alt="Passport Photo" style="max-width: 200px; height: auto; border: 1px solid #ccc; margin-top: 10px;" />
          </div>
          <p class="text-body1 q-mt-lg">
            إلى من يهمه الأمر، نفيدكم بأن الجواز رقم ({{ passportData?.passport_number }}) الخاص بالسيد/السيدة ({{ passportData?.full_name }}) وكوده الخاص ({{ passportData?.unique_code }}) قيد الإجراء لدى الجهات المختصة لتجديد الإقامة أو إنهاء المعاملة المطلوبة. نرجو من الجهات المختصة التعاون معه لتسهيل إجراءاته.
          </p>
          <p class="text-body2 q-mt-lg">
            صلاحية هذه الورقة: 3 أشهر من تاريخ الإصدار
          </p>
        </q-card-section>

        <q-card-actions align="right" class="q-pt-lg">
          <q-btn
            color="primary"
            label="Print"
            icon="print"
            @click="printPage"
          />
          <q-btn
            color="negative"
            flat
            label="Back"
            @click="router.go(-1)"
          />
        </q-card-actions>
      </q-card>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { defineProps, ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { api } from 'boot/axios'; // Import the configured API instance

const props = defineProps<{
  id: string;
}>();

const router = useRouter();
const passportData = ref<any>(null); // Using 'any' for now, can define a proper interface later
const displayPassportPhotoUrl = ref<string | null>(null);
const blobUrls: string[] = []; // To keep track of created blob URLs for revocation

async function fetchAndCreateBlobUrl(path: string): Promise<string | null> {
  if (!path) return null;

  // If the path is already an absolute URL (e.g., from an external source), return it as is
  if (path) { // Simplified check for non-empty path
    // Extract just the filename
    const filename = path.split('/').pop();
    if (!filename) return null;

    // Construct the full URL using the specific image serving route
    const imageUrl = `http://localhost:8000/api/admin/passports/photos/${filename}`;

    try {
      const response = await api.get(imageUrl, { responseType: 'blob' });
      if (response.data) {
        const blobUrl = URL.createObjectURL(response.data);
        blobUrls.push(blobUrl); // Store for revocation
        return blobUrl;
      }
    } catch (error) {
      console.error('Error fetching protected image:', error);
      // $q.notify is not available here, handle error directly or add Quasar instance
    }
  }
  return null;
}

onMounted(async () => {
  console.log('Fetching passport data for ID:', props.id);
  try {
    const response = await api.get(`/admin/passports/${props.id}`); // Use the 'api' instance
    console.log('Passport API response:', response.data);
    passportData.value = response.data;

    if (passportData.value?.passport_photo) {
      displayPassportPhotoUrl.value = await fetchAndCreateBlobUrl(passportData.value.passport_photo);
    }
  } catch (error) {
    console.error('Error fetching passport data:', error);
    // Handle error, e.g., show a notification
  }
});

onUnmounted(() => {
  // Revoke all created blob URLs to free up memory
  blobUrls.forEach(url => URL.revokeObjectURL(url));
});

const printPage = () => {
  window.print();
};
</script>

<style lang="scss" scoped>
.creation-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}
</style>
