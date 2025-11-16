<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useQuasar } from 'quasar';
import { useCardsStore } from '@/stores/cards';
import PassCardForm from '@/components/PassCardForm.vue'; // Corrected import name

const route = useRoute();
const router = useRouter();
const $q = useQuasar();
const cardsStore = useCardsStore();

const id = ref(Number(route.params.id));
const submitLoading = ref(false);
const cardData = ref<any>(null); // To store fetched card data
const formRef = ref<InstanceType<typeof PassCardForm> | null>(null); // Corrected type

onMounted(async () => {
  try {
    const response = await cardsStore.get(id.value); // Corrected method name
    cardData.value = response; // The 'get' action directly returns the data
    console.log('PassCardEdit: Fetched card data:', cardData.value);
  } catch (error) {
    console.error('PassCardEdit: Error fetching card data:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء جلب بيانات البطاقة'
    });
  }
});

async function onSubmit({ form }: { form: any }) {
  submitLoading.value = true;
  try {
    const response = await cardsStore.update(id.value, form);
    console.log('Card update response:', response);
    $q.notify({
      type: 'positive',
      message: 'تم تحديث البطاقة بنجاح'
    });
    router.push({ name: 'PassCardIndex' });
  } catch (error) {
    console.error('Error updating card:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء تحديث البطاقة'
    });
  } finally {
    submitLoading.value = false;
  }
}

async function saveChanges() {
  if (formRef.value) {
    await formRef.value.submit();
  }
}
</script>

<template>
  <q-page>
    <base-breadcrumbs />
    <div class="q-pa-md">
      <q-card flat bordered>
        <q-card-section>
          <div class="text-h6 text-primary q-mb-md">تعديل البطاقة</div>

          <PassCardForm
            v-if="cardData"
            ref="formRef"
            :id="id"
            :submit-loading="submitLoading"
            :initial-personal-photo-url="cardData.personal_photo ? `/api/admin/cards/photos/${cardData.personal_photo.split('/').pop()}` : ''"
            :initial-passport-photo-url="cardData.passport_photo ? `/api/admin/cards/photos/${cardData.passport_photo.split('/').pop()}` : ''"
            @submit="onSubmit"
            v-model:form-data="cardData"
          />
          <q-inner-loading :showing="!cardData">
            <q-spinner-gears size="50px" color="primary" />
          </q-inner-loading>

          <div class="q-mt-lg flex justify-end">
            <q-btn
              color="negative"
              flat
              label="إلغاء"
              :to="{ name: 'PassCardIndex' }"
              class="q-mr-sm"
            />
            <q-btn
              color="primary"
              label="حفظ التغييرات"
              :loading="submitLoading"
              @click="saveChanges"
            />
            <q-btn
              color="secondary"
              label="طباعة"
              class="q-ml-sm"
              :to="{ name: 'PassCardPrint', params: { id } }"
            />
          </div>
        </q-card-section>
      </q-card>
    </div>
  </q-page>
</template>
