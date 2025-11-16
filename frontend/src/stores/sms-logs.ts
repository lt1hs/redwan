import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { api } from "@/utils/axios";
import { useQuasar } from 'quasar';
import { useHelper } from '@/composables/helper';
import { SmsService } from '@/services/sms';
import type { SmsLog, SmsCredit } from '@/services/sms';

export type { SmsLog };

export const useSmsLogsStore = defineStore('smsLogs', () => {
  // State
  const logs = ref<SmsLog[]>([]);
  const totalLogs = ref(0);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const smsService = SmsService.getInstance();
  
  // Local storage logs (for mock testing without backend)
  const getLocalStorageLogs = (): SmsLog[] => {
    const storedLogs = localStorage.getItem('sms_logs');
    return storedLogs ? JSON.parse(storedLogs) : [];
  };
  
  const saveLogsToLocalStorage = (logsToSave: SmsLog[]) => {
    localStorage.setItem('sms_logs', JSON.stringify(logsToSave));
  };
  
  // Computed properties
  const sentLogs = computed(() => logs.value.filter(log => log.status === 'SENT'));
  const failedLogs = computed(() => logs.value.filter(log => log.status === 'FAILED'));
  const pendingLogs = computed(() => logs.value.filter(log => log.status === 'PENDING'));

  // Actions
  const fetchLogs = async (params: any = {}) => {
    loading.value = true;
    error.value = null;
    
    try {
      // In a real implementation, we would call an API
      // For now, we'll just use local storage
      const localLogs = getLocalStorageLogs();
      
      // Apply filters if provided
      let filteredLogs = [...localLogs];
      
      if (params.status) {
        filteredLogs = filteredLogs.filter(log => log.status === params.status);
      }
      
      if (params.limit) {
        filteredLogs = filteredLogs.slice(0, params.limit);
      }
      
      // Sort by creation date (newest first)
      filteredLogs.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
      
      logs.value = filteredLogs;
      totalLogs.value = localLogs.length;
      
      return filteredLogs;
    } catch (e) {
      console.error('Error fetching logs:', e);
      error.value = 'Failed to fetch SMS logs';
      return [];
    } finally {
      loading.value = false;
    }
  };
  
  const logSms = async (smsData: Partial<SmsLog>) => {
    try {
      const newLog: SmsLog = {
        id: Date.now(),
        recipient: smsData.recipient || '',
        message: smsData.message || '',
        status: smsData.status || 'PENDING',
        created_at: new Date().toISOString(),
        error: smsData.error,
        type: smsData.type || 'manual'
      };
      
      // Add to local storage
      const localLogs = getLocalStorageLogs();
      localLogs.push(newLog);
      saveLogsToLocalStorage(localLogs);
      
      // Update state
      await fetchLogs();
      
      return newLog;
    } catch (e) {
      console.error('Error logging SMS:', e);
      error.value = 'Failed to log SMS';
      throw e;
    }
  };
  
  const retryFailedSms = async (id?: number) => {
    loading.value = true;
    error.value = null;
    
    try {
      const localLogs = getLocalStorageLogs();
      const logsToRetry = id 
        ? localLogs.filter(log => log.id === id && log.status === 'FAILED')
        : localLogs.filter(log => log.status === 'FAILED');
      
      if (logsToRetry.length === 0) {
        return { success: true, retried: 0 };
      }
      
      let retriedCount = 0;
      
      for (const log of logsToRetry) {
        try {
          // Try to send the SMS again
          const result = await smsService.sendSms(log.recipient, log.message, log.type);
          
          // Update the log
          const index = localLogs.findIndex(l => l.id === log.id);
          if (index !== -1) {
            localLogs[index] = {
              ...log,
              status: result.status,
              updated_at: new Date().toISOString(),
              retries: (log.retries || 0) + 1
            };
            retriedCount++;
          }
        } catch (e) {
          console.error(`Failed to retry SMS ${log.id}:`, e);
        }
      }
      
      // Save updated logs
      saveLogsToLocalStorage(localLogs);
      
      // Refresh logs in state
      await fetchLogs();
      
      return { success: true, retried: retriedCount };
    } catch (e) {
      console.error('Error retrying SMS:', e);
      error.value = 'Failed to retry SMS';
      return { success: false, retried: 0 };
    } finally {
      loading.value = false;
    }
  };
  
  const deleteLog = async (id: number) => {
    try {
      const localLogs = getLocalStorageLogs();
      const updatedLogs = localLogs.filter(log => log.id !== id);
      saveLogsToLocalStorage(updatedLogs);
      
      // Update state
      await fetchLogs();
      
      return true;
    } catch (e) {
      console.error('Error deleting log:', e);
      error.value = 'Failed to delete log';
      return false;
    }
  };
  
  const clearOldLogs = async (daysToKeep: number = 30) => {
    try {
      const localLogs = getLocalStorageLogs();
      const cutoffDate = new Date();
      cutoffDate.setDate(cutoffDate.getDate() - daysToKeep);
      
      const updatedLogs = localLogs.filter(log => {
        const logDate = new Date(log.created_at);
        return logDate >= cutoffDate;
      });
      
      saveLogsToLocalStorage(updatedLogs);
      
      // Update state
      await fetchLogs();
      
      return { success: true, deleted: localLogs.length - updatedLogs.length };
    } catch (e) {
      console.error('Error clearing old logs:', e);
      error.value = 'Failed to clear old logs';
      return { success: false, deleted: 0 };
    }
  };
  
  const clearError = () => {
    error.value = null;
  };

  return {
    // State
    logs,
    totalLogs,
    loading,
    error,
    
    // Computed
    sentLogs,
    failedLogs,
    pendingLogs,
    
    // Actions
    fetchLogs,
    logSms,
    retryFailedSms,
    deleteLog,
    clearOldLogs,
    clearError
  };
}); 