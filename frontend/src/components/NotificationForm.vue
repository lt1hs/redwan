<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue';
import { useQuasar } from 'quasar';

import { useNotificationsStore } from '@/stores/notifications';
import BaseFileManagerDialog from './BaseFileManagerDialog.vue';

const notifications = useNotificationsStore();

const $q = useQuasar();

const getInitialData = (): any => ({ 
  title: '', 
  body: '', 
  target: 'general', 
  image: '',
  sendSms: false,
  recipients: [],
  phoneNumbers: []
});

const form = ref(getInitialData());
const formRef = ref<any>(null);
const fetchLoading = ref(false);
const smsCredit = ref<number | null>(null);
const recipients = ref<any[]>([]);
const selectedRecipients = ref<any[]>([]);
const manualPhoneNumbers = ref<string>('');

// Get SMS credit on mount
onMounted(async () => {
  try {
    smsCredit.value = await notifications.getSmsCredit();
  } catch (error) {
    console.error('Error fetching SMS credit:', error);
  }
});

// Parse phone numbers from comma-separated string
const parsePhoneNumbers = (input: string): string[] => {
  if (!input) return [];
  return input.split(',')
    .map(num => num.trim())
    .filter(num => num.length > 0);
};

// Combined phone numbers from selected recipients and manual entry
const combinedPhoneNumbers = computed(() => {
  const fromRecipients = selectedRecipients.value.map(recipient => recipient.phone);
  const manualNumbers = parsePhoneNumbers(manualPhoneNumbers.value);
  return [...new Set([...fromRecipients, ...manualNumbers])];
});

function onReset() {
  form.value = getInitialData();
  selectedRecipients.value = [];
  manualPhoneNumbers.value = '';
}

const props = defineProps({
  submitLoading: {
    type: Boolean,
    default: false
  },
  id: {
    type: Number,
    required: false
  },
  readonly: {
    type: Boolean,
    default: false
  }
});

async function fetch() {
  if (!props.id) {
    if (formRef.value) formRef.value.reset();
    return;
  }

  fetchLoading.value = true;
  form.value = await notifications.fetchDetails(props.id);
  fetchLoading.value = false;
}

// Prepare form data for submission
function prepareFormData() {
  return {
    ...form.value,
    phoneNumbers: form.value.sendSms ? combinedPhoneNumbers.value : [],
    recipients: form.value.sendSms ? selectedRecipients.value.map(r => r.id) : []
  };
}

// Handle form submission
function handleSubmit() {
  const formData = prepareFormData();
  
  // Validate phone numbers if SMS is enabled
  if (formData.sendSms && formData.phoneNumbers.length === 0) {
    $q.notify({
      type: 'negative',
      message: 'يرجى إضافة رقم هاتف واحد على الأقل'
    });
    return;
  }
  
  emit('submit', { form: formData });
}

watch(
  () => props.id,
  () => {
    fetch();
  },
  { immediate: true }
);

defineExpose({ fetch });
const emit = defineEmits(['submit']);
</script>

<template>
  <q-form
    @submit.prevent="handleSubmit"
    @reset="onReset"
    @validation-error="
      (ref: any) =>
        ref.$el.scrollIntoView({
          behavior: 'smooth',
          block: 'end',
          inline: 'nearest'
        })
    "
    ref="formRef"
  >
    <div class="row items-start q-col-gutter-md">
      <div class="col-12 col-md-3">
        <q-card>
          <q-card-section>
            <div class="row q-col-gutter-y-md">
              <div class="col-12">
                <label class="label" for="target"> النوع </label>
                <q-input
                  disable
                  for="target"
                  class="q-mt-xs"
                  hide-bottom-space
                  v-model="form.target"
                  outlined
                  dense
                  bottom-slots
                />
              </div>
              
              <!-- SMS Options -->
              <div class="col-12 q-mt-md">
                <q-card bordered class="bg-grey-1">
                  <q-card-section>
                    <div class="text-h6">خيارات الرسائل النصية SMS</div>
                    
                    <div class="q-mt-sm" v-if="smsCredit !== null">
                      <div class="text-caption">الرصيد المتبقي</div>
                      <div class="text-body1 q-mb-md">{{ smsCredit }} رسالة</div>
                    </div>
                    
                    <q-toggle
                      v-model="form.sendSms"
                      label="إرسال رسالة نصية SMS"
                      color="primary"
                    />
                    
                    <div v-if="form.sendSms" class="q-mt-md">
                      <div class="text-subtitle2 q-mb-xs">أرقام الهواتف</div>
                      <q-input
                        v-model="manualPhoneNumbers"
                        label="أدخل الأرقام مفصولة بفواصل"
                        hint="مثال: +98501234567,+98509876543"
                        outlined
                        dense
                      />
                      
                      <div class="text-caption q-mt-sm">
                        سيتم إرسال {{ combinedPhoneNumbers.length }} رسالة
                      </div>
                    </div>
                  </q-card-section>
                </q-card>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
      
      <div class="col-12 col-md-9">
        <q-card>
          <q-card-section>
            <div class="row q-col-gutter-x-md q-col-gutter-y-md">
              <q-input
                class="col-12"
                label="العنوان"
                outlined
                dense
                v-model="form.title"
                input-debounce="0"
                hide-bottom-space
                :rules="[(val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة']"
              />

              <div class="col-12">
                <q-input
                  outlined
                  dense
                  v-model="form.body"
                  type="textarea"
                  label="النص"
                  input-style="height: 150px"
                />
              </div>
              <q-input
                class="col-12"
                dir="ltr"
                outlined
                dense
                v-model="form.image"
                label="رابط الصورة"
                clearable
              >
                <template v-slot:append
                  ><q-icon
                    @click="
                      $q.dialog({
                        component: BaseFileManagerDialog,
                        componentProps: {
                          type: 'file'
                        }
                      }).onOk((files) => {
                        form.image = files[0].url;
                      })
                    "
                    name="o_upload"
                    class="cursor-pointer"
                /></template>
              </q-input>
            </div>

            <div class="row q-mt-md">
              <q-btn
                :loading="submitLoading"
                icon="o_notifications_active"
                :label="form.sendSms ? 'إرسال الإشعار والرسائل النصية' : 'إرسال الإشعار'"
                type="submit"
                class="q-mr-sm"
                color="primary"
              />
              
              <q-btn
                icon="o_cancel"
                label="إلغاء"
                type="reset"
                flat
                color="grey"
              />
            </div>
          </q-card-section>
        </q-card>
        
        <!-- SMS Preview Card -->
        <q-card v-if="form.sendSms" class="q-mt-md">
          <q-card-section>
            <div class="text-h6">معاينة الرسالة النصية</div>
            <div class="q-pa-md bg-grey-2 rounded-borders q-mt-sm">
              <div class="text-weight-bold">{{ form.title }}</div>
              <div>{{ form.body }}</div>
            </div>
            <div class="text-caption q-mt-xs">
              عدد الأحرف: {{ form.title.length + form.body.length }}
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-form>
</template>

<style scoped>
.phone-number-chip {
  height: 32px;
}
</style>
