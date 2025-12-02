import { api } from '@/boot/axios';

export interface DashboardStats {
  clients: number;
  newClients: number;
  activeTransactions: number;
  expiringResidencies: number;
  smsCredit: number;
  totalPassports: number;
  totalCards: number;
  totalContracts: number;
  deliveredPassports: number;
  pendingPayments: number;
}

export interface Activity {
  id: number;
  title: string;
  description: string;
  time: string;
  icon: string;
  color: string;
  type: string;
}

export interface Transaction {
  id: number;
  title: string;
  client: string;
  status: string;
  date: string;
  type: string;
}

export interface Notification {
  id: number;
  title: string;
  message: string;
  time: string;
  icon: string;
  color: string;
  read: boolean;
}

export interface ChartData {
  labels: string[];
  datasets: {
    label: string;
    data: number[];
    backgroundColor?: string;
    borderColor?: string;
  }[];
}

class DashboardService {
  private static instance: DashboardService;

  private constructor() {}

  static getInstance(): DashboardService {
    if (!DashboardService.instance) {
      DashboardService.instance = new DashboardService();
    }
    return DashboardService.instance;
  }

  async getStats(): Promise<DashboardStats> {
    try {
      const [passports, cards, contracts] = await Promise.all([
        api.get('/admin/passports'),
        api.get('/admin/cards/all'),
        api.get('/admin/contracts')
      ]);

      const passportData = passports.data.data || passports.data || [];
      const cardData = Array.isArray(cards.data) ? cards.data : (cards.data.data || cards.data || []);
      const contractData = contracts.data.data || contracts.data || [];

      // Calculate stats from real data
      const now = new Date();
      const thirtyDaysAgo = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);
      const ninetyDaysFromNow = new Date(now.getTime() + 90 * 24 * 60 * 60 * 1000);

      const newClients = cardData.filter((card: any) => {
        const createdAt = new Date(card.created_at);
        return createdAt >= thirtyDaysAgo;
      }).length;

      const activeTransactions = passportData.filter((passport: any) => 
        passport.passport_status === 'قيد التنفيذ' || passport.passport_status === 'جاري المراجعة'
      ).length;

      const expiringResidencies = cardData.filter((card: any) => {
        if (!card.residence_expiry_date) return false;
        const expiryDate = new Date(card.residence_expiry_date);
        return expiryDate <= ninetyDaysFromNow && expiryDate >= now;
      }).length;

      const deliveredPassports = passportData.filter((passport: any) => 
        passport.passport_status === 'مكتملة' || passport.passport_status === 'تم التسليم'
      ).length;

      const pendingPayments = passportData.filter((passport: any) => 
        passport.payment_status === 'pending' || passport.payment_status === 'معلق'
      ).length;

      return {
        clients: cardData.length,
        newClients,
        activeTransactions,
        expiringResidencies,
        smsCredit: 0, // Will be updated by SMS service
        totalPassports: passportData.length,
        totalCards: cardData.length,
        totalContracts: contractData.length,
        deliveredPassports,
        pendingPayments
      };
    } catch (error) {
      console.error('Error fetching dashboard stats:', error);
      throw error;
    }
  }

  async getRecentActivities(limit = 10): Promise<Activity[]> {
    try {
      const [passports, cards] = await Promise.all([
        api.get('/admin/passports', { params: { per_page: 20 } }),
        api.get('/admin/cards/all')
      ]);

      const passportData = passports.data.data || passports.data || [];
      const cardData = Array.isArray(cards.data) ? cards.data : (cards.data.data || cards.data || []);

      const activities: Activity[] = [];

      // Add passport activities
      passportData.slice(0, 5).forEach((passport: any) => {
        activities.push({
          id: passport.id,
          title: `معاملة جواز سفر - ${passport.transaction_type || 'تجديد'}`,
          description: `${passport.full_name} - ${passport.passport_number}`,
          time: this.formatRelativeTime(passport.updated_at || passport.created_at),
          icon: 'o_description',
          color: this.getStatusColor(passport.passport_status),
          type: 'passport'
        });
      });

      // Add card activities
      cardData.slice(0, 5).forEach((card: any) => {
        activities.push({
          id: card.id,
          title: 'تم إضافة عميل جديد',
          description: `${card.full_name_ar} - ${card.unique_code}`,
          time: this.formatRelativeTime(card.created_at),
          icon: 'o_person_add',
          color: 'primary',
          type: 'card'
        });
      });

      // Sort by time and limit
      return activities
        .sort((a, b) => this.parseRelativeTime(a.time) - this.parseRelativeTime(b.time))
        .slice(0, limit);
    } catch (error) {
      console.error('Error fetching recent activities:', error);
      return [];
    }
  }

  async getRecentTransactions(limit = 10): Promise<Transaction[]> {
    try {
      const response = await api.get('/admin/passports', { params: { per_page: limit } });
      const passportData = response.data.data || response.data || [];

      return passportData.map((passport: any) => ({
        id: passport.id,
        title: `${passport.transaction_type || 'تجديد جواز سفر'}`,
        client: passport.full_name,
        status: passport.passport_status || 'قيد التنفيذ',
        date: this.formatDate(passport.created_at),
        type: passport.transaction_type || 'passport'
      }));
    } catch (error) {
      console.error('Error fetching recent transactions:', error);
      return [];
    }
  }

  async getNotifications(): Promise<Notification[]> {
    try {
      const cards = await api.get('/admin/cards/all');
      const cardData = Array.isArray(cards.data) ? cards.data : (cards.data.data || cards.data || []);

      const notifications: Notification[] = [];
      const now = new Date();
      const thirtyDaysFromNow = new Date(now.getTime() + 30 * 24 * 60 * 60 * 1000);
      const ninetyDaysFromNow = new Date(now.getTime() + 90 * 24 * 60 * 60 * 1000);

      // Check for expiring residencies
      cardData.forEach((card: any) => {
        if (!card.residence_expiry_date) return;
        
        const expiryDate = new Date(card.residence_expiry_date);
        
        if (expiryDate <= thirtyDaysFromNow && expiryDate >= now) {
          notifications.push({
            id: card.id,
            title: 'إقامة قاربت على الانتهاء',
            message: `إقامة العميل ${card.full_name_ar} تنتهي خلال ${Math.ceil((expiryDate.getTime() - now.getTime()) / (1000 * 60 * 60 * 24))} يوم`,
            time: this.formatRelativeTime(card.residence_expiry_date),
            icon: 'o_warning',
            color: 'warning',
            read: false
          });
        } else if (expiryDate <= ninetyDaysFromNow && expiryDate > thirtyDaysFromNow) {
          notifications.push({
            id: card.id,
            title: 'تنبيه انتهاء إقامة',
            message: `إقامة العميل ${card.full_name_ar} تنتهي خلال ${Math.ceil((expiryDate.getTime() - now.getTime()) / (1000 * 60 * 60 * 24))} يوم`,
            time: this.formatRelativeTime(card.residence_expiry_date),
            icon: 'o_info',
            color: 'info',
            read: false
          });
        }
      });

      return notifications.slice(0, 10);
    } catch (error) {
      console.error('Error fetching notifications:', error);
      return [];
    }
  }

  async getChartData(period: 'daily' | 'weekly' | 'monthly'): Promise<ChartData> {
    try {
      const response = await api.get('/admin/passports');
      const passportData = response.data.data || response.data || [];

      const now = new Date();
      let labels: string[] = [];
      let data: number[] = [];

      if (period === 'daily') {
        // Last 7 days
        for (let i = 6; i >= 0; i--) {
          const date = new Date(now.getTime() - i * 24 * 60 * 60 * 1000);
          labels.push(date.toLocaleDateString('ar-SA', { weekday: 'short' }));
          
          const count = passportData.filter((p: any) => {
            const createdDate = new Date(p.created_at);
            return createdDate.toDateString() === date.toDateString();
          }).length;
          
          data.push(count);
        }
      } else if (period === 'weekly') {
        // Last 4 weeks
        for (let i = 3; i >= 0; i--) {
          const weekStart = new Date(now.getTime() - i * 7 * 24 * 60 * 60 * 1000);
          labels.push(`الأسبوع ${4 - i}`);
          
          const count = passportData.filter((p: any) => {
            const createdDate = new Date(p.created_at);
            const weekEnd = new Date(weekStart.getTime() + 7 * 24 * 60 * 60 * 1000);
            return createdDate >= weekStart && createdDate < weekEnd;
          }).length;
          
          data.push(count);
        }
      } else {
        // Last 6 months
        for (let i = 5; i >= 0; i--) {
          const date = new Date(now.getFullYear(), now.getMonth() - i, 1);
          labels.push(date.toLocaleDateString('ar-SA', { month: 'short' }));
          
          const count = passportData.filter((p: any) => {
            const createdDate = new Date(p.created_at);
            return createdDate.getMonth() === date.getMonth() && 
                   createdDate.getFullYear() === date.getFullYear();
          }).length;
          
          data.push(count);
        }
      }

      return {
        labels,
        datasets: [{
          label: 'المعاملات',
          data,
          backgroundColor: 'rgba(25, 118, 210, 0.2)',
          borderColor: 'rgba(25, 118, 210, 1)'
        }]
      };
    } catch (error) {
      console.error('Error fetching chart data:', error);
      return { labels: [], datasets: [] };
    }
  }

  private formatRelativeTime(dateString: string): string {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'الآن';
    if (diffMins < 60) return `منذ ${diffMins} دقيقة`;
    if (diffHours < 24) return `منذ ${diffHours} ساعة`;
    if (diffDays < 7) return `منذ ${diffDays} يوم`;
    return date.toLocaleDateString('ar-SA');
  }

  private parseRelativeTime(timeString: string): number {
    if (timeString === 'الآن') return 0;
    const match = timeString.match(/منذ (\d+)/);
    if (!match) return 999999;
    return parseInt(match[1]);
  }

  private formatDate(dateString: string): string {
    const date = new Date(dateString);
    return date.toLocaleDateString('ar-SA', { 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric' 
    });
  }

  private getStatusColor(status: string): string {
    const colorMap: { [key: string]: string } = {
      'مكتملة': 'positive',
      'تم التسليم': 'positive',
      'قيد التنفيذ': 'info',
      'معلقة': 'warning',
      'جاري المراجعة': 'secondary',
      'ملغية': 'negative'
    };
    return colorMap[status] || 'grey';
  }
}

export const dashboardService = DashboardService.getInstance();
