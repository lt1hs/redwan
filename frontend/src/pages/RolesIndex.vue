<script setup lang="ts">
import { ref } from 'vue';
import { useQuasar, type QTableColumn } from 'quasar';
import { useRolesStore } from '../stores/roles';

const $q = useQuasar();
const roles = useRolesStore();
const columns: QTableColumn[] = [
  {
    label: 'الاسم',
    name: 'name',
    field: 'name',
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

async function fetch() {
  loading.value = true;
  roles.fetch();
  loading.value = false;
}
fetch();
</script>
<template>
  <q-page>
    <base-breadcrumbs />

    <q-card>
      <q-card-section class="q-px-none">
        <div class="flex q-px-lg q-pb-md items-center justify-between">
          <div class="text-h6">قائمة الأدوار</div>

          <q-btn color="primary" :to="{ name: 'RolesCreate' }" label="اضافة دور" />
        </div>
        <q-separator />
        <q-table
          flat
          :loading="loading"
          :rows="roles.roles"
          :columns="columns"
          row-key="id"
          binary-state-sort
          :rows-per-page-options="[0]"
          hide-pagination
        >
          <template v-slot:body-cell-actions="props">
            <q-td :props="props">
              <q-btn
                :to="{ name: 'RolesEdit', params: { id: props.row.id } }"
                :disable="props.row.name === 'Super-Admin'"
                icon="o_edit"
                round
                dense
                flat
              />
              <q-btn
                :disable="props.row.name === 'Super-Admin'"
                icon="o_delete"
                @click.stop="
                  $q.dialog({
                    title: 'حذف',
                    message: 'هل أنت متأكد من رغبتك في الحذف؟',
                    cancel: true,
                    persistent: false
                  }).onOk(async () => {
                    await roles.destroy(props.row.id);
                    fetch();
                  })
                "
                round
                dense
                flat
              />
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>
  </q-page>
</template>
