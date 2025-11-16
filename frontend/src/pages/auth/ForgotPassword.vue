<script setup lang="ts">
import { ref } from 'vue';
import { api } from '@/utils/axios';
import { Notify } from 'quasar';
import { useQuasar } from 'quasar';

const $q = useQuasar();

const form = ref({ email: '' });
const loading = ref(false);

async function send() {
  loading.value = true;
  await api.get('/sanctum/csrf-cookie').catch(() => {});
  api
    .post('/forgot-password', form.value)
    .then(async (response) => {
      $q.notify({
        type: 'positive',
        message: response.data.message
      });
      loading.value = false;

      // this.user = { temporary: true }; // make a temporary user state to bypass router guard
      // this.router.push(this.returnUrl || { name: "dashboard" });
      // this.returnUrl = null;
    })
    .catch((error) => {
      loading.value = false;
      Notify.create({
        type: 'negative',
        message: error?.response?.data?.message
      });
    });
}
</script>

<!-- <script>
import Form from "vform";

export default {
  middleware: "guest",
  layout: "guest",
  title: "Reset Password",

  data: () => ({
    status: "",
    form: new Form({
      email: "",
    }),
  }),

  methods: {
    async send() {
      const { data } = await this.form.post("/api/password/email");
      this.status = data.status;
      this.form.reset();
    },
  },
};
</script> -->

<template>
  <q-form @submit="send">
    <div class="q-mt-xl row q-col-gutter-md">
      <div>
        نسيت كلمة المرور؟ لا مشكلة. فقط أخبرنا بعنوان بريدك الإلكتروني وسنرسل لك عبر البريد
        الإلكتروني رابط إعادة تعيين كلمة المرور الذي سيسمح لك باختيار عنوان جديد.
      </div>

      <q-input
        dir="ltr"
        class="col-12"
        outlined
        dense
        v-model="form.email"
        type="email"
        bottom-slots
        hide-bottom-space
        label="البريد الالكتروني"
      />

      <div class="col-12">
        <q-btn
          class="full-width"
          type="submit"
          color="primary"
          label="ارسال رابط إعادة تعيين كلمة مرور"
          :loading="loading"
        />
      </div>

      <div class="col-12 text-center" style="font-size: 0.75rem">
        <router-link :to="{ name: 'login' }">
          Did you remember your password? Click to login.
        </router-link>
      </div>
    </div>
  </q-form>
</template>
