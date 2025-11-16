import { useNotificationStore } from '@/stores/notification';

class NotificationScheduler {
  private store = useNotificationStore();
  private intervalId: number | null = null;

  start() {
    // Check for expiring residencies every day at 9:00 AM
    const now = new Date();
    const nextRun = new Date(
      now.getFullYear(),
      now.getMonth(),
      now.getDate(),
      9, // 9 AM
      0,
      0
    );

    if (now > nextRun) {
      nextRun.setDate(nextRun.getDate() + 1);
    }

    const timeUntilNextRun = nextRun.getTime() - now.getTime();

    // Initial run after delay
    setTimeout(() => {
      this.checkExpiringResidencies();

      // Then run every 24 hours
      this.intervalId = window.setInterval(
        () => {
          this.checkExpiringResidencies();
        },
        24 * 60 * 60 * 1000
      );
    }, timeUntilNextRun);
  }

  stop() {
    if (this.intervalId !== null) {
      clearInterval(this.intervalId);
      this.intervalId = null;
    }
  }

  private async checkExpiringResidencies() {
    try {
      await this.store.checkExpiringResidencies();
    } catch (error) {
      console.error('Error checking expiring residencies:', error);
    }
  }
}

export const notificationScheduler = new NotificationScheduler();
