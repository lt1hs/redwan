<script setup lang="ts">
import { ref, watch } from 'vue';
import BaseRichEditor from '../components/BaseRichEditor.vue';
import { usePagesStore } from '@/stores/pages';

const pages = usePagesStore();

const getInitialData = () => ({
  slug: '',
  is_published: true,
  title: '',
  content: ''
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
  form.value = await pages.fetchDetails(props.id);
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
      <div class="col-12 col-md-3">
        <q-card>
          <q-card-section>
            <div class="row">
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

              <div class="q-mt-sm col-12 flex items-center justify-between">
                <q-toggle v-model="form.is_published" label="عرض في الموقع" />
                <q-btn :loading="submitLoading" label="حفظ" type="submit" color="primary" />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

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

              <div class="col-12">
                <BaseRichEditor v-model="form.content" />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-form>
</template>
