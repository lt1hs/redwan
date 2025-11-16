<template>
  <q-page>
    <div class="q-pa-md">
      <q-card flat bordered>
        <q-card-section class="q-px-lg">
          <div class="text-h6 text-primary q-mb-md">إعدادات الإشعارات</div>

          <div class="row q-col-gutter-md">
            <!-- SMS Settings -->
            <div class="col-12">
              <q-card flat bordered>
                <q-card-section>
                  <div class="text-subtitle1 text-weight-medium q-mb-md">
                    إعدادات الرسائل النصية
                  </div>

                  <div class="row q-col-gutter-md">
                    <div class="col-12 col-md-6">
                      <q-input
                        v-model="settings.sms.apiKey"
                        label="مفتاح API"
                        outlined
                        :type="showApiKey ? 'text' : 'password'"
                      >
                        <template v-slot:append>
                          <q-icon
                            :name="showApiKey ? 'visibility_off' : 'visibility'"
                            class="cursor-pointer"
                            @click="showApiKey = !showApiKey"
                          />
                        </template>
                      </q-input>
                    </div>

                    <div class="col-12 col-md-6">
                      <q-input v-model="settings.sms.sender" label="اسم المرسل" outlined />
                    </div>

                    <div class="col-12">
                      <q-toggle v-model="settings.sms.enabled" label="تفعيل خدمة الرسائل النصية" />
                    </div>
                  </div>
                </q-card-section>
              </q-card>
            </div>

            <!-- Schedule Settings -->
            <div class="col-12">
              <q-card flat bordered>
                <q-card-section>
                  <div class="text-subtitle1 text-weight-medium q-mb-md">
                    إعدادات جدولة الإشعارات
                  </div>

                  <div class="row q-col-gutter-md">
                    <div class="col-12 col-md-6">
                      <q-input
                        v-model="settings.schedule.checkTime"
                        type="time"
                        label="وقت فحص الإقامات يومياً"
                        outlined
                      />
                    </div>

                    <div class="col-12">
                      <q-toggle
                        v-model="settings.schedule.enabled"
                        label="تفعيل الإشعارات التلقائية"
                      />
                    </div>
                  </div>
                </q-card-section>
              </q-card>
            </div>

            <!-- Test Settings -->
            <div class="col-12">
              <q-card flat bordered>
                <q-card-section>
                  <div class="text-subtitle1 text-weight-medium q-mb-md">اختبار الإعدادات</div>

                  <div class="row q-col-gutter-md">
                    <div class="col-12 col-md-6">
                      <q-input
                        v-model="testPhone"
                        label="رقم الجوال للاختبار"
                        outlined
                        mask="#### ### ###"
                        placeholder="05XX XXX XXX"
                      />
                    </div>

                    <div class="col-12">
                      <q-btn
                        color="primary"
                        icon="o_send"
                        label="إرسال رسالة اختبار"
                        :loading="sending"
                        @click="sendTestMessage"
                      />
                    </div>
                  </div>
                </q-card-section>
              </q-card>
            </div>
          </div>

          <div class="row justify-end q-mt-md">
            <q-btn
              color="primary"
              icon="o_save"
              label="حفظ الإعدادات"
              :loading="saving"
              @click="saveSettings"
            />
          </div>
        </q-card-section>
      </q-card>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useQuasar } from 'quasar';

const $q = useQuasar();
const showApiKey = ref(false);
const saving = ref(false);
const sending = ref(false);
const testPhone = ref('');

const settings = ref({
  sms: {
    enabled: true,
    apiKey: '',
    sender: 'Radwan'
  },
  schedule: {
    enabled: true,
    checkTime: '09:00'
  }
});

async function saveSettings() {
  saving.value = true;
  try {
    // Implement save settings logic here
    await new Promise((resolve) => setTimeout(resolve, 1000)); // Simulated API call
    $q.notify({
      type: 'positive',
      message: 'تم حفظ الإعدادات بنجاح'
    });
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء حفظ الإعدادات'
    });
  } finally {
    saving.value = false;
  }
}

async function sendTestMessage() {
  if (!testPhone.value) {
    $q.notify({
      type: 'warning',
      message: 'يرجى إدخال رقم الجوال للاختبار'
    });
    return;
  }

  sending.value = true;
  try {
    // Implement send test message logic here
    await new Promise((resolve) => setTimeout(resolve, 1000)); // Simulated API call
    $q.notify({
      type: 'positive',
      message: 'تم إرسال رسالة الاختبار بنجاح'
    });
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء إرسال رسالة الاختبار'
    });
  } finally {
    sending.value = false;
  }
}
</script>

<style lang="scss" scoped>
.settings-section {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 16px;
  margin-bottom: 16px;
}
</style>
