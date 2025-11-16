<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useQuasar } from 'quasar';
import { usePassportsStore } from '@/stores/passports';
import PassForm from '@/components/PassForm.vue';

const route = useRoute();
const router = useRouter();
const $q = useQuasar();
const passportsStore = usePassportsStore();

const id = ref(Number(route.params.id));
const loading = ref(false);
const submitLoading = ref(false);
const formRef = ref<InstanceType<typeof PassForm> | null>(null);
const recipientName = ref('');

async function onSubmit(form: any) {
  submitLoading.value = true;
  try {
    // Update validation

    if (!(form.get('no_signature') === '1') && !form.get('signature_data')) {
      throw new Error('يجب التوقيع أو اختيار استلام بدون توقيع');
    }

    if ((form.get('no_signature') === '1') && !form.get('no_signature_reason')) {
      throw new Error('يجب ذكر سبب عدم التوقيع');
    }

    // Add delivery information to the form data
    const deliveryData = {
      ...form,
      recipient_name: recipientName.value,
      passport_status: 'تم تسليمه',
      passport_delivery_date: new Date().toISOString().split('T')[0],
      delivery_method: 'barcode',  // Updated since we only use barcode now
      delivery_timestamp: new Date().toISOString()
    };

    await passportsStore.update(id.value, deliveryData);

    $q.notify({
      type: 'positive',
      message: 'تم تسليم الجواز بنجاح'
    });

    router.push({ name: 'PassIndex' });
  } catch (error: any) {
    console.error('Error delivering passport:', error);
    $q.notify({
      type: 'negative',
      message: error.message || 'حدث خطأ أثناء تحديث بيانات الجواز'
    });
  } finally {
    submitLoading.value = false;
  }
}

onMounted(() => {
  if (formRef.value) {
    formRef.value.fetch();
  }
});
</script>

<template>
  <q-page>
    <base-breadcrumbs />
    <div class="q-pa-md">
      <q-card flat bordered>
        <q-card-section>
          <div class="text-h6 text-primary q-mb-md">تسليم الجواز</div>

          <!-- Passport Form with delivery options -->
          <PassForm
            ref="formRef"
            :id="id"
            :submit-loading="submitLoading"
            @submit="onSubmit"
            :readonly="true"
            :showDeliveryOptions="true"
          />

          <!-- Recipient Name Field -->
          <div class="q-mt-lg">
            <q-card flat bordered>
              <q-card-section>
                <div class="text-h6 text-primary q-mb-md">معلومات المستلم</div>
                <div class="row q-col-gutter-md">
                  <div class="col-12 col-md-6">
                    <q-input
                      v-model="recipientName"
                      label="اسم المستلم"
                      outlined
                      :rules="[(val) => !!val || 'يرجى إدخال اسم المستلم']"
                    />
                  </div>
                </div>
              </q-card-section>
            </q-card>
          </div>

          <!-- Submit Button -->
          <div class="q-mt-lg flex justify-end">
            <q-btn
              color="negative"
              flat
              label="إلغاء"
              :to="{ name: 'PassIndex' }"
              class="q-mr-sm"
            />
            <q-btn
              color="primary"
              label="تأكيد التسليم"
              :loading="submitLoading"
              @click="formRef?.onSubmit()"
            />
          </div>
        </q-card-section>
      </q-card>
    </div>
  </q-page>
</template>

<style lang="scss" scoped>
.label-title {
  font-size: 0.875rem;
  font-weight: 500;
  color: #666;
  display: block;
  margin-bottom: 4px;
}
</style>
