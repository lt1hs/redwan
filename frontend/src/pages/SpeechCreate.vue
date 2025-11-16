<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useQuasar } from 'quasar';
import { useSpeechStore } from '@/stores/speech';
import SpeechForm from '@/components/SpeechForm.vue';

const router = useRouter();
const $q = useQuasar();
const speechStore = useSpeechStore();

const submitLoading = ref(false);
const formRef = ref<InstanceType<typeof SpeechForm> | null>(null);

async function onSubmit({ form }: { form: any }) {
  submitLoading.value = true;
  try {
    await speechStore.create(form);
    $q.notify({
      type: 'positive',
      message: 'تم إنشاء الخطاب بنجاح'
    });
    router.push({ name: 'SpeechIndex' });
  } catch (error) {
    console.error('Error creating speech:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء إنشاء الخطاب'
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
        <q-card-section class="q-px-lg">
          <div class="row items-center justify-between q-mb-lg">
            <div class="text-h6 text-primary">إنشاء خطاب جديد</div>
            <q-btn
              color="grey"
              icon="o_arrow_back"
              label="العودة للقائمة"
              flat
              :to="{ name: 'SpeechIndex' }"
            />
          </div>

          <SpeechForm
            ref="formRef"
            :submit-loading="submitLoading"
            @submit="onSubmit"
            class="q-mt-md"
          />

          <div class="row justify-end q-gutter-sm q-mt-lg">
            <q-btn
              color="grey"
              flat
              label="إلغاء"
              :to="{ name: 'SpeechIndex' }"
              :disable="submitLoading"
            />
            <q-btn
              color="primary"
              :loading="submitLoading"
              :label="submitLoading ? 'جاري الحفظ...' : 'حفظ الخطاب'"
              @click="formRef?.submit()"
            />
          </div>
        </q-card-section>
      </q-card>
    </div>
  </q-page>
</template>
