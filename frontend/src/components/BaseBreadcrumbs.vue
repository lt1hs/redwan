<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
const route = useRoute();

const matched = computed(() => {
  return route.matched
    .filter((e) => e.meta.breadCrumbTitle)
    .map((e) => {
      return {
        name: e.name,
        path: e.path,
        children: e.children,
        label: e.meta.breadCrumbTitle as string,
        icon: e.meta.breadCrumbIcon as string
      };
    });
});
</script>

<template>
  <q-card flat bordered class="q-mb-md">
    <q-card-section class="q-pa-sm">
      <q-breadcrumbs separator="/" class="text-grey-8">
        <q-breadcrumbs-el to="/" icon="o_home" label="الرئيسية" />
        <template v-for="match in matched" :key="match.path">
          <q-breadcrumbs-el
            :to="
              match.name
                ? { name: match.name, params: route.params }
                : match.children?.[0]?.name && match.children?.[0]?.path == ''
                  ? { name: match.children[0].name, params: route.params }
                  : null
            "
            :label="match.label"
            :icon="match.icon"
          />
        </template>
      </q-breadcrumbs>
    </q-card-section>
  </q-card>
</template>

<style lang="scss" scoped>
.q-breadcrumbs {
  font-size: 0.875rem;

  :deep(.q-icon) {
    font-size: 1.25rem;
  }

  :deep(.q-breadcrumbs__separator) {
    margin: 0 8px;
  }
}
</style>
