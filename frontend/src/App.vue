<script setup lang="ts">
import { RouterView } from 'vue-router';
import { useQuasar } from 'quasar';
import { onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import DefaultLayout from '@/layouts/DefaultLayout.vue';

const $q = useQuasar();
const auth = useAuthStore();

onMounted(() => {
  // Disable dark mode
  $q.dark.set(false);
});
</script>

<template>
  <q-layout view="lHr Lpr lff">
    <DefaultLayout>
      <RouterView />
    </DefaultLayout>
  </q-layout>
</template>

<style lang="scss">
// Import the Quasar variables
@import '@/quasar-variables.scss';

// Global styles
body {
  font-family: $typography-font-family;
  margin: 0;
  padding: 0;
}

// Light mode styles
.body--light {
  background: #f8f7fa;

  .q-drawer {
    background: white;
  }

  .q-header {
    background: white;
    color: $dark;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  }
}

// Dark mode styles
.body--dark {
  background: $dark-page;

  .q-drawer {
    background: lighten($dark-page, 3%);
  }

  .q-header {
    background: lighten($dark-page, 3%);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
  }
}

// RTL Support
[dir='rtl'] {
  .q-drawer--left {
    border-left: 1px solid rgba(0, 0, 0, 0.12);
  }

  .q-item__section--side {
    padding-right: 0;
    padding-left: 12px;
  }
}

// Common styles
.q-drawer {
  .q-item {
    border-radius: 8px;
    margin: 0 8px;
  }

  .q-item--active {
    font-weight: 600;
  }
}

.q-header {
  .q-toolbar {
    height: 64px;
  }

  .q-btn {
    border-radius: 8px;
  }
}
</style>
