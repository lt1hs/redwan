import { defineStore } from 'pinia';
import { ref } from 'vue';
import { api } from "@/utils/axios";
import { useQuasar } from 'quasar';
import { useHelper } from '@/composables/helper';
import SmsService from '@/services/sms';

interface NotificationData {
  title: string;
  body: string;
  target: string;
  image?: string;
  sendSms?: boolean;
  recipients?: string[];
  phoneNumbers?: string[];
}

export const useNotificationsStore = defineStore('notifications', () => {
  const notifications = ref<any[]>([]);
  const credit = ref<number | null>(null);
  const smsLogs = ref<any[]>([]);
  const loading = ref(false);
  const $q = useQuasar();
  const helper = useHelper();
  const smsService = SmsService.getInstance();

  async function fetch() {
    loading.value = true;
    try {
      const response = await api.get('/api/admin/notifications');
      notifications.value = response.data;
    } catch (error) {
      helper.handleServerError(error);
    } finally {
      loading.value = false;
    }
  }

  async function fetchSmsLogs() {
    loading.value = true;
    try {
      const response = await api.get('/api/admin/sms-logs');
      smsLogs.value = response.data;
    } catch (error) {
      helper.handleServerError(error);
    } finally {
      loading.value = false;
    }
  }

  async function getSmsCredit() {
    try {
      credit.value = await smsService.checkCredit();
      return credit.value;
    } catch (error) {
      helper.handleServerError(error);
      return null;
    }
  }

  async function create(data: NotificationData) {
    try {
      loading.value = true;
      
      // Create notification in the database
      const response = await api.post('/api/admin/notifications', {
        title: data.title,
        body: data.body,
        target: data.target,
        image: data.image
      });
      
      // Send SMS if requested
      if (data.sendSms && data.phoneNumbers && data.phoneNumbers.length > 0) {
        try {
          // Format SMS text
          const smsText = `${data.title}\n${data.body}`;
          
          // Send bulk SMS if there are multiple recipients
          if (data.phoneNumbers.length > 1) {
            await smsService.sendBulkSms(data.phoneNumbers, smsText);
          } else {
            // Send individual SMS
            await smsService.sendSms(data.phoneNumbers[0], smsText);
          }
          
          // Log SMS sending
          await api.post('/api/admin/sms-logs', {
            notificationId: response.data.id,
            recipients: data.phoneNumbers,
            message: smsText,
            status: 'SENT',
            sentAt: new Date().toISOString()
          });
          
          $q.notify({
            type: 'positive',
            message: 'تم إرسال الرسالة النصية بنجاح'
          });
        } catch (smsError) {
          console.error('SMS sending failed:', smsError);
          
          // Log SMS failure
          await api.post('/api/admin/sms-logs', {
            notificationId: response.data.id,
            recipients: data.phoneNumbers,
            message: `${data.title}\n${data.body}`,
            status: 'FAILED',
            sentAt: new Date().toISOString(),
            error: JSON.stringify(smsError)
          });
          
          $q.notify({
            type: 'negative',
            message: 'فشل إرسال الرسالة النصية'
          });
        }
      }
      
      // Show success notification
      $q.notify({
        type: 'positive',
        message: 'تم اضافة الخبر'
      });

      return response.data;
    } catch (error) {
      helper.handleServerError(error);
      return null;
    } finally {
      loading.value = false;
    }
  }

  async function fetchDetails(id: number): Promise<any> {
    try {
      loading.value = true;
      const response = await api.get('/api/admin/notifications/' + id);
      return response.data;
    } catch (error) {
      helper.handleServerError(error);
      return null;
    } finally {
      loading.value = false;
    }
  }

  async function sendSms(phoneNumber: string, message: string) {
    try {
      loading.value = true;
      const result = await smsService.sendSms(phoneNumber, message);
      
      $q.notify({
        type: 'positive',
        message: 'تم إرسال الرسالة النصية بنجاح'
      });
      
      return result;
    } catch (error) {
      helper.handleServerError(error);
      return null;
    } finally {
      loading.value = false;
    }
  }

  async function destroy(id: number) {
    try {
      loading.value = true;
      await api.delete('/api/admin/notifications/' + id);
      $q.notify({
        color: 'green-4',
        textColor: 'white',
        icon: 'cloud_done',
        message: 'تم حذف الخبر.',
      });
    } catch (error) {
      helper.handleServerError(error);
    } finally {
      loading.value = false;
    }
  }

  return { 
    notifications, 
    smsLogs,
    credit,
    loading,
    fetch, 
    fetchSmsLogs,
    getSmsCredit,
    create, 
    fetchDetails, 
    sendSms,
    destroy 
  };
});
