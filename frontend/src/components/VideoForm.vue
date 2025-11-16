<script setup lang="ts">
import { ref, watch } from 'vue';
import { useQuasar } from 'quasar';
import BaseRichEditor from '@/components/BaseRichEditor.vue';
import BaseImagePreviewDialog from '@/components/BaseImagePreviewDialog.vue';
import BaseFileManagerDialog from '@/components/BaseFileManagerDialog.vue';
import { useCategoriesStore } from '../stores/categories';
import { useHelper } from '@/composables/helper';
import { useVideosStore } from '../stores/videos';

const videos = useVideosStore();
const categoriesStore = useCategoriesStore();
categoriesStore.fetchCategories('videos');

const $q = useQuasar();
const helper = useHelper();

const getInitialData = (): any => ({
  slug: '',
  date: '',
  show_on_homepage: true,
  is_published: true,
  title: '',
  content: '',
  description: '',
  categories: [],
  tags: [],
  video: '',
  featured_image: null,
  featured_image_url: null
});
const form = ref(getInitialData());
const formRef = ref<any>(null);
const fetchLoading = ref(false);

function onReset() {
  form.value = getInitialData();
}

async function handlePastedData(file: File) {
  form.value.featured_image = await helper.imageFileToBase64(file);
}

async function requestPaste() {
  const clipboardItems = await navigator.clipboard.read();

  for (const clipboardItem of clipboardItems) {
    for (const type of clipboardItem.types) {
      if (/^image\//.test(type)) {
        const blob = await clipboardItem.getType(type);

        const file = new File([blob], 'file name', {
          type: type,
          lastModified: new Date().getTime()
        });
        handlePastedData(file);
      }
    }
  }
}

document.addEventListener('paste', function (evt) {
  if (!evt.clipboardData || !evt.clipboardData.files.length) return;
  const file = evt.clipboardData.files[0];

  if (!/^image\//.test(file.type)) return;

  handlePastedData(file);
});

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
  form.value = await videos.fetchDetails(props.id);
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
      <div class="col-12 col-md-9">
        <q-card>
          <q-card-section>
            <div class="row q-gutter-y-md">
              <div class="col-12 col-md-8">
                <label class="label" for="title">العنوان</label>
                <q-input
                  class="q-mt-xs"
                  for="title"
                  hide-bottom-space
                  outlined
                  dense
                  v-model="form.title"
                  :rules="[(val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة']"
                />
              </div>
              <div class="col-12 col-md-8">
                <label class="label" for="description"> نص مختصر </label>
                <q-input
                  for="description"
                  class="q-mt-xs"
                  hide-bottom-space
                  outlined
                  dense
                  v-model="form.description"
                  type="textarea"
                  input-style="height: 150px"
                />
              </div>

              <div class="col-12 col-md-8">
                <label class="label" for="video"> رابط الفيديو </label>
                <q-input
                  for="video"
                  dir="ltr"
                  class="q-mt-xs"
                  hide-bottom-space
                  outlined
                  dense
                  v-model="form.video"
                  hint="اجعل هنا رابط الفيديو (YouTube أو غيرهن) أو اختار ملف داخلية"
                  :rules="[(val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة']"
                >
                  <template v-slot:append
                    ><q-icon
                      @click="
                        $q.dialog({
                          component: BaseFileManagerDialog,
                          componentProps: {
                            type: 'video'
                          }
                        }).onOk((files) => {
                          form.video = files[0].url;
                        })
                      "
                      name="o_upload"
                      class="cursor-pointer"
                  /></template>
                </q-input>
              </div>

              <div class="col-12">
                <BaseRichEditor v-model="form.content" />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-3">
        <q-card>
          <q-card-section>
            <div class="row">
              <div class="col-12 text-center">
                <label class="label" for="name"> الصورة </label>
                <div class="q-mt-md">
                  <q-avatar
                    square
                    size="100px"
                    font-size="52px"
                    style="background: #ebf4ff; color: #7f9cf5; width: 100%"
                  >
                    <img
                      class="cursor-pointer"
                      @click="
                        $q.dialog({
                          component: BaseImagePreviewDialog,
                          componentProps: {
                            image: form['featured_image']
                          }
                        })
                      "
                      v-if="form['featured_image']"
                      :src="form['featured_image']"
                    />
                    <img
                      class="cursor-pointer"
                      @click="
                        $q.dialog({
                          component: BaseImagePreviewDialog,
                          componentProps: {
                            image: form['featured_image_url']
                          }
                        })
                      "
                      v-else-if="form['featured_image_url']"
                      :src="form['featured_image_url']"
                    />
                    <span v-else>ش</span>
                  </q-avatar>
                </div>

                <div class="q-my-md">
                  <q-btn
                    round
                    dense
                    color="indigo-5"
                    icon="o_file_upload"
                    @click="
                      async () => {
                        form.featured_image = await helper.imageFileInputToBase64();
                      }
                    "
                  />
                  <q-btn
                    class="q-ml-sm"
                    v-if="form['featured_image'] || form['featured_image_url']"
                    round
                    dense
                    color="negative"
                    icon="o_delete"
                    @click="
                      form['featured_image'] = null;
                      form['featured_image_url'] = null;
                    "
                  />
                </div>

                <div>
                  <div>
                    <q-btn dense icon="o_paste" color="indigo-4" @click="requestPaste" rounded />
                    او ضغط <kbd class="key">Ctrl</kbd> +
                    <kbd class="key">V</kbd>
                  </div>
                </div>
              </div>

              <div class="col-12 q-mt-md" dir="ltr">
                <label dir="ltr" class="label" for="slug"> Slug </label>
                <q-input
                  v-model="form.slug"
                  for="slug"
                  class="q-mt-xs"
                  hide-bottom-space
                  outlined
                  dense
                  bottom-slots
                />
              </div>

              <div class="col-12 q-mt-md">
                <label class="label" for="date"> التاريخ </label>
                <q-input class="q-mt-xs" dir="ltr" dense outlined v-model="form.date">
                  <template v-slot:prepend>
                    <q-icon name="event" class="cursor-pointer">
                      <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                        <q-date v-model="form.date" mask="YYYY-MM-DD HH:mm">
                          <div class="row items-center justify-end">
                            <q-btn v-close-popup label="Close" color="primary" flat />
                          </div>
                        </q-date>
                      </q-popup-proxy>
                    </q-icon>
                  </template>

                  <template v-slot:append>
                    <q-icon name="access_time" class="cursor-pointer">
                      <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                        <q-time v-model="form.date" mask="YYYY-MM-DD HH:mm" format24h>
                          <div class="row items-center justify-end">
                            <q-btn v-close-popup label="Close" color="primary" flat />
                          </div>
                        </q-time>
                      </q-popup-proxy>
                    </q-icon>
                  </template>
                </q-input>
              </div>

              <div class="col-12 q-mt-md">
                <label class="label" for="date"> التصانيف </label>
                <q-select
                  class="q-mt-xs"
                  dense
                  outlined
                  multiple
                  :options="categoriesStore.categories['videos']"
                  v-model="form.categories"
                  option-value="id"
                  option-label="name"
                  emit-value
                  map-options
                  clearable
                  :rules="[(val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة']"
                />
              </div>

              <div class="col-12 q-mt-md">
                <label class="label" for="date"> كلمات مفتاحية </label>
                <q-select
                  class="q-mt-xs"
                  dense
                  outlined
                  v-model="form.tags"
                  use-input
                  use-chips
                  multiple
                  hide-dropdown-icon
                  input-debounce="0"
                  new-value-mode="add-unique"
                />
              </div>

              <q-toggle
                class="q-mt-sm"
                v-model="form.show_on_homepage"
                label="عرض في الصفحة الرئيسية"
              />
              <div class="q-mt-sm col-12 flex items-center justify-between">
                <q-toggle v-model="form.is_published" label="عرض في الموقع" />
                <q-btn :loading="submitLoading" label="حفظ" type="submit" color="primary" />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-form>
</template>

<style scoped>
.key {
  background-color: #f7fafc;
  border: 1px solid #cbd5e0;
  border-radius: 0.25rem;
  padding: 0.25rem;
}
</style>
