<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useQuasar, type QTableColumn } from 'quasar';
import { useContractsStore } from '@/stores/contracts';
import { useRouter } from 'vue-router';

const router = useRouter();
const $q = useQuasar();
const contractsStore = useContractsStore();

const rows = computed(() => {
  return contractsStore.contracts.map((row, index) => ({
    index: index + 1,
    ...row
  }));
});

const columns: QTableColumn[] = [
  {
    name: 'index',
    label: 'التسلسل',
    field: 'index',
    align: 'left'
  },
  {
    label: 'اسم الزوج',
    name: 'husband_name',
    field: 'husband_name',
    sortable: true,
    align: 'left'
  },
  {
    label: 'اسم الزوجة',
    name: 'wife_name',
    field: 'wife_name',
    sortable: true,
    align: 'left'
  },
  {
    label: 'تاريخ العقد',
    name: 'contract_date',
    field: 'contract_date',
    sortable: true,
    align: 'left'
  },
  {
    label: 'رقم العقد',
    name: 'contract_number',
    field: 'contract_number',
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

async function fetch() {
  loading.value = true;
  try {
    await contractsStore.fetch();
  } catch (error) {
    console.error('Error fetching contracts:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء جلب بيانات العقود.'
    });
  } finally {
    loading.value = false;
  }
}

function handleEdit(id: number) {
  router.push({
    name: 'ContractEdit',
    params: { id: String(id) }
  });
}

function handlePrint(id: number) {
  router.push({
    name: 'ContractPrint',
    params: { id: String(id) }
  });
}

onMounted(() => {
  fetch();
});
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
            label="إنشاء عقد جديد"
            :to="{ name: 'ContractCreate' }"
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
          <template v-slot:body-cell-actions="props">
            <q-td :props="props">
              <q-btn
                @click="handleEdit(props.row.id)"
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
                @click="handlePrint(props.row.id)"
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
                icon="o_delete"
                @click.stop="
                  $q.dialog({
                    title: 'حذف',
                    message: 'هل أنت متأكد من رغبتك في حذف هذا العقد؟',
                    cancel: {
                      label: 'إلغاء',
                      flat: true,
                      color: 'grey'
                    },
                    ok: {
                      label: 'حذف',
                      flat: true,
                      color: 'negative'
                    },
                    persistent: true
                  }).onOk(async () => {
                    try {
                      await contractsStore.destroy(props.row.id);
                      $q.notify({
                        type: 'positive',
                        message: 'تم حذف العقد بنجاح.'
                      });
                      fetch();
                    } catch (error) {
                      $q.notify({
                        type: 'negative',
                        message: 'حدث خطأ أثناء حذف العقد.'
                      });
                    }
                  })
                "
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