<script setup lang="ts">
import { ref, computed } from 'vue';
import { useQuasar, type QTableColumn } from 'quasar';
import { usePagesStore } from '@/stores/pages';

const $q = useQuasar();

const pages = usePagesStore();
const rows = computed(() => {
  const results = pages.pages.map((row, index) => ({
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
    name: 'actions',
    label: 'اجراءات',
    field: 'actions',
    sortable: false,
    align: 'right'
  }
];

const search = ref('');

const loading = ref(true);

function fetch() {
  loading.value = true;
  pages.fetch();
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
          <q-btn color="primary" icon="o_add" label="صفحة جديدة" :to="{ name: 'PagesCreate' }" />

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
                :to="{
                  name: 'PagesEdit',
                  params: { id: props.row.id }
                }"
                icon="o_edit"
                round
                dense
                flat
              />
              <q-btn
                icon="o_delete"
                @click.stop="
                  $q.dialog({
                    title: 'حذف',
                    message: 'هل أنت متأكد من رغبتك في الحذف؟',
                    cancel: true,
                    persistent: false
                  }).onOk(async () => {
                    await pages.destroy(props.row.id);
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
