<template>
  <q-page padding>
    <div class="q-mb-lg">
      <div class="text-h5 q-mb-lg text-primary">إعدادات الرسائل النصية</div>

      <div class="row q-col-gutter-md">
        <!-- Provider Settings -->
        <div class="col-12 col-md-6">
          <q-card>
            <q-card-section>
              <div class="text-h6">إعدادات مزود الخدمة Melipayamak</div>
              
              <q-form @submit="saveProviderSettings" class="q-gutter-md q-mt-md">
                <q-toggle v-model="providerSettings.enabled" label="تفعيل خدمة الرسائل النصية" />
                
                <div class="text-subtitle2 q-mt-md">بيانات اعتماد Melipayamak API</div>
                
                <q-input
                  v-model="providerSettings.username"
                  label="اسم المستخدم"
                  :disable="!providerSettings.enabled"
                  outlined
                  :rules="[val => !!val || 'حقل مطلوب']"
                />
                
                <q-input
                  v-model="providerSettings.password"
                  label="كلمة المرور"
                  :disable="!providerSettings.enabled"
                  :type="showApiKey ? 'text' : 'password'"
                  outlined
                  :rules="[val => !!val || 'حقل مطلوب']"
                >
                  <template v-slot:append>
                    <q-icon
                      :name="showApiKey ? 'o_visibility_off' : 'o_visibility'"
                      class="cursor-pointer"
                      @click="showApiKey = !showApiKey"
                    />
                  </template>
                </q-input>
                
                <q-input
                  v-model="providerSettings.sender"
                  label="رقم المرسل"
                  :disable="!providerSettings.enabled"
                  outlined
                  :rules="[val => !!val || 'حقل مطلوب']"
                  hint="رقم المرسل المعتمد من Melipayamak"
                />
                
                <q-toggle 
                  v-model="providerSettings.useMockMode" 
                  label="استخدام وضع المحاكاة (للاختبار فقط)"
                  class="q-my-md"
                />
                
                <q-btn
                  type="submit"
                  color="primary"
                  icon="o_save"
                  label="حفظ الإعدادات"
                  :loading="saving"
                  class="q-mt-md"
                />
              </q-form>
            </q-card-section>
          </q-card>
        </div>
        
        <!-- Test Connection -->
        <div class="col-12 col-md-6">
          <q-card>
            <q-card-section>
              <div class="text-h6">اختبار الاتصال</div>
              
              <div class="q-mt-md">
                <q-btn
                  color="primary"
                  icon="o_refresh"
                  label="اختبار الاتصال بمزود الخدمة"
                  :loading="testing"
                  @click="testConnection"
                  class="q-mb-md full-width"
                />
                
                <q-banner v-if="testResult" :class="testResult.success ? 'bg-positive' : 'bg-negative'" class="text-white q-mt-md">
                  <template v-slot:avatar>
                    <q-icon :name="testResult.success ? 'o_check_circle' : 'o_error'" />
                  </template>
                  <div>{{ testResult.message }}</div>
                </q-banner>
                
                <q-card-section class="q-pt-none">
                  <div class="text-h6 q-mt-md">اختبار إرسال رسالة</div>
                  <div class="q-mt-md">
                    <q-input v-model="testPhone" label="رقم الهاتف" dir="ltr" hint="أدخل رقم الهاتف مع رمز البلد مثل 98xx" outlined />
                    <q-input v-model="testMessage" label="نص الرسالة" type="textarea" rows="3" class="q-mt-sm" outlined />
                    
                    <q-btn
                      color="primary"
                      icon="o_send"
                      label="إرسال رسالة اختبارية"
                      :loading="sending"
                      @click="sendTestSms"
                      class="q-mt-md full-width"
                    />
                  </div>
                </q-card-section>
              </div>
            </q-card-section>
          </q-card>
          
          <!-- SMS Credit -->
          <q-card class="q-mt-md">
            <q-card-section>
              <div class="text-h6">رصيد الرسائل النصية</div>
              
              <div class="row items-center q-mt-md">
                <div class="col">
                  <div class="text-h5 text-primary">
                    <q-spinner-dots v-if="checkingCredit" color="primary" size="2em" />
                    <template v-else>
                      {{ smsCredit.balance || 0 }}
                      <span class="text-caption">{{ smsCredit.currency || 'IRR' }}</span>
                    </template>
                  </div>
                  <div class="text-caption" v-if="smsCredit.expiry_date">
                    صالح حتى: {{ formatDate(smsCredit.expiry_date) }}
                  </div>
                </div>
                <div class="col-auto">
                  <q-btn
                    flat
                    round
                    color="primary"
                    icon="o_refresh"
                    :loading="checkingCredit"
                    @click="checkSmsCredit"
                  />
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>
        
        <!-- Advanced Settings -->
        <div class="col-12">
          <q-expansion-item
            expand-separator
            icon="o_settings"
            label="إعدادات متقدمة"
            header-class="text-primary"
          >
            <q-card>
              <q-card-section>
                <div class="row q-col-gutter-md">
                  <div class="col-12 col-md-6">
                    <q-input
                      v-model.number="advancedSettings.retryCount"
                      label="عدد مرات إعادة المحاولة"
                      type="number"
                      min="0"
                      max="5"
                      outlined
                    />
                    
                    <q-input
                      v-model.number="advancedSettings.retryDelay"
                      label="الفاصل الزمني بين المحاولات (بالثواني)"
                      type="number"
                      min="30"
                      max="3600"
                      class="q-mt-md"
                      outlined
                    />
                  </div>
                  
                  <div class="col-12 col-md-6">
                    <q-select
                      v-model="advancedSettings.logRetention"
                      :options="retentionOptions"
                      label="فترة الاحتفاظ بالسجلات"
                      map-options
                      emit-value
                      outlined
                    />
                    
                    <q-input
                      v-model.number="advancedSettings.maxSmsPerDay"
                      label="الحد الأقصى للرسائل اليومية"
                      type="number"
                      min="0"
                      class="q-mt-md"
                      outlined
                    />
                  </div>
                  
                  <div class="col-12">
                    <q-btn
                      color="primary"
                      icon="o_save"
                      label="حفظ الإعدادات المتقدمة"
                      :loading="savingAdvanced"
                      @click="saveAdvancedSettings"
                      class="q-mt-md"
                    />
                  </div>
                </div>
              </q-card-section>
            </q-card>
          </q-expansion-item>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import { SmsService, type SmsCredit } from '@/services/sms';
import { useSmsLogsStore } from '@/stores/sms-logs';
import { date } from 'quasar';

// Store
const smsLogsStore = useSmsLogsStore();
const $q = useQuasar();
const smsService = SmsService.getInstance();

// State
const showApiKey = ref(false);
const showApiSecret = ref(false);
const saving = ref(false);
const savingAdvanced = ref(false);
const testing = ref(false);
const sending = ref(false);
const checkingCredit = ref(false);
const testPhone = ref('');
const testMessage = ref('عزيزي العميل، هذه رسالة اختبارية من نظام Radwan');
const testResult = ref<null | { success: boolean; message: string }>(null);
const smsCredit = ref<SmsCredit>({ balance: 0, currency: 'IRR' });

// Provider settings
const providerSettings = reactive({
  enabled: true,
  provider: 'melipayamak',
  username: '',
  password: '',
  sender: '',
  useMockMode: false
});

// Advanced settings
const advancedSettings = reactive({
  retryCount: 3,
  retryDelay: 60,
  logRetention: 90,
  maxSmsPerDay: 1000
});

// Options
const providerOptions = [
  { label: 'Unifonic', value: 'unifonic' },
  { label: 'Twilio', value: 'twilio' },
  { label: 'Vonage (Nexmo)', value: 'vonage' },
  { label: 'أخرى', value: 'other' }
];

const retentionOptions = [
  { label: '30 يوم', value: 30 },
  { label: '60 يوم', value: 60 },
  { label: '90 يوم', value: 90 },
  { label: '180 يوم', value: 180 },
  { label: 'سنة كاملة', value: 365 }
];

// Lifecycle
onMounted(async () => {
  await loadSavedSettings();
  await checkSmsCredit();
});

// Methods
async function loadSavedSettings() {
  try {
    // Load credentials from the SmsService
    const credentials = SmsService.getCredentials();
    providerSettings.username = credentials.username;
    providerSettings.password = credentials.password;
    providerSettings.sender = credentials.sender;
    
    // Mock mode is disabled by default for production
    providerSettings.useMockMode = false;
  } catch (error) {
    console.error('Error loading saved settings:', error);
  }
}

async function saveProviderSettings() {
  saving.value = true;
  try {
    // Save credentials to SmsService (which will persist to localStorage)
    SmsService.setCredentials(
      providerSettings.username,
      providerSettings.password,
      providerSettings.sender
    );
    
    // Set mock mode
    SmsService.setMockMode(providerSettings.useMockMode);
    
    $q.notify({
      type: 'positive',
      message: 'تم حفظ إعدادات مزود الخدمة بنجاح'
    });
  } catch (error) {
    console.error('Error saving provider settings:', error);
    $q.notify({
      type: 'negative',
      message: 'فشل حفظ الإعدادات، يرجى المحاولة مرة أخرى'
    });
  } finally {
    saving.value = false;
  }
}

async function saveAdvancedSettings() {
  savingAdvanced.value = true;
  try {
    // Here you would save the advanced settings to your backend
    // For now, we'll just simulate an API call
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    $q.notify({
      type: 'positive',
      message: 'تم حفظ الإعدادات المتقدمة بنجاح'
    });
  } catch (error) {
    console.error('Error saving advanced settings:', error);
    $q.notify({
      type: 'negative',
      message: 'فشل حفظ الإعدادات المتقدمة، يرجى المحاولة مرة أخرى'
    });
  } finally {
    savingAdvanced.value = false;
  }
}

async function testConnection() {
  if (!providerSettings.username || !providerSettings.password) {
    testResult.value = {
      success: false,
      message: 'يرجى إدخال بيانات الاعتماد أولاً'
    };
    return;
  }
  
  testing.value = true;
  testResult.value = null;
  
  try {
    // Test the connection by checking the credit balance
    const credit = await smsService.checkCredit();
    
    testResult.value = {
      success: true,
      message: `تم الاتصال بمزود الخدمة بنجاح. الرصيد الحالي: ${credit} IRR`
    };
    
    // Also update the credit display
    smsCredit.value = {
      balance: credit,
      currency: 'IRR'
    };
  } catch (error) {
    console.error('Error testing connection:', error);
    testResult.value = {
      success: false,
      message: 'فشل الاتصال بمزود الخدمة، يرجى التحقق من صحة بيانات الاعتماد'
    };
  } finally {
    testing.value = false;
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
      message: 'تم إرسال الرسالة الاختبارية بنجاح'
    });
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
      message: 'فشل إرسال الرسالة الاختبارية، يرجى التحقق من الإعدادات'
    });
  } finally {
    sending.value = false;
  }
}

async function checkSmsCredit() {
  checkingCredit.value = true;
  try {
    const credit = await smsService.checkCredit();
    smsCredit.value = {
      balance: credit,
      currency: 'IRR'
    };
  } catch (error) {
    console.error('Error checking SMS credit:', error);
  } finally {
    checkingCredit.value = false;
  }
}

function formatDate(dateString: string) {
  return date.formatDate(dateString, 'YYYY/MM/DD');
}
</script>

<style scoped>
/* Custom styling for the settings page */
</style> 