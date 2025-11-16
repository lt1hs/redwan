<!-- eslint-disable vue/no-useless-template-attributes -->
<!-- eslint-disable vue/valid-v-slot -->
<script setup lang="ts">
import { ref, computed, useAttrs } from 'vue';

const attrs = useAttrs() as any;
const props = defineProps(['options']);
const needle = ref('');
const options = computed(() =>
  props.options?.filter(
    (v: any) =>
      // (typeof v === "string" ? v : v[attrs["option-label"] ?? "label"])
      (typeof v === 'object' ? v[attrs['option-label'] ?? 'label'] : v)
        .toString()
        .toLowerCase()
        .indexOf(needle.value) > -1
  )
);

function filterFn(val: any, update: any, abort: any) {
  update(() => {
    needle.value = val.toLowerCase();
  });
}

const model = defineModel();
</script>

<template>
  <q-select
    @filter="filterFn"
    use-input
    hide-selected
    fill-input
    :options="options"
    input-debounce="0"
    v-model="model"
  >
    <template v-slot:no-option>
      <q-item v-if="$attrs.onInputValue === undefined">
        <q-item-section class="text-grey"> لا توجد نتائج </q-item-section>
      </q-item>
    </template>
    <!-- https://github.com/vuejs/core/issues/5312#issuecomment-1780896783 -->
    <template v-for="(index, name) of Object.keys($slots) as {}" v-slot:[name]>
      <slot :name="name as string" />
    </template>
  </q-select>
</template>
