<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useQuasar, type QTableColumn } from 'quasar';
import { useCardsStore } from '@/stores/cards';
import { useRouter } from 'vue-router';
import { Notify } from 'quasar';

const router = useRouter();
const $q = useQuasar();
const cardsStore = useCardsStore();

const loading = ref(false);
const search = ref('');
const pagination = ref({
  sortBy: 'desc',
  descending: false,
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

const columns: QTableColumn[] = [
  {
    name: 'index',
    required: true,
    label: 'التسلسل',
    align: 'center' as const,
    field: (row: any) => row.index,
    sortable: false
  },
  {
    name: 'full_name_fa',
    required: true,
    label: 'نام کامل',
    align: 'right' as const,
    field: (row: any) => row.full_name_fa,
    format: (val: string) => `${val}`,
    sortable: true
  },
  {
    name: 'full_name_en',
    required: true,
    label: 'Full Name',
    align: 'right' as const,
    field: (row: any) => row.full_name_en,
    format: (val: string) => `${val}`,
    sortable: true
  },
  {
    name: 'passport_number',
    required: true,
    label: 'شماره پاسپورت',
    align: 'right' as const,
    field: (row: any) => row.passport_number,
    format: (val: string) => `${val}`,
    sortable: true
  },
  {
    name: 'national_id',
    required: true,
    label: 'کد ملی',
    align: 'right' as const,
    field: (row: any) => row.national_id,
    format: (val: string) => `${val}`,
    sortable: true
  },
  {
    name: 'card_type',
    required: true,
    label: 'نوع کارت',
    align: 'right' as const,
    field: (row: any) => row.card_type,
    format: (val: string) => `${val}`,
    sortable: true
  },
  {
    name: 'parent_card',
    align: 'right' as const,
    label: 'کارت اصلی',
    field: (row: any) => row.parent_name || '-',
    format: (val: string) => val
  },
  {
    name: 'status',
    required: true,
    label: 'وضعیت',
    align: 'right' as const,
    field: (row: any) => row.status,
    format: (val: string) => `${val}`,
    sortable: true
  },
  {
    name: 'card_expiry_date',
    required: true,
    label: 'تاریخ انقضا',
    align: 'right' as const,
    field: (row: any) => row.card_expiry_date,
    format: (val: string) => `${val}`,
    sortable: true
  },
  {
    name: 'actions',
    required: true,
    label: 'عملیات',
    align: 'right' as const,
    field: (row: any) => row.id
  }
];

const rows = computed(() => {
  return cardsStore.cards || [];
});

async function onRequest(props: any) {
  const { page, rowsPerPage, sortBy, descending } = props.pagination;
  loading.value = true;

  try {
    await cardsStore.list(page, rowsPerPage === 0 ? 'all' : rowsPerPage);
    
    pagination.value.page = cardsStore.currentPage;
    pagination.value.rowsPerPage = rowsPerPage;
    pagination.value.rowsNumber = cardsStore.total;
    pagination.value.sortBy = sortBy;
    pagination.value.descending = descending;
  } catch (error) {
    console.error('Error loading cards:', error);
    Notify.create({
      type: 'negative',
      message: 'خطا در بارگذاری لیست کارت‌ها',
      position: 'top'
    });
  } finally {
    loading.value = false;
  }
}

const onPerPageChange = () => {
  pagination.value.page = 1;
  onRequest({ pagination: pagination.value });
};

onMounted(() => {
  cardsStore.cards = [];
  onRequest({
    pagination: pagination.value
  });
});

async function handleDelete(id: number) {
  try {
    await cardsStore.delete(id);
    Notify.create({
      type: 'positive',
      message: 'کارت با موفقیت حذف شد',
      position: 'top'
    });
  } catch (error) {
    console.error('Error deleting card:', error);
    Notify.create({
      type: 'negative',
      message: 'خطا در حذف کارت',
      position: 'top'
    });
  }
}

function handleView(id: number) {
  router.push({
    name: 'PassCardView',
    params: { id: String(id) }
  });
}

function handleRowClick(evt: Event, row: any) {
  if (evt.target) {
    const target = evt.target as HTMLElement;
    // Prevent navigation if a button inside the row was clicked
    if (target.closest('.q-btn')) {
      return;
    }
  }
  handleView(row.id);
}

function handlePrint(id: number) {
  router.push({
    name: 'PassCardPrint',
    params: { id: String(id) }
  });
}

function handleAddFamily(id: number) {
  router.push({
    name: 'PassCardFamilyAdd',
    params: { id: String(id) }
  });
}

// Add helper functions for card display
function getCardTypeColor(cardType: string): string {
  switch (cardType) {
    case 'personal':
      return 'primary';
    case 'wife':
      return 'pink-6';
    case 'son':
      return 'green-6';
    case 'daughter':
      return 'purple-6';
    default:
      return 'grey';
  }
}

function getCardTypeName(cardType: string): string {
  switch (cardType) {
    case 'personal':
      return 'شخصی';
    case 'wife':
      return 'همسر';
    case 'son':
      return 'پسر';
    case 'daughter':
      return 'دختر';
    default:
      return cardType;
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
            label="إنشاء بطاقة جديدة"
            :to="{ name: 'PassCardCreate' }"
          />

          <q-space />

          <q-input outlined dense debounce="300" v-model="search" label="البحث">
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>
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
        
        <q-table
          flat
          :loading="loading"
          :rows="rows"
          :columns="columns"
          row-key="id"
          :filter="search"
          v-model:pagination="pagination"
          @request="onRequest"
          @row-click="handleRowClick"
          class="clickable-rows"
        >
          <template v-slot:body-cell="props">
            <q-td :props="props" :class="{ 'family-member-row': props.row.is_family_member }">
              {{ props.value }}
            </q-td>
          </template>

          <template v-slot:body-cell-card_type="props">
            <q-td :props="props" :class="{ 'family-member-row': props.row.is_family_member }">
              <q-badge :color="getCardTypeColor(props.value)" class="q-pa-xs">
                {{ getCardTypeName(props.value) }}
              </q-badge>
            </q-td>
          </template>

          <template v-slot:body-cell-actions="props">
            <q-td :props="props">
              <q-btn
                @click.stop="handleEdit(props.row.id)"
                icon="o_edit"
                round
                dense
                flat
                color="primary"
                class="q-mr-sm"
              >
                <q-tooltip>تعديل</q-tooltip>
              </q-btn>
              <q-btn
                @click.stop="handlePrint(props.row.id)"
                icon="o_print"
                round
                dense
                flat
                color="secondary"
                class="q-mr-sm"
              >
                <q-tooltip>طباعة</q-tooltip>
              </q-btn>
              <q-btn
                v-if="props.row.card_type === 'personal'"
                @click.stop="handleAddFamily(props.row.id)"
                icon="o_group_add"
                round
                dense
                flat
                color="info"
                class="q-mr-sm"
              >
                <q-tooltip>إضافة عضو عائلة</q-tooltip>
              </q-btn>
              <q-btn
                icon="o_delete"
                @click.stop="handleDelete(props.row.id)"
                round
                dense
                flat
                color="negative"
              >
                <q-tooltip>حذف</q-tooltip>
              </q-btn>
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<style lang="scss" scoped>
.family-member-row {
  background-color: rgba(220, 240, 255, 0.2);
}

.clickable-rows .q-tr:hover {
  cursor: pointer;
}
</style>

