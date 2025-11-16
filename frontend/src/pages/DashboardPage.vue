<template>
  <q-page class="q-pa-md">
    <div class="row q-col-gutter-md">
      <!-- Welcome Card -->
      <div class="col-12">
        <q-card class="bg-primary text-white welcome-card">
          <q-card-section class="row items-center">
            <div class="col-12 col-md-8">
              <div class="text-h5">مرحباً بك في نظام إدارة Radwan</div>
              <div class="text-subtitle1 q-mt-sm">
                تاريخ اليوم: {{ getCurrentDate() }} | آخر تسجيل دخول: {{ getLastLoginTime() }}
              </div>
            </div>
            <div class="col-12 col-md-4 text-right">
              <q-btn outline color="white" label="الملف الشخصي" class="q-mr-sm" icon="o_person" />
              <q-btn outline color="white" label="المساعدة" icon="o_help" />
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Quick Stats Cards -->
      <div class="col-12 col-md-3">
        <q-card class="bg-positive dashboard-card">
          <q-card-section>
            <div class="text-subtitle2">إجمالي العملاء</div>
            <div class="row items-center">
              <div class="text-h4">{{ stats.clients }}</div>
              <q-chip color="white" text-color="positive" class="q-ml-sm">
                <q-icon name="o_trending_up" size="sm" />
                +{{ stats.newClients }}
              </q-chip>
            </div>
          </q-card-section>
          <q-separator dark />
          <q-card-actions>
            <q-btn flat label="عرض العملاء" icon="o_visibility" to="/clients" />
          </q-card-actions>
        </q-card>
      </div>

      <div class="col-12 col-md-3">
        <q-card class="bg-info dashboard-card">
          <q-card-section>
            <div class="text-subtitle2">المعاملات الحالية</div>
            <div class="row items-center">
              <div class="text-h4">{{ stats.activeTransactions }}</div>
              <q-icon name="o_assignment" class="q-ml-sm" size="md" />
            </div>
          </q-card-section>
          <q-separator dark />
          <q-card-actions>
            <q-btn flat label="إدارة المعاملات" icon="o_task" to="/transactions" />
          </q-card-actions>
        </q-card>
      </div>

      <div class="col-12 col-md-3">
        <q-card class="bg-warning dashboard-card text-white">
          <q-card-section>
            <div class="text-subtitle2">إقامات قاربت على الانتهاء</div>
            <div class="row items-center">
              <div class="text-h4">{{ stats.expiringResidencies }}</div>
              <q-icon name="o_warning" class="q-ml-sm" size="md" />
            </div>
          </q-card-section>
          <q-separator dark />
          <q-card-actions>
            <q-btn flat label="عرض التفاصيل" icon="o_event" to="/residencies/expiring" />
          </q-card-actions>
        </q-card>
      </div>

      <div class="col-12 col-md-3">
        <q-card class="bg-primary dashboard-card">
          <q-card-section>
            <div class="text-subtitle2">رصيد الرسائل النصية</div>
            <div class="row items-center">
              <div class="text-h4">{{ stats.smsCredit }}</div>
              <q-btn v-if="!loadingCredit" flat round dense icon="o_refresh" @click="checkSmsCredit" class="q-ml-sm">
                <q-tooltip>تحديث الرصيد</q-tooltip>
              </q-btn>
              <q-spinner v-else color="white" class="q-ml-sm" />
            </div>
          </q-card-section>
          <q-separator dark />
          <q-card-actions>
            <q-btn flat label="إعدادات الرسائل" icon="o_sms" to="/sms/settings" />
          </q-card-actions>
        </q-card>
      </div>

      <!-- Quick Actions & Charts -->
      <div class="col-12 col-md-8">
        <q-card class="dashboard-chart-card">
          <q-card-section>
            <div class="text-h6">نشاط النظام</div>
            <div class="row q-mt-md">
              <div class="col-12">
                <q-tabs v-model="chartTab" class="text-grey" active-color="primary" indicator-color="primary" align="justify">
                  <q-tab name="daily" label="يومي" />
                  <q-tab name="weekly" label="أسبوعي" />
                  <q-tab name="monthly" label="شهري" />
                </q-tabs>

                <q-separator />

                <q-tab-panels v-model="chartTab" animated>
                  <q-tab-panel name="daily">
                    <div class="chart-container">
                      <div class="text-center text-grey q-pa-xl">
                        <q-icon name="o_bar_chart" size="50px" color="grey-4" />
                        <div class="q-mt-sm">رسم بياني للنشاط اليومي (سيتم عرض البيانات هنا)</div>
                      </div>
                    </div>
                  </q-tab-panel>
                  
                  <q-tab-panel name="weekly">
                    <div class="chart-container">
                      <div class="text-center text-grey q-pa-xl">
                        <q-icon name="o_bar_chart" size="50px" color="grey-4" />
                        <div class="q-mt-sm">رسم بياني للنشاط الأسبوعي (سيتم عرض البيانات هنا)</div>
                      </div>
                    </div>
                  </q-tab-panel>
                  
                  <q-tab-panel name="monthly">
                    <div class="chart-container">
                      <div class="text-center text-grey q-pa-xl">
                        <q-icon name="o_bar_chart" size="50px" color="grey-4" />
                        <div class="q-mt-sm">رسم بياني للنشاط الشهري (سيتم عرض البيانات هنا)</div>
                      </div>
                    </div>
                  </q-tab-panel>
                </q-tab-panels>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Quick Actions Card -->
      <div class="col-12 col-md-4">
        <q-card class="dashboard-actions-card">
          <q-card-section>
            <div class="text-h6">إجراءات سريعة</div>
          </q-card-section>
          
          <q-list separator>
            <q-item clickable to="/clients/new" class="quick-action-item">
              <q-item-section avatar>
                <q-avatar color="primary" text-color="white" icon="o_person_add" />
              </q-item-section>
              <q-item-section>إضافة عميل جديد</q-item-section>
            </q-item>
            
            <q-item clickable to="/transactions/new" class="quick-action-item">
              <q-item-section avatar>
                <q-avatar color="secondary" text-color="white" icon="o_add_task" />
              </q-item-section>
              <q-item-section>إنشاء معاملة جديدة</q-item-section>
            </q-item>
            
            <q-item clickable to="/sms/templates" class="quick-action-item">
              <q-item-section avatar>
                <q-avatar color="accent" text-color="white" icon="o_message" />
              </q-item-section>
              <q-item-section>إدارة قوالب الرسائل</q-item-section>
            </q-item>
            
            <q-item clickable to="/reports" class="quick-action-item">
              <q-item-section avatar>
                <q-avatar color="positive" text-color="white" icon="o_summarize" />
              </q-item-section>
              <q-item-section>إنشاء تقرير</q-item-section>
            </q-item>
            
            <q-item clickable @click="openScannerDialog" class="quick-action-item">
              <q-item-section avatar>
                <q-avatar color="deep-purple" text-color="white" icon="o_qr_code_scanner" />
              </q-item-section>
              <q-item-section>مسح الوثائق</q-item-section>
            </q-item>
          </q-list>
        </q-card>
      </div>

      <!-- Recent Activity & Transactions -->
      <div class="col-12">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-h6">
              آخر النشاطات
              <q-btn flat round dense size="sm" icon="o_refresh" color="primary" class="q-ml-sm" @click="refreshActivity">
                <q-tooltip>تحديث</q-tooltip>
              </q-btn>
            </div>
          </q-card-section>

          <q-tabs
            v-model="activityTab"
            dense
            class="text-grey"
            active-color="primary"
            indicator-color="primary"
            align="justify"
            narrow-indicator
          >
            <q-tab name="recent" label="النشاطات الأخيرة" />
            <q-tab name="transactions" label="المعاملات" />
            <q-tab name="notifications" label="الإشعارات" />
          </q-tabs>

          <q-separator />

          <q-tab-panels v-model="activityTab" animated>
            <q-tab-panel name="recent">
              <q-list separator>
                <q-item v-for="(activity, index) in recentActivities" :key="index" class="activity-item">
                  <q-item-section avatar>
                    <q-avatar :color="activity.color" text-color="white">
                      <q-icon :name="activity.icon" />
                    </q-avatar>
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>{{ activity.title }}</q-item-label>
                    <q-item-label caption>{{ activity.description }}</q-item-label>
                  </q-item-section>
                  <q-item-section side>
                    <q-item-label caption>{{ activity.time }}</q-item-label>
                  </q-item-section>
                </q-item>
                
                <q-item v-if="recentActivities.length === 0">
                  <q-item-section class="text-center text-grey q-py-md">
                    لا توجد نشاطات حديثة
                  </q-item-section>
                </q-item>
              </q-list>
              
              <div class="text-center q-mt-md">
                <q-btn flat color="primary" label="عرض المزيد" icon-right="o_arrow_forward" to="/activities" />
              </div>
            </q-tab-panel>

            <q-tab-panel name="transactions">
              <q-list separator>
                <q-item v-for="(transaction, index) in recentTransactions" :key="index" class="transaction-item">
                  <q-item-section avatar>
                    <q-avatar :color="getStatusColor(transaction.status)" text-color="white">
                      <q-icon :name="getStatusIcon(transaction.status)" />
                    </q-avatar>
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>{{ transaction.title }}</q-item-label>
                    <q-item-label caption>{{ transaction.client }}</q-item-label>
                  </q-item-section>
                  <q-item-section side>
                    <q-chip :color="getStatusColor(transaction.status)" text-color="white" dense>
                      {{ transaction.status }}
                    </q-chip>
                  </q-item-section>
                </q-item>
                
                <q-item v-if="recentTransactions.length === 0">
                  <q-item-section class="text-center text-grey q-py-md">
                    لا توجد معاملات حديثة
                  </q-item-section>
                </q-item>
              </q-list>
              
              <div class="text-center q-mt-md">
                <q-btn flat color="primary" label="عرض المزيد" icon-right="o_arrow_forward" to="/transactions" />
              </div>
            </q-tab-panel>

            <q-tab-panel name="notifications">
              <q-list separator>
                <q-item v-for="(notification, index) in notifications" :key="index" class="notification-item">
                  <q-item-section avatar>
                    <q-avatar :color="notification.color" text-color="white">
                      <q-icon :name="notification.icon" />
                    </q-avatar>
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>{{ notification.title }}</q-item-label>
                    <q-item-label caption>{{ notification.message }}</q-item-label>
                  </q-item-section>
                  <q-item-section side>
                    <q-item-label caption>{{ notification.time }}</q-item-label>
                    <q-btn flat round dense size="sm" icon="o_close" @click="dismissNotification(index)">
                      <q-tooltip>إزالة</q-tooltip>
                    </q-btn>
                  </q-item-section>
                </q-item>
                
                <q-item v-if="notifications.length === 0">
                  <q-item-section class="text-center text-grey q-py-md">
                    لا توجد إشعارات حالياً
                  </q-item-section>
                </q-item>
              </q-list>
              
              <div class="text-center q-mt-md">
                <q-btn flat color="primary" label="عرض كل الإشعارات" icon-right="o_arrow_forward" to="/notifications" />
              </div>
            </q-tab-panel>
          </q-tab-panels>
        </q-card>
      </div>
    </div>

    <!-- Scanner Dialog -->
    <q-dialog v-model="scannerDialogOpen">
      <q-card style="width: 700px; max-width: 80vw;">
        <q-card-section class="row items-center">
          <div class="text-h6">مسح الوثائق</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <div class="text-center q-pa-md">
            <q-icon name="o_document_scanner" size="100px" color="primary" />
            <div class="text-h6 q-mt-md">الرجاء وضع المستند على الماسح الضوئي</div>
            <div class="text-subtitle2 q-mt-sm">أو قم بتحميل ملف</div>
            
            <div class="q-mt-lg">
              <q-file
                outlined
                v-model="scanFile"
                label="تحميل ملف"
                accept=".pdf,.jpg,.jpeg,.png"
                class="full-width"
              >
                <template v-slot:prepend>
                  <q-icon name="o_attach_file" />
                </template>
              </q-file>
            </div>
            
            <div class="row justify-center q-mt-lg">
              <q-btn color="primary" label="بدء المسح" icon="o_scanner" />
              <q-btn flat class="q-ml-sm" label="إلغاء" v-close-popup />
            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import { SmsService } from '@/services/sms';

const $q = useQuasar();
const smsService = SmsService.getInstance();

// State
const chartTab = ref('daily');
const activityTab = ref('recent');
const scannerDialogOpen = ref(false);
const scanFile = ref(null);
const loadingCredit = ref(false);

const stats = ref({
  clients: 152,
  newClients: 8,
  activeTransactions: 24,
  expiringResidencies: 7,
  smsCredit: 0
});

const recentActivities = ref([
  { 
    title: 'تم إضافة عميل جديد', 
    description: 'محمد أحمد - جواز سفر: A123456', 
    time: 'منذ 30 دقيقة', 
    icon: 'o_person_add', 
    color: 'primary' 
  },
  { 
    title: 'تم تجديد إقامة', 
    description: 'عبدالله محمد - رقم الإقامة: 2040598712', 
    time: 'منذ ساعتين', 
    icon: 'o_fact_check', 
    color: 'positive' 
  },
  { 
    title: 'طلب جديد', 
    description: 'طلب تجديد جواز سفر - علي خالد', 
    time: 'منذ 5 ساعات', 
    icon: 'o_description', 
    color: 'accent' 
  },
  { 
    title: 'إرسال رسائل تذكير', 
    description: 'تم إرسال 12 رسالة تذكير لانتهاء الإقامة', 
    time: 'اليوم 09:30', 
    icon: 'o_sms', 
    color: 'info' 
  },
]);

const recentTransactions = ref([
  { 
    title: 'تجديد إقامة', 
    client: 'سعيد محمد', 
    status: 'قيد التنفيذ',
    id: '10045'
  },
  { 
    title: 'إصدار تأشيرة زيارة', 
    client: 'فاطمة علي', 
    status: 'مكتملة',
    id: '10044'
  },
  { 
    title: 'نقل كفالة', 
    client: 'خالد عبدالرحمن', 
    status: 'معلقة',
    id: '10043'
  },
  { 
    title: 'تجديد جواز سفر', 
    client: 'أحمد محمود', 
    status: 'جاري المراجعة',
    id: '10042'
  },
]);

const notifications = ref([
  { 
    title: 'إقامة قاربت على الانتهاء', 
    message: 'إقامة العميل محمد أحمد تنتهي خلال 30 يوم', 
    time: 'اليوم', 
    icon: 'o_warning', 
    color: 'warning' 
  },
  { 
    title: 'معاملة مكتملة', 
    message: 'تم الانتهاء من معاملة نقل كفالة للعميل علي خالد', 
    time: 'البارحة', 
    icon: 'o_task_alt', 
    color: 'positive' 
  },
  { 
    title: 'رسالة جديدة', 
    message: 'لديك رسالة جديدة من الإدارة', 
    time: 'البارحة', 
    icon: 'o_mail', 
    color: 'primary' 
  },
]);

// Lifecycle
onMounted(async () => {
  await checkSmsCredit();
});

// Methods
const getCurrentDate = () => {
  const today = new Date();
  return today.toLocaleDateString('ar-SA', { 
    weekday: 'long', 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric'
  });
};

const getLastLoginTime = () => {
  return 'اليوم الساعة 09:15';
};

const getStatusColor = (status: string) => {
  const statusMap: {[key: string]: string} = {
    'مكتملة': 'positive',
    'قيد التنفيذ': 'info',
    'معلقة': 'warning',
    'جاري المراجعة': 'secondary'
  };
  return statusMap[status] || 'grey';
};

const getStatusIcon = (status: string) => {
  const iconMap: {[key: string]: string} = {
    'مكتملة': 'o_check_circle',
    'قيد التنفيذ': 'o_pending',
    'معلقة': 'o_hourglass_empty',
    'جاري المراجعة': 'o_rate_review'
  };
  return iconMap[status] || 'o_help';
};

const openScannerDialog = () => {
  scannerDialogOpen.value = true;
};

const dismissNotification = (index: number) => {
  notifications.value.splice(index, 1);
  $q.notify({
    message: 'تم إزالة الإشعار',
    icon: 'o_check',
    color: 'positive'
  });
};

const refreshActivity = () => {
  $q.loading.show({ message: 'جاري تحديث النشاطات...' });
  setTimeout(() => {
    $q.loading.hide();
    $q.notify({
      message: 'تم تحديث النشاطات',
      icon: 'o_refresh',
      color: 'positive'
    });
  }, 1000);
};

const checkSmsCredit = async () => {
  loadingCredit.value = true;
  try {
    const credit = await smsService.checkCredit();
    stats.value.smsCredit = credit;
  } catch (error) {
    console.error('Error checking SMS credit:', error);
  } finally {
    loadingCredit.value = false;
  }
};
</script>

<style scoped>
.welcome-card {
  border-radius: 10px;
  background: linear-gradient(135deg, var(--q-primary) 0%, #1976d2 100%);
}

.dashboard-card {
  border-radius: 10px;
  transition: all 0.3s ease;
}

.dashboard-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.dashboard-chart-card {
  border-radius: 10px;
  height: 100%;
}

.dashboard-actions-card {
  border-radius: 10px;
  height: 100%;
}

.chart-container {
  height: 300px;
  width: 100%;
}

.quick-action-item {
  transition: background-color 0.3s ease;
}

.quick-action-item:hover {
  background-color: rgba(0, 0, 0, 0.03);
}

.activity-item, .transaction-item, .notification-item {
  transition: background-color 0.3s ease;
}

.activity-item:hover, .transaction-item:hover, .notification-item:hover {
  background-color: rgba(0, 0, 0, 0.03);
}
</style>
