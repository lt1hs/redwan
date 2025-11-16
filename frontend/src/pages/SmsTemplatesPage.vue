<template>
  <q-page padding>
    <div class="q-mb-lg">
      <div class="text-h5 q-mb-lg text-primary">قوالب الرسائل النصية</div>

      <div class="row q-col-gutter-md">
        <!-- Credit Card -->
        <div class="col-12 col-md-4">
          <q-card class="bg-primary text-white">
            <q-card-section>
              <div class="text-h6">رصيد الرسائل النصية</div>
              <div class="text-subtitle2 q-mt-sm">
                <q-spinner-dots v-if="checkingCredit" />
                <template v-else>
                  {{ smsCredit.balance || 'غير متوفر' }}
                  {{ smsCredit.currency || '' }}
                </template>
              </div>
            </q-card-section>
            <q-card-actions align="right">
              <q-btn flat label="تحديث" icon="o_refresh" @click="checkSmsCredit" :loading="checkingCredit" />
            </q-card-actions>
          </q-card>
        </div>

        <!-- Failed Messages Card -->
        <div class="col-12 col-md-4">
          <q-card class="bg-negative text-white">
            <q-card-section>
              <div class="text-h6">الرسائل الفاشلة</div>
              <div class="text-subtitle2 q-mt-sm">
                <q-spinner-dots v-if="loading" />
                <template v-else>
                  {{ failedLogsCount }} رسالة
                </template>
              </div>
            </q-card-section>
            <q-card-actions align="right">
              <q-btn flat label="عرض السجلات" icon="o_view_list" :to="{ name: 'SmsLogs', query: { status: 'failed' } }" />
            </q-card-actions>
          </q-card>
        </div>

        <!-- Test SMS Card -->
        <div class="col-12 col-md-4">
          <q-card>
            <q-card-section>
              <div class="text-h6">اختبار إرسال رسالة</div>
              <div class="q-mt-sm">
                <q-input v-model="testPhone" label="رقم الهاتف" dir="ltr" prefix="+98" dense />
                <q-input v-model="testMessage" label="نص الرسالة" type="textarea" rows="2" class="q-mt-sm" dense />
              </div>
            </q-card-section>
            <q-card-actions align="right">
              <q-btn color="primary" label="إرسال" icon="o_send" @click="sendTestSms" :loading="sending" />
            </q-card-actions>
          </q-card>
        </div>
      </div>

      <!-- Templates Section -->
      <div class="q-mt-xl">
        <div class="text-h6 q-mb-md">قوالب الرسائل</div>
        
        <q-tabs
          v-model="activeTab"
          class="text-primary"
          active-color="primary"
          indicator-color="primary"
          align="justify"
        >
          <q-tab name="expiry" label="انتهاء الإقامة" />
          <q-tab name="passport" label="جوازات السفر" />
          <q-tab name="generic" label="قوالب عامة" />
          <q-tab name="settings" label="إعدادات الرسائل" />
        </q-tabs>

        <q-separator />

        <q-tab-panels v-model="activeTab" animated>
          <!-- Expiry Templates -->
          <q-tab-panel name="expiry">
            <div class="q-gutter-md">
              <template v-for="(template, index) in templates.filter(t => t.type.includes('expiry'))" :key="index">
                <q-card class="template-card">
                  <q-card-section>
                    <div class="text-h6">{{ getTemplateTitle(template.type) }}</div>
                    <q-toggle v-model="template.smsEnabled" label="تفعيل إرسال الرسائل النصية" />
                    
                    <q-input
                      v-model="template.smsTemplate"
                      type="textarea"
                      label="قالب الرسالة النصية"
                      rows="3"
                      class="q-mt-md"
                      :disable="!template.smsEnabled"
                    />
                    
                    <div class="text-caption q-mt-sm">
                      المتغيرات المتاحة: {{ "{name}" }} - {{ "{days}" }} - {{ "{expiry_date}" }}
                    </div>
                  </q-card-section>
                  <q-card-actions align="right">
                    <q-btn flat color="grey" @click="copyTemplate(template.smsTemplate)" icon="o_content_copy" label="نسخ" />
                    <q-btn flat color="primary" @click="saveTemplate(template)" icon="o_save" label="حفظ" />
                  </q-card-actions>
                </q-card>
              </template>
            </div>
          </q-tab-panel>

          <!-- Passport Templates -->
          <q-tab-panel name="passport">
            <div class="q-gutter-md">
              <template v-for="(template, index) in templates.filter(t => t.type.includes('passport'))" :key="index">
                <q-card class="template-card">
                  <q-card-section>
                    <div class="text-h6">{{ getTemplateTitle(template.type) }}</div>
                    <q-toggle v-model="template.smsEnabled" label="تفعيل إرسال الرسائل النصية" />
                    
                    <q-input
                      v-model="template.smsTemplate"
                      type="textarea"
                      label="قالب الرسالة النصية"
                      rows="3"
                      class="q-mt-md"
                      :disable="!template.smsEnabled"
                    />
                    
                    <div class="text-caption q-mt-sm">
                      المتغيرات المتاحة: {{ "{name}" }} - {{ "{id}" }} - {{ "{status}" }}
                    </div>
                  </q-card-section>
                  <q-card-actions align="right">
                    <q-btn flat color="grey" @click="copyTemplate(template.smsTemplate)" icon="o_content_copy" label="نسخ" />
                    <q-btn flat color="primary" @click="saveTemplate(template)" icon="o_save" label="حفظ" />
                  </q-card-actions>
                </q-card>
              </template>
            </div>
          </q-tab-panel>

          <!-- Generic Templates -->
          <q-tab-panel name="generic">
            <div class="q-gutter-md">
              <template v-for="(template, index) in templates.filter(t => t.type === 'generic')" :key="index">
                <q-card class="template-card">
                  <q-card-section>
                    <div class="text-h6">قالب عام</div>
                    
                    <q-input
                      v-model="template.smsTemplate"
                      type="textarea"
                      label="قالب الرسالة النصية"
                      rows="3"
                      class="q-mt-md"
                    />
                  </q-card-section>
                  <q-card-actions align="right">
                    <q-btn flat color="grey" @click="copyTemplate(template.smsTemplate)" icon="o_content_copy" label="نسخ" />
                    <q-btn flat color="primary" @click="saveTemplate(template)" icon="o_save" label="حفظ" />
                  </q-card-actions>
                </q-card>
              </template>

              <div class="q-mt-md">
                <q-btn color="primary" label="إضافة قالب جديد" icon="o_add" @click="addGenericTemplate" />
              </div>
            </div>
          </q-tab-panel>

          <!-- SMS Settings -->
          <q-tab-panel name="settings">
            <q-form @submit="saveSettings" class="q-gutter-md">
              <q-card>
                <q-card-section>
                  <div class="text-h6">إعدادات الرسائل النصية</div>
                  
                  <q-toggle v-model="smsSettings.enabled" label="تفعيل خدمة الرسائل النصية" />
                  
                  <q-input
                    v-model="smsSettings.defaultSender"
                    label="اسم المرسل الافتراضي"
                    class="q-mt-md"
                    :disable="!smsSettings.enabled"
                  />
                  
                  <q-input
                    v-model="smsSettings.apiKey"
                    label="مفتاح الـ API"
                    class="q-mt-md"
                    :disable="!smsSettings.enabled"
                    type="password"
                  />
                  
                  <q-select
                    v-model="smsSettings.provider"
                    :options="['unifonic', 'twilio', 'other']"
                    label="مزود خدمة الرسائل"
                    class="q-mt-md"
                    :disable="!smsSettings.enabled"
                  />
                </q-card-section>
                <q-card-actions align="right">
                  <q-btn type="submit" color="primary" label="حفظ الإعدادات" icon="o_save" />
                </q-card-actions>
              </q-card>
            </q-form>
          </q-tab-panel>
        </q-tab-panels>
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue';
import { useQuasar } from 'quasar';
import { SmsService } from '@/services/sms';
import { useSmsLogsStore } from '@/stores/sms-logs';

// Store
const smsLogsStore = useSmsLogsStore();
const $q = useQuasar();
const smsService = SmsService.getInstance();

// State
const activeTab = ref('expiry');
const checkingCredit = ref(false);
const smsCredit = ref({ balance: 0, currency: 'IRT' });
const loading = ref(false);
const sending = ref(false);
const testPhone = ref('');
const testMessage = ref('');
const failedLogsCount = ref(0);

// Templates
const templates = ref([
  {
    type: 'expiry_30',
    name: 'انتهاء الإقامة بعد 30 يوم',
    smsEnabled: true,
    smsTemplate: 'عزيزي {name}، نود إعلامك بأن إقامتك ستنتهي بعد {days} يوم في تاريخ {expiry_date}. يرجى التواصل معنا لتجديدها.'
  },
  {
    type: 'expiry_15',
    name: 'انتهاء الإقامة بعد 15 يوم',
    smsEnabled: true,
    smsTemplate: 'تنبيه هام: عزيزي {name}، إقامتك ستنتهي بعد {days} يوم في تاريخ {expiry_date}. برجاء سرعة التواصل معنا لتجديدها.'
  },
  {
    type: 'expiry_7',
    name: 'انتهاء الإقامة بعد 7 أيام',
    smsEnabled: true,
    smsTemplate: 'تنبيه عاجل: عزيزي {name}، إقامتك ستنتهي بعد {days} أيام فقط في تاريخ {expiry_date}. يرجى سرعة التواصل لتجديدها قبل فرض غرامات التأخير.'
  },
  {
    type: 'expiry_expired',
    name: 'انتهاء الإقامة (متأخر)',
    smsEnabled: true,
    smsTemplate: 'تنبيه عاجل جداً: عزيزي {name}، انتهت إقامتك منذ {days} يوم. برجاء التواصل فوراً لتجديدها وتفادي غرامات التأخير.'
  },
  {
    type: 'passport_approved',
    name: 'الموافقة على طلب الجواز',
    smsEnabled: true,
    smsTemplate: 'عزيزي {name}، تمت الموافقة على طلب جواز السفر رقم {id}. يمكنك استلامه من مكتبنا.'
  },
  {
    type: 'passport_rejected',
    name: 'رفض طلب الجواز',
    smsEnabled: true,
    smsTemplate: 'عزيزي {name}، نعتذر عن رفض طلب جواز السفر رقم {id}. يرجى التواصل معنا لمعرفة السبب وإعادة التقديم.'
  },
  {
    type: 'passport_pending',
    name: 'طلب جواز قيد المراجعة',
    smsEnabled: true,
    smsTemplate: 'عزيزي {name}، طلبك للجواز رقم {id} قيد المراجعة. سيتم إخطارك فور الانتهاء.'
  },
  {
    type: 'generic',
    name: 'قالب عام',
    smsEnabled: true,
    smsTemplate: 'عزيزي العميل، شكراً لتعاملك معنا. نتطلع دائماً لخدمتك.'
  }
]);

// SMS Settings
const smsSettings = reactive({
  enabled: true,
  defaultSender: 'Radwan',
  apiKey: '',
  provider: 'unifonic'
});

// Lifecycle
onMounted(async () => {
  await checkSmsCredit();
  await fetchFailedLogs();
});

// Methods
async function checkSmsCredit() {
  checkingCredit.value = true;
  try {
    const credit = await smsService.checkCredit();
    smsCredit.value = { balance: credit, currency: 'IRT' };
  } catch (error) {
    console.error('Error checking SMS credit:', error);
    $q.notify({
      type: 'negative',
      message: 'فشل التحقق من رصيد الرسائل'
    });
  } finally {
    checkingCredit.value = false;
  }
}

async function fetchFailedLogs() {
  loading.value = true;
  try {
    await smsLogsStore.fetchLogs({ status: 'FAILED' });
    failedLogsCount.value = smsLogsStore.failedLogs.length;
    
    if (smsLogsStore.error) {
      $q.notify({
        type: 'negative',
        message: smsLogsStore.error
      });
      smsLogsStore.clearError();
    }
  } catch (error) {
    console.error('Error fetching failed logs:', error);
    $q.notify({
      type: 'negative',
      message: 'فشل تحميل سجلات الرسائل الفاشلة'
    });
  } finally {
    loading.value = false;
  }
}

async function sendTestSms() {
  if (!testPhone.value || !testMessage.value) {
    $q.notify({
      type: 'warning',
      message: 'يرجى إدخال رقم الهاتف ونص الرسالة'
    });
    return;
  }

  sending.value = true;
  
  try {
    const formattedPhone = SmsService.formatPhoneNumber(testPhone.value);
    const result = await smsService.sendSms(formattedPhone, testMessage.value, 'test');
    
    // Log the successful SMS
    await smsLogsStore.logSms({
      recipient: formattedPhone,
      message: testMessage.value,
      status: 'SENT',
      created_at: new Date().toISOString(),
      type: 'test'
    });
    
    $q.notify({
      type: 'positive',
      message: 'تم إرسال الرسالة بنجاح'
    });
    
    // Clear the message after successful send
    testMessage.value = '';
  } catch (error) {
    console.error('Error sending test SMS:', error);
    
    // Log the failed SMS
    await smsLogsStore.logSms({
      recipient: testPhone.value,
      message: testMessage.value,
      status: 'FAILED',
      error: error instanceof Error ? error.message : String(error),
      created_at: new Date().toISOString(),
      type: 'test'
    });
    
    $q.notify({
      type: 'negative',
      message: 'فشل إرسال الرسالة'
    });
  } finally {
    sending.value = false;
  }
}

function getTemplateTitle(type: string) {
  const template = templates.value.find(t => t.type === type);
  return template ? template.name : type;
}

function copyTemplate(text: string) {
  navigator.clipboard.writeText(text);
  $q.notify({
    type: 'positive',
    message: 'تم نسخ القالب',
    timeout: 1000
  });
}

async function saveTemplate(template: any) {
  // Here you would save the template to your backend
  // For now, we'll just show a success notification
  $q.notify({
    type: 'positive',
    message: 'تم حفظ القالب بنجاح'
  });
}

function addGenericTemplate() {
  templates.value.push({
    type: 'generic',
    name: 'قالب عام جديد',
    smsEnabled: true,
    smsTemplate: ''
  });
}

async function saveSettings() {
  // Here you would save the settings to your backend
  // For now, we'll just show a success notification
  $q.notify({
    type: 'positive',
    message: 'تم حفظ الإعدادات بنجاح'
  });
}
</script>

<style scoped>
.template-card {
  transition: all 0.3s;
}

.template-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}
</style> 