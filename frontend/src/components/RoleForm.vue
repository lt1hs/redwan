<script setup lang="ts">
import { ref, watch } from 'vue';
import { useRolesStore } from '@/stores/roles';
import { api } from '@/utils/axios';

const roles = useRolesStore();
const permissions = ref([]);

const getInitialData = () => ({
  name: '',
  permissions: []
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
  const response = await api.get('/admin/roles/permissions/all');
  permissions.value = response.data.data;

  if (!props.id) {
    if (formRef.value) formRef.value.reset();
    return;
  }

  fetchLoading.value = true;
  form.value = await roles.fetchDetails(props.id);
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
    <q-card>
      <q-card-section>
        <div class="row q-col-gutter-x-md q-col-gutter-y-md">
          <div class="col-12 col-md-8">
            <label class="label" for="name"> الاسم </label>
            <q-input
              for="name"
              class="q-mt-xs"
              hide-bottom-space
              v-model="form.name"
              outlined
              dense
              bottom-slots
            />
          </div>
          <div class="col-8">
            <q-card bordered>
              <q-card-section class="flex items-center">
                <div class="text-subtitle1">الاذونات</div>
              </q-card-section>
              <q-separator inset />
              <q-card-section>
                <q-option-group
                  v-model="form.permissions"
                  :options="permissions.map((e) => ({ label: e, value: e }))"
                  color="green"
                  type="checkbox"
                />
              </q-card-section>
            </q-card>
          </div>
        </div>
      </q-card-section>
      <q-separator />
      <q-card-section class="flex justify-end">
        <q-btn :loading="submitLoading" label="حفظ" type="submit" color="primary" />
      </q-card-section>
    </q-card>
  </q-form>
</template>
