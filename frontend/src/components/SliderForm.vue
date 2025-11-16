<script setup lang="ts">
import { ref, watch } from "vue";
import { useSlidersStore } from "../stores/sliders";

import BaseImagePreviewDialog from "./BaseImagePreviewDialog.vue";
import BaseFileManagerDialog from "./BaseFileManagerDialog.vue";

const sliders = useSlidersStore();

const getInitialData = (): any => ({
  title: "",
  slides: [],
});
const form = ref(getInitialData());
const formRef = ref<any>(null);
const fetchLoading = ref(false);

function onReset() {
  form.value = getInitialData();
}

const props = defineProps({
  submitLoading: {
    type: Boolean,
    default: false,
  },
  id: {
    type: Number,
    required: false,
  },
  readonly: {
    type: Boolean,
    default: false,
  },
});

async function fetch() {
  if (!props.id) {
    if (formRef.value) formRef.value.reset();
    return;
  }

  fetchLoading.value = true;
  form.value = await sliders.fetchDetails(props.id);
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
defineEmits(["submit"]);
</script>

<template>
  <q-form
    @submit="$emit('submit', { $event, form })"
    @reset="onReset"
    @validation-error="
      (ref:any) =>
        ref.$el.scrollIntoView({
          behavior: 'smooth',
          block: 'end',
          inline: 'nearest',
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
                  :rules="[
                    (val:any) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة',
                  ]"
                />
              </div>

              <div class="q-mt-sm col-12 flex items-center justify-end">
                <q-btn
                  :loading="submitLoading"
                  label="حفظ"
                  type="submit"
                  color="primary"
                />
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
              @click="form.slides.push({ image: null })"
            />
          </q-card-section>

          <q-card-section class="q-gutter-y-md">
            <div
              class="row q-col-gutter-x-md"
              v-for="(slide, index) in form.slides"
              :key="index"
            >
              <div class="col-12 col-md-4">
                <q-avatar
                  rounded
                  color="grey-3"
                  style="cursor: pointer; width: 100%; height: 100%"
                  @click="
                    if (slide.image) {
                      $q.dialog({
                        component: BaseImagePreviewDialog,
                        componentProps: {
                          image: slide.image,
                        },
                      });
                    } else {
                      $q.dialog({
                        component: BaseFileManagerDialog,
                        componentProps: {
                          type: 'slide',
                        },
                      }).onOk((files) => {
                        slide.image = files[0].url;
                      });
                    }
                  "
                >
                  <q-img
                    v-if="slide.image"
                    :src="slide.image"
                    style="cursor: pointer"
                  />
                  <q-icon v-else name="file_upload" />
                </q-avatar>
              </div>
              <div class="col-12 col-md-4 column">
                <div>
                  <label class="label" for="title"> العنوان </label>
                  <q-input
                    for="title"
                    class="q-mt-xs"
                    outlined
                    dense
                    v-model="slide.title"
                  />
                </div>

                <div class="q-mt-md">
                  <label class="label" for="url"> الرابط </label>
                  <q-input
                    for="url"
                    dir="ltr"
                    class="q-mt-xs"
                    outlined
                    dense
                    v-model="slide.url"
                  />
                </div>
                <q-space />
                <div>
                  <q-btn
                    :disable="index == 0"
                    flat
                    dense
                    icon="arrow_upward"
                    @click.stop="
                      () => {
                        const parent = form.slides;
                        const from = index;
                        const to = from - 1;
                        const item = parent.splice(from, 1)[0];
                        parent.splice(to, 0, item);
                      }
                    "
                  />
                  <q-btn
                    :disable="index == form.slides.length - 1"
                    flat
                    dense
                    icon="arrow_downward"
                    @click.stop="
                      () => {
                        const parent = form.slides;
                        const from = index;
                        const to = from + 1;
                        const item = parent.splice(from, 1)[0];
                        parent.splice(to, 0, item);
                      }
                    "
                  />
                  <q-btn
                    color="negative"
                    flat
                    dense
                    icon="o_delete"
                    @click.stop="
                      () => {
                        form.slides.splice(index, 1);
                      }
                    "
                  />
                </div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-form>
</template>
