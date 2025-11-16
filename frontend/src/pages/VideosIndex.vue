<script setup lang="ts">
import { ref, computed } from 'vue';
import { useQuasar, type QTableColumn } from 'quasar';
import { useCategoriesStore } from '../stores/categories';
import { useVideosStore } from '../stores/videos';

const $q = useQuasar();
const categoriesStore = useCategoriesStore();
categoriesStore.fetchCategories('videos');

const videos = useVideosStore();
const rows = computed(() => {
  const results = videos.videos.map((row, index) => ({
    index: index + 1,
    ...row
  }));

  if (filter.value.categories.length) {
    return results.filter((e: any) =>
      e.categories.some((x: any) => filter.value.categories.includes(x.id))
    );
  }
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
    label: 'التاريخ',
    name: 'date',
    field: 'date',

    sortable: true,
    align: 'left'
  },

  {
    label: 'التصانيف',
    name: 'categories',
    field: (row: any) => row.categories.map((value: any) => value.name).join(', '),

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

const filter = ref({ q: '', categories: [] as Array<number> });

const loading = ref(true);

function fetch() {
  loading.value = true;
  videos.fetch();
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
          <q-btn color="primary" icon="o_add" label="خبر جديد" :to="{ name: 'VideosCreate' }" />

          <q-space />

          <q-select
            dense
            outlined
            label="عرض التصانيف"
            multiple
            v-model="filter.categories"
            style="max-width: 100%; width: 250px"
            :options="categoriesStore.categories['videos']"
            emit-value
            map-options
            option-value="id"
            option-label="name"
            clearable
          >
            <template v-slot:prepend>
              <q-icon style="" name="o_category" />
            </template>
          </q-select>
          <q-input outlined dense debounce="300" v-model="filter.q" label="البحث">
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
          :filter="filter.q"
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
                  name: 'VideosEdit',
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
                    await videos.destroy(props.row.id);
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
