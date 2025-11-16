<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import { useNotificationStore } from '@/stores/notification';
import SmsService from '@/services/sms';

const $q = useQuasar();
const notificationStore = useNotificationStore();

const templates = ref([...notificationStore.templates]);
const smsCredit = ref<number | null>(null);
const loading = ref(false);
const failedLogsCount = ref(0);
const testPhone = ref('');
const testMessage = ref('');

// Get current SMS credit and failed SMS count on mount
onMounted(async () => {
  try {
    loading.value = true;
    
    // Get SMS credit
    const smsService = SmsService.getInstance();
    smsCredit.value = await smsService.checkCredit();
    
    // Get failed SMS logs count
    const response = await fetch('/api/sms-logs?status=FAILED');
    const failedLogs = await response.json();
    failedLogsCount.value = failedLogs.length;
  } catch (error) {
    console.error('Error fetching SMS data:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء جلب بيانات الرسائل النصية'
    });
  } finally {
    loading.value = false;
  }
});

// Save template changes
async function saveTemplate(template: any) {
  try {
    loading.value = true;
    await notificationStore.updateTemplate(template);
    
    $q.notify({
      type: 'positive',
      message: 'تم حفظ قالب الرسائل النصية بنجاح'
    });
  } catch (error) {
    console.error('Error saving template:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء حفظ قالب الرسائل النصية'
    });
  } finally {
    loading.value = false;
  }
}

// Resend failed SMS
async function resendFailedSms() {
  try {
    loading.value = true;
    const result = await notificationStore.resendFailedSms();
    
    $q.notify({
      type: 'positive',
      message: `تمت إعادة إرسال ${result.success} رسالة بنجاح من أصل ${result.total} رسالة فاشلة`
    });
    
    // Update count
    failedLogsCount.value = result.failed;
  } catch (error) {
    console.error('Error resending failed SMS:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء إعادة إرسال الرسائل النصية الفاشلة'
    });
  } finally {
    loading.value = false;
  }
}

// Send test SMS
async function sendTestSms() {
  if (!testPhone.value || !testMessage.value) {
    $q.notify({
      type: 'negative',
      message: 'يرجى إدخال رقم الهاتف ونص الرسالة'
    });
    return;
  }
  
  try {
    loading.value = true;
    const smsService = SmsService.getInstance();
    await smsService.sendSms(testPhone.value, testMessage.value);
    
    $q.notify({
      type: 'positive',
      message: 'تم إرسال الرسالة النصية بنجاح'
    });
    
    // Clear test form
    testPhone.value = '';
    testMessage.value = '';
  } catch (error) {
    console.error('Error sending test SMS:', error);
    $q.notify({
      type: 'negative',
      message: 'فشل إرسال الرسالة النصية'
    });
  } finally {
    loading.value = false;
  }
}

// Format expiration types to Arabic
function formatExpirationType(type: string): string {
  switch (type) {
    case 'ONE_MONTH':
      return 'قبل شهر واحد';
    case 'TWO_WEEKS':
      return 'قبل أسبوعين';
    case 'ONE_WEEK':
      return 'قبل أسبوع واحد';
    default:
      return type;
  }
}
</script>

<template>
  <div>
    <q-card class="q-mb-md">
      <q-card-section>
        <div class="text-h6">إعدادات الرسائل النصية SMS</div>
        
        <div class="row q-mt-md q-col-gutter-md">
          <div class="col-12 col-md-4">
            <q-card bordered flat class="bg-green-1">
              <q-card-section>
                <div class="text-subtitle1">الرصيد الحالي</div>
                <div class="text-h5 q-mt-sm">
                  <q-skeleton v-if="smsCredit === null" type="text" width="100px" />
                  <template v-else>{{ smsCredit }} رسالة</template>
                </div>
              </q-card-section>
            </q-card>
          </div>
          
          <div class="col-12 col-md-4">
            <q-card bordered flat class="bg-orange-1">
              <q-card-section>
                <div class="text-subtitle1">رسائل فاشلة</div>
                <div class="text-h5 q-mt-sm">
                  <q-skeleton v-if="loading" type="text" width="100px" />
                  <template v-else>{{ failedLogsCount }} رسالة</template>
                </div>
                
                <q-btn 
                  v-if="failedLogsCount > 0"
                  class="q-mt-sm" 
                  color="primary" 
                  label="إعادة الإرسال" 
                  :loading="loading"
                  @click="resendFailedSms"
                />
              </q-card-section>
            </q-card>
          </div>
        </div>
      </q-card-section>
    </q-card>
    
    <q-card>
      <q-card-section>
        <div class="text-h6">قوالب الرسائل النصية</div>
        
        <div class="q-mt-md">
          <q-table
            :rows="templates"
            :columns="[
              { name: 'type', label: 'نوع الإشعار', field: 'type', align: 'left' },
              { name: 'messageTemplate', label: 'نص الرسالة', field: 'messageTemplate', align: 'left' },
              { name: 'status', label: 'الحالة', field: 'isActive', align: 'center' },
              { name: 'smsEnabled', label: 'إرسال رسالة نصية', field: 'smsEnabled', align: 'center' },
              { name: 'actions', label: 'الإجراءات', field: 'actions', align: 'center' }
            ]"
            row-key="id"
            flat
            bordered
          >
            <template v-slot:body="props">
              <q-tr :props="props">
                <q-td key="type" :props="props">
                  {{ formatExpirationType(props.row.type) }}
                </q-td>
                <q-td key="messageTemplate" :props="props">
                  {{ props.row.messageTemplate }}
                </q-td>
                <q-td key="status" :props="props" class="text-center">
                  <q-chip
                    :color="props.row.isActive ? 'green' : 'grey'"
                    text-color="white"
                    dense
                  >
                    {{ props.row.isActive ? 'مفعل' : 'غير مفعل' }}
                  </q-chip>
                </q-td>
                <q-td key="smsEnabled" :props="props" class="text-center">
                  <q-toggle
                    v-model="props.row.smsEnabled"
                    color="primary"
                    :disable="!props.row.isActive"
                    @update:model-value="saveTemplate(props.row)"
                  />
                </q-td>
                <q-td key="actions" :props="props" class="text-center">
                  <q-btn
                    flat
                    round
                    color="primary"
                    icon="o_edit"
                    @click="
                      $q.dialog({
                        title: 'تعديل نص الرسالة',
                        message: 'يمكنك استخدام {username} و {expirationDate} داخل نص الرسالة',
                        prompt: {
                          model: props.row.messageTemplate,
                          type: 'textarea',
                          isValid: val => val.length > 0
                        },
                        cancel: true,
                        persistent: true
                      }).onOk(data => {
                        props.row.messageTemplate = data;
                        saveTemplate(props.row);
                      })
                    "
                  >
                    <q-tooltip>تعديل نص الرسالة</q-tooltip>
                  </q-btn>
                  
                  <q-btn
                    flat
                    round
                    :color="props.row.isActive ? 'negative' : 'positive'"
                    :icon="props.row.isActive ? 'o_toggle_off' : 'o_toggle_on'"
                    @click="
                      props.row.isActive = !props.row.isActive;
                      saveTemplate(props.row);
                    "
                  >
                    <q-tooltip>
                      {{ props.row.isActive ? 'تعطيل' : 'تفعيل' }}
                    </q-tooltip>
                  </q-btn>
                </q-td>
              </q-tr>
            </template>
          </q-table>
        </div>
      </q-card-section>
    </q-card>
    
    <q-card class="q-mt-md">
      <q-card-section>
        <div class="text-h6">اختبار إرسال رسالة نصية</div>
        
        <q-form @submit.prevent="sendTestSms" class="q-mt-md">
          <div class="row q-col-gutter-md">
            <div class="col-12 col-md-6">
              <q-input
                v-model="testPhone"
                label="رقم الهاتف"
                hint="مثال: +98501234567"
                outlined
                dense
                :rules="[val => !!val || 'هذا الحقل مطلوب']"
              />
            </div>
            
            <div class="col-12">
              <q-input
                v-model="testMessage"
                label="نص الرسالة"
                type="textarea"
                outlined
                dense
                :rules="[val => !!val || 'هذا الحقل مطلوب']"
              />
            </div>
            
            <div class="col-12">
              <q-btn
                type="submit"
                color="primary"
                label="إرسال رسالة اختبار"
                :loading="loading"
                icon="o_send"
              />
            </div>
          </div>
        </q-form>
      </q-card-section>
    </q-card>
  </div>
</template>

<style scoped>
.q-table__top {
  padding: 8px 0;
}
</style> 