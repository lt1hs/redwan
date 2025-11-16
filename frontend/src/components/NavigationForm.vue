<script setup lang="ts">
/*
این فایل بد نوشته شده نیاز به بازنویسی داره.





















*/

import { useNavigationsStore } from '@/stores/navigations';
import { ref, computed, watch } from 'vue';

const navigations = useNavigationsStore();
const getInitialData = (): any => ({
  title: '',
  tree: []
});
const form = ref(getInitialData());
const insertTo = ref<any>(null);
const navNode = ref<any>({});
const navForm = ref<any>({});

const formRef = ref<any>(null);
const fetchLoading = ref(false);

function onReset() {
  form.value = getInitialData();
}

function getNodeParentByKey(key: any) {
  function checkArray(array: any): any {
    if (array.some((element: any) => element.id == key)) {
      return array;
    } else {
      for (let index = 0; index < array.length; index++) {
        const element = array[index];
        if (element.children) {
          let fnResult = checkArray(element.children);
          if (fnResult) return fnResult;
        }
      }
      return null;
    }
  }
  return checkArray(form.value.tree);
}

const showDialog = ref(false);

const newId = computed(() => {
  const ids: any = [];
  function getIdsForEachFn(element: any, index: any, array: any) {
    ids.push(element.id);
    if (element.children) {
      element.children.forEach(getIdsForEachFn);
    }
  }
  form.value.tree.forEach(getIdsForEachFn);
  return Math.max(...ids, 0) + 1;
});

const treeRef = ref<any>(null);

const props = defineProps({
  submitLoading: {
    type: Boolean,
    default: false
  },
  id: {
    type: Number,
    required: false
  },
  readonly: {
    type: Boolean,
    default: false
  }
});

async function fetch() {
  if (!props.id) {
    if (formRef.value) formRef.value.reset();
    return;
  }

  fetchLoading.value = true;
  form.value = await navigations.fetchDetails(props.id);
  fetchLoading.value = false;
}

watch(
  () => props.id,
  () => {
    fetch();
  },
  { immediate: true }
);

defineExpose({ fetch });
defineEmits(['submit']);
</script>

<template>
  <q-dialog v-model="showDialog" :position="'right'" maximized>
    <q-card style="width: 350px" class="column">
      <q-form
        @submit="
          if (navNode == null) {
            if (insertTo == null) {
              form.tree.push({ id: newId, ...navForm });
            } else {
              insertTo.children.push({ id: newId, ...navForm });
              treeRef.setExpanded(insertTo.id, true);
            }
          } else {
            navNode.title = navForm.title ?? '';
            navNode.url = navForm.url ?? '';
            navNode.target = navForm.target ?? '';
          }
        "
      >
        <q-card-section class="row q-gutter-y-md">
          <div class="col-12">
            <label class="label text-subtitle2" for="title">العنوان</label>
            <q-input
              autofocus
              class="q-mt-xs"
              for="title"
              hide-bottom-space
              outlined
              dense
              v-model="navForm.title"
              :rules="[(val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة']"
            />
          </div>

          <div class="col-12">
            <label class="label text-subtitle2" for="url">الرابط</label>
            <q-input
              dir="ltr"
              class="q-mt-xs"
              for="url"
              hide-bottom-space
              outlined
              dense
              v-model="navForm.url"
            />
          </div>

          <div class="col-12">
            <label class="label text-subtitle2" for="target">تفتح في</label>
            <q-select
              class="q-mt-xs"
              for="target"
              hide-bottom-space
              outlined
              dense
              v-model="navForm.target"
              :options="[
                { label: 'نفس الصفحة', value: '_self' },
                { label: 'صفحة جديدة', value: '_blank' }
              ]"
              emit-value
              map-options
            />
          </div>
        </q-card-section>
        <q-space />
        <q-card-actions align="right">
          <q-btn type="submit" v-close-popup color="primary">ارسال</q-btn>
          <q-btn color="negative" v-close-popup>الغاء</q-btn>
        </q-card-actions>
      </q-form>
    </q-card>
  </q-dialog>

  <q-form
    @submit="$emit('submit', { $event, form })"
    @reset="onReset"
    @validation-error="
      (ref: any) =>
        ref.$el.scrollIntoView({
          behavior: 'smooth',
          block: 'end',
          inline: 'nearest'
        })
    "
    ref="formRef"
  >
    <div class="row items-start q-col-gutter-md">
      <div class="col-12 col-md-3">
        <q-card>
          <q-card-section>
            <div class="row">
              <div class="col-12">
                <label class="label" for="title"> العنوان </label>
                <q-input
                  for="title"
                  class="q-mt-xs"
                  hide-bottom-space
                  v-model="form.title"
                  outlined
                  dense
                  bottom-slots
                  :rules="[(val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة']"
                />
              </div>

              <div class="q-mt-md col-12 flex items-center justify-end">
                <q-btn :loading="submitLoading" label="حفظ" type="submit" color="primary" />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-9">
        <q-card>
          <q-card-section>
            <q-btn
              icon="o_add"
              flat
              dense
              @click="
                navNode = null;
                insertTo = null;
                navForm = { target: '_self' };
                showDialog = true;
              "
            />
          </q-card-section>

          <q-separator inset />

          <q-card-section>
            <q-tree
              :nodes="form.tree"
              default-expand-all
              node-key="id"
              label-key="title"
              ref="treeRef"
            >
              <!-- <template v-slot:default-body> </template> -->
              <template v-slot:default-header="props">
                <!-- <pre>{{ props }}</pre> -->
                {{ props.node.title }}<q-space />
                <q-btn
                  :disable="getNodeParentByKey(props.node.id).indexOf(props.node) == 0"
                  flat
                  dense
                  icon="arrow_upward"
                  @click.stop="
                    () => {
                      const parent = getNodeParentByKey(props.node.id);
                      const from = parent.indexOf(props.node);
                      const to = from - 1;
                      const item = parent.splice(from, 1)[0];
                      parent.splice(to, 0, item);
                    }
                  "
                />
                <q-btn
                  :disable="
                    getNodeParentByKey(props.node.id).indexOf(props.node) ==
                    getNodeParentByKey(props.node.id).length - 1
                  "
                  flat
                  dense
                  icon="arrow_downward"
                  @click.stop="
                    () => {
                      const parent = getNodeParentByKey(props.node.id);
                      const from = parent.indexOf(props.node);
                      const to = from + 1;
                      const item = parent.splice(from, 1)[0];
                      parent.splice(to, 0, item);
                    }
                  "
                />
                <q-btn dense round flat icon="more_horiz" @click.stop="">
                  <q-menu cover auto-close>
                    <q-list dense>
                      <q-item
                        clickable
                        @click="
                          navNode = null;
                          if (!props.node.children) props.node.children = [];
                          insertTo = props.node;
                          navForm = { target: '_self' };
                          showDialog = true;
                        "
                      >
                        <q-item-section avatar>
                          <q-icon name="o_add" />
                        </q-item-section>
                        <q-item-section>اضافة</q-item-section>
                      </q-item>
                      <q-item
                        clickable
                        @click="
                          navNode = props.node;
                          navForm = JSON.parse(JSON.stringify(navNode));
                          showDialog = true;
                        "
                      >
                        <q-item-section avatar>
                          <q-icon name="o_edit" />
                        </q-item-section>
                        <q-item-section>تعديل</q-item-section>
                      </q-item>

                      <q-item
                        clickable
                        class="text-negative"
                        @click="
                          () => {
                            const parent = getNodeParentByKey(props.node.id);
                            parent.splice(parent.indexOf(props.node), 1);
                          }
                        "
                      >
                        <q-item-section avatar>
                          <q-icon name="delete" />
                        </q-item-section>
                        <q-item-section>حذف</q-item-section>
                      </q-item>
                    </q-list>
                  </q-menu>
                </q-btn>
              </template>
            </q-tree>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-form>
</template>
