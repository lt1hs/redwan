<script setup lang="ts">
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useQuasar } from 'quasar';
import { useCardsStore } from '@/stores/cards';
import PassCardForm from '@/components/PassCardForm.vue';

interface SubmitEvent {
  form: FormData;
}

const route = useRoute();
const router = useRouter();
const $q = useQuasar();
const cardsStore = useCardsStore();

const submitLoading = ref(false);
const formRef = ref<InstanceType<typeof PassCardForm> | null>(null);
const parentId = ref(Number(route.params.id));

// Add validation for parent ID
if (!parentId.value || isNaN(parentId.value)) {
  $q.notify({
    type: 'negative',
    message: 'Parent card ID is required / معرف البطاقة الأصل مطلوب'
  });
  router.push({ name: 'PassCardIndex' });
}

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

  if (!parentId.value || isNaN(parentId.value)) {
    $q.notify({
      type: 'negative',
      message: 'Parent card ID is required / معرف البطاقة الأصل مطلوب'
    });
    return;
  }

  submitLoading.value = true;
  try {
    // Ensure parent_card_id is set
    const formData = event.form;
    formData.set('parent_card_id', String(parentId.value));
    
    // Remove unique_code field if it exists
    if (formData.has('unique_code')) {
      formData.delete('unique_code');
    }

    // Log form data for debugging
    const formDataObj: Record<string, string> = {};
    formData.forEach((value, key) => {
      formDataObj[key] = value.toString();
    });
    console.log('Submitting form data:', formDataObj);
    console.log('Parent card ID:', parentId.value);
    console.log('Form data entries:', Array.from(formData.entries()));

    // Create the card directly using the cards store
    const result = await cardsStore.create(formData);

    $q.notify({
      type: 'positive',
      message: 'تم إضافة عضو العائلة بنجاح / Family member added successfully'
    });

    // Redirect to cards list
    router.push({ name: 'PassCardIndex' });
  } catch (error: any) {
    console.error('Error creating family member card:', error);
    console.error('Error details:', {
      status: error.response?.status,
      statusText: error.response?.statusText,
      data: error.response?.data,
      headers: error.response?.headers
    });
    
    // Try to log more detailed error information
    if (error.response?.data) {
      console.error('Server error response:', error.response.data);
      if (error.response.data.exception) {
        console.error('Exception:', error.response.data.exception);
        console.error('File:', error.response.data.file);
        console.error('Line:', error.response.data.line);
        console.error('Trace:', error.response.data.trace);
      }
    }
    
    const errorMessage = error.response?.data?.message || error.message || 'حدث خطأ أثناء إضافة عضو العائلة / Error adding family member';
    const validationErrors = error.response?.data?.errors;
    
    if (validationErrors) {
      // Show each validation error
      Object.entries(validationErrors).forEach(([field, messages]) => {
        $q.notify({
          type: 'negative',
          message: Array.isArray(messages) ? messages[0] : String(messages)
        });
      });
    } else {
      $q.notify({
        type: 'negative',
        message: errorMessage
      });
    }
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
  <q-page>
    <base-breadcrumbs />
    <div class="q-pa-md">
      <q-card flat bordered class="creation-card">
        <q-card-section>
          <div class="text-h6 text-weight-bold q-mb-lg">
            <q-icon name="o_group_add" color="primary" size="28px" class="q-mr-sm" />
            إضافة عضو عائلة 
          </div>

          <q-card-section class="create-section">
            <PassCardForm 
              ref="formRef" 
              @submit="onSubmit" 
              @cancel="router.push({ name: 'PassCardIndex' })"
              :submit-loading="submitLoading"
              :parent-card-id="parentId"
              :force-family-type="true"
            />
          </q-card-section>

          <!-- Action Buttons -->
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
              :loading="submitLoading"
              label="حفظ"
              @click="handleSaveClick"
            >
              <template v-slot:loading>
                <q-spinner-dots />
              </template>
            </q-btn>
          </div>
        </q-card-section>
      </q-card>
    </div>
  </q-page>
</template>

<style lang="scss" scoped>
.creation-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);

  .create-section {
    background: #f8fafc;
    border-radius: 8px;
    margin-top: 1rem;
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