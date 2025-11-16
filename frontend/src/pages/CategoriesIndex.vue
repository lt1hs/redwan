<script setup lang="ts">
import { useSortable } from '@vueuse/integrations/useSortable';
import { moveArrayElement } from '@vueuse/integrations/useSortable';
import { api } from '../utils/axios';
import { ref, computed, onMounted, nextTick } from 'vue';
import { useQuasar } from 'quasar';
import { useRoute } from 'vue-router';

const $q = useQuasar();
const route = useRoute();

const apiUrl = route.meta.apiUrl as string;

const categories = ref<any>([]);

const rows = computed(() => {
  const computedCategories = [...categories.value];

  categories.value
    .filter((e: any) => e.parent_id)
    .forEach((e: any) => {
      const parent = categories.value.find((x: any) => x.id == e.parent_id);
      if (!parent) {
        // Ignore if parent doesn't exists
        return;
      }
      if (!('children' in parent)) parent.children = [];
      parent.children.push(e);
      computedCategories.splice(computedCategories.indexOf(e), 1);
    });

  let level = 0;
  computedCategories.forEach((e) => (e.level = level));

  while (computedCategories.some((e) => 'children' in e)) {
    level++;
    computedCategories
      .filter((e) => 'children' in e)
      .forEach((e) => {
        const items = e.children;
        items.forEach((e: any) => (e.level = level));
        computedCategories.splice(computedCategories.indexOf(e) + 1, 0, ...items);
        delete e.children;
      });
  }

  return computedCategories;
});
const loading = ref(true);
const submitLoading = ref(false);
onMounted(() => {
  api.get(apiUrl).then((response) => {
    categories.value = response.data;
    loading.value = false;
  });
});
const createForm = ref({
  name: null,
  slug: null,
  parent_id: null,
  description: null
});

useSortable('.q-virtual-scroll__content', categories, {
  handle: '.handle',
  animation: 200,

  // Look at: https://github.com/SortableJS/Sortable/issues/546
  onEnd: (e: any) => {
    e.item.remove();
    if (e.oldIndex !== undefined) {
      e.from.insertBefore(e.item, e.from.children[e.oldIndex]);
    }
  },

  onUpdate: (e: any) => {
    // moveArrayElement(categories.value, e.oldIndex, e.newIndex);
    const array = [...rows.value];
    moveArrayElement(array, e.oldIndex, e.newIndex);

    // nextTick required here as moveArrayElement is executed in a microtas
    // so we need to wait until the next tick until that is finished.
    nextTick(() => {
      // categories.value = array;
      api
        .post(apiUrl + '/set-order', {
          ids: array.map((value) => value.id)
        })
        .then((response) => {
          $q.notify({
            type: 'positive',
            message: 'تم تعديل الترتيب'
          });
          categories.value = response.data;
        });
    });
  }
});

const editDialog = ref(false);
const editForm = ref<any>({});
</script>

<template>
  <q-page>
    <base-breadcrumbs />

    <q-dialog v-model="editDialog" :position="'right'" maximized>
      <q-card style="width: 350px" class="column">
        <q-form
          @submit="
            (evt: any) => {
              submitLoading = true;

              api
                .put(apiUrl + '/' + editForm.id, editForm)
                .then((response) => {
                  $q.notify({
                    type: 'positive',
                    message: 'تم ادراج المعلومات بشكل صحيح'
                  });
                  categories = response.data;
                  submitLoading = false;
                  evt.target.reset();
                })
                .catch((error) => {
                  submitLoading = false;
                  $q.notify({
                    type: 'negative',
                    message: error?.response?.data?.message
                  });
                });
            }
          "
        >
          <q-card-section>
            <div>
              <label class="label" for="name"> الاسم </label>
              <q-input
                for="name"
                class="q-mt-xs"
                hide-bottom-space
                v-model="editForm.name"
                outlined
                dense
                bottom-slots
                lazy-rules
                :rules="[(val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة']"
              >
              </q-input>
            </div>

            <div class="q-mt-md">
              <label class="label" for="slug"> Slug </label>
              <q-input
                v-model="editForm.slug"
                dir="ltr"
                for="slug"
                class="q-mt-xs"
                hide-bottom-space
                outlined
                dense
                bottom-slots
              />
            </div>

            <div class="q-mt-md">
              <label class="label" for="parent_id"> التصنيف الأب </label>
              <q-select
                for="parent_id"
                class="q-mt-xs"
                hide-bottom-space
                v-model="editForm.parent_id"
                :options="
                  categories.map((value: any) => ({
                    label: value.name,
                    value: value.id
                  }))
                "
                emit-value
                map-options
                outlined
                dense
                bottom-slots
                clearable
              >
              </q-select>
            </div>
            <div class="q-mt-md">
              <label class="label" for="description"> الوصف </label>
              <q-input
                for="description"
                class="q-mt-xs"
                hide-bottom-space
                v-model="editForm.description"
                outlined
                dense
                bottom-slots
                type="textarea"
                input-style="height: 150px"
              >
              </q-input>
            </div>
          </q-card-section>

          <q-space />
          <q-card-actions align="right">
            <q-btn :loading="submitLoading" type="submit" v-close-popup color="primary">حفظ</q-btn>
            <q-btn color="negative" v-close-popup>الغاء</q-btn>
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>
    <div class="row items-start q-col-gutter-md">
      <div class="col-12 col-md-4">
        <q-form
          @submit="
            (evt: any) => {
              submitLoading = true;

              api
                .post(apiUrl, createForm)
                .then((response) => {
                  $q.notify({
                    type: 'positive',
                    message: 'تم ادراج المعلومات بشكل صحيح'
                  });
                  categories = response.data;
                  submitLoading = false;
                  evt.target?.reset();
                })
                .catch((error) => {
                  submitLoading = false;
                  $q.notify({
                    type: 'negative',
                    message: error?.response?.data?.message
                  });
                });
            }
          "
          @reset="
            createForm.name = null;
            createForm.slug = null;
            createForm.parent_id = null;
            createForm.description = null;
          "
        >
          <q-card>
            <q-card-section>
              <div>
                <label class="label" for="name"> الاسم </label>
                <q-input
                  for="name"
                  class="q-mt-xs"
                  hide-bottom-space
                  v-model="createForm.name"
                  outlined
                  dense
                  bottom-slots
                  lazy-rules
                  :rules="[(val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة']"
                >
                </q-input>
              </div>

              <div class="q-mt-md">
                <label class="label" for="slug"> Slug </label>
                <q-input
                  v-model="createForm.slug"
                  dir="ltr"
                  for="slug"
                  class="q-mt-xs"
                  hide-bottom-space
                  outlined
                  dense
                  bottom-slots
                />
              </div>

              <div class="q-mt-md">
                <label class="label" for="parent_id"> التصنيف الأب </label>
                <q-select
                  for="parent_id"
                  class="q-mt-xs"
                  hide-bottom-space
                  v-model="createForm.parent_id"
                  :options="
                    categories.map((value: any) => ({
                      label: value.name,
                      value: value.id
                    }))
                  "
                  emit-value
                  map-options
                  outlined
                  dense
                  bottom-slots
                  clearable
                >
                </q-select>
              </div>
              <div class="q-mt-md">
                <label class="label" for="description"> الوصف </label>
                <q-input
                  for="description"
                  class="q-mt-xs"
                  hide-bottom-space
                  v-model="createForm.description"
                  outlined
                  dense
                  bottom-slots
                  type="textarea"
                  input-style="height: 150px"
                >
                </q-input>
              </div>
              <div class="q-mt-md flex items-center justify-end">
                <q-btn :loading="submitLoading" label="اضافة" type="submit" color="primary" />
              </div>
            </q-card-section>
          </q-card>
        </q-form>
      </div>

      <div class="col-12 col-md-8">
        <q-card>
          <q-card-section>
            <q-table
              flat
              virtual-scroll
              :rows="rows"
              :columns="[
                // {
                //   name: 'index',
                //   field: 'index',
                //   label: 'ت',
                //   align: 'left',
                //   sortable: true,
                // },
                {
                  name: 'name',
                  field: 'name',
                  label: 'الاسم',
                  align: 'left'
                },
                {
                  name: 'description',
                  field: 'description',
                  label: 'الوصف',
                  align: 'left'
                },
                {
                  name: 'contents_count',
                  field: 'contents_count',
                  label: 'عدد المحتويات',
                  align: 'right'
                },
                {
                  name: 'actions',
                  field: 'actions',
                  label: 'اجراءات',
                  align: 'right'
                }
              ]"
              row-key="id"
              binary-state-sort
              :rows-per-page-options="[0]"
              hide-pagination
              :loading="loading"
              wrap-cells
            >
              <template v-slot:body-cell-name="props">
                <q-td :props="props">
                  <q-icon
                    name="circle"
                    v-for="n in props.row.level"
                    :key="n"
                    size="xs"
                    class="q-mr-sm"
                  />
                  {{ props.value }}</q-td
                >
              </template>
              <template v-slot:body-cell-actions="props">
                <q-td :props="props">
                  <div style="white-space: nowrap">
                    <q-btn
                      icon="o_edit"
                      round
                      dense
                      flat
                      @click="
                        editForm = JSON.parse(JSON.stringify(props.row));
                        editDialog = true;
                      "
                    />
                    <q-btn
                      icon="o_delete"
                      round
                      dense
                      flat
                      @click="
                        () => {
                          $q.dialog({
                            title: 'حذف',
                            message: 'هل أنت متأكد من رغبتك في الحذف؟',
                            cancel: true,
                            persistent: false
                          }).onOk(() => {
                            api
                              .delete(apiUrl + '/' + props.row.id)
                              .then((response) => {
                                $q.notify({
                                  color: 'green-4',
                                  textColor: 'white',
                                  icon: 'cloud_done',
                                  message: 'تم حذف المعلومات.'
                                });
                                categories = response.data;
                              })
                              .catch((error) => {
                                $q.notify({
                                  type: 'negative',
                                  message: error?.response?.data?.message
                                });
                              });
                          });
                        }
                      "
                    />
                    <q-btn icon="o_drag_indicator" class="handle" round dense flat />
                  </div>
                </q-td>
              </template>
            </q-table>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>
