<template>
  <q-dialog ref="dialogRef" @hide="onDialogHide">
    <q-card ref="" class="q-dialog-plugin" style="width: 100%; max-width: 1280px">
      <iframe
        ref="filemanager"
        :src="api.defaults.baseURL + '/filemanager' + '?type=' + type"
        style="width: 100%; height: 70vh; border: 0; display: block"
      ></iframe>
    </q-card>
  </q-dialog>
</template>

<script setup lang="ts">
import { useDialogPluginComponent } from 'quasar';
import { ref, onMounted } from 'vue';
import { api } from '@/utils/axios';

const filemanager = ref<any>(null);

const props = defineProps({
  type: {
    type: String,
    default: 'file'
  }
});

onMounted(() => {
  window.addEventListener('message', (event) => {
    if (event.source !== filemanager?.value?.contentWindow) {
      return; // Skip message in this event listener
    }
    onOKClick(event.data);
  });
});

defineEmits([
  // REQUIRED; need to specify some events that your
  // component will emit through useDialogPluginComponent()
  ...useDialogPluginComponent.emits
]);

const { dialogRef, onDialogHide, onDialogOK, onDialogCancel } = useDialogPluginComponent();
// dialogRef      - Vue ref to be applied to QDialog
// onDialogHide   - Function to be used as handler for @hide on QDialog
// onDialogOK     - Function to call to settle dialog with "ok" outcome
//                    example: onDialogOK() - no payload
//                    example: onDialogOK({ /*...*/ }) - with payload
// onDialogCancel - Function to call to settle dialog with "cancel" outcome

// this is part of our example (so not required)
function onOKClick(files: any) {
  // on OK, it is REQUIRED to
  // call onDialogOK (with optional payload)
  onDialogOK(files);
  // or with payload: onDialogOK({ ... })
  // ...and it will also hide the dialog automatically
}
</script>
