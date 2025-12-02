<script setup lang="ts">
// Add router initialization
const router = useRouter();

import { ref, computed, onMounted, watch } from 'vue';
import { useQuasar, type QTableColumn } from 'quasar';
import { usePassportsStore } from '@/stores/passports';
import { useRouter } from 'vue-router';
import moment from 'moment-jalaali';
import { utils as XLSXUtils, writeFile } from 'xlsx';
import { SmsService } from '@/services/sms';

interface Passport {
  id: number;
  full_name: string;
  nationality: string;
  passport_number: string;
  date_of_birth: string;
  residence_expiry_date: string;
  phone_number: string;
  mobile_number: string;
  passport_status: string;
  passport_delivery_date: string;
  transaction_type: string;
  payment_status: string;
  address: string;
  zipcode: string;
  delivered_by: string;
  created_at: string;
  [key: string]: any;
}

const $q = useQuasar();

const passportsStore = usePassportsStore();
const loading = ref<boolean>(false);
const search = ref('');
const filterType = ref('all');
const maxProcessingDays = 7; // Maximum allowed processing days

// Pagination
const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 0
});

const perPageOptions = [10, 50, 100, 0]; // 0 represents "all"

const perPageLabels = {
  10: '10',
  50: '50', 
  100: '100',
  0: 'الكل'
};

// Filter options
const statusFilters = [
  { label: 'الكل', value: 'all' },
  { label: 'قيد الانجاز', value: 'processing' },
  { label: 'جاهز للاستلام', value: 'ready' },
  { label: 'تم تسليمه', value: 'delivered' }
];

// Add status options - use exactly the valid enum values from backend
const statusOptions = [
  { label: 'قيد الانجاز', value: 'قيد الانجاز', color: 'warning' },
  { label: 'جاهز للاستلام', value: 'جاهز للاستلام', color: 'info' },
  { label: 'تم تسليمه', value: 'تم تسليمه', color: 'positive' }
];

// Calculate processing time
const calculateProcessingTime = (receiptDate: string, deliveryDate?: string) => {
  const start = moment(receiptDate);
  const end = deliveryDate ? moment(deliveryDate) : moment();
  const days = end.diff(start, 'days');
  return days;
};

// Check if processing time exceeded
const hasExceededProcessingTime = (receiptDate: string, deliveryDate?: string) => {
  const days = calculateProcessingTime(receiptDate, deliveryDate);
  return days > maxProcessingDays;
};

// Format processing time for display
const formatProcessingTime = (days: number) => {
  if (days === 0) return 'اليوم';
  if (days === 1) return 'يوم واحد';
  if (days === 2) return 'يومين';
  return `${days} أيام`;
};

// Use passports directly from store for table
const passports = computed(() => passportsStore.passports);

const filteredRows = computed(() => {
  let results = passportsStore.passports.map((row) => ({
    index: row.index || 1, // Use backend-provided index
    ...row,
    processing_time: calculateProcessingTime(row.passport_delivery_date, row.delivery_timestamp),
    processing_exceeded: hasExceededProcessingTime(
      row.passport_delivery_date,
      row.delivery_timestamp
    )
  }));

  // Apply search
  if (search.value) {
    const searchLower = search.value.toLowerCase();
    results = results.filter(
      (row) =>
        row.full_name?.toLowerCase().includes(searchLower) ||
        row.passport_number?.toLowerCase().includes(searchLower) ||
        row.unique_code?.toLowerCase().includes(searchLower)
    );
  }

  // Apply status filter
  if (filterType.value !== 'all') {
    switch (filterType.value) {
      case 'processing':
        results = results.filter((row) => row.passport_status === 'قيد الانجاز');
        break;
      case 'ready':
        results = results.filter((row) => row.passport_status === 'جاهز للاستلام');
        break;
      case 'delivered':
        results = results.filter((row) => row.passport_status === 'تم تسليمه');
        break;
    }
  }

  return results;
});

const columns: QTableColumn[] = [
  {
    name: 'index',
    label: 'التسلسل',
    field: 'index',
    align: 'left'
  },
  {
    name: 'passport_number',
    label: 'رقم الجواز',
    field: 'passport_number',
    sortable: true,
    align: 'left'
  },
  {
    name: 'unique_code',
    label: 'کود المکتب',
    field: 'unique_code',
    sortable: true,
    align: 'left'
  },
  {
    name: 'full_name',
    label: 'الاسم الكامل',
    field: 'full_name',
    sortable: true,
    align: 'left'
  },
  {
    name: 'passport_delivery_date',
    label: 'تاريخ استلام الجواز',
    field: 'passport_delivery_date',
    sortable: true,
    align: 'left',
    format: (val) => moment(val).format('jYYYY/jMM/jDD')
  },
  {
    name: 'processing_time',
    label: 'مدة المعالجة',
    field: 'processing_time',
    sortable: true,
    align: 'left',
    format: (val) => formatProcessingTime(val)
  },
  {
    name: 'passport_status',
    label: 'حالة الجواز',
    field: 'passport_status',
    sortable: true,
    align: 'left'
  },
  {
    name: 'transaction_type',
    label: 'نوع المعاملة',
    field: 'transaction_type',
    sortable: true,
    align: 'left'
  },
  {
    name: 'actions',
    label: 'اجراءات',
    field: 'actions',
    sortable: false,
    align: 'right'
  }
];

// Export to Excel function
const exportToExcel = () => {
  const exportData = filteredRows.value.map((row) => ({
    التسلسل: row.index,
    'رقم الجواز': row.passport_number,
    'رقم التتبع': row.unique_code,
    'الاسم الكامل': row.full_name,
    'تاريخ استلام الجواز': moment(row.passport_delivery_date).format('jYYYY/jMM/jDD'),
    'مدة المعالجة': formatProcessingTime(row.processing_time),
    'حالة الجواز': row.passport_status,
    'نوع المعاملة': row.transaction_type
  }));

  const ws = XLSXUtils.json_to_sheet(exportData, { skipHeader: false });
  const wb = XLSXUtils.book_new();
  XLSXUtils.book_append_sheet(wb, ws, 'Passports');
  writeFile(wb, `passports_${moment().format('YYYYMMDD_HHmmss')}.xlsx`);
};

// Check for exceeded processing time and show notifications
const checkExceededProcessingTime = () => {
  const exceededPassports = filteredRows.value.filter(
    (row) => row.passport_status !== 'تم تسليمه' && row.processing_exceeded
  );

  if (exceededPassports.length > 0) {
    $q.notify({
      type: 'warning',
      message: `يوجد ${exceededPassports.length} جواز تجاوز وقت المعالجة المحدد`,
      position: 'top',
      timeout: 5000
    });
  }
};

async function fetch() {
  loading.value = true;
  try {
    await passportsStore.fetch();
  } catch (error) {
    console.error('Error fetching passports:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء جلب بيانات الجوازات.'
    });
  } finally {
    loading.value = false;
  }
}

// Add this function to handle edit
// Update the handleEdit function
const handleEdit = (row: Passport) => {
  console.log('Editing passport with ID:', row.id);
  router.push({
    name: 'PassEdit',
    params: { id: String(row.id) }
  });
};

// Add this function to handle delivery
async function handleDelivery(id: number) {
  try {
    loading.value = true;
    const passport = await passportsStore.fetchById(Number(id));
    if (passport) {
      router.push({
        name: 'PassDelivery',
        params: { id: String(id) }
      });
    } else {
      $q.notify({
        type: 'warning',
        message: 'الجواز غير موجود',
        position: 'top'
      });
    }
  } catch (error) {
    console.error('Error fetching passport:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء تحميل بيانات الجواز',
      position: 'top'
    });
  } finally {
    loading.value = false;
  }
}

// Add delete handler function
const handleDelete = async (id: number) => {
  try {
    loading.value = true;
    const result = await passportsStore.destroy(id);
    if (result) {
      $q.notify({
        type: 'positive',
        message: 'تم حذف الجواز بنجاح',
        position: 'top',
        timeout: 3000
      });
      // Refresh the data after successful deletion
      await fetch();
    }
  } catch (error) {
    console.error('Error deleting passport:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء حذف الجواز',
      position: 'top',
      timeout: 3000
    });
  } finally {
    loading.value = false;
  }
};

// Add status change handler
const handleStatusChange = async (row: any, newStatus: string) => {
  console.log('Starting status change for passport ID:', row.id, 'New status:', newStatus);
  
  try {
    loading.value = true;
    
    // Create FormData object for the update
    const formData = new FormData();
    
    // Add the status with quotes to avoid SQL truncation
    formData.append('passport_status', newStatus);
    console.log('Using exact status value from enum:', newStatus);
    
    // If status is changed to "تم تسليمه", update delivery timestamp
    if (newStatus === 'تم تسليمه') {
      const timestamp = new Date().toISOString();
      formData.append('delivery_timestamp', timestamp);
      console.log('Adding delivery timestamp:', timestamp);
    }
    
    console.log('Sending update to store with data:', 
      Array.from(formData.entries()).reduce((obj, [key, value]) => {
        obj[key] = value;
        return obj;
      }, {} as Record<string, any>)
    );
    
    const result = await passportsStore.update(row.id, formData);
    console.log('Update completed successfully:', result);

    $q.notify({
      type: 'positive',
      message: 'تم تحديث حالة الجواز بنجاح',
      position: 'top'
    });

    // Send SMS notification
    if (row.phone_number) {
      try {
        const message = `عزيزي ${row.full_name}, تم تحديث حالة جواز سفرك إلى: ${newStatus}`;
        await SmsService.sendSms(row.phone_number, message, `passport_${newStatus}`, row.id, row.full_name);
        $q.notify({
          type: 'positive',
          message: 'تم إرسال إشعار SMS بنجاح',
          position: 'top'
        });
      } catch (smsError) {
        console.error('Error sending SMS:', smsError);
        $q.notify({
          type: 'negative',
          message: 'فشل إرسال إشعار SMS',
          position: 'top'
        });
      }
    }

    // Refresh the data
    await fetch();
  } catch (error: any) {
    console.error('Error updating passport status:', error);
    console.error('Error details:', error.response?.data || error.message);
    
    // More detailed error notification
    $q.notify({
      type: 'negative',
      message: `حدث خطأ أثناء تحديث حالة الجواز: ${error.response?.data?.message || error.message || 'خطأ غير معروف'}`,
      position: 'top',
      timeout: 5000
    });
  } finally {
    loading.value = false;
  }
};

// Add function to handle letter printing
function handleLetterPrint(id: number) {
  router.push({
    name: 'PassLetterPrint',
    params: { id: String(id) }
  });
}

// Pagination methods
const onRequest = async (props: any) => {
  const { page, rowsPerPage } = props.pagination;
  loading.value = true;
  
  try {
    const result = await passportsStore.fetch({
      page,
      per_page: rowsPerPage === 0 ? 'all' : rowsPerPage,
      passport_status: filterType.value !== 'all' ? getStatusValue(filterType.value) : undefined
    });
    
    pagination.value.page = result.current_page;
    pagination.value.rowsPerPage = rowsPerPage;
    pagination.value.rowsNumber = result.total;
  } catch (error) {
    console.error('Error fetching passports:', error);
  } finally {
    loading.value = false;
  }
};

const onPerPageChange = () => {
  pagination.value.page = 1;
  onRequest({ pagination: pagination.value });
};

// Watch for filter changes
watch(filterType, () => {
  pagination.value.page = 1;
  onRequest({ pagination: pagination.value });
});

const getStatusValue = (filterValue: string) => {
  const statusMap: Record<string, string> = {
    'processing': 'قيد الانجاز',
    'ready': 'جاهز للاستلام', 
    'delivered': 'تم تسليمه'
  };
  return statusMap[filterValue];
};

onMounted(() => {
  onRequest({ pagination: pagination.value });
  // Check for exceeded processing time every hour
  checkExceededProcessingTime();
  setInterval(checkExceededProcessingTime, 3600000);
});

function getStatusColor(status: string) {
  switch (status) {
    case 'تم تسليمه':
      return 'positive';
    case 'قيد الانجاز':
      return 'warning';
    case 'جاهز للاستلام':
      return 'info';
    default:
      return 'grey';
  }
}
</script>

<template>
  <q-page>
    <base-breadcrumbs />
    <q-card>
      <q-card-section class="q-px-none">
        <div class="flex q-px-lg q-pb-md items-center q-gutter-x-sm">
          <q-btn
            color="primary"
            icon="o_add"
            label="إضافة جواز جديد"
            :to="{ name: 'PassCreate' }"
          />

          <q-space />

          <!-- Search and Filter Controls -->
          <div class="row q-col-gutter-sm items-center">
            <!-- Status Filter -->
            <div class="col-auto">
              <q-select
                v-model="filterType"
                :options="statusFilters"
                outlined
                dense
                emit-value
                map-options
                label="حالة الجواز"
                style="min-width: 150px"
              />
            </div>

            <!-- Search Input -->
            <div class="col-auto">
              <q-input
                outlined
                dense
                debounce="300"
                v-model="search"
                placeholder="البحث بالاسم، رقم الجواز، أو رقم التتبع"
                style="min-width: 300px"
              >
                <template v-slot:append>
                  <q-icon name="search" />
                </template>
              </q-input>
            </div>

            <!-- Export Button -->
            <div class="col-auto">
              <q-btn
                color="green"
                icon="o_file_download"
                label="تصدير إلى Excel"
                @click="exportToExcel"
                :disable="filteredRows.length === 0"
              />
            </div>
            <!-- New button for Delivered Passports -->
            <div class="col-auto">
              <q-btn
                color="blue-grey"
                icon="o_check_circle"
                label="الجوازات المسلمة"
                :to="{ name: 'PassDeliveredIndex' }"
              />
            </div>
          </div>
        </div>

        <q-separator />

        <!-- Controls Row -->
        <div class="row items-center justify-between q-pa-md">
          <div class="row items-center q-gutter-md">
            <!-- Per Page Selector -->
            <q-select
              v-model="pagination.rowsPerPage"
              :options="perPageOptions"
              :option-label="(opt) => perPageLabels[opt]"
              label="عدد العناصر"
              dense
              outlined
              style="min-width: 120px"
              @update:model-value="onPerPageChange"
            />
          </div>
        </div>

        <!-- Table -->
        <q-table
          flat
          :loading="loading"
          :rows="passports"
          :columns="columns"
          row-key="id"
          :filter="search"
          v-model:pagination="pagination"
          @request="onRequest"
        >
          <!-- Status Cell Template -->
          <template v-slot:body-cell-passport_status="props">
            <q-td :props="props">
              <q-btn-dropdown
                :color="getStatusColor(props.value)"
                :label="props.value"
                dense
                flat
                :class="{ blink: props.row.processing_exceeded && props.value !== 'تم تسليمه' }"
              >
                <q-list>
                  <q-item
                    v-for="status in statusOptions"
                    :key="status.value"
                    clickable
                    v-close-popup
                    @click="handleStatusChange(props.row, status.value)"
                    :active="props.value === status.value"
                  >
                    <q-item-section>
                      <q-item-label>
                        <q-chip :color="status.color" text-color="white" dense class="q-px-sm">
                          {{ status.label }}
                        </q-chip>
                      </q-item-label>
                    </q-item-section>
                  </q-item>
                </q-list>
              </q-btn-dropdown>
            </q-td>
          </template>

          <!-- Processing Time Cell Template -->
          <template v-slot:body-cell-processing_time="props">
            <q-td :props="props">
              <div
                :class="{
                  'text-negative':
                    props.row.processing_exceeded && props.row.passport_status !== 'تم تسليمه',
                  'font-weight-bold':
                    props.row.processing_exceeded && props.row.passport_status !== 'تم تسليمه'
                }"
              >
                {{ props.value }}
              </div>
            </q-td>
          </template>

          <!-- Actions Cell Template -->
          <template v-slot:body-cell-actions="props">
            <q-td :props="props">
              <q-btn-group flat>
                <q-btn
                  @click="handleEdit(props.row)"
                  icon="o_edit"
                  round
                  dense
                  flat
                  color="primary"
                >
                  <q-tooltip>تعديل</q-tooltip>
                </q-btn>

                <q-btn
                  v-if="props.row.passport_status !== 'تم تسليمه'"
                  @click="handleDelivery(props.row.id)"
                  icon="o_local_shipping"
                  round
                  dense
                  flat
                  color="secondary"
                >
                  <q-tooltip>تسليم الجواز</q-tooltip>
                </q-btn>

                <q-btn
                  v-if="props.row.passport_status === 'قيد الانجاز'"
                  @click="handleLetterPrint(props.row.id)"
                  icon="o_description"
                  round
                  dense
                  flat
                  color="info"
                >
                  <q-tooltip>طباعة خطاب رسمي</q-tooltip>
                </q-btn>

                <q-btn
                  icon="o_delete"
                  @click.stop="
                    $q
                      .dialog({
                        title: 'حذف',
                        message: 'هل أنت متأكد من رغبتك في حذف هذا الجواز؟',
                        cancel: {
                          label: 'إلغاء',
                          flat: true,
                          color: 'grey'
                        },
                        ok: {
                          label: 'حذف',
                          flat: true,
                          color: 'negative'
                        },
                        persistent: true
                      })
                      .onOk(() => handleDelete(props.row.id))
                  "
                  round
                  dense
                  flat
                  color="negative"
                >
                  <q-tooltip>حذف</q-tooltip>
                </q-btn>
              </q-btn-group>
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<style lang="scss" scoped>
.blink {
  animation: blink 2s linear infinite;
}

@keyframes blink {
  0% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
  100% {
    opacity: 1;
  }
}

.q-btn-dropdown {
  .q-btn__content {
    text-align: right;
  }
}

.status-menu {
  min-width: 150px;
}
</style>
