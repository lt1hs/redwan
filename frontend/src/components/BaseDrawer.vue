<script setup lang="ts">
import MenuItem from './MenuItem.vue';
import { ref } from 'vue';

const props = defineProps({
  modelValue: Boolean,
  menuItems: Array
});

const emit = defineEmits(["update:modelValue"]);

const isCompact = ref(false);
</script>

<template>
  <q-drawer
    :modelValue="modelValue"
    @update:modelValue="(newValue) => $emit('update:modelValue', newValue)"
    :width="isCompact ? 80 : 280"
    show-if-above
    class="bg-white"
  >
    <!-- Header -->
    <div class="q-pa-md border-bottom" style="display: flex; align-items: center; justify-content: space-between;">
      <div style="flex: 1; display: flex; justify-content: center;">
        <img src="@/assets/logo.png" alt="Logo" style="max-height: 40px; object-fit: contain;" />
      </div>
      
      <q-btn
        flat
        round
        dense
        :icon="isCompact ? 'chevron_right' : 'chevron_left'"
        size="sm"
        @click="isCompact = !isCompact"
      />
    </div>

    <!-- Menu Items -->
    <q-list>
      <MenuItem
        v-for="(item, index) in menuItems"
        :key="`menu-${index}`"
        :item="item"
        :compact="isCompact"
      />
    </q-list>
  </q-drawer>
</template>
