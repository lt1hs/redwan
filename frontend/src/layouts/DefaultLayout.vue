<script setup lang="ts">
import { useRoute } from 'vue-router';
import { computed } from 'vue';

const route = useRoute();

const pageTitle = computed(() => {
  return route.meta.title || '';
});
</script>

<template>
  <!-- <q-header elevated class="bg-primary text-white">
    <q-toolbar>
      <q-toolbar-title>{{ pageTitle }}</q-toolbar-title>
    </q-toolbar>
  </q-header> -->

  <q-page-container>
    <slot></slot>
  </q-page-container>
</template>
