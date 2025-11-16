<script setup lang="ts">
import { useRoute } from 'vue-router';

const route = useRoute();
</script>

<template>
  <div class="q-pa-sm q-px-md">
    <q-breadcrumbs>
      <q-breadcrumbs-el icon="home" to="/" />
      <q-breadcrumbs-el
        v-for="(breadcrumb, index) in route.matched"
        :key="index"
        :label="breadcrumb.meta.title || breadcrumb.name"
        :to="breadcrumb.path"
      />
    </q-breadcrumbs>
  </div>
</template>
