<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useQuasar } from 'quasar';
import { useCardsStore } from '@/stores/cards';
import PassCardForm from '@/components/PassCardForm.vue';

interface SubmitEvent {
  form: FormData;
}

const router = useRouter();
const $q = useQuasar();
const cardsStore = useCardsStore();

const submitLoading = ref(false);
const formRef = ref<InstanceType<typeof PassCardForm> | null>(null);

// Add function to generate unique code
const generateUniqueCode = () => {
  const timestamp = Date.now();
  const randomPart = Math.random().toString(36).substring(2, 8).toUpperCase();
  const datePart = new Date(timestamp).toISOString().slice(2, 10).replace(/-/g, '');
  const sequencePart = Math.floor(Math.random() * 10000)
    .toString()
    .padStart(4, '0');

  // Format: PC-YYMMDD-RANDOM-SEQ
  return `PC-${datePart}-${randomPart}-${sequencePart}`;
};

async function onSubmit(event: SubmitEvent) {
  if (!event?.form) {
    console.error('Form data is missing');
    return;
  }

  submitLoading.value = true;
  try {
    // Log form data for debugging
    const formDataObj: Record<string, string> = {};
    event.form.forEach((value, key) => {
      formDataObj[key] = value.toString();
    });
    console.log('Submitting form data:', formDataObj);

    // Create the card directly using the cards store
    const result = await cardsStore.create(event.form);

    $q.notify({
      type: 'positive',
      message: 'تم إنشاء البطاقة بنجاح / Card created successfully'
    });

    // Redirect to cards list
    router.push({ name: 'PassCardIndex' });
  } catch (error: any) {
    console.error('Error creating card:', error);
    const errorMessage = error.response?.data?.message || error.message || 'حدث خطأ أثناء إنشاء البطاقة / Error creating card';
    $q.notify({
      type: 'negative',
      message: errorMessage
    });
  } finally {
    submitLoading.value = false;
  }
}

// Add method to handle save button click
const handleSaveClick = async () => {
  try {
    if (formRef.value) {
      await formRef.value.submit();
    }
  } catch (error) {
    console.error('Error submitting form:', error);
    $q.notify({
      type: 'negative',
      message: 'Please fill in all required fields / يرجى ملء جميع الحقول المطلوبة'
    });
  }
};
</script>

<template>
  <q-page class="q-pa-md">
    <div class="q-mx-auto" >
      <base-breadcrumbs />
      <q-card flat bordered class="creation-card q-pa-lg">
        <q-card-section class="q-pb-none">
          <div class="text-h5 text-weight-bold q-mb-md row items-center">
            <q-icon name="add_card" color="primary" size="32px" class="q-mr-sm" />
            إنشاء بطاقة جديدة 
          </div>
        </q-card-section>

        <q-card-section class="create-section q-pa-lg">
          <PassCardForm 
            ref="formRef" 
            @submit="onSubmit" 
            @cancel="router.push({ name: 'PassCardIndex' })"
            :submit-loading="submitLoading" 
          />
        </q-card-section>

        <!-- Action Buttons -->
        <q-card-actions align="right" class="q-pt-lg">
          <q-btn
            color="negative"
            flat
            label="إلغاء"
            :to="{ name: 'PassCardIndex' }"
            class="q-mr-sm"
          />
          <q-btn
            color="primary"
            :loading="submitLoading"
            label="حفظ "
            @click="handleSaveClick"
          >
            <template v-slot:loading>
              <q-spinner-dots />
            </template>
          </q-btn>
        </q-card-actions>
      </q-card>
    </div>
  </q-page>
</template>

<style lang="scss" scoped>
.creation-card {
  background: white;
  border-radius: 16px; /* Slightly larger border-radius */
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); /* More prominent shadow */

  .create-section {
    background: #f8fafc;
    border-radius: 12px;
    margin-top: 1.5rem; /* Increased margin-top */
  }

  :deep(.q-tab) {
    padding: 1rem;
    min-height: 50px;
  }

  :deep(.q-tab--active) {
    background: rgba(var(--q-primary), 0.05);
  }

  :deep(.q-select) {
    .q-field__control {
      background: white;
      border-radius: 8px;
    }
  }
}

// Responsive Adjustments
@media (max-width: 768px) {
  .creation-card {
    margin: 0;

    :deep(.q-tab) {
      padding: 0.5rem;
    }
  }
}
</style>
