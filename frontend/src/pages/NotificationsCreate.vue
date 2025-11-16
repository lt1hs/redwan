<script setup lang="ts">
import NotificationForm from '@/components/NotificationForm.vue';
import { ref } from 'vue';
import { useNotificationsStore } from '@/stores/notifications';
import { useQuasar } from 'quasar';

const notifications = useNotificationsStore();
const $q = useQuasar();
const submitLoading = ref(false);

async function onSubmit({ $event, form }: { $event: any; form: any }) {
  $q.dialog({
    title: 'اشعار',
    message: 'هل أنت متأكد من رغبتك في ارسال اشعار؟',
    cancel: true,
    persistent: false
  }).onOk(async () => {
    submitLoading.value = true;
    try {
      await notifications.create(form);
      $event.target.reset();
    } catch {
      /* empty */
    }
    submitLoading.value = false;
  });
}
</script>
<template>
  <q-page>
    <base-breadcrumbs />

    <NotificationForm @submit="onSubmit" :submitLoading="submitLoading" />
  </q-page>
</template>
