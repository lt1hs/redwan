<template>
  <q-page padding>
    <div class="text-h5 q-mb-md">لوحة تحكم الرسائل النصية</div>
    
    <div class="row q-col-gutter-md">
      <!-- Stats Cards -->
      <div class="col-12 col-md-3">
        <q-card class="bg-primary text-white">
          <q-card-section>
            <div class="text-subtitle2">رصيد الرسائل</div>
            <div class="row items-center">
              <div class="text-h4">{{ smsCredit !== null ? smsCredit : '--' }}</div>
              <q-spinner v-if="checkingCredit" color="white" class="q-ml-sm" />
              <q-btn v-else flat round dense icon="refresh" @click="checkSmsCredit">
                <q-tooltip>تحديث الرصيد</q-tooltip>
              </q-btn>
            </div>
          </q-card-section>
          <q-separator dark />
          <q-card-actions>
            <q-btn flat label="فحص الرصيد" @click="checkSmsCredit" :loading="checkingCredit" />
          </q-card-actions>
        </q-card>
      </div>
      
      <div class="col-12 col-md-3">
        <q-card class="bg-positive text-white">
          <q-card-section>
            <div class="text-subtitle2">تم إرسالها</div>
            <div class="text-h4">{{ statistics.sent }}</div>
          </q-card-section>
          <q-separator dark />
          <q-card-actions>
            <q-btn flat label="عرض التفاصيل" to="/sms/logs?status=SENT" />
          </q-card-actions>
        </q-card>
      </div>
      
      <div class="col-12 col-md-3">
        <q-card class="bg-negative text-white">
          <q-card-section>
            <div class="text-subtitle2">فشل الإرسال</div>
            <div class="text-h4">{{ statistics.failed }}</div>
          </q-card-section>
          <q-separator dark />
          <q-card-actions>
            <q-btn flat label="عرض التفاصيل" to="/sms/logs?status=FAILED" />
            <q-btn flat label="إعادة الإرسال" @click="retryAllFailed" 
                  :disable="statistics.failed === 0" />
          </q-card-actions>
        </q-card>
      </div>
      
      <div class="col-12 col-md-3">
        <q-card class="bg-info text-white">
          <q-card-section>
            <div class="text-subtitle2">اليوم</div>
            <div class="text-h4">{{ statistics.today }}</div>
          </q-card-section>
          <q-separator dark />
          <q-card-actions>
            <q-btn flat label="عرض التفاصيل" to="/sms/logs" />
          </q-card-actions>
        </q-card>
      </div>
      
      <!-- Quick Actions -->
      <div class="col-12">
        <q-card>
          <q-card-section>
            <div class="text-h6">إجراءات سريعة</div>
            
            <div class="row q-col-gutter-md q-mt-md">
              <div class="col-12 col-md-6">
                <q-card flat bordered>
                  <q-card-section>
                    <div class="text-subtitle1">إرسال رسالة نصية جديدة</div>
                    <q-form @submit="sendTestSms" class="q-mt-md">
                      <q-input 
                        v-model="newSms.recipient" 
                        label="رقم الهاتف" 
                        outlined 
                        dense
                        :rules="[val => !!val || 'يجب إدخال رقم الهاتف']"
                      />
                      
                      <q-input 
                        v-model="newSms.message" 
                        label="نص الرسالة" 
                        type="textarea" 
                        outlined 
                        dense
                        class="q-mt-sm"
                        counter
                        maxlength="160"
                        :rules="[val => !!val || 'يجب إدخال نص الرسالة']"
                      />
                      
                      <div class="q-mt-md">
                        <q-btn 
                          type="submit" 
                          color="primary" 
                          label="إرسال" 
                          :loading="sending"
                        />
                      </div>
                    </q-form>
                  </q-card-section>
                </q-card>
              </div>
              
              <div class="col-12 col-md-6">
                <q-card flat bordered>
                  <q-card-section>
                    <div class="text-subtitle1">قوالب الرسائل</div>
                    
                    <q-list bordered separator class="q-mt-sm">
                      <q-item v-for="(template, index) in smsTemplates" :key="index" clickable @click="selectTemplate(template)">
                        <q-item-section>
                          <q-item-label>{{ template.title }}</q-item-label>
                          <q-item-label caption>{{ template.template }}</q-item-label>
                        </q-item-section>
                        <q-item-section side>
                          <q-btn flat round dense icon="content_copy" size="sm" @click.stop="copyTemplate(template.template)">
                            <q-tooltip>نسخ</q-tooltip>
                          </q-btn>
                        </q-item-section>
                      </q-item>
                    </q-list>
                    
                    <div class="q-mt-md">
                      <q-btn color="secondary" label="إدارة القوالب" to="/sms/templates" />
                    </div>
                  </q-card-section>
                </q-card>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
      
      <!-- Recent SMS Logs -->
      <div class="col-12">
        <q-card>
          <q-card-section>
            <div class="text-h6">
              آخر الرسائل المرسلة
              <q-btn flat round dense icon="refresh" size="sm" class="q-ml-sm" @click="refreshLogs" :loading="loading">
                <q-tooltip>تحديث</q-tooltip>
              </q-btn>
            </div>
            
            <q-table
              :rows="recentLogs"
              :columns="columns"
              row-key="id"
              :loading="loading"
              :pagination="{ rowsPerPage: 5 }"
              :rows-per-page-options="[5]"
              flat
              bordered
            >
              <template v-slot:body="props">
                <q-tr :props="props">
                  <q-td key="recipient" :props="props">{{ props.row.recipient }}</q-td>
                  <q-td key="message" :props="props">
                    <div class="message-text">{{ props.row.message }}</div>
                  </q-td>
                  <q-td key="status" :props="props">
                    <q-chip 
                      :color="getStatusColor(props.row.status)" 
                      text-color="white"
                      dense
                    >
                      {{ getStatusText(props.row.status) }}
                    </q-chip>
                  </q-td>
                  <q-td key="created_at" :props="props">
                    {{ formatDate(props.row.created_at) }}
                  </q-td>
                </q-tr>
              </template>
              
              <template v-slot:no-data>
                <div class="full-width text-center q-pa-md">
                  لا توجد رسائل مرسلة حديثاً
                </div>
              </template>
            </q-table>
            
            <div class="text-right q-mt-sm">
              <q-btn color="primary" label="عرض كل السجلات" to="/sms/logs" flat />
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useQuasar, date } from 'quasar';
import { useSmsLogsStore } from '@/stores/sms-logs';
import type { SmsLog } from '@/stores/sms-logs';
import { SmsService } from '@/services/sms';

const $q = useQuasar();
const smsLogs = useSmsLogsStore();
const smsService = SmsService.getInstance();

const checkingCredit = ref(false);
const smsCredit = ref<number | null>(null);
const loading = ref(false);
const sending = ref(false);
const recentLogs = ref<SmsLog[]>([]);

// Statistics
const statistics = ref({
  sent: 0,
  failed: 0,
  pending: 0,
  today: 0
});

// New SMS form data
const newSms = ref({
  recipient: '',
  message: ''
});

// SMS Templates
const smsTemplates = ref([
  {
    title: 'ترحيب بالعملاء',
    template: 'مرحباً {الاسم}، نشكرك على التسجيل في خدماتنا. يمكنك التواصل معنا على الرقم 98XXXXXXXXX لأي استفسار.'
  },
  {
    title: 'تأكيد استلام الجواز',
    template: 'تم استلام جواز السفر الخاص بك بنجاح. رقم المتابعة: {رقم_المتابعة}. شكراً لتعاملك معنا.'
  },
  {
    title: 'إشعار انتهاء الإقامة',
    template: 'تنبيه: ستنتهي إقامتك بتاريخ {تاريخ_الانتهاء}. يرجى المبادرة بتجديدها. للاستفسار: 98XXXXXXXXX'
  }
]);

const columns = [
  { name: 'recipient', label: 'رقم الهاتف', field: 'recipient', sortable: true, align: 'left' as const },
  { name: 'message', label: 'الرسالة', field: 'message', sortable: false, align: 'left' as const },
  { name: 'status', label: 'الحالة', field: 'status', sortable: true, align: 'center' as const },
  { name: 'created_at', label: 'وقت الإرسال', field: 'created_at', sortable: true, align: 'center' as const }
];

onMounted(async () => {
  await Promise.all([
    refreshLogs(),
    checkSmsCredit(),
    calculateStatistics()
  ]);
});

// Check SMS credit
const checkSmsCredit = async () => {
  checkingCredit.value = true;
  try {
    smsCredit.value = await smsService.checkCredit();
  } catch (error) {
    console.error('Error checking SMS credit:', error);
    smsCredit.value = null;
    $q.notify({
      type: 'negative',
      message: 'فشل التحقق من رصيد الرسائل'
    });
  } finally {
    checkingCredit.value = false;
  }
};

// Format date
const formatDate = (dateString: string) => {
  if (!dateString) return '';
  return date.formatDate(dateString, 'YYYY-MM-DD HH:mm:ss');
};

// Get status color
const getStatusColor = (status: string) => {
  switch (status) {
    case 'SENT': return 'positive';
    case 'PENDING': return 'warning';
    case 'FAILED': return 'negative';
    default: return 'grey';
  }
};

// Get status text
const getStatusText = (status: string) => {
  switch (status) {
    case 'SENT': return 'تم الإرسال';
    case 'PENDING': return 'قيد الانتظار';
    case 'FAILED': return 'فشل الإرسال';
    default: return status;
  }
};

// Refresh logs
const refreshLogs = async () => {
  loading.value = true;
  try {
    const logs = await smsLogs.fetchLogs({ limit: 5 });
    recentLogs.value = logs;
  } catch (error) {
    console.error('Error fetching logs:', error);
  } finally {
    loading.value = false;
  }
};

// Calculate statistics
const calculateStatistics = async () => {
  loading.value = true;
  try {
    await smsLogs.fetchLogs();
    
    statistics.value.sent = smsLogs.sentLogs.length;
    statistics.value.failed = smsLogs.failedLogs.length;
    statistics.value.pending = smsLogs.pendingLogs.length;
    
    // Calculate today's SMS count
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    statistics.value.today = smsLogs.logs.filter(log => {
      const logDate = new Date(log.created_at);
      return logDate >= today;
    }).length;
  } catch (error) {
    console.error('Error calculating statistics:', error);
  } finally {
    loading.value = false;
  }
};

// Send test SMS
const sendTestSms = async () => {
  sending.value = true;
  try {
    // Validate recipient format
    let recipient = newSms.value.recipient.trim();
    
    // Format phone number if needed using the service
    recipient = SmsService.formatPhoneNumber(recipient);
    
    // Send SMS
    const result = await smsService.sendSms(recipient, newSms.value.message);
    
    // Log to the SMS logs store
    await smsLogs.logSms({
      recipient,
      message: newSms.value.message,
      status: 'SENT',
      created_at: new Date().toISOString()
    });
    
    // Update recent logs and statistics
    await Promise.all([refreshLogs(), calculateStatistics()]);
    
    // Reset form
    newSms.value.recipient = '';
    newSms.value.message = '';
    
    // Show success notification
    $q.notify({
      type: 'positive',
      message: 'تم إرسال الرسالة بنجاح'
    });
  } catch (error) {
    console.error('Error sending SMS:', error);
    
    // Show error notification
    $q.notify({
      type: 'negative',
      message: 'فشل إرسال الرسالة'
    });
    
    // Log failed attempt
    await smsLogs.logSms({
      recipient: newSms.value.recipient,
      message: newSms.value.message,
      status: 'FAILED',
      error: JSON.stringify(error),
      created_at: new Date().toISOString()
    });
    
    // Update statistics
    await calculateStatistics();
  } finally {
    sending.value = false;
  }
};

// Retry all failed SMS
const retryAllFailed = () => {
  $q.dialog({
    title: 'إعادة إرسال الرسائل الفاشلة',
    message: `هل تريد إعادة إرسال ${statistics.value.failed} رسالة فاشلة؟`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    loading.value = true;
    try {
      const result = await smsLogs.retryFailedSms();
      
      // Update the stats
      await Promise.all([refreshLogs(), calculateStatistics()]);
    } catch (error) {
      console.error('Error retrying failed SMS:', error);
    } finally {
      loading.value = false;
    }
  });
};

// Select template
const selectTemplate = (template: {title: string, template: string}) => {
  newSms.value.message = template.template;
};

// Copy template to clipboard
const copyTemplate = (template: string) => {
  navigator.clipboard.writeText(template).then(() => {
    $q.notify({
      type: 'positive',
      message: 'تم نسخ القالب بنجاح',
      timeout: 1000
    });
  });
};
</script>

<style scoped>
.message-text {
  max-width: 300px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.q-card {
  border-radius: 8px;
}
</style> 