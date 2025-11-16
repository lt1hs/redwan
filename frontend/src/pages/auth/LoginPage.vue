<script setup lang="ts">
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';

const auth = useAuthStore();
const router = useRouter();

const loading = ref(false);

const form = ref({
  email: '',
  password: '',
  remember: false
});
const isPwd = ref(true);

async function login() {
  loading.value = true;

  try {
    await auth.login(form.value);
    router.push(auth.returnUrl || { name: 'dashboard' });
    auth.$patch({ returnUrl: null });
  } catch {
    /* */
  }

  loading.value = false;
}
</script>

<template>
  <q-form @submit="login">
    <div class="row q-col-gutter-md">
      <img class="col-12" style="width: 200px; margin: 0 auto" src="@/assets/logo.png" />
      <q-input
        dir="ltr"
        class="col-12"
        dense
        outlined
        v-model="form.email"
        type="email"
        bottom-slots
        hide-bottom-space
        label="البريد الالكتروني"
        :rules="[(val, rules) => rules.email(val) || 'يرجى إدخال البريد الإلكتروني الصحيح']"
      />
      <q-input
        dir="ltr"
        class="col-12"
        outlined
        dense
        v-model="form.password"
        :type="isPwd ? 'password' : 'text'"
        bottom-slots
        hide-bottom-space
        label="كلمة المرور"
        :rules="[(val) => !!val || 'ادخل رقمك السري']"
      >
        <template v-slot:prepend>
          <q-icon
            :name="isPwd ? 'visibility_off' : 'visibility'"
            class="cursor-pointer"
            @click="isPwd = !isPwd"
          />
        </template>
      </q-input>
      <div class="col-12 flex justify-between items-center" style="font-size: 0.75rem">
        <q-checkbox size="xs" v-model="form.remember" name="remember" label="تذكرني" />
        <div>
          <router-link :to="{ name: 'forgot-password' }"> نسيت كلمة المرور؟ </router-link>
        </div>
      </div>
      <div class="col-12">
        <q-btn type="submit" color="primary" label="دخول" :loading="loading" class="full-width" />
      </div>
    </div>
  </q-form>
</template>
