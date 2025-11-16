import { createApp } from 'vue';
import { EditorContent } from '@tiptap/vue-3';
import { createPinia } from 'pinia';
import {
  Quasar,
  Notify,
  LocalStorage,
  SessionStorage,
  LoadingBar,
  Dialog,
  AppFullscreen
} from 'quasar';

// Import Quasar css
import 'quasar/src/css/index.sass';

// Import icon libraries
import '@quasar/extras/material-icons/material-icons.css';
import '@quasar/extras/material-icons-outlined/material-icons-outlined.css';

import langAr from 'quasar/lang/ar';
import './assets/main.scss';
import App from './App.vue';
import router from './router';

import { useAuthStore } from '@/stores/auth';
import BaseAutoComplete from '@/components/BaseAutoComplete.vue';
import BaseBreadcrumbs from '@/components/BaseBreadcrumbs.vue';
import BaseDateInput from '@/components/BaseDateInput.vue';
import MenuItem from '@/components/MenuItem.vue';

const app = createApp(App);
app.use(createPinia());
app.use(Quasar, {
  plugins: {
    Notify,
    LocalStorage,
    SessionStorage,
    LoadingBar,
    Dialog,
    AppFullscreen
  },
  lang: langAr
});
app.component('base-auto-complete', BaseAutoComplete);
app.component('base-breadcrumbs', BaseBreadcrumbs);
app.component('base-date-input', BaseDateInput);
app.component('editor-content', EditorContent);
app.component('MenuItem', MenuItem);

const auth = useAuthStore();
auth.fetchUser().then(() => {
  app.use(router);
  app.mount('#app');
});
