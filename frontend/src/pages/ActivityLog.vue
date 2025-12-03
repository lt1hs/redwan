<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useQuasar } from 'quasar';
import { useActivityLogStore } from '@/stores/activityLog';
import moment from 'moment-hijri';
import ActivityReports from '@/components/ActivityReports.vue';
import type { ActivityLog, User, ActivityLogFilter } from '@/types/activity';

const $q = useQuasar();
const activityLogStore = useActivityLogStore();

const loading = ref(false);
const activities = ref<ActivityLog[]>([]);
const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 0
});

const filter = ref<ActivityLogFilter>({
  dateRange: {
    from: moment().subtract(7, 'days').format('YYYY-MM-DD'),
    to: moment().format('YYYY-MM-DD')
  },
  user: null,
  action: null,
  module: null
});

const users = ref<User[]>([]);
const actions = ref<ActivityLog['action'][]>(['create', 'update', 'delete', 'login', 'logout']);
const modules = ref(['contracts', 'users', 'roles', 'permissions', 'system']);

const displayDateRange = computed({
  get: () => {
    const from = filter.value.dateRange.from;
    const to = filter.value.dateRange.to;
    if (from && to) {
      return `${from} - ${to}`;
    }
    return '';
  },
  set: (val: string) => {
    // The q-date component directly updates filter.dateRange,
    // so direct typing into this input is not the primary interaction.
    // This setter can be a no-op for this specific use case.
  }
});

const columns = [
  {
    name: 'created_at',
    label: 'التاريخ والوقت',
    field: 'created_at',
    sortable: true,
    align: 'right' as const
  },
  {
    name: 'user',
    label: 'المستخدم',
    field: (row: ActivityLog) => row.user?.name,
    sortable: true,
    align: 'right' as const
  },
  {
    name: 'action',
    label: 'الإجراء',
    field: 'action',
    sortable: true,
    align: 'right' as const
  },
  {
    name: 'module',
    label: 'الوحدة',
    field: 'module',
    sortable: true,
    align: 'right' as const
  },
  {
    name: 'description',
    label: 'الوصف',
    field: 'description',
    sortable: false,
    align: 'right' as const
  }
];

const formattedActivities = computed(() => {
  if (!Array.isArray(activities.value)) {
    return [];
  }
  return activities.value.map((activity) => ({
    ...activity,
    created_at: formatDateTime(activity.created_at),
    action_formatted: formatAction(activity.action)
  }));
});

function formatDateTime(dateTime: string): string {
  return moment(dateTime).format('YYYY-MM-DD HH:mm:ss');
}

function formatAction(action: ActivityLog['action']): string {
  const actionMap = {
    create: 'إنشاء',
    update: 'تحديث',
    delete: 'حذف',
    login: 'تسجيل دخول',
    logout: 'تسجيل خروج'
  };
  return actionMap[action] || action;
}

function getActionColor(action: ActivityLog['action']): string {
  const colorMap = {
    create: 'positive',
    update: 'info',
    delete: 'negative',
    login: 'primary',
    logout: 'grey-7'
  };
  return colorMap[action] || 'grey';
}

async function fetchActivities() {
  loading.value = true;
  try {
    const response = await activityLogStore.fetch({
      page: pagination.value.page,
      per_page: pagination.value.rowsPerPage,
      ...filter.value
    });

    activities.value = Array.isArray(response.data) ? response.data : [];

    if (response.meta) {
      pagination.value.rowsNumber = response.meta.total;
    } else {
      pagination.value.rowsNumber = activities.value.length;
    }
  } catch (error) {
    console.error('Error fetching activities:', error);
    activities.value = []; // Ensure it's always an array
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء تحميل سجل النشاط'
    });
  } finally {
    loading.value = false;
  }
}

async function fetchUsers() {
  try {
    const response = await activityLogStore.fetchUsers();
    users.value = response;
  } catch (error) {
    console.error('Error fetching users:', error);
  }
}

interface RequestProps {
  pagination: {
    page: number;
    rowsPerPage: number;
  };
}

function onRequest(props: RequestProps) {
  const { page, rowsPerPage } = props.pagination;
  pagination.value.page = page;
  pagination.value.rowsPerPage = rowsPerPage;
  fetchActivities();
}

function applyFilter() {
  pagination.value.page = 1;
  fetchActivities();
}

function resetFilter() {
  filter.value = {
    dateRange: {
      from: moment().subtract(7, 'days').format('YYYY-MM-DD'),
      to: moment().format('YYYY-MM-DD')
    },
    user: null,
    action: null,
    module: null
  };
  applyFilter();
}

async function exportActivities() {
  loading.value = true;
  try {
    await activityLogStore.exportLogs(filter.value);
    $q.notify({
      type: 'positive',
      message: 'تم تصدير سجل النشاط بنجاح'
    });
  } catch (error) {
    console.error('Error exporting activities:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء تصدير سجل النشاط'
    });
  } finally {
    loading.value = false;
  }
}

const activeTab = ref('activity');

onMounted(() => {
  fetchActivities();
  fetchUsers();
});
</script>

<template>
  <q-page class="q-pa-md">
    <div class="text-h5 q-mb-md">سجل نشاط النظام</div>

    <q-tabs
      v-model="activeTab"
      dense
      class="text-grey"
      active-color="primary"
      indicator-color="primary"
      align="left"
      narrow-indicator
    >
      <q-tab name="activity" label="سجل النشاط" />
      <q-tab name="reports" label="التقارير" />
    </q-tabs>

    <q-separator />

    <q-tab-panels v-model="activeTab" animated>
      <q-tab-panel name="activity">
        <!-- Filter Section -->
        <q-card flat bordered class="q-mb-md">
          <q-card-section>
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-4">
                <q-input
                  outlined
                  dense
                  v-model="displayDateRange"
                  type="text"
                  label="نطاق التاريخ"
                  stack-label
                >
                  <template v-slot:append>
                    <q-icon name="event" class="cursor-pointer">
                      <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                        <q-date v-model="filter.dateRange" range mask="YYYY-MM-DD">
                          <div class="row items-center justify-end q-gutter-sm">
                            <q-btn label="تطبيق" color="primary" flat v-close-popup />
                          </div>
                        </q-date>
                      </q-popup-proxy>
                    </q-icon>
                  </template>
                </q-input>
              </div>

              <div class="col-12 col-md-2">
                <q-select
                  outlined
                  dense
                  v-model="filter.user"
                  :options="users"
                  option-value="id"
                  option-label="name"
                  label="المستخدم"
                  clearable
                  emit-value
                  map-options
                  stack-label
                />
              </div>

              <div class="col-12 col-md-2">
                <q-select
                  outlined
                  dense
                  v-model="filter.action"
                  :options="actions"
                  label="الإجراء"
                  clearable
                  stack-label
                />
              </div>

              <div class="col-12 col-md-2">
                <q-select
                  outlined
                  dense
                  v-model="filter.module"
                  :options="modules"
                  label="الوحدة"
                  clearable
                  stack-label
                />
              </div>

              <div class="col-12 col-md-2 flex items-end">
                <div class="row q-col-gutter-sm">
                  <div class="col">
                    <q-btn
                      color="primary"
                      icon="search"
                      label="بحث"
                      no-caps
                      unelevated
                      @click="applyFilter"
                      class="full-width"
                    />
                  </div>
                  <div class="col">
                    <q-btn
                      color="secondary"
                      icon="refresh"
                      label="إعادة تعيين"
                      no-caps
                      unelevated
                      @click="resetFilter"
                      class="full-width"
                    />
                  </div>
                </div>
              </div>
            </div>
          </q-card-section>
        </q-card>

        <!-- Table Section -->
        <q-card flat bordered>
          <q-card-section>
            <div class="row items-center justify-between q-mb-sm">
              <div class="text-subtitle1">نتائج البحث</div>
              <q-btn
                color="primary"
                icon="download"
                label="تصدير"
                no-caps
                unelevated
                @click="exportActivities"
                :loading="loading"
              />
            </div>

            <q-table
              :rows="formattedActivities"
              :columns="columns"
              :loading="loading"
              row-key="id"
              :pagination="pagination"
              @request="onRequest"
              binary-state-sort
              :rows-per-page-options="[10, 25, 50, 100]"
            >
              <template v-slot:body-cell-action="props">
                <q-td :props="props">
                  <q-chip :color="getActionColor(props.row.action)" text-color="white" size="sm">
                    {{ props.row.action_formatted }}
                  </q-chip>
                </q-td>
              </template>
            </q-table>
          </q-card-section>
        </q-card>
      </q-tab-panel>

      <q-tab-panel name="reports">
        <activity-reports />
      </q-tab-panel>
    </q-tab-panels>
  </q-page>
</template>

<style lang="scss" scoped>
.q-table {
  direction: rtl;
}
</style>
