<template>
  <div>
    <q-card class="q-mb-md">
      <q-card-section>
        <div class="text-h6">إشعار الجواز عبر الرسائل النصية</div>
        
        <q-form @submit="sendNotification" class="q-mt-md">
          <div class="row q-col-gutter-md">
            <div class="col-12 col-md-6">
              <q-input
                v-model="phone"
                label="رقم الهاتف"
                dir="ltr"
                outlined
                :rules="[val => !!val || 'يرجى إدخال رقم الهاتف']"
              />
            </div>
            
            <div class="col-12 col-md-6">
              <q-input
                v-model="name"
                label="اسم المستلم"
                outlined
                :rules="[val => !!val || 'يرجى إدخال اسم المستلم']"
              />
            </div>
            
            <div class="col-12">
              <q-select
                v-model="templateType"
                :options="templateOptions"
                label="نوع الرسالة"
                outlined
                emit-value
                map-options
                @update:model-value="updateMessageTemplate"
              />
            </div>
            
            <div class="col-12">
              <q-input
                v-model="message"
                type="textarea"
                label="نص الرسالة"
                outlined
                rows="3"
                counter
                :rules="[val => !!val || 'يرجى إدخال نص الرسالة']"
              />
              
              <div class="text-caption q-mt-sm">
                المتغيرات المتاحة: {{ "{name}" }} - {{ "{id}" }} - {{ "{status}" }}
              </div>
            </div>
          </div>
          
          <div class="q-mt-md">
            <q-btn
              type="submit"
              color="primary"
              icon="o_sms"
              label="إرسال الإشعار"
              :loading="sending"
              :disable="!messageValid"
            />
          </div>
        </q-form>
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useQuasar } from 'quasar';
import { SmsService } from '@/services/sms';
import { useNotification } from '@/composables/notification';

const props = defineProps({
  passportId: {
    type: [Number, String],
    required: true
  },
  passportStatus: {
    type: String,
    default: 'pending'
  },
  recipientName: {
    type: String,
    default: ''
  },
  recipientPhone: {
    type: String,
    default: ''
  }
});

const $q = useQuasar();
const { showSuccess, showError } = useNotification();

// State
const phone = ref(props.recipientPhone || '');
const name = ref(props.recipientName || '');
const message = ref('');
const templateType = ref('passport_pending');
const sending = ref(false);
const messageValid = computed(() => message.value && phone.value && name.value);

// Update local state when props change
watch(() => props.recipientPhone, (newValue) => {
  if (newValue) phone.value = newValue;
});

watch(() => props.recipientName, (newValue) => {
  if (newValue) name.value = newValue;
});

watch(() => props.passportStatus, (newValue) => {
  if (newValue) {
    if (newValue === 'approved') templateType.value = 'passport_approved';
    else if (newValue === 'rejected') templateType.value = 'passport_rejected';
    else templateType.value = 'passport_pending';
    
    updateMessageTemplate();
  }
});

// Template options
const templateOptions = [
  { label: 'موافق عليه', value: 'passport_approved' },
  { label: 'مرفوض', value: 'passport_rejected' },
  { label: 'قيد المراجعة', value: 'passport_pending' }
];

// Template messages
const templates: Record<string, string> = {
  passport_approved: 'عزيزي {name}، تمت الموافقة على طلب جواز السفر رقم {id}. يمكنك استلامه من مكتبنا.',
  passport_rejected: 'عزيزي {name}، نعتذر عن رفض طلب جواز السفر رقم {id}. يرجى التواصل معنا لمعرفة السبب وإعادة التقديم.',
  passport_pending: 'عزيزي {name}، طلبك للجواز رقم {id} قيد المراجعة. سيتم إخطارك فور الانتهاء.'
};

// Methods
function updateMessageTemplate() {
  const template = templates[templateType.value] || '';
  
  // Replace variables with actual values
  let formattedMessage = template
    .replace('{name}', name.value)
    .replace('{id}', String(props.passportId))
    .replace('{status}', getStatusText(props.passportStatus));
  
  message.value = formattedMessage;
}

function getStatusText(status: string): string {
  switch (status) {
    case 'approved': return 'تمت الموافقة';
    case 'rejected': return 'مرفوض';
    case 'pending': return 'قيد المراجعة';
    default: return status;
  }
}

async function sendNotification() {
  if (!messageValid.value) return;
  
  sending.value = true;
  
  try {
    const formattedPhone = SmsService.formatPhoneNumber(phone.value);
    
    await SmsService.sendSms(
      formattedPhone,
      message.value,
      `passport_${props.passportStatus}`,
      Number(props.passportId),
      name.value
    );
    
    showSuccess('تم إرسال الإشعار بنجاح');
  } catch (error) {
    console.error('Error sending passport notification:', error);
    showError('فشل إرسال الإشعار');
  } finally {
    sending.value = false;
  }
}

// Initialize the message template
updateMessageTemplate();

defineExpose({
  sendNotification
});
</script> 