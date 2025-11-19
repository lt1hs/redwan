<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useQuasar, type QTableColumn } from 'quasar';
import { useRouter } from 'vue-router';
import moment from 'moment-jalaali';
import { utils as XLSXUtils, writeFile } from 'xlsx';
import { useUnfinishedPassportsStore, type UnfinishedPassport } from '@/stores/unfinishedPassports';

const $q = useQuasar();
const router = useRouter();
const store = useUnfinishedPassportsStore();

const loading = ref<boolean>(false);
const search = ref('');
const filterType = ref('all');
const importFile = ref<File | null>(null);

const statusFilters = [
  { label: 'الكل', value: 'all' },
  { label: 'قيد المراجعة', value: 'قيد المراجعة' },
  { label: 'جاهز للنقل', value: 'جاهز للنقل' },
  { label: 'حذف', value: 'حذف' }
];

const statusOptions = [
  { label: 'قيد المراجعة', value: 'قيد المراجعة', color: 'warning' },
  { label: 'جاهز للنقل', value: 'جاهز للنقل', color: 'positive' },
  { label: 'حذف', value: 'حذف', color: 'negative' }
];

const filteredRows = computed(() => {
  let results = (store.unfinishedPassports || []).map((row, index) => ({
    index: index + 1,
    ...row
  }));

  if (search.value) {
    const searchLower = search.value.toLowerCase();
    results = results.filter(
      (row) =>
        row.full_name?.toLowerCase().includes(searchLower) ||
        row.passport_number?.toLowerCase().includes(searchLower)
    );
  }

  if (filterType.value !== 'all') {
    results = results.filter((row) => row.completion_status === filterType.value);
  }

  return results;
});

const columns: QTableColumn[] = [
  { name: 'index', label: 'التسلسل', field: 'index', align: 'left' },
  { name: 'full_name', label: 'الاسم الكامل', field: 'full_name', sortable: true, align: 'left' },
  { name: 'passport_number', label: 'رقم الجواز', field: 'passport_number', sortable: true, align: 'left' },
  { name: 'nationality', label: 'الجنسية', field: 'nationality', sortable: true, align: 'left' },
  { name: 'mobile_number', label: 'رقم الهاتف', field: 'mobile_number', sortable: true, align: 'left' },
  { name: 'completion_status', label: 'حالة الإكمال', field: 'completion_status', sortable: true, align: 'left' },
  { name: 'updated_status', label: 'حالة التحديث', field: 'updated_status', sortable: false, align: 'center' },
  { name: 'created_at', label: 'تاريخ الإنشاء', field: 'created_at', sortable: true, align: 'left', format: (val) => moment(val).format('jYYYY/jMM/jDD') },
  { name: 'actions', label: 'اجراءات', field: 'actions', sortable: false, align: 'right' }
];

const isRecentlyUpdated = (row: any) => {
  const updatedAt = moment(row.updated_at);
  const createdAt = moment(row.created_at);
  return updatedAt.isAfter(createdAt.add(1, 'minute'));
};

const exportToExcel = () => {
  const exportData = filteredRows.value.map((row) => ({
    التسلسل: row.index,
    'الاسم الكامل': row.full_name || '',
    'رقم الجواز': row.passport_number || '',
    'الجنسية': row.nationality || '',
    'رقم الهاتف': row.mobile_number || '',
    'حالة الإكمال': row.completion_status,
    'تاريخ الإنشاء': moment(row.created_at).format('jYYYY/jMM/jDD')
  }));

  const ws = XLSXUtils.json_to_sheet(exportData, { skipHeader: false });
  const wb = XLSXUtils.book_new();
  XLSXUtils.book_append_sheet(wb, ws, 'UnfinishedPassports');
  writeFile(wb, `unfinished_passports_${moment().format('YYYYMMDD_HHmmss')}.xlsx`);
};

async function fetch() {
  loading.value = true;
  try {
    await store.fetch();
  } catch (error) {
    console.error('Error fetching unfinished passports:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء جلب بيانات الجوازات غير المكتملة.'
    });
  } finally {
    loading.value = false;
  }
}

const handleEdit = (row: UnfinishedPassport) => {
  router.push({
    name: 'UnfinishedPassportEdit',
    params: { id: String(row.id) }
  });
};

const handleDelete = async (id: number) => {
  try {
    loading.value = true;
    await store.destroy(id);
    $q.notify({
      type: 'positive',
      message: 'تم حذف الجواز غير المكتمل بنجاح',
      position: 'top'
    });
  } catch (error) {
    console.error('Error deleting unfinished passport:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء حذف الجواز غير المكتمل',
      position: 'top'
    });
  } finally {
    loading.value = false;
  }
};

const handleStatusChange = async (row: any, newStatus: string) => {
  try {
    loading.value = true;
    const formData = new FormData();
    formData.append('completion_status', newStatus);
    await store.update(row.id, formData);
    $q.notify({
      type: 'positive',
      message: 'تم تحديث الحالة بنجاح',
      position: 'top'
    });
  } catch (error) {
    console.error('Error updating status:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء تحديث الحالة',
      position: 'top'
    });
  } finally {
    loading.value = false;
  }
};

const handleConvert = async (id: number) => {
  try {
    loading.value = true;
    await store.convertToPassport(id);
    $q.notify({
      type: 'positive',
      message: 'تم نقل الجواز إلى قاعدة البيانات الرئيسية بنجاح',
      position: 'top'
    });
  } catch (error: any) {
    console.error('Error converting passport:', error);
    $q.notify({
      type: 'negative',
      message: error.response?.data?.error || 'حدث خطأ أثناء نقل الجواز',
      position: 'top'
    });
  } finally {
    loading.value = false;
  }
};

const handleImport = async (file: File) => {
  try {
    loading.value = true;
    console.log('Starting import with file:', file.name);
    await store.importExcel(file);
    $q.notify({
      type: 'positive',
      message: 'تم استيراد الملف بنجاح',
      position: 'top'
    });
  } catch (error: any) {
    console.error('Error importing file:', error);
    let errorMessage = 'حدث خطأ أثناء استيراد الملف';
    
    if (error.response?.data?.error) {
      errorMessage = error.response.data.error;
    } else if (error.message === 'Network Error') {
      errorMessage = 'خطأ في الشبكة - تأكد من الاتصال بالخادم';
    }
    
    $q.notify({
      type: 'negative',
      message: errorMessage,
      position: 'top'
    });
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetch();
});

function getStatusColor(status: string) {
  switch (status) {
    case 'جاهز للنقل': return 'positive';
    case 'قيد المراجعة': return 'warning';
    case 'حذف': return 'negative';
    default: return 'grey';
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
            label="إضافة جواز غير مكتمل"
            :to="{ name: 'UnfinishedPassportCreate' }"
          />

          <q-btn
            color="secondary"
            icon="o_upload"
            label="استيراد من Excel"
          >
            <q-popup-proxy>
              <q-card style="min-width: 300px">
                <q-card-section>
                  <div class="text-h6">استيراد من ملف Excel</div>
                  <div class="text-caption text-grey-6 q-mt-sm">
                    ترتيب الأعمدة: التسلسل، سیده، الاسم الكامل، رقم الجواز، الجنسية، تاريخ الميلاد، رقم الهاتف، تاريخ الانتهاء، كد ناجا، المحافظة، العنوان
                  </div>
                </q-card-section>
                <q-card-section>
                  <q-file
                    v-model="importFile"
                    label="اختر ملف Excel"
                    accept=".xlsx,.xls,.csv"
                    outlined
                    @update:model-value="(file) => file && handleImport(file)"
                  >
                    <template v-slot:prepend>
                      <q-icon name="attach_file" />
                    </template>
                  </q-file>
                </q-card-section>
              </q-card>
            </q-popup-proxy>
          </q-btn>

          <q-space />

          <div class="row q-col-gutter-sm items-center">
            <div class="col-auto">
              <q-select
                v-model="filterType"
                :options="statusFilters"
                outlined
                dense
                emit-value
                map-options
                label="حالة الإكمال"
                style="min-width: 150px"
              />
            </div>

            <div class="col-auto">
              <q-input
                outlined
                dense
                debounce="300"
                v-model="search"
                placeholder="البحث بالاسم أو رقم الجواز"
                style="min-width: 300px"
              >
                <template v-slot:append>
                  <q-icon name="search" />
                </template>
              </q-input>
            </div>

            <div class="col-auto">
              <q-btn
                color="green"
                icon="o_file_download"
                label="تصدير إلى Excel"
                @click="exportToExcel"
                :disable="filteredRows.length === 0"
              />
            </div>
          </div>
        </div>

        <q-separator />

        <q-table
          flat
          :loading="loading"
          :rows="filteredRows"
          :columns="columns"
          row-key="id"
          :rows-per-page-options="[0]"
          hide-pagination
        >
          <template v-slot:body-cell-completion_status="props">
            <q-td :props="props">
              <q-btn-dropdown
                :color="getStatusColor(props.value)"
                :label="props.value"
                dense
                flat
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

          <template v-slot:body-cell-updated_status="props">
            <q-td :props="props">
              <q-chip
                v-if="isRecentlyUpdated(props.row)"
                color="info"
                text-color="white"
                dense
                icon="update"
                label="محدث"
              />
            </q-td>
          </template>

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
                  v-if="props.row.completion_status === 'جاهز للنقل'"
                  @click="handleConvert(props.row.id)"
                  icon="o_send"
                  round
                  dense
                  flat
                  color="positive"
                >
                  <q-tooltip>نقل إلى الجوازات الرئيسية</q-tooltip>
                </q-btn>

                <q-btn
                  icon="o_delete"
                  @click.stop="
                    $q
                      .dialog({
                        title: 'حذف',
                        message: 'هل أنت متأكد من رغبتك في حذف هذا الجواز غير المكتمل؟',
                        cancel: { label: 'إلغاء', flat: true, color: 'grey' },
                        ok: { label: 'حذف', flat: true, color: 'negative' },
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
.q-btn-dropdown {
  .q-btn__content {
    text-align: right;
  }
}
</style>
