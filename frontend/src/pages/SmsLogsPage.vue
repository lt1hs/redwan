<template>
  <q-page padding>
    <div class="text-h5 q-mb-md">سجلات الرسائل النصية</div>
    
    <q-card bordered flat>
      <q-card-section>
        <div class="row items-center justify-between q-mb-md">
          <div class="text-subtitle1 text-weight-medium">
            إجمالي الرسائل: {{ smsLogs.totalLogs }}
          </div>
          
          <div class="row q-gutter-sm">
            <q-btn color="primary" icon="refresh" label="تحديث" @click="refreshLogs" :loading="smsLogs.loading" />
            <q-btn color="negative" icon="delete_sweep" label="حذف السجلات القديمة" @click="confirmClearOldLogs" />
            <q-btn color="secondary" icon="restart_alt" label="إعادة إرسال الفاشلة" 
                   @click="retryAllFailed" 
                   :disable="!smsLogs.failedLogs.length" />
          </div>
        </div>
        
        <div class="row q-col-gutter-md q-mb-md">
          <div class="col-12 col-md-3">
            <q-card class="bg-green-1">
              <q-card-section>
                <div class="text-subtitle2">تم إرسالها</div>
                <div class="text-h5">{{ smsLogs.sentLogs.length }}</div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-12 col-md-3">
            <q-card class="bg-orange-1">
              <q-card-section>
                <div class="text-subtitle2">قيد الانتظار</div>
                <div class="text-h5">{{ smsLogs.pendingLogs.length }}</div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-12 col-md-3">
            <q-card class="bg-red-1">
              <q-card-section>
                <div class="text-subtitle2">فشل الإرسال</div>
                <div class="text-h5">{{ smsLogs.failedLogs.length }}</div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-12 col-md-3">
            <q-card class="bg-blue-1">
              <q-card-section>
                <div class="text-subtitle2">رصيد الرسائل</div>
                <div class="text-h5">{{ smsCredit }} <q-spinner v-if="checkingCredit" size="1.5em" /></div>
              </q-card-section>
            </q-card>
          </div>
        </div>
        
        <div class="q-mb-md">
          <q-option-group
            v-model="filter.status"
            :options="[
              {label: 'الكل', value: ''},
              {label: 'تم الإرسال', value: 'SENT'},
              {label: 'قيد الانتظار', value: 'PENDING'},
              {label: 'فشل الإرسال', value: 'FAILED'}
            ]"
            inline
            @update:model-value="onFilterChange"
          />
        </div>
        
        <q-table
          :rows="smsLogs.logs"
          :columns="columns"
          row-key="id"
          :loading="smsLogs.loading"
          :filter="searchText"
          :pagination.sync="pagination"
          :rows-per-page-options="[10, 25, 50, 100]"
        >
          <template v-slot:top-right>
            <q-input v-model="searchText" placeholder="بحث..." clearable dense class="q-mr-md">
              <template v-slot:append>
                <q-icon name="search" />
              </template>
            </q-input>
          </template>
          
          <template v-slot:body="props">
            <q-tr :props="props">
              <q-td key="id" :props="props">{{ props.row.id }}</q-td>
              <q-td key="recipient" :props="props">{{ props.row.recipient }}</q-td>
              <q-td key="message" :props="props">
                <q-badge color="primary" v-if="props.row.related_type">{{ props.row.related_type }}</q-badge>
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
              <q-td key="actions" :props="props">
                <q-btn flat round size="sm" color="info" icon="visibility" @click="showDetails(props.row)">
                  <q-tooltip>عرض التفاصيل</q-tooltip>
                </q-btn>
                
                <q-btn v-if="props.row.status === 'FAILED'" flat round size="sm" color="primary" icon="replay"
                       @click="retrySms(props.row.id)">
                  <q-tooltip>إعادة الإرسال</q-tooltip>
                </q-btn>
                
                <q-btn flat round size="sm" color="negative" icon="delete" @click="confirmDelete(props.row.id)">
                  <q-tooltip>حذف</q-tooltip>
                </q-btn>
              </q-td>
            </q-tr>
          </template>
          
          <template v-slot:no-data>
            <div class="full-width text-center q-pa-md">
              لا توجد سجلات للرسائل النصية
            </div>
          </template>
        </q-table>
      </q-card-section>
    </q-card>
    
    <!-- SMS Details Dialog -->
    <q-dialog v-model="detailsDialog" persistent>
      <q-card style="min-width: 350px; max-width: 600px;">
        <q-card-section class="row items-center">
          <div class="text-h6">تفاصيل الرسالة</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        
        <q-card-section>
          <div v-if="selectedSms">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-6">
                <div class="text-subtitle2">رقم الهاتف</div>
                <div>{{ selectedSms.recipient }}</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="text-subtitle2">الحالة</div>
                <q-chip :color="getStatusColor(selectedSms.status)" text-color="white">
                  {{ getStatusText(selectedSms.status) }}
                </q-chip>
              </div>
              <div class="col-12">
                <div class="text-subtitle2">وقت الإرسال</div>
                <div>{{ formatDate(selectedSms.created_at) }}</div>
              </div>
              <div class="col-12">
                <div class="text-subtitle2">نص الرسالة</div>
                <q-card bordered flat class="bg-grey-1 q-pa-sm">
                  {{ selectedSms.message }}
                </q-card>
              </div>
              
              <div class="col-12" v-if="selectedSms.error">
                <div class="text-subtitle2">رسالة الخطأ</div>
                <q-card bordered flat class="bg-red-1 q-pa-sm">
                  {{ selectedSms.error }}
                </q-card>
              </div>
              
              <div class="col-12" v-if="selectedSms.response_data">
                <div class="text-subtitle2">استجابة الخادم</div>
                <q-card bordered flat class="bg-blue-1 q-pa-sm">
                  <pre>{{ formatResponse(selectedSms.response_data) }}</pre>
                </q-card>
              </div>
              
              <div class="col-12" v-if="selectedSms.retries">
                <div class="text-subtitle2">عدد مرات المحاولة</div>
                <div>{{ selectedSms.retries }}</div>
              </div>
            </div>
          </div>
        </q-card-section>
        
        <q-card-actions align="right">
          <q-btn flat label="إغلاق" color="primary" v-close-popup />
          <q-btn 
            v-if="selectedSms && selectedSms.status === 'FAILED'"
            color="primary" 
            label="إعادة الإرسال" 
            @click="retrySms(selectedSms.id); detailsDialog = false"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
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
const searchText = ref('');
const detailsDialog = ref(false);
const selectedSms = ref<SmsLog | null>(null);
const smsCredit = ref<number | null>(null);
const checkingCredit = ref(false);
const smsService = SmsService.getInstance();

const pagination = ref({
  sortBy: 'id',
  descending: true,
  page: 1,
  rowsPerPage: 25
});

const filter = ref({
  status: '',
  page: 1,
  limit: 100
});

const columns = [
  { name: 'id', label: '#', field: 'id', sortable: true, align: 'left' as const },
  { name: 'recipient', label: 'رقم الهاتف', field: 'recipient', sortable: true, align: 'left' as const },
  { name: 'message', label: 'الرسالة', field: 'message', sortable: false, align: 'left' as const },
  { name: 'status', label: 'الحالة', field: 'status', sortable: true, align: 'center' as const },
  { name: 'created_at', label: 'وقت الإرسال', field: 'created_at', sortable: true, align: 'center' as const },
  { name: 'actions', label: 'الإجراءات', field: 'actions', sortable: false, align: 'center' as const }
];

onMounted(async () => {
  await refreshLogs();
  await checkSmsCredit();
});

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
  await smsLogs.fetchLogs(filter.value);
};

// Show SMS details
const showDetails = (sms: SmsLog) => {
  selectedSms.value = sms;
  detailsDialog.value = true;
};

// Format JSON response
const formatResponse = (responseData: string) => {
  try {
    // If it's a JSON string, parse and format it
    const parsed = typeof responseData === 'string' ? JSON.parse(responseData) : responseData;
    return JSON.stringify(parsed, null, 2);
  } catch (e) {
    // If parsing fails, return the original string
    return responseData;
  }
};

// Filter change handler
const onFilterChange = () => {
  refreshLogs();
};

// Retry sending a failed SMS
const retrySms = async (id: number) => {
  try {
    await smsLogs.retryFailedSms(id);
    refreshLogs();
  } catch (error) {
    console.error('Error retrying SMS:', error);
  }
};

// Retry all failed SMS
const retryAllFailed = async () => {
  try {
    $q.dialog({
      title: 'إعادة إرسال الرسائل الفاشلة',
      message: 'هل تريد إعادة إرسال جميع الرسائل التي فشل إرسالها؟',
      cancel: true,
      persistent: true
    }).onOk(async () => {
      await smsLogs.retryFailedSms();
      refreshLogs();
    });
  } catch (error) {
    console.error('Error retrying all failed SMS:', error);
  }
};

// Check SMS credit
const checkSmsCredit = async () => {
  checkingCredit.value = true;
  try {
    smsCredit.value = await smsService.checkCredit();
  } catch (error) {
    console.error('Error checking SMS credit:', error);
    smsCredit.value = null;
  } finally {
    checkingCredit.value = false;
  }
};

// Confirm delete SMS log
const confirmDelete = (id: number) => {
  $q.dialog({
    title: 'حذف السجل',
    message: 'هل أنت متأكد من حذف هذا السجل؟',
    cancel: true,
    persistent: true
  }).onOk(async () => {
    await smsLogs.deleteLog(id);
    refreshLogs();
  });
};

// Confirm clear old logs
const confirmClearOldLogs = () => {
  $q.dialog({
    title: 'حذف السجلات القديمة',
    message: 'حدد عدد الأيام للاحتفاظ بالسجلات الأحدث منها',
    prompt: {
      model: '30',
      type: 'number'
    },
    cancel: true,
    persistent: true
  }).onOk(async (days) => {
    await smsLogs.clearOldLogs(parseInt(days));
    refreshLogs();
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