<script setup lang="ts">
import { ref, watch } from 'vue';
import { useAdminsStore } from '../stores/admins';
import { useRolesStore } from '../stores/roles';
import { useRoute } from 'vue-router';

const admins = useAdminsStore();
const roles = useRolesStore();
const route = useRoute();
roles.fetch();

const getInitialData = (): any => ({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  roles: []
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
  form.value = await admins.fetchDetails(props.id);
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
    autocorrect="off"
    autocapitalize="off"
    autocomplete="off"
    spellcheck="false"
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
              placeholder="الاسم"
              outlined
              dense
              bottom-slots
            />
          </div>
          <div class="col-12 col-md-8">
            <label class="label" for="email"> البريد الالكتروني </label>
            <q-input
              for="email"
              dir="ltr"
              class="q-mt-xs"
              hide-bottom-space
              v-model="form.email"
              type="email"
              placeholder="email@domain.com"
              outlined
              dense
              bottom-slots
            />
          </div>
          <div class="col-12 col-md-8">
            <label class="label" for="password"> كلمة المرور </label>
            <q-input
              for="password"
              dir="ltr"
              class="q-mt-xs"
              hide-bottom-space
              v-model="form.password"
              autocomplete="new-password"
              type="password"
              placeholder="كلمة المرور"
              outlined
              dense
              bottom-slots
              :rules="[
                (val) =>
                  !!val || (!val && route.path.split('/').pop() == 'edit') || 'ادخل رقمك السري'
              ]"
            />
          </div>
          <div class="col-12 col-md-8">
            <label class="label" for="password_confirmation"> اعد كلمة المرور </label>
            <q-input
              for="password_confirmation"
              dir="ltr"
              class="q-mt-xs"
              hide-bottom-space
              v-model="form.password_confirmation"
              type="password"
              outlined
              dense
              bottom-slots
              placeholder="اعد كلمة المرور"
              :rules="[(val) => val == form.password || 'لا تتطابق مع كلمة المرور التي تم إدخالها']"
            />
          </div>

          <div class="col-12 col-md-8">
            <q-card bordered>
              <q-card-section class="flex items-center">
                <div class="text-subtitle1">السمة</div>
              </q-card-section>
              <q-separator inset />
              <q-card-section>
                <q-option-group
                  v-model="form.roles"
                  :options="
                    roles.roles.map((role: any) => {
                      return { label: role.name, value: role.name };
                    })
                  "
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
