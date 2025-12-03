<template>
  <q-page>
    <base-breadcrumbs />
    <q-card>
      <q-card-section class="q-px-none">
        <div class="flex q-px-lg q-pb-md items-center q-gutter-x-sm">
          <q-btn
            color="primary"
            icon="o_refresh"
            label="تحديث"
            @click="refreshData"
          />

          <q-btn
            color="secondary"
            icon="o_download"
            label="تصدير Excel"
            @click="exportToExcel"
          />

          <q-space />

          <q-input
            v-model="search"
            placeholder="البحث بالاسم أو الهاتف..."
            dense
            outlined
            class="q-ml-sm"
            style="min-width: 250px"
            @input="onSearch"
            clearable
          >
            <template v-slot:prepend>
              <q-icon name="search" />
            </template>
          </q-input>

          <q-select
            v-model="pagination.rowsPerPage"
            :options="perPageOptions"
            :option-label="(opt) => perPageLabels[opt]"
            dense
            outlined
            style="min-width: 100px"
            @update:model-value="onPerPageChange"
          />
        </div>

        <q-table
          :rows="filteredUsers"
          :columns="columns"
          :loading="allUsersStore.loading"
          :pagination="pagination"
          @request="onRequest"
          row-key="id"
          flat
          class="full-width"
          :rows-per-page-options="[]"
        >
          <template v-slot:body-cell-index="props">
            <q-td :props="props" class="text-center">
              <span class="text-weight-medium">{{ props.row.index }}</span>
            </q-td>
          </template>

          <template v-slot:body-cell-name="props">
            <q-td :props="props">
              <div class="text-weight-medium">{{ props.row.name }}</div>
            </q-td>
          </template>

          <template v-slot:body-cell-phone="props">
            <q-td :props="props" class="text-center">
              <div class="text-weight-medium" dir="ltr">{{ props.row.phone }}</div>
            </q-td>
          </template>

          <template v-slot:body-cell-source="props">
            <q-td :props="props" class="text-center">
              <q-chip
                :color="getSourceColor(props.row.source)"
                text-color="white"
                dense
                class="q-ma-none"
                :icon="getSourceIcon(props.row.source)"
              >
                {{ getSourceLabel(props.row.source) }}
              </q-chip>
            </q-td>
          </template>

          <template v-slot:body-cell-created_at="props">
            <q-td :props="props" class="text-center">
              <div class="text-caption text-grey-7">{{ formatDate(props.row.created_at) }}</div>
            </q-td>
          </template>

          <template v-slot:no-data>
            <div class="full-width row flex-center q-gutter-sm text-grey-7">
              <q-icon size="2em" name="o_search_off" />
              <span>لا توجد بيانات للعرض</span>
            </div>
          </template>
        </q-table>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useAllUsersStore } from '@/stores/allUsers';
import { date, useQuasar } from 'quasar';
import { utils as XLSXUtils, writeFile } from 'xlsx';
import BaseBreadcrumbs from '@/components/BaseBreadcrumbs.vue';

const $q = useQuasar();
const allUsersStore = useAllUsersStore();
const search = ref('');

const perPageOptions = [10, 50, 100, 0];

const perPageLabels = {
  10: '10',
  50: '50', 
  100: '100',
  0: 'الكل'
};

const pagination = computed({
  get: () => allUsersStore.pagination,
  set: (val) => {
    allUsersStore.pagination = val;
  }
});

const filteredUsers = computed(() => {
  if (!search.value) {
    return allUsersStore.users;
  }
  
  const searchTerm = search.value.toLowerCase();
  return allUsersStore.users.filter(user => 
    user.name?.toLowerCase().includes(searchTerm) ||
    user.phone?.toLowerCase().includes(searchTerm)
  );
});

const columns = [
  {
    name: 'index',
    label: '#',
    field: 'index',
    align: 'center',
    style: 'width: 60px'
  },
  {
    name: 'name',
    label: 'الاسم الكامل',
    field: 'name',
    align: 'right',
    sortable: true,
    style: 'min-width: 200px'
  },
  {
    name: 'phone',
    label: 'رقم الهاتف',
    field: 'phone',
    align: 'center',
    sortable: true,
    style: 'width: 150px'
  },
  {
    name: 'source',
    label: 'المصدر',
    field: 'source',
    align: 'center',
    style: 'width: 120px'
  },
  {
    name: 'created_at',
    label: 'تاريخ الإنشاء',
    field: 'created_at',
    align: 'center',
    sortable: true,
    style: 'width: 150px'
  }
];

const getSourceColor = (source: string) => {
  const colors = {
    passport: 'blue',
    card: 'green',
    unfinished_passport: 'orange',
    contract: 'purple'
  };
  return colors[source] || 'grey';
};

const getSourceIcon = (source: string) => {
  const icons = {
    passport: 'o_flight_takeoff',
    card: 'o_credit_card',
    unfinished_passport: 'o_pending',
    contract: 'o_description'
  };
  return icons[source] || 'o_help';
};

const getSourceLabel = (source: string) => {
  const labels = {
    passport: 'جواز سفر',
    card: 'بطاقة',
    unfinished_passport: 'جواز غير مكتمل',
    contract: 'عقد'
  };
  return labels[source] || source;
};

const formatDate = (dateString: string) => {
  return date.formatDate(dateString, 'YYYY/MM/DD');
};

const onRequest = async (props) => {
  const { page, rowsPerPage } = props.pagination;
  
  await allUsersStore.fetchUsers({
    page,
    per_page: rowsPerPage === 0 ? 'all' : rowsPerPage
  });
};

const onPerPageChange = () => {
  pagination.value.page = 1;
  onRequest({ pagination: pagination.value });
};

const onSearch = () => {
  // Search is handled by computed filteredUsers
};

const refreshData = async () => {
  await onRequest({ pagination: pagination.value });
  $q.notify({
    type: 'positive',
    message: 'تم تحديث البيانات بنجاح',
    position: 'top'
  });
};

const exportToExcel = () => {
  const data = allUsersStore.users.map((user, index) => ({
    '#': index + 1,
    'الاسم': user.name,
    'رقم الهاتف': user.phone,
    'المصدر': getSourceLabel(user.source),
    'تاريخ الإنشاء': formatDate(user.created_at)
  }));

  const ws = XLSXUtils.json_to_sheet(data);
  const wb = XLSXUtils.book_new();
  XLSXUtils.book_append_sheet(wb, ws, 'جميع المستخدمين');
  
  writeFile(wb, `جميع_المستخدمين_${date.formatDate(new Date(), 'YYYY-MM-DD')}.xlsx`);
  
  $q.notify({
    type: 'positive',
    message: 'تم تصدير البيانات بنجاح',
    position: 'top'
  });
};

onMounted(() => {
  onRequest({ pagination: pagination.value });
});
</script>
