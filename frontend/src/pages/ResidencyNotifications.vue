<template>
  <q-page class="q-pa-md">
    <div class="text-h5 q-mb-md">إشعارات الإقامة</div>

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

              <q-btn color="primary" icon="add" label="إضافة إشعار" @click="openDialog()" />
            </div>
          </template>

          <template #body-cell-actions="props">
            <q-td :props="props" class="text-center">
              <q-btn-group flat>
                <q-btn
                  flat
                  round
                  color="primary"
                  icon="edit"
                  @click="editNotification(props.row)"
                />
                <q-btn
                  flat
                  round
                  color="negative"
                  icon="delete"
                  @click="confirmDelete(props.row)"
                />
              </q-btn-group>
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>

    <q-dialog v-model="showDialog" persistent>
      <q-card style="min-width: 500px">
        <q-card-section class="row items-center">
          <div class="text-h6">{{ editMode ? 'تعديل إشعار' : 'إضافة إشعار جديد' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-form @submit="saveNotification" class="q-gutter-md">
            <q-input
              v-model="form.title"
              label="عنوان الإشعار"
              :rules="[(val) => !!val || 'هذا الحقل مطلوب']"
              outlined
            />

            <q-input
              v-model="form.message"
              label="نص الإشعار"
              type="textarea"
              :rules="[(val) => !!val || 'هذا الحقل مطلوب']"
              outlined
            />

            <q-select
              v-model="form.type"
              :options="notificationTypes"
              label="نوع الإشعار"
              :rules="[(val) => !!val || 'هذا الحقل مطلوب']"
              outlined
            />

            <div class="row justify-end q-gutter-sm">
              <q-btn label="إلغاء" color="grey" v-close-popup />
              <q-btn label="حفظ" type="submit" color="primary" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <q-dialog v-model="showDeleteDialog">
      <q-card>
        <q-card-section class="row items-center">
          <q-avatar icon="warning" color="negative" text-color="white" />
          <span class="q-mr-sm">هل أنت متأكد من حذف هذا الإشعار؟</span>
        </q-card-section>

        <q-card-actions align="left">
          <q-btn flat label="إلغاء" color="grey" v-close-popup />
          <q-btn flat label="حذف" color="negative" @click="deleteNotification" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useQuasar } from 'quasar';

const $q = useQuasar();

const loading = ref(false);
const search = ref('');
const showDialog = ref(false);
const showDeleteDialog = ref(false);
const editMode = ref(false);
const selectedNotification = ref(null);

const pagination = ref({
  sortBy: 'created_at',
  descending: true,
  page: 1,
  rowsPerPage: 10
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
    name: 'created_at',
    required: true,
    label: 'تاريخ الإنشاء',
    align: 'right',
    field: 'created_at',
    sortable: true
  },
  {
    name: 'actions',
    required: true,
    label: 'الإجراءات',
    align: 'center',
    field: 'actions'
  }
];

const notifications = ref([]);
const notificationTypes = [
  { label: 'تنبيه', value: 'alert' },
  { label: 'تحذير', value: 'warning' },
  { label: 'معلومة', value: 'info' }
];

const form = ref({
  title: '',
  message: '',
  type: null
});

const resetForm = () => {
  form.value = {
    title: '',
    message: '',
    type: null
  };
};

const openDialog = () => {
  editMode.value = false;
  resetForm();
  showDialog.value = true;
};

const editNotification = (notification: any) => {
  editMode.value = true;
  selectedNotification.value = notification;
  form.value = { ...notification };
  showDialog.value = true;
};

const confirmDelete = (notification: any) => {
  selectedNotification.value = notification;
  showDeleteDialog.value = true;
};

const fetchNotifications = async () => {
  try {
    loading.value = true;
    // TODO: Implement API call to fetch notifications
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

const saveNotification = async () => {
  try {
    // TODO: Implement API call to save notification
    await fetchNotifications();
    showDialog.value = false;
    $q.notify({
      color: 'positive',
      message: editMode.value ? 'تم تحديث الإشعار بنجاح' : 'تم إضافة الإشعار بنجاح',
      icon: 'check'
    });
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: 'حدث خطأ أثناء حفظ البيانات',
      icon: 'error'
    });
  }
};

const deleteNotification = async () => {
  try {
    // TODO: Implement API call to delete notification
    await fetchNotifications();
    $q.notify({
      color: 'positive',
      message: 'تم حذف الإشعار بنجاح',
      icon: 'check'
    });
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: 'حدث خطأ أثناء حذف الإشعار',
      icon: 'error'
    });
  }
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
