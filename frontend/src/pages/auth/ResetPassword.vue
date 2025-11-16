<script setup lang="ts">
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import { api } from '@/utils/axios';
import { useQuasar } from 'quasar';

const $q = useQuasar();
const route = useRoute();

const form = ref({
  email: '',
  password: '',
  password_confirmation: ''
});
const loading = ref(false);

async function submit() {
  loading.value = true;
  await api.get('/sanctum/csrf-cookie').catch(() => {});

  api
    .post('/reset-password', form.value)
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
      $q.notify({
        type: 'negative',
        message: error?.response?.data?.message
      });
    });
}

// import Form from 'vform'

// export default {
//   middleware: 'guest',
//   layout: 'guest',
//   title: 'Reset Password',

//   data: () => ({
//     status: '',
//     form: new Form({
//       token: '',
//       email: '',
//       password: '',
//       password_confirmation: '',
//     }),
//   }),

//   created() {
//     this.form.email = this.$route.query.email
//     this.form.token = this.$route.params.token
//   },

//   methods: {
//     async reset() {
//       const { data } = await this.form.post('/api/password/reset')
//       this.status = data.status
//       this.form.reset()
//     },
//   },
// }
</script>

<template>
  <q-form
    @submit="submit"
    autocorrect="off"
    autocapitalize="off"
    autocomplete="off"
    spellcheck="false"
  >
    <div class="q-mt-xl row q-col-gutter-md">
      <q-input
        dir="ltr"
        outlined
        dense
        hide-bottom-space
        class="col-12"
        v-model="form.email"
        type="email"
        bottom-slots
        label="البريد الالكتروني"
      />

      <q-input
        dir="ltr"
        outlined
        dense
        hide-bottom-space
        class="col-12"
        v-model="form.password"
        autocomplete="new-password"
        type="password"
        bottom-slots
        label="كلمة المرور"
      />

      <q-input
        dir="ltr"
        outlined
        dense
        hide-bottom-space
        class="col-12"
        v-model="form.password_confirmation"
        type="password"
        bottom-slots
        label="اعد كلمة المرور"
      />

      <div class="col-12">
        <q-btn class="full-width" type="submit" color="primary" label="إعادة تعيين كلمة المرور" />
      </div>
    </div>
  </q-form>
</template>
