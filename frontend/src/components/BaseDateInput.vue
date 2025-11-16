<script setup lang="ts">
const model = defineModel<any>();
</script>
<template>
  <q-input mask="####-##-##" v-model="model">
    <template v-slot:append>
      <q-icon name="event" class="cursor-pointer">
        <q-popup-proxy cover :breakpoint="600" ref="qDateProxy">
          <q-date
            mask="YYYY-MM-DD"
            v-model="model"
            @update:modelValue="
              (newValue) => {
                ($refs.qDateProxy as any).hide();
              }
            "
          />
        </q-popup-proxy>
      </q-icon>
    </template>
  </q-input>
</template>
