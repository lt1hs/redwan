import { defineStore } from 'pinia';
import axios from '@/plugins/axios';
import SmsService from '@/services/sms';

interface NotificationTemplate {
  id: number;
  type: 'ONE_MONTH' | 'TWO_WEEKS' | 'ONE_WEEK';
  messageTemplate: string;
  smsEnabled: boolean;
  isActive: boolean;
}

interface Notification {
  id: number;
  userId: number;
  username: string;
  message: string;
  type: string;
  expirationDate: string;
  sentAt: string;
  status: 'PENDING' | 'SENT' | 'FAILED';
  phoneNumber?: string;
  smsSent?: boolean;
  smsStatus?: 'PENDING' | 'SENT' | 'FAILED';
}

export const useNotificationStore = defineStore('notification', {
  state: () => ({
    notifications: [] as Notification[],
    templates: [
      {
        id: 1,
        type: 'ONE_MONTH',
        messageTemplate:
          'عزيزي {username}، ستنتهي إقامتك في {expirationDate}. يرجى تقديم جواز سفرك لتجديد إقامتك.',
        smsEnabled: true,
        isActive: true
      },
      {
        id: 2,
        type: 'TWO_WEEKS',
        messageTemplate:
          'عزيزي {username}، ستنتهي إقامتك خلال أسبوعين. يرجى تقديم جواز سفرك في أقرب وقت ممكن.',
        smsEnabled: true,
        isActive: true
      },
      {
        id: 3,
        type: 'ONE_WEEK',
        messageTemplate:
          'عزيزي {username}، ستنتهي إقامتك خلال أسبوع واحد. يرجى تقديم جواز سفرك فوراً لتجنب أي تأخير.',
        smsEnabled: true,
        isActive: true
      }
    ] as NotificationTemplate[],
    smsLogs: [] as any[],
    loading: false,
    error: null as string | null
  }),

  actions: {
    async fetchNotifications() {
      this.loading = true;
      try {
        const response = await axios.get('/api/notifications');
        this.notifications = response.data;
      } catch (error) {
        console.error('Error fetching notifications:', error);
        this.error = 'حدث خطأ أثناء جلب الإشعارات';
      } finally {
        this.loading = false;
      }
    },

    async fetchSmsLogs() {
      this.loading = true;
      try {
        const response = await axios.get('/api/sms-logs');
        this.smsLogs = response.data;
      } catch (error) {
        console.error('Error fetching SMS logs:', error);
        this.error = 'حدث خطأ أثناء جلب سجلات الرسائل النصية';
      } finally {
        this.loading = false;
      }
    },

    async sendNotification(notification: Partial<Notification>) {
      try {
        // First save the notification to the database
        const response = await axios.post('/api/notifications', notification);
        const savedNotification = response.data;
        
        // Send SMS if applicable and phone number exists
        if (notification.phoneNumber && this.shouldSendSms(notification.type || '')) {
          await this.sendSmsNotification(
            notification.phoneNumber,
            notification.message || '',
            savedNotification.id
          );
        }
        
        return savedNotification;
      } catch (error) {
        console.error('Error sending notification:', error);
        throw new Error('حدث خطأ أثناء إرسال الإشعار');
      }
    },

    shouldSendSms(notificationType: string): boolean {
      const template = this.templates.find(t => t.type === notificationType);
      return template ? template.smsEnabled && template.isActive : false;
    },

    async sendSmsNotification(phoneNumber: string, message: string, notificationId: number) {
      try {
        const smsService = SmsService.getInstance();
        
        // Log the SMS attempt
        await axios.post('/api/sms-logs', {
          notificationId,
          recipient: phoneNumber,
          message,
          status: 'PENDING',
          sentAt: new Date().toISOString()
        });
        
        // Send the SMS
        const result = await smsService.sendSms(phoneNumber, message);
        
        // Update the log with success
        await axios.put(`/api/sms-logs/${notificationId}`, {
          status: 'SENT',
          responseData: JSON.stringify(result)
        });
        
        return result;
      } catch (error) {
        console.error('Error sending SMS notification:', error);
        
        // Update the log with failure
        await axios.put(`/api/sms-logs/${notificationId}`, {
          status: 'FAILED',
          error: JSON.stringify(error)
        });
        
        throw error;
      }
    },

    async checkExpiringResidencies() {
      try {
        const response = await axios.get('/api/residencies/expiring');
        const expiringResidencies = response.data;

        for (const residency of expiringResidencies) {
          const daysUntilExpiration = this.calculateDaysUntilExpiration(residency.expirationDate);

          let template: NotificationTemplate | undefined;

          if (daysUntilExpiration === 30) {
            template = this.templates.find((t) => t.type === 'ONE_MONTH');
          } else if (daysUntilExpiration === 14) {
            template = this.templates.find((t) => t.type === 'TWO_WEEKS');
          } else if (daysUntilExpiration === 7) {
            template = this.templates.find((t) => t.type === 'ONE_WEEK');
          }

          if (template && template.isActive) {
            const message = this.formatMessage(template.messageTemplate, {
              username: residency.username,
              expirationDate: new Date(residency.expirationDate).toLocaleDateString('ar-SA')
            });

            await this.sendNotification({
              userId: residency.userId,
              username: residency.username,
              message,
              type: template.type,
              expirationDate: residency.expirationDate,
              status: 'PENDING',
              phoneNumber: residency.phoneNumber, // Include phone number for SMS
              smsSent: false
            });
          }
        }
      } catch (error) {
        console.error('Error checking expiring residencies:', error);
        this.error = 'حدث خطأ أثناء التحقق من الإقامات المنتهية';
      }
    },

    calculateDaysUntilExpiration(expirationDate: string): number {
      const today = new Date();
      const expiration = new Date(expirationDate);
      const diffTime = expiration.getTime() - today.getTime();
      return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    },

    formatMessage(template: string, data: { [key: string]: string }): string {
      return template.replace(/{(\w+)}/g, (match, key) => data[key] || match);
    },

    async updateTemplate(template: NotificationTemplate) {
      try {
        const response = await axios.put(`/api/notification-templates/${template.id}`, template);
        const updatedTemplate = response.data;
        const index = this.templates.findIndex((t) => t.id === updatedTemplate.id);
        if (index !== -1) {
          this.templates[index] = updatedTemplate;
        }
      } catch (error) {
        console.error('Error updating template:', error);
        throw new Error('حدث خطأ أثناء تحديث القالب');
      }
    },
    
    async resendFailedSms() {
      try {
        // Fetch failed SMS logs
        const response = await axios.get('/api/sms-logs?status=FAILED');
        const failedLogs = response.data;
        
        const smsService = SmsService.getInstance();
        let successCount = 0;
        
        for (const log of failedLogs) {
          try {
            // Attempt to resend the SMS
            const result = await smsService.sendSms(log.recipient, log.message);
            
            // Update the log status
            await axios.put(`/api/sms-logs/${log.id}`, {
              status: 'SENT',
              responseData: JSON.stringify(result),
              retries: (log.retries || 0) + 1
            });
            
            successCount++;
          } catch (error) {
            console.error(`Failed to resend SMS to ${log.recipient}:`, error);
            
            // Update retry count
            await axios.put(`/api/sms-logs/${log.id}`, {
              retries: (log.retries || 0) + 1
            });
          }
        }
        
        return {
          total: failedLogs.length,
          success: successCount,
          failed: failedLogs.length - successCount
        };
      } catch (error) {
        console.error('Error resending failed SMS:', error);
        throw new Error('حدث خطأ أثناء إعادة إرسال الرسائل النصية الفاشلة');
      }
    }
  }
});
