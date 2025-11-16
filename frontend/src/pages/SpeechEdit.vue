<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useQuasar } from 'quasar';
import { useSpeechStore } from '@/stores/speech';
import SpeechForm from '@/components/SpeechForm.vue';

const route = useRoute();
const router = useRouter();
const $q = useQuasar();
const speechStore = useSpeechStore();

const id = ref(Number(route.params.id));
const submitLoading = ref(false);
const formRef = ref<InstanceType<typeof SpeechForm> | null>(null);

async function onSubmit({ form }: { form: any }) {
  submitLoading.value = true;
  try {
    await speechStore.update(id.value, form);
    $q.notify({
      type: 'positive',
      message: 'تم تحديث الخطاب بنجاح'
    });
    router.push({ name: 'SpeechIndex' });
  } catch (error) {
    console.error('Error updating speech:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء تحديث الخطاب'
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
        <q-card-section class="q-px-lg">
          <div class="row items-center justify-between q-mb-lg">
            <div class="text-h6 text-primary">تعديل الخطاب</div>
            <div class="row q-gutter-sm">
              <q-btn
                color="grey"
                icon="o_arrow_back"
                label="العودة للقائمة"
                flat
                :to="{ name: 'SpeechIndex' }"
              />
              <q-btn
                color="secondary"
                icon="o_print"
                label="طباعة"
                :to="{ name: 'SpeechPrint', params: { id } }"
              />
            </div>
          </div>

          <SpeechForm
            ref="formRef"
            :id="id"
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
              :label="submitLoading ? 'جاري الحفظ...' : 'حفظ التغييرات'"
              @click="formRef?.submit()"
            />
          </div>
        </q-card-section>
      </q-card>
    </div>
  </q-page>
</template>
