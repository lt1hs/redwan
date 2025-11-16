<template>
  <q-dialog ref="dialogRef" @hide="onDialogHide">
    <q-card class="q-dialog-plugin" style="width: 700px; max-width: 80vw;">
      <q-card-section class="bg-primary text-white">
        <div class="text-h6">تفاصيل الجواز</div>
      </q-card-section>

      <q-card-section class="q-pa-md">
        <q-card flat bordered class="q-mb-md">
          <q-card-section class="bg-grey-2">
            <div class="text-h6">معلومات الجواز</div>
          </q-card-section>
          <q-card-section>
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-6">
                <div class="q-item">
                  <div class="q-item__section column q-item__section--main">
                    <div class="q-item__label q-item__label--caption text-grey-7">رقم الجواز:</div>
                    <div class="q-item__label text-body1">{{ passport.passport_number || 'N/A' }}</div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="q-item">
                  <div class="q-item__section column q-item__section--main">
                    <div class="q-item__label q-item__label--caption text-grey-7">الاسم الكامل:</div>
                    <div class="q-item__label text-body1">{{ passport.full_name || 'N/A' }}</div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="q-item">
                  <div class="q-item__section column q-item__section--main">
                    <div class="q-item__label q-item__label--caption text-grey-7">الجنسية:</div>
                    <div class="q-item__label text-body1">{{ passport.nationality || 'N/A' }}</div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="q-item">
                  <div class="q-item__section column q-item__section--main">
                    <div class="q-item__label q-item__label--caption text-grey-7">تاريخ الميلاد:</div>
                    <div class="q-item__label text-body1">{{ passport.date_of_birth || 'N/A' }}</div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="q-item">
                  <div class="q-item__section column q-item__section--main">
                    <div class="q-item__label q-item__label--caption text-grey-7">تاريخ انتهاء الإقامة:</div>
                    <div class="q-item__label text-body1">{{ passport.residence_expiry_date || 'N/A' }}</div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="q-item">
                  <div class="q-item__section column q-item__section--main">
                    <div class="q-item__label q-item__label--caption text-grey-7">رقم الهاتف:</div>
                    <div class="q-item__label text-body1">{{ passport.phone_number || 'N/A' }}</div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="q-item">
                  <div class="q-item__section column q-item__section--main">
                    <div class="q-item__label q-item__label--caption text-grey-7">رقم الجوال:</div>
                    <div class="q-item__label text-body1">{{ passport.mobile_number || 'N/A' }}</div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="q-item">
                  <div class="q-item__section column q-item__section--main">
                    <div class="q-item__label q-item__label--caption text-grey-7">حالة الجواز:</div>
                    <div class="q-item__label text-body1">{{ passport.passport_status || 'N/A' }}</div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="q-item">
                  <div class="q-item__section column q-item__section--main">
                    <div class="q-item__label q-item__label--caption text-grey-7">تاريخ تسليم الجواز:</div>
                    <div class="q-item__label text-body1">{{ passport.passport_delivery_date || 'N/A' }}</div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="q-item">
                  <div class="q-item__section column q-item__section--main">
                    <div class="q-item__label q-item__label--caption text-grey-7">نوع المعاملة:</div>
                    <div class="q-item__label text-body1">{{ passport.transaction_type || 'N/A' }}</div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="q-item">
                  <div class="q-item__section column q-item__section--main">
                    <div class="q-item__label q-item__label--caption text-grey-7">حالة الدفع:</div>
                    <div class="q-item__label text-body1">{{ passport.payment_status || 'N/A' }}</div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="q-item">
                  <div class="q-item__section column q-item__section--main">
                    <div class="q-item__label q-item__label--caption text-grey-7">الرمز الفريد:</div>
                    <div class="q-item__label text-body1">{{ passport.unique_code || 'N/A' }}</div>
                  </div>
                </div>
              </div>
            </div>
          </q-card-section>
        </q-card>

        <q-card flat bordered class="q-mb-md">
          <q-card-section class="bg-grey-2">
            <div class="text-h6">معلومات المستلم</div>
          </q-card-section>
          <q-card-section>
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-6">
                <div class="q-item">
                  <div class="q-item__section column q-item__section--main">
                    <div class="q-item__label q-item__label--caption text-grey-7">اسم المستلم:</div>
                    <div class="q-item__label text-body1">{{ passport.recipient_name || 'N/A' }}</div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="q-item">
                  <div class="q-item__section column q-item__section--main">
                    <div class="q-item__label q-item__label--caption text-grey-7">هاتف المستلم:</div>
                    <div class="q-item__label text-body1">{{ passport.recipient_phone || 'N/A' }}</div>
                  </div>
                </div>
              </div>
              <div class="col-12">
                <div class="q-item">
                  <div class="q-item__section column q-item__section--main">
                    <div class="q-item__label q-item__label--caption text-grey-7">العنوان:</div>
                    <div class="q-item__label text-body1">{{ passport.address || 'N/A' }}</div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="q-item">
                  <div class="q-item__section column q-item__section--main">
                    <div class="q-item__label q-item__label--caption text-grey-7">الرمز البريدي:</div>
                    <div class="q-item__label text-body1">{{ passport.zipcode || 'N/A' }}</div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="q-item">
                  <div class="q-item__section column q-item__section--main">
                    <div class="q-item__label q-item__label--caption text-grey-7">تم التسليم بواسطة:</div>
                    <div class="q-item__label text-body1">{{ passport.delivered_by || 'N/A' }}</div>
                  </div>
                </div>
              </div>
            </div>
          </q-card-section>
        </q-card>

        <q-card flat bordered v-if="personalPhotoUrl || passportPhotoUrl">
          <q-card-section class="bg-grey-2">
            <div class="text-h6">الصور</div>
          </q-card-section>
          <q-card-section>
            <div class="row q-col-gutter-md justify-center">
              <div class="col-12 col-md-6 flex flex-center" v-if="personalPhotoUrl">
                <q-card flat bordered class="q-ma-sm">
                  <q-img :src="personalPhotoUrl" style="max-width: 200px; height: auto;" fit="contain" />
                  <q-card-section class="text-center q-pt-sm q-pb-xs">
                    <div class="text-caption text-grey-8">الصورة الشخصية</div>
                  </q-card-section>
                </q-card>
              </div>
              <div class="col-12 col-md-6 flex flex-center" v-if="passportPhotoUrl">
                <q-card flat bordered class="q-ma-sm">
                  <q-img :src="passportPhotoUrl" style="max-width: 200px; height: auto;" fit="contain" />
                  <q-card-section class="text-center q-pt-sm q-pb-xs">
                    <div class="text-caption text-grey-8">صورة الجواز</div>
                  </q-card-section>
                </q-card>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn color="primary" label="إغلاق" @click="onDialogHide" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useDialogPluginComponent, useQuasar } from 'quasar';
import { api } from 'src/boot/axios'; // Import the configured API instance

const props = defineProps<{
  passport: any;
}>();

const { dialogRef, onDialogHide, onDialogOK } = useDialogPluginComponent();
const $q = useQuasar();

const personalPhotoUrl = ref<string | null>(null);
const passportPhotoUrl = ref<string | null>(null);

const blobUrls: string[] = []; // To keep track of created blob URLs for revocation

async function fetchAndCreateBlobUrl(path: string): Promise<string | null> {
  if (!path) return null;

  // If the path is already an absolute URL (e.g., from an external source), return it as is
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path;
  }

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
    $q.notify({
      type: 'negative',
      message: 'فشل تحميل الصورة.',
    });
  }
  return null;
}

onMounted(async () => {
  if (props.passport.personal_photo) {
    personalPhotoUrl.value = await fetchAndCreateBlobUrl(props.passport.personal_photo);
  }
  if (props.passport.passport_photo) {
    passportPhotoUrl.value = await fetchAndCreateBlobUrl(props.passport.passport_photo);
  }
});

onUnmounted(() => {
  // Revoke all created blob URLs to free up memory
  blobUrls.forEach(url => URL.revokeObjectURL(url));
});
</script>

<style scoped>
/* Add any specific styles for this component here */
</style>
