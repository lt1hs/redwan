import { Notify } from 'quasar';

export function useNotification() {
  function showSuccess(message: string) {
    Notify.create({
      type: 'positive',
      message,
      position: 'top',
      timeout: 2000
    });
  }

  function showError(message: string) {
    Notify.create({
      type: 'negative',
      message,
      position: 'top',
      timeout: 3000
    });
  }

  function showWarning(message: string) {
    Notify.create({
      type: 'warning',
      message,
      position: 'top',
      timeout: 3000
    });
  }

  function showInfo(message: string) {
    Notify.create({
      type: 'info',
      message,
      position: 'top',
      timeout: 2000
    });
  }

  return {
    showSuccess,
    showError,
    showWarning,
    showInfo
  };
} 