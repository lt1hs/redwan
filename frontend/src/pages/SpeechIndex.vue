<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useQuasar, type QTableColumn } from 'quasar';
import { useSpeechStore } from '@/stores/speech';
import { useRouter } from 'vue-router';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

const router = useRouter();
const $q = useQuasar();
const speechStore = useSpeechStore();

const rows = computed(() => {
  return speechStore.speeches.map((row, index) => ({
    index: index + 1,
    ...row
  }));
});

const columns: QTableColumn[] = [
  {
    name: 'index',
    label: 'التسلسل',
    field: 'index',
    align: 'right'
  },
  {
    label: 'العنوان',
    name: 'title',
    field: 'title',
    sortable: true,
    align: 'right'
  },
  {
    label: 'المستلم',
    name: 'recipient',
    field: 'recipient',
    sortable: true,
    align: 'right'
  },
  {
    label: 'حجم الورق',
    name: 'paper_size',
    field: 'paper_size',
    sortable: true,
    align: 'right'
  },
  {
    label: 'تاريخ الإنشاء',
    name: 'created_at',
    field: 'created_at',
    sortable: true,
    align: 'right'
  },
  {
    name: 'actions',
    label: 'اجراءات',
    field: 'actions',
    sortable: false,
    align: 'left'
  }
];

const search = ref('');
const loading = ref(true);
const pagination = ref({
  sortBy: 'created_at',
  descending: true,
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10
});

async function fetch() {
  loading.value = true;
  try {
    await speechStore.fetch();
  } catch (error) {
    console.error('Error fetching speeches:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء جلب بيانات الخطابات.'
    });
  } finally {
    loading.value = false;
  }
}

function handleEdit(id: number) {
  router.push({
    name: 'SpeechEdit',
    params: { id: String(id) }
  });
}

function handlePrint(id: number) {
  router.push({
    name: 'SpeechPrint',
    params: { id: String(id) }
  });
}

onMounted(() => {
  fetch();
});
</script>

<template>
  <q-page>
    <base-breadcrumbs />
    <div class="q-pa-md">
      <q-card flat bordered>
        <q-card-section class="q-px-lg">
          <div class="row items-center justify-between q-mb-md">
            <div class="text-h6 text-primary">قائمة الخطابات</div>
            <q-btn
              color="primary"
              icon="o_add"
              label="إنشاء خطاب جديد"
              :to="{ name: 'SpeechCreate' }"
            />
          </div>

          <div class="row items-center q-mb-md">
            <q-input
              outlined
              dense
              debounce="300"
              v-model="search"
              placeholder="البحث في الخطابات"
              class="col-12 col-md-4"
            >
              <template v-slot:append>
                <q-icon name="search" />
              </template>
            </q-input>
          </div>

          <q-table
            flat
            :loading="loading"
            :rows="rows"
            :columns="columns"
            row-key="id"
            :filter="search"
            v-model:pagination="pagination"
            :rows-per-page-options="[10, 25, 50, 100]"
          >
            <template v-slot:body-cell-paper_size="props">
              <q-td :props="props">
                <q-chip
                  :color="props.row.paper_size === 'A4' ? 'primary' : 'secondary'"
                  text-color="white"
                  size="sm"
                >
                  {{ props.row.paper_size }}
                </q-chip>
              </q-td>
            </template>

            <template v-slot:body-cell-actions="props">
              <q-td :props="props" class="q-gutter-x-sm">
                <q-btn
                  @click="handleEdit(props.row.id)"
                  icon="o_edit"
                  round
                  dense
                  flat
                  color="primary"
                >
                  <q-tooltip>تعديل</q-tooltip>
                </q-btn>
                <q-btn
                  @click="handlePrint(props.row.id)"
                  icon="o_print"
                  round
                  dense
                  flat
                  color="secondary"
                >
                  <q-tooltip>طباعة</q-tooltip>
                </q-btn>
                <q-btn
                  icon="o_delete"
                  @click.stop="
                    $q
                      .dialog({
                        title: 'تأكيد الحذف',
                        message: 'هل أنت متأكد من رغبتك في حذف هذا الخطاب؟',
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
                      .onOk(async () => {
                        try {
                          await speechStore.delete(props.row.id);
                          $q.notify({
                            type: 'positive',
                            message: 'تم حذف الخطاب بنجاح.'
                          });
                          fetch();
                        } catch (error) {
                          $q.notify({
                            type: 'negative',
                            message: 'حدث خطأ أثناء حذف الخطاب.'
                          });
                        }
                      })
                  "
                  round
                  dense
                  flat
                  color="negative"
                >
                  <q-tooltip>حذف</q-tooltip>
                </q-btn>
              </q-td>
            </template>

            <template v-slot:loading>
              <q-inner-loading showing color="primary">
                <q-spinner-dots size="50px" color="primary" />
              </q-inner-loading>
            </template>

            <template v-slot:no-data>
              <div class="full-width row flex-center q-pa-md text-grey-7">لا توجد خطابات متاحة</div>
            </template>
          </q-table>
        </q-card-section>
      </q-card>
    </div>
  </q-page>
</template>

<style lang="scss" scoped>
.q-table {
  direction: rtl;
}
</style>
