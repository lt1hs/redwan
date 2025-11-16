<script setup lang="ts">
import { ref, computed } from 'vue';
import { useQuasar } from 'quasar';
import { usePassportsStore } from '@/stores/passports';
import moment from 'moment-hijri';

const $q = useQuasar();
const passportsStore = usePassportsStore();

const loading = ref(false);
const reportType = ref('pending_passports');
const dateRange = ref({
  from: moment().startOf('month').format('YYYY-MM-DD'),
  to: moment().format('YYYY-MM-DD')
});
const reportData = ref([]);

const reportTypes = [
  { label: 'الجوازات قيد المعالجة', value: 'pending_passports' },
  { label: 'الجوازات المسلمة', value: 'delivered_passports' },
  { label: 'حالة الدفع للمستخدمين', value: 'payment_status' },
  { label: 'التنبيهات حسب الحالة', value: 'alerts_by_status' }
];

const columns = computed(() => {
  switch (reportType.value) {
    case 'pending_passports':
      return [
        { name: 'passport_number', label: 'رقم الجواز', field: 'passport_number', sortable: true },
        { name: 'full_name', label: 'الاسم الكامل', field: 'full_name', sortable: true },
        {
          name: 'processing_time',
          label: 'مدة المعالجة',
          field: 'processing_time',
          sortable: true
        },
        { name: 'status', label: 'الحالة', field: 'passport_status', sortable: true }
      ];
    case 'delivered_passports':
      return [
        { name: 'passport_number', label: 'رقم الجواز', field: 'passport_number', sortable: true },
        { name: 'full_name', label: 'الاسم الكامل', field: 'full_name', sortable: true },
        {
          name: 'delivery_date',
          label: 'تاريخ التسليم',
          field: 'delivery_timestamp',
          sortable: true
        },
        { name: 'delivered_by', label: 'تم التسليم بواسطة', field: 'delivered_by', sortable: true }
      ];
    case 'payment_status':
      return [
        { name: 'full_name', label: 'اسم المستخدم', field: 'full_name', sortable: true },
        { name: 'passport_number', label: 'رقم الجواز', field: 'passport_number', sortable: true },
        { name: 'payment_status', label: 'حالة الدفع', field: 'payment_status', sortable: true },
        { name: 'amount', label: 'المبلغ', field: 'amount', sortable: true }
      ];
    case 'alerts_by_status':
      return [
        { name: 'alert_date', label: 'تاريخ التنبيه', field: 'created_at', sortable: true },
        { name: 'alert_type', label: 'نوع التنبيه', field: 'alert_type', sortable: true },
        { name: 'status', label: 'الحالة', field: 'status', sortable: true },
        { name: 'description', label: 'الوصف', field: 'description', sortable: true }
      ];
    default:
      return [];
  }
});

async function generateReport() {
  loading.value = true;
  try {
    const params = {
      type: reportType.value,
      from_date: dateRange.value.from,
      to_date: dateRange.value.to
    };

    const response = await passportsStore.generateReport(params);
    reportData.value = response.data;

    $q.notify({
      type: 'positive',
      message: 'تم إنشاء التقرير بنجاح'
    });
  } catch (error) {
    console.error('Error generating report:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء إنشاء التقرير'
    });
  } finally {
    loading.value = false;
  }
}

async function exportReport() {
  loading.value = true;
  try {
    const params = {
      type: reportType.value,
      from_date: dateRange.value.from,
      to_date: dateRange.value.to,
      export: true
    };

    await passportsStore.exportReport(params);

    $q.notify({
      type: 'positive',
      message: 'تم تصدير التقرير بنجاح'
    });
  } catch (error) {
    console.error('Error exporting report:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء تصدير التقرير'
    });
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div class="activity-reports">
    <!-- Report Controls -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section>
        <div class="row q-col-gutter-md">
          <div class="col-12 col-md-4">
            <q-select
              outlined
              dense
              v-model="reportType"
              :options="reportTypes"
              option-label="label"
              option-value="value"
              label="نوع التقرير"
              stack-label
            />
          </div>

          <div class="col-12 col-md-4">
            <q-input outlined dense v-model="dateRange" label="نطاق التاريخ" stack-label>
              <template v-slot:append>
                <q-icon name="event" class="cursor-pointer">
                  <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                    <q-date v-model="dateRange" range mask="YYYY-MM-DD">
                      <div class="row items-center justify-end q-gutter-sm">
                        <q-btn label="تطبيق" color="primary" flat v-close-popup />
                      </div>
                    </q-date>
                  </q-popup-proxy>
                </q-icon>
              </template>
            </q-input>
          </div>

          <div class="col-12 col-md-4 flex items-end">
            <div class="row q-col-gutter-sm">
              <div class="col">
                <q-btn
                  color="primary"
                  icon="analytics"
                  label="إنشاء التقرير"
                  no-caps
                  unelevated
                  @click="generateReport"
                  :loading="loading"
                  class="full-width"
                />
              </div>
              <div class="col">
                <q-btn
                  color="secondary"
                  icon="download"
                  label="تصدير"
                  no-caps
                  unelevated
                  @click="exportReport"
                  :loading="loading"
                  :disable="!reportData.length"
                  class="full-width"
                />
              </div>
            </div>
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Report Results -->
    <q-card flat bordered>
      <q-card-section>
        <q-table
          :rows="reportData"
          :columns="columns"
          :loading="loading"
          row-key="id"
          binary-state-sort
          :rows-per-page-options="[10, 25, 50, 100]"
        >
          <!-- Custom cell renderers -->
          <template v-slot:body-cell-payment_status="props">
            <q-td :props="props">
              <q-chip
                :color="props.value === 'paid' ? 'positive' : 'negative'"
                text-color="white"
                size="sm"
              >
                {{ props.value === 'paid' ? 'تم الدفع' : 'لم يتم الدفع' }}
              </q-chip>
            </q-td>
          </template>

          <template v-slot:body-cell-status="props">
            <q-td :props="props">
              <q-chip
                :color="props.value === 'قيد المعالجة' ? 'warning' : 'positive'"
                text-color="white"
                size="sm"
              >
                {{ props.value }}
              </q-chip>
            </q-td>
          </template>

          <template v-slot:body-cell-delivery_date="props">
            <q-td :props="props">
              {{ props.value ? moment(props.value).format('YYYY-MM-DD HH:mm') : '-' }}
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>
  </div>
</template>

<style lang="scss" scoped>
.activity-reports {
  .q-table {
    direction: rtl;
  }
}
</style>
