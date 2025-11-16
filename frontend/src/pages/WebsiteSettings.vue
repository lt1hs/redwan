<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { api } from '../utils/axios';
import { useQuasar } from 'quasar';

const $q = useQuasar();

const settingsForm = ref<any>({});
const formLoading = ref(true);
const settingsSubmitLoading = ref(false);

onMounted(async () => {
  api.get('/api/admin/options').then((response) => {
    settingsForm.value.social_networks = response.data.social_networks ?? [];

    formLoading.value = false;
  });
});

async function onSubmit() {
  settingsSubmitLoading.value = true;

  api
    .post('/api/admin/options/update', settingsForm.value)
    .then(async (response) => {
      $q.notify({
        type: 'positive',
        message: 'تم ادراج المعلومات بشكل صحيح'
      });
      settingsSubmitLoading.value = false;
    })
    .catch((error) => {
      settingsSubmitLoading.value = false;
      $q.notify({
        type: 'negative',
        message: error?.response?.data?.message
      });
    });
}
</script>

<template>
  <q-page>
    <base-breadcrumbs />
    <div class="row items-start q-col-gutter-md">
      <div class="col-12 col-md-4">
        <q-card>
          <q-card-section>
            <p class="text-h6 text-primary">معلومات الاعدادات</p>
            <ul
              style="list-style-position: inside; letter-spacing: unset"
              class="q-pl-md q-my-none"
            >
              <li>قم بتحديث معلومات الاعدادات</li>
            </ul>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-8">
        <q-form @submit="onSubmit">
          <q-card>
            <q-card-section>
              <div class="row q-gutter-y-md">
                <div class="col-12">
                  <div class="flex items-center q-gutter-x-sm">
                    <div class="text-h6 text-primary">روابط مواقع التواصل الاجتماعي</div>
                    <q-btn
                      icon="o_add"
                      color="primary"
                      dense
                      size="sm"
                      @click="
                        settingsForm.social_networks.push({
                          id: Math.max(...settingsForm.social_networks.map((o: any) => o.id), 0) + 1
                        })
                      "
                    />
                  </div>
                  <div
                    v-for="(social_network, index) in settingsForm.social_networks"
                    :key="social_network.id"
                    class="flex items-center q-mt-md"
                  >
                    <div class="row q-col-gutter-sm" style="flex: 1">
                      <q-select
                        class="col-12 col-md-4"
                        hide-bottom-space
                        outlined
                        dense
                        v-model="social_network.name"
                        label="الموقع"
                        :options="[
                          { label: 'X', value: 'x' },
                          { label: 'YouTube', value: 'youtube' },
                          { label: 'Facebook', value: 'facebook' },
                          { label: 'Telegram', value: 'telegram' },
                          { label: 'WhatsApp', value: 'whatsapp' },
                          { label: 'Instagram', value: 'instagram' },
                          { label: 'TikTok', value: 'tiktok' }
                        ]"
                        emit-value
                        map-options
                        :rules="[(val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة']"
                      />
                      <q-input
                        dir="ltr"
                        class="col-12 col-md-8"
                        hide-bottom-space
                        outlined
                        dense
                        v-model="social_network.url"
                        label="الرابط"
                        :rules="[(val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة']"
                      />
                    </div>
                    <q-btn
                      round
                      dense
                      flat
                      icon="o_delete"
                      color="negative"
                      @click="settingsForm.social_networks.splice(index, 1)"
                    />
                    <q-btn
                      :disable="index == 0"
                      flat
                      dense
                      icon="arrow_upward"
                      @click.stop="
                        () => {
                          const parent = settingsForm.social_networks;
                          const from = index;
                          const to = from - 1;
                          const item = parent.splice(from, 1)[0];
                          parent.splice(to, 0, item);
                        }
                      "
                    />
                    <q-btn
                      :disable="index == settingsForm.social_networks.length - 1"
                      flat
                      dense
                      icon="arrow_downward"
                      @click.stop="
                        () => {
                          const parent = settingsForm.social_networks;
                          const from = index;
                          const to = from + 1;
                          const item = parent.splice(from, 1)[0];
                          parent.splice(to, 0, item);
                        }
                      "
                    />
                  </div>
                </div>
              </div>
              <q-inner-loading :showing="formLoading" />
            </q-card-section>
            <q-separator />
            <q-card-section class="flex justify-end">
              <q-btn type="submit" :loading="settingsSubmitLoading" label="حفظ" color="primary" />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </div>
  </q-page>
</template>
<script setup lang="ts"></script>
