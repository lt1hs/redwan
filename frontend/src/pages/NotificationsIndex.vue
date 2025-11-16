<script setup lang="ts">
import { ref, computed } from 'vue';
import { useQuasar, type QTableColumn } from 'quasar';
import { useNotificationsStore } from '@/stores/notifications';
import BaseImagePreviewDialog from '@/components/BaseImagePreviewDialog.vue';

const $q = useQuasar();

const notifications = useNotificationsStore();
const rows = computed(() => {
  const results = notifications.notifications.map((row, index) => ({
    index: index + 1,
    ...row
  }));

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
    label: 'العنوان',
    name: 'title',
    field: 'title',
    sortable: true,
    align: 'left'
  },
  {
    label: 'النص',
    name: 'body',
    field: 'body',
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

const loading = ref(true);
const search = ref(null);

async function fetch() {
  loading.value = true;
  notifications.fetch();
  loading.value = false;
}
fetch();

async function confirmDelete(id: number) {
  $q.dialog({
    title: 'حذف',
    message: 'هل أنت متأكد من رغبتك في الحذف؟',
    cancel: true,
    persistent: false
  }).onOk(async () => {
    await notifications.destroy(id);
    await fetch();
  });
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
            label="اشعار جديد"
            :to="{ name: 'NotificationsCreate' }"
          />

          <q-space />

          <q-input outlined dense debounce="300" v-model="search" label="البحث">
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
        <q-separator />
        <q-table
          flat
          :loading="loading"
          :rows="rows"
          :columns="columns"
          row-key="id"
          :filter="search"
          :rows-per-page-options="[0]"
          hide-pagination
        >
          <template v-slot:body-cell-date="props">
            <q-td :props="props" dir="ltr">
              {{ props.value }}
            </q-td>
          </template>
          <template v-slot:body-cell-actions="props">
            <q-td :props="props">
              <q-btn
                @click="
                  $q.dialog({
                    component: BaseImagePreviewDialog,
                    componentProps: {
                      image: props.row.image
                    }
                  })
                "
                icon="o_image"
                round
                dense
                flat
                v-if="props.row.image"
              />
              <q-btn icon="o_delete" @click.stop="confirmDelete(props.row.id)" round dense flat />
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>
  </q-page>
</template>
