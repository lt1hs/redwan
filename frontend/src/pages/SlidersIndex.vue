<script setup lang="ts">
import { ref, computed } from 'vue';
import { useQuasar, type QTableColumn } from 'quasar';
import { useSlidersStore } from '../stores/sliders';

const $q = useQuasar();
const sliders = useSlidersStore();

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
    name: 'actions',
    label: 'اجراءات',
    field: 'actions',
    sortable: false,
    align: 'right'
  }
];

const loading = ref(true);
const rows = computed(() =>
  sliders.sliders.map((row: any, index: number) => ({
    index: index + 1,
    ...row
  }))
);

function confirmDelete(id: number) {
  $q.dialog({
    title: 'حذف',
    message: 'هل أنت متأكد من رغبتك في الحذف؟',
    cancel: true,
    persistent: false
  }).onOk(async () => {
    await sliders.destroy(id);
    await fetch();
  });
}

async function fetch() {
  loading.value = true;
  sliders.fetch();
  loading.value = false;
}
fetch();
</script>
<template>
  <q-page>
    <base-breadcrumbs />
    <q-card>
      <q-card-section class="q-px-none">
        <div class="flex q-px-lg q-pb-md items-center q-gutter-x-sm">
          <q-btn color="primary" icon="o_add" label="سلايدر جديد" :to="{ name: 'SlidersCreate' }" />

          <q-space />
        </div>
        <q-separator />
        <q-table
          flat
          :loading="loading"
          :rows="rows"
          :columns="columns"
          row-key="id"
          :rows-per-page-options="[0]"
        >
          <template v-slot:body-cell-actions="props">
            <q-td :props="props">
              <q-btn
                :to="{
                  name: 'SlidersEdit',
                  params: { id: props.row.id }
                }"
                icon="o_edit"
                round
                dense
                flat
              />
              <q-btn icon="o_delete" @click.stop="confirmDelete(props.row.id)" round dense flat />
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>
  </q-page>
</template>
