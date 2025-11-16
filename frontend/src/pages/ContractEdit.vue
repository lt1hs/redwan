<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useQuasar } from 'quasar';
import { useContractsStore } from '@/stores/contracts';
import ContractForm from '@/components/ContractForm.vue';

const route = useRoute();
const router = useRouter();
const $q = useQuasar();
const contractsStore = useContractsStore();

const id = ref(Number(route.params.id));
const submitLoading = ref(false);
const formRef = ref<InstanceType<typeof ContractForm> | null>(null);

async function onSubmit({ form }: { form: any }) {
  submitLoading.value = true;
  try {
    await contractsStore.update(id.value, form);
    $q.notify({
      type: 'positive',
      message: 'تم تحديث العقد بنجاح'
    });
    router.push({ name: 'ContractIndex' });
  } catch (error) {
    console.error('Error updating contract:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء تحديث العقد'
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
          <div class="text-h6 text-primary q-mb-md">تعديل عقد الزواج</div>
          
          <ContractForm
            ref="formRef"
            :id="id"
            :submit-loading="submitLoading"
            @submit="onSubmit"
          />
          
          <div class="q-mt-lg flex justify-end">
            <q-btn
              color="negative"
              flat
              label="إلغاء"
              :to="{ name: 'ContractIndex' }"
              class="q-mr-sm"
            />
            <q-btn
              color="primary"
              label="حفظ التغييرات"
              :loading="submitLoading"
              @click="formRef?.submit()"
            />
            <q-btn
              color="secondary"
              label="طباعة"
              class="q-ml-sm"
              :to="{ name: 'ContractPrint', params: { id } }"
            />
          </div>
        </q-card-section>
      </q-card>
    </div>
  </q-page>
</template>