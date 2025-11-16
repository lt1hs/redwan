<template>
  <q-page>
    <div class="q-pa-md">
      <q-card flat bordered>
        <q-card-section class="q-px-lg">
          <div class="text-h6 text-primary q-mb-md">قوالب الإشعارات</div>

          <div class="row q-col-gutter-md">
            <!-- Template List -->
            <div class="col-12">
              <q-table
                :rows="templates"
                :columns="columns"
                row-key="id"
                :loading="loading"
                flat
                bordered
              >
                <!-- Actions Column -->
                <template v-slot:body-cell-actions="props">
                  <q-td :props="props" class="q-gutter-sm">
                    <q-btn
                      flat
                      round
                      color="primary"
                      icon="o_edit"
                      size="sm"
                      @click="editTemplate(props.row)"
                    />
                    <q-btn
                      flat
                      round
                      color="negative"
                      icon="o_delete"
                      size="sm"
                      @click="confirmDelete(props.row)"
                    />
                  </q-td>
                </template>

                <!-- Type Column -->
                <template v-slot:body-cell-type="props">
                  <q-td :props="props">
                    <q-chip color="primary" text-color="white" size="sm" square>
                      {{ getTypeLabel(props.value) }}
                    </q-chip>
                  </q-td>
                </template>

                <!-- Loading -->
                <template v-slot:loading>
                  <q-inner-loading showing color="primary">
                    <q-spinner-dots size="50px" color="primary" />
                  </q-inner-loading>
                </template>

                <!-- No Data -->
                <template v-slot:no-data>
                  <div class="full-width row flex-center q-pa-md text-grey-7">
                    لا توجد قوالب إشعارات
                  </div>
                </template>
              </q-table>
            </div>
          </div>

          <!-- Add Template Button -->
          <div class="row justify-end q-mt-md">
            <q-btn
              color="primary"
              icon="o_add"
              label="إضافة قالب جديد"
              @click="showTemplateDialog()"
            />
          </div>
        </q-card-section>
      </q-card>
    </div>

    <!-- Template Dialog -->
    <q-dialog v-model="templateDialog" persistent>
      <q-card style="min-width: 500px">
        <q-card-section>
          <div class="text-h6">{{ editingTemplate ? 'تعديل قالب' : 'إضافة قالب جديد' }}</div>
        </q-card-section>

        <q-card-section>
          <div class="row q-col-gutter-md">
            <div class="col-12">
              <q-select
                v-model="currentTemplate.type"
                :options="notificationTypes"
                label="نوع الإشعار"
                outlined
                emit-value
                map-options
                :rules="[(val) => !!val || 'هذا الحقل مطلوب']"
              />
            </div>

            <div class="col-12">
              <q-input
                v-model="currentTemplate.title"
                label="عنوان القالب"
                outlined
                :rules="[(val) => !!val || 'هذا الحقل مطلوب']"
              />
            </div>

            <div class="col-12">
              <q-input
                v-model="currentTemplate.content"
                label="محتوى الرسالة"
                type="textarea"
                outlined
                :rules="[(val) => !!val || 'هذا الحقل مطلوب']"
              >
                <template v-slot:hint>
                  يمكنك استخدام المتغيرات التالية: {name}, {days}, {expiry_date}
                </template>
              </q-input>
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="إلغاء" color="negative" v-close-popup />
          <q-btn flat label="حفظ" color="primary" @click="saveTemplate" :loading="saving" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Delete Confirmation Dialog -->
    <q-dialog v-model="deleteDialog" persistent>
      <q-card>
        <q-card-section class="row items-center">
          <q-avatar icon="warning" color="negative" text-color="white" />
          <span class="q-mr-sm">هل أنت متأكد من حذف هذا القالب؟</span>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="إلغاء" color="primary" v-close-popup />
          <q-btn flat label="حذف" color="negative" @click="deleteTemplate" :loading="deleting" />
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
const saving = ref(false);
const deleting = ref(false);
const templateDialog = ref(false);
const deleteDialog = ref(false);
const editingTemplate = ref(false);

const templates = ref([]);
const currentTemplate = ref({
  id: null,
  type: '',
  title: '',
  content: ''
});

const columns = [
  {
    name: 'title',
    required: true,
    label: 'عنوان القالب',
    align: 'right',
    field: 'title',
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
    name: 'content',
    required: true,
    label: 'محتوى الرسالة',
    align: 'right',
    field: 'content'
  },
  {
    name: 'actions',
    required: true,
    label: 'الإجراءات',
    align: 'center',
    field: 'actions'
  }
];

const notificationTypes = [
  { label: 'شهر قبل الانتهاء', value: 'ONE_MONTH' },
  { label: 'أسبوعين قبل الانتهاء', value: 'TWO_WEEKS' },
  { label: 'أسبوع قبل الانتهاء', value: 'ONE_WEEK' }
];

function getTypeLabel(type: string): string {
  const found = notificationTypes.find((t) => t.value === type);
  return found ? found.label : type;
}

function showTemplateDialog(template = null) {
  if (template) {
    currentTemplate.value = { ...template };
    editingTemplate.value = true;
  } else {
    currentTemplate.value = {
      id: null,
      type: '',
      title: '',
      content: ''
    };
    editingTemplate.value = false;
  }
  templateDialog.value = true;
}

function editTemplate(template) {
  showTemplateDialog(template);
}

function confirmDelete(template) {
  currentTemplate.value = template;
  deleteDialog.value = true;
}

async function fetchTemplates() {
  loading.value = true;
  try {
    // Implement fetch templates logic here
    await new Promise((resolve) => setTimeout(resolve, 1000)); // Simulated API call
    templates.value = [
      {
        id: 1,
        type: 'ONE_MONTH',
        title: 'تنبيه انتهاء الإقامة - شهر',
        content: 'عزيزي {name}، نود إخبارك بأن إقامتك ستنتهي بعد {days} يوم في تاريخ {expiry_date}'
      }
    ];
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء جلب القوالب'
    });
  } finally {
    loading.value = false;
  }
}

async function saveTemplate() {
  saving.value = true;
  try {
    // Implement save template logic here
    await new Promise((resolve) => setTimeout(resolve, 1000)); // Simulated API call
    $q.notify({
      type: 'positive',
      message: `تم ${editingTemplate.value ? 'تعديل' : 'إضافة'} القالب بنجاح`
    });
    templateDialog.value = false;
    await fetchTemplates();
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء حفظ القالب'
    });
  } finally {
    saving.value = false;
  }
}

async function deleteTemplate() {
  deleting.value = true;
  try {
    // Implement delete template logic here
    await new Promise((resolve) => setTimeout(resolve, 1000)); // Simulated API call
    $q.notify({
      type: 'positive',
      message: 'تم حذف القالب بنجاح'
    });
    deleteDialog.value = false;
    await fetchTemplates();
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء حذف القالب'
    });
  } finally {
    deleting.value = false;
  }
}

onMounted(() => {
  fetchTemplates();
});
</script>

<style lang="scss" scoped>
.q-table {
  direction: rtl;
}
</style>
