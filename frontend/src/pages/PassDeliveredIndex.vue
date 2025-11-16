<template>
  <q-page>
    <base-breadcrumbs />
    <q-card>
      <q-card-section class="q-px-none">
        <div class="flex q-px-lg q-pb-md items-center q-gutter-x-sm">
          <div class="text-h6">الجوازات المسلمة</div>
          <q-space />
          <div class="row q-col-gutter-sm items-center">
            <!-- Search Input -->
            <div class="col-auto">
              <q-input
                outlined
                dense
                debounce="300"
                v-model="filter"
                placeholder="البحث برقم الجواز، الاسم الكامل، أو اسم المستلم"
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
                :disable="deliveredPassports.length === 0"
              />
            </div>
          </div>
        </div>
        <q-separator />
        <q-table
          flat
          :rows="deliveredPassports"
          :columns="columns"
          row-key="id"
          :loading="loading"
          :filter="filter"
          :rows-per-page-options="[0]"
          hide-pagination
        >
          <template v-slot:body-cell-actions="props">
            <q-td :props="props">
              <q-btn
                icon="info"
                flat
                round
                dense
                @click="viewDetails(props.row)"
              >
                <q-tooltip>عرض التفاصيل</q-tooltip>
              </q-btn>
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { usePassportsStore } from 'src/stores/passports';
import { useQuasar } from 'quasar';
import type { QTableColumn } from 'quasar'; // Import QTableColumn as a type
import { useRouter } from 'vue-router';
import BaseBreadcrumbs from '@/components/BaseBreadcrumbs.vue'; // Import BaseBreadcrumbs
import { utils as XLSXUtils, writeFile } from 'xlsx'; // Import for Excel export
import PassportDetailsDialog from '@/components/PassportDetailsDialog.vue'; // Import the new dialog component

const passportsStore = usePassportsStore();
const $q = useQuasar();
const router = useRouter();

const deliveredPassports = ref([]);
const loading = ref(false);
const filter = ref('');

import moment from 'moment-jalaali'; // Import moment-jalaali

const columns: QTableColumn[] = [
  { name: 'passport_number', align: 'left', label: 'رقم الجواز', field: 'passport_number', sortable: true },
  { name: 'full_name', align: 'left', label: 'الاسم الكامل', field: 'full_name', sortable: true },
  { name: 'recipient_name', align: 'left', label: 'اسم المستلم', field: 'recipient_name', sortable: true },
  { name: 'recipient_phone', align: 'left', label: 'هاتف المستلم', field: 'recipient_phone', sortable: true },
  { name: 'passport_delivery_date', align: 'left', label: 'تاريخ التسليم', field: 'passport_delivery_date', sortable: true, format: (val) => moment(val).format('jYYYY/jMM/jDD HH:mm') },
  { name: 'delivered_by', align: 'left', label: 'تم التسليم بواسطة', field: 'delivered_by', sortable: true },
  { name: 'actions', label: 'إجراءات', field: 'actions', align: 'center' },
];

// Export to Excel function (similar to PassIndex.vue)
const exportToExcel = () => {
  const exportData = deliveredPassports.value.map((row: any) => ({
    'رقم الجواز': row.passport_number,
    'الاسم الكامل': row.full_name,
    'اسم المستلم': row.recipient_name,
    'هاتف المستلم': row.recipient_phone,
    'تاريخ التسليم': moment(row.passport_delivery_date).format('jYYYY/jMM/jDD HH:mm'),
    'تم التسليم بواسطة': row.delivered_by,
    'حالة الجواز': row.passport_status,
    'نوع المعاملة': row.transaction_type,
    'حالة الدفع': row.payment_status,
    'العنوان': row.address,
    'الرمز البريدي': row.zipcode,
    'الرمز الفريد': row.unique_code,
  }));

  const ws = XLSXUtils.json_to_sheet(exportData, { skipHeader: false });
  const wb = XLSXUtils.book_new();
  XLSXUtils.book_append_sheet(wb, ws, 'الجوازات المسلمة');
  writeFile(wb, `الجوازات_المسلمة_${moment().format('YYYYMMDD_HHmmss')}.xlsx`);
};

onMounted(async () => {
  await fetchDeliveredPassports();
});

async function fetchDeliveredPassports() {
  loading.value = true;
  try {
    // Assuming the store has a method to fetch passports by status
    // The fetch method in src/stores/passports.ts now accepts parameters
    const response = await passportsStore.fetch({ passport_status: 'تم تسليمه' });
    deliveredPassports.value = response.data; // Adjust based on actual API response structure
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'فشل جلب الجوازات المسلمة.',
    });
    console.error('Error fetching delivered passports:', error);
  } finally {
    loading.value = false;
  }
}

function viewDetails(passport: any) {
  $q.dialog({
    component: PassportDetailsDialog,
    componentProps: {
      passport: passport,
    },
  });
}
</script>

<style scoped>
/* Add any specific styles for this page here */
</style>
