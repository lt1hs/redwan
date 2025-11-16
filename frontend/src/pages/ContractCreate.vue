<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useQuasar } from 'quasar';
import { useContractsStore } from '@/stores/contracts';
import ContractForm from '@/components/ContractForm.vue';

const router = useRouter();
const $q = useQuasar();
const contractsStore = useContractsStore();

const submitLoading = ref(false);
const formRef = ref<InstanceType<typeof ContractForm> | null>(null);

async function onSubmit(form: any) { // Changed to directly accept form
  console.log('onSubmit triggered with form:', form); // Debug log
  submitLoading.value = true;
  try {
    console.log('Attempting to store contract...'); // Debug log
    await contractsStore.store(form);
    console.log('Contract stored successfully.'); // Debug log
    $q.notify({
      type: 'positive',
      message: 'تم إنشاء العقد بنجاح / قرارداد با موفقیت ایجاد شد'
    });
    router.push({ name: 'ContractIndex' });
  } catch (error) {
    console.error('Error creating contract:', error); // Debug log
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء إنشاء العقد / خطا در ایجاد قرارداد'
    });
  } finally {
    submitLoading.value = false;
  }
}
</script>

<template>
  <q-page>
    <base-breadcrumbs />
    <div class="q-pa-md">
      <q-card flat bordered>
        <q-card-section>
          <div class="text-h6 text-primary q-mb-md">
            إنشاء عقد زواج جديد 
          </div>

          <ContractForm ref="formRef" :submit-loading="submitLoading" @submit="onSubmit" />

          <div class="q-mt-lg flex justify-end">
            <q-btn
              color="negative"
              flat
              label="إلغاء "
              :to="{ name: 'ContractIndex' }"
              class="q-mr-sm"
            />
            <q-btn
              color="primary"
              label="حفظ العقد "
              :loading="submitLoading"
              @click="() => { console.log('Attempting to submit form via formRef.submit()'); formRef?.submit(); }"
            />
          </div>
        </q-card-section>
      </q-card>
    </div>
  </q-page>
</template>
