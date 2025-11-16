<template>
  <q-page class="q-pa-md">
    <div class="text-h5 q-mb-md">سجل الإشعارات</div>

    <q-card>
      <q-card-section>
        <q-table
          :rows="notifications"
          :columns="columns"
          row-key="id"
          :loading="loading"
          :rows-per-page-options="[10, 20, 50]"
          binary-state-sort
          v-model:pagination="pagination"
          class="rtl-table"
        >
          <template #top>
            <div class="row full-width justify-between q-pa-sm">
              <q-input v-model="search" placeholder="بحث..." dense outlined class="col-4">
                <template #append>
                  <q-icon name="search" />
                </template>
              </q-input>

              <div class="row q-gutter-sm">
                <q-select
                  v-model="filters.type"
                  :options="notificationTypes"
                  label="نوع الإشعار"
                  outlined
                  dense
                  clearable
                  emit-value
                  map-options
                />
                <q-select
                  v-model="filters.status"
                  :options="notificationStatuses"
                  label="الحالة"
                  outlined
                  dense
                  clearable
                  emit-value
                  map-options
                />
                <q-btn color="primary" icon="filter_list" label="تصفية" @click="applyFilters" />
              </div>
            </div>
          </template>

          <template #body-cell-status="props">
            <q-td :props="props">
              <q-chip :color="getStatusColor(props.value)" text-color="white" dense>
                {{ getStatusLabel(props.value) }}
              </q-chip>
            </q-td>
          </template>

          <template #body-cell-type="props">
            <q-td :props="props">
              <q-chip :color="getTypeColor(props.value)" text-color="white" dense>
                {{ getTypeLabel(props.value) }}
              </q-chip>
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useQuasar } from 'quasar';

const $q = useQuasar();

const loading = ref(false);
const search = ref('');
const notifications = ref([]);

const pagination = ref({
  sortBy: 'created_at',
  descending: true,
  page: 1,
  rowsPerPage: 10
});

const filters = ref({
  type: null,
  status: null
});

const columns = [
  {
    name: 'title',
    required: true,
    label: 'العنوان',
    align: 'right',
    field: 'title',
    sortable: true
  },
  {
    name: 'message',
    required: true,
    label: 'الرسالة',
    align: 'right',
    field: 'message',
    sortable: true
  },
  {
    name: 'type',
    required: true,
    label: 'النوع',
    align: 'right',
    field: 'type',
    sortable: true
  },
  {
    name: 'status',
    required: true,
    label: 'الحالة',
    align: 'right',
    field: 'status',
    sortable: true
  },
  {
    name: 'sent_at',
    required: true,
    label: 'تاريخ الإرسال',
    align: 'right',
    field: 'sent_at',
    sortable: true
  },
  {
    name: 'read_at',
    required: true,
    label: 'تاريخ القراءة',
    align: 'right',
    field: 'read_at',
    sortable: true
  }
];

const notificationTypes = [
  { label: 'تنبيه', value: 'alert' },
  { label: 'تحذير', value: 'warning' },
  { label: 'معلومة', value: 'info' }
];

const notificationStatuses = [
  { label: 'تم الإرسال', value: 'sent' },
  { label: 'تم التسليم', value: 'delivered' },
  { label: 'تم القراءة', value: 'read' },
  { label: 'فشل', value: 'failed' }
];

const getStatusColor = (status: string) => {
  const colors = {
    sent: 'blue',
    delivered: 'orange',
    read: 'green',
    failed: 'red'
  };
  return colors[status] || 'grey';
};

const getStatusLabel = (status: string) => {
  const labels = {
    sent: 'تم الإرسال',
    delivered: 'تم التسليم',
    read: 'تم القراءة',
    failed: 'فشل'
  };
  return labels[status] || status;
};

const getTypeColor = (type: string) => {
  const colors = {
    alert: 'red',
    warning: 'orange',
    info: 'blue'
  };
  return colors[type] || 'grey';
};

const getTypeLabel = (type: string) => {
  const labels = {
    alert: 'تنبيه',
    warning: 'تحذير',
    info: 'معلومة'
  };
  return labels[type] || type;
};

const fetchNotifications = async () => {
  try {
    loading.value = true;
    // TODO: Implement API call to fetch notifications history
    notifications.value = [];
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: 'حدث خطأ أثناء جلب البيانات',
      icon: 'error'
    });
  } finally {
    loading.value = false;
  }
};

const applyFilters = () => {
  fetchNotifications();
};

onMounted(() => {
  fetchNotifications();
});
</script>

<style lang="scss" scoped>
.rtl-table {
  direction: rtl;
}
</style>
