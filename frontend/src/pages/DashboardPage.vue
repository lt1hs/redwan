<template>
  <q-page class="q-pa-md">
    <div class="row q-col-gutter-md">
      <!-- Welcome Card -->
      <div class="col-12">
        <q-card class="welcome-card">
          <q-card-section class="row items-center q-pa-lg">
            <div class="col-12 col-md-8">
              <div class="welcome-title">مرحباً {{ userName }}</div>
              <div class="welcome-subtitle q-mt-sm">
                <q-icon name="o_calendar_today" size="18px" class="q-mr-xs" />
                {{ getCurrentDate() }}
                <span class="q-mx-md">|</span>
                <q-icon name="o_access_time" size="18px" class="q-mr-xs" />
                آخر تسجيل دخول: {{ getLastLoginTime() }}
              </div>
            </div>
            <div class="col-12 col-md-4 text-right q-gutter-sm">
              <q-btn 
                unelevated 
                color="white" 
                text-color="primary" 
                label="الملف الشخصي" 
                icon="o_person"
                class="welcome-btn"
              />
              <q-btn 
                outline 
                color="white" 
                label="المساعدة" 
                icon="o_help"
                class="welcome-btn-outline"
              />
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Quick Stats Cards -->
      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="stat-card stat-card-primary">
          <q-card-section class="q-pb-none">
            <div class="row items-center justify-between">
              <div>
                <div class="stat-label">إجمالي العملاء</div>
                <div class="stat-value">{{ loadingData ? '...' : stats.clients }}</div>
                <div class="stat-badge positive">
                  <q-icon name="o_trending_up" size="14px" />
                  <span>+{{ stats.newClients }} جديد</span>
                </div>
              </div>
              <q-avatar size="56px" color="primary-light" text-color="primary" icon="o_people" />
            </div>
          </q-card-section>
          <q-separator class="q-my-sm" />
          <q-card-actions class="q-px-md q-pb-md">
            <q-btn 
              flat 
              dense 
              size="sm" 
              label="عرض الكل" 
              icon-right="o_arrow_forward" 
              color="primary"
              :to="{ name: 'PassCardIndex' }"
              class="stat-action-btn"
            />
          </q-card-actions>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="stat-card stat-card-info">
          <q-card-section class="q-pb-none">
            <div class="row items-center justify-between">
              <div>
                <div class="stat-label">المعاملات النشطة</div>
                <div class="stat-value">{{ loadingData ? '...' : stats.activeTransactions }}</div>
                <div class="stat-badge info">
                  <q-icon name="o_pending" size="14px" />
                  <span>قيد التنفيذ</span>
                </div>
              </div>
              <q-avatar size="56px" color="info-light" text-color="info" icon="o_assignment" />
            </div>
          </q-card-section>
          <q-separator class="q-my-sm" />
          <q-card-actions class="q-px-md q-pb-md">
            <q-btn 
              flat 
              dense 
              size="sm" 
              label="إدارة المعاملات" 
              icon-right="o_arrow_forward" 
              color="info"
              :to="{ name: 'PassIndex' }"
              class="stat-action-btn"
            />
          </q-card-actions>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="stat-card stat-card-warning">
          <q-card-section class="q-pb-none">
            <div class="row items-center justify-between">
              <div>
                <div class="stat-label">إقامات منتهية قريباً</div>
                <div class="stat-value">{{ loadingData ? '...' : stats.expiringResidencies }}</div>
                <div class="stat-badge warning">
                  <q-icon name="o_warning" size="14px" />
                  <span>تحتاج متابعة</span>
                </div>
              </div>
              <q-avatar size="56px" color="warning-light" text-color="warning" icon="o_event" />
            </div>
          </q-card-section>
          <q-separator class="q-my-sm" />
          <q-card-actions class="q-px-md q-pb-md">
            <q-btn 
              flat 
              dense 
              size="sm" 
              label="عرض التفاصيل" 
              icon-right="o_arrow_forward" 
              color="warning"
              :to="{ name: 'ResidencyNotifications' }"
              class="stat-action-btn"
            />
          </q-card-actions>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="stat-card stat-card-accent">
          <q-card-section class="q-pb-none">
            <div class="row items-center justify-between">
              <div>
                <div class="stat-label">رصيد الرسائل</div>
                <div class="stat-value">
                  {{ loadingCredit ? '...' : stats.smsCredit }}
                  <q-btn 
                    v-if="!loadingCredit" 
                    flat 
                    round 
                    dense 
                    size="sm"
                    icon="o_refresh" 
                    @click="checkSmsCredit"
                    class="q-ml-xs"
                  >
                    <q-tooltip>تحديث الرصيد</q-tooltip>
                  </q-btn>
                </div>
                <div class="stat-badge accent">
                  <q-icon name="o_sms" size="14px" />
                  <span>رسالة متاحة</span>
                </div>
              </div>
              <q-avatar size="56px" color="accent-light" text-color="accent" icon="o_message" />
            </div>
          </q-card-section>
          <q-separator class="q-my-sm" />
          <q-card-actions class="q-px-md q-pb-md">
            <q-btn 
              flat 
              dense 
              size="sm" 
              label="إدارة الرسائل" 
              icon-right="o_arrow_forward" 
              color="accent"
              :to="{ name: 'SmsDashboard' }"
              class="stat-action-btn"
            />
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
                <q-tabs 
                  v-model="chartTab" 
                  class="text-grey" 
                  active-color="primary" 
                  indicator-color="primary" 
                  align="justify"
                  @update:model-value="onChartTabChange"
                >
                  <q-tab name="daily" label="يومي" icon="o_today" />
                  <q-tab name="weekly" label="أسبوعي" icon="o_date_range" />
                  <q-tab name="monthly" label="شهري" icon="o_calendar_month" />
                </q-tabs>

                <q-separator />

                <q-tab-panels v-model="chartTab" animated>
                  <q-tab-panel name="daily">
                    <div class="chart-container">
                      <div v-if="chartData && chartData.labels.length > 0" class="q-pa-md">
                        <div class="row q-col-gutter-sm q-mb-md">
                          <div 
                            v-for="(value, index) in chartData.datasets[0].data" 
                            :key="index"
                            class="col"
                          >
                            <div class="chart-bar-container">
                              <div 
                                class="chart-bar" 
                                :style="{ height: `${(value / Math.max(...chartData.datasets[0].data)) * 150}px` }"
                              >
                                <div class="chart-bar-value">{{ value }}</div>
                              </div>
                              <div class="chart-bar-label">{{ chartData.labels[index] }}</div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div v-else class="text-center text-grey q-pa-xl">
                        <q-icon name="o_bar_chart" size="50px" color="grey-4" />
                        <div class="q-mt-sm">لا توجد بيانات لعرضها</div>
                      </div>
                    </div>
                  </q-tab-panel>
                  
                  <q-tab-panel name="weekly">
                    <div class="chart-container">
                      <div v-if="chartData && chartData.labels.length > 0" class="q-pa-md">
                        <div class="row q-col-gutter-sm q-mb-md">
                          <div 
                            v-for="(value, index) in chartData.datasets[0].data" 
                            :key="index"
                            class="col"
                          >
                            <div class="chart-bar-container">
                              <div 
                                class="chart-bar" 
                                :style="{ height: `${(value / Math.max(...chartData.datasets[0].data)) * 150}px` }"
                              >
                                <div class="chart-bar-value">{{ value }}</div>
                              </div>
                              <div class="chart-bar-label">{{ chartData.labels[index] }}</div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div v-else class="text-center text-grey q-pa-xl">
                        <q-icon name="o_bar_chart" size="50px" color="grey-4" />
                        <div class="q-mt-sm">لا توجد بيانات لعرضها</div>
                      </div>
                    </div>
                  </q-tab-panel>
                  
                  <q-tab-panel name="monthly">
                    <div class="chart-container">
                      <div v-if="chartData && chartData.labels.length > 0" class="q-pa-md">
                        <div class="row q-col-gutter-sm q-mb-md">
                          <div 
                            v-for="(value, index) in chartData.datasets[0].data" 
                            :key="index"
                            class="col"
                          >
                            <div class="chart-bar-container">
                              <div 
                                class="chart-bar" 
                                :style="{ height: `${(value / Math.max(...chartData.datasets[0].data)) * 150}px` }"
                              >
                                <div class="chart-bar-value">{{ value }}</div>
                              </div>
                              <div class="chart-bar-label">{{ chartData.labels[index] }}</div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div v-else class="text-center text-grey q-pa-xl">
                        <q-icon name="o_bar_chart" size="50px" color="grey-4" />
                        <div class="q-mt-sm">لا توجد بيانات لعرضها</div>
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
            <q-item clickable :to="{ name: 'PassCardCreate' }" class="quick-action-item">
              <q-item-section avatar>
                <q-avatar color="primary" text-color="white" icon="o_person_add" />
              </q-item-section>
              <q-item-section>إضافة عميل جديد</q-item-section>
            </q-item>
            
            <q-item clickable :to="{ name: 'PassCreate' }" class="quick-action-item">
              <q-item-section avatar>
                <q-avatar color="secondary" text-color="white" icon="o_add_task" />
              </q-item-section>
              <q-item-section>إنشاء معاملة جديدة</q-item-section>
            </q-item>
            
            <q-item clickable :to="{ name: 'SmsTemplates' }" class="quick-action-item">
              <q-item-section avatar>
                <q-avatar color="accent" text-color="white" icon="o_message" />
              </q-item-section>
              <q-item-section>إدارة قوالب الرسائل</q-item-section>
            </q-item>
            
            <q-item clickable :to="{ name: 'ActivityLog' }" class="quick-action-item">
              <q-item-section avatar>
                <q-avatar color="positive" text-color="white" icon="o_summarize" />
              </q-item-section>
              <q-item-section>سجل النشاط</q-item-section>
            </q-item>
            
            <q-item clickable :to="{ name: 'UnfinishedPassportsIndex' }" class="quick-action-item">
              <q-item-section avatar>
                <q-avatar color="deep-purple" text-color="white" icon="o_draft" />
              </q-item-section>
              <q-item-section>الجوازات غير المكتملة</q-item-section>
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
                <q-item 
                  v-for="(activity, index) in recentActivities" 
                  :key="index" 
                  clickable
                  @click="navigateToActivity(activity)"
                  class="activity-item"
                >
                  <q-item-section avatar>
                    <q-avatar :color="activity.color" text-color="white">
                      <q-icon :name="activity.icon" />
                    </q-avatar>
                  </q-item-section>
                  <q-item-section>
                    <q-item-label class="text-weight-medium">{{ activity.title }}</q-item-label>
                    <q-item-label caption lines="1">{{ activity.description }}</q-item-label>
                  </q-item-section>
                  <q-item-section side>
                    <q-item-label caption class="text-grey-7">{{ activity.time }}</q-item-label>
                  </q-item-section>
                </q-item>
                
                <q-item v-if="recentActivities.length === 0 && !loadingData">
                  <q-item-section class="text-center text-grey q-py-md">
                    <q-icon name="o_inbox" size="48px" color="grey-4" />
                    <div class="q-mt-sm">لا توجد نشاطات حديثة</div>
                  </q-item-section>
                </q-item>
                
                <q-item v-if="loadingData">
                  <q-item-section class="text-center q-py-md">
                    <q-spinner color="primary" size="40px" />
                  </q-item-section>
                </q-item>
              </q-list>
              
              <div class="text-center q-mt-md">
                <q-btn flat color="primary" label="عرض المزيد" icon-right="o_arrow_forward" :to="{ name: 'ActivityLog' }" />
              </div>
            </q-tab-panel>

            <q-tab-panel name="transactions">
              <q-list separator>
                <q-item 
                  v-for="(transaction, index) in recentTransactions" 
                  :key="index" 
                  clickable
                  @click="navigateToTransaction(transaction)"
                  class="transaction-item"
                >
                  <q-item-section avatar>
                    <q-avatar :color="getStatusColor(transaction.status)" text-color="white">
                      <q-icon :name="getStatusIcon(transaction.status)" />
                    </q-avatar>
                  </q-item-section>
                  <q-item-section>
                    <q-item-label class="text-weight-medium">{{ transaction.title }}</q-item-label>
                    <q-item-label caption>{{ transaction.client }}</q-item-label>
                  </q-item-section>
                  <q-item-section side>
                    <q-chip 
                      :color="getStatusColor(transaction.status)" 
                      text-color="white" 
                      size="sm"
                      dense
                    >
                      {{ transaction.status }}
                    </q-chip>
                  </q-item-section>
                </q-item>
                
                <q-item v-if="recentTransactions.length === 0 && !loadingData">
                  <q-item-section class="text-center text-grey q-py-md">
                    <q-icon name="o_inbox" size="48px" color="grey-4" />
                    <div class="q-mt-sm">لا توجد معاملات حديثة</div>
                  </q-item-section>
                </q-item>
                
                <q-item v-if="loadingData">
                  <q-item-section class="text-center q-py-md">
                    <q-spinner color="primary" size="40px" />
                  </q-item-section>
                </q-item>
              </q-list>
              
              <div class="text-center q-mt-md">
                <q-btn flat color="primary" label="عرض المزيد" icon-right="o_arrow_forward" :to="{ name: 'PassIndex' }" />
              </div>
            </q-tab-panel>

            <q-tab-panel name="notifications">
              <q-list separator>
                <q-item v-for="(notification, index) in notifications" :key="index" class="notification-item">
                  <q-item-section avatar>
                    <q-avatar :color="notification.color" text-color="white" size="40px">
                      <q-icon :name="notification.icon" />
                    </q-avatar>
                  </q-item-section>
                  <q-item-section>
                    <q-item-label class="text-weight-medium">{{ notification.title }}</q-item-label>
                    <q-item-label caption lines="2">{{ notification.message }}</q-item-label>
                  </q-item-section>
                  <q-item-section side top>
                    <div class="column items-end">
                      <q-item-label caption class="text-grey-7 q-mb-xs">{{ notification.time }}</q-item-label>
                      <q-btn 
                        flat 
                        round 
                        dense 
                        size="sm" 
                        icon="o_close" 
                        @click.stop="dismissNotification(index)"
                        color="grey-7"
                      >
                        <q-tooltip>إزالة</q-tooltip>
                      </q-btn>
                    </div>
                  </q-item-section>
                </q-item>
                
                <q-item v-if="notifications.length === 0 && !loadingData">
                  <q-item-section class="text-center text-grey q-py-md">
                    <q-icon name="o_notifications_none" size="48px" color="grey-4" />
                    <div class="q-mt-sm">لا توجد إشعارات حالياً</div>
                  </q-item-section>
                </q-item>
                
                <q-item v-if="loadingData">
                  <q-item-section class="text-center q-py-md">
                    <q-spinner color="primary" size="40px" />
                  </q-item-section>
                </q-item>
              </q-list>
              
              <div class="text-center q-mt-md">
                <q-btn flat color="primary" label="عرض كل الإشعارات" icon-right="o_arrow_forward" :to="{ name: 'NotificationsHistory' }" />
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
import { ref, onMounted, computed } from 'vue';
import { useQuasar } from 'quasar';
import { useRouter } from 'vue-router';
import { SmsService } from '@/services/sms';
import { dashboardService, type DashboardStats, type Activity, type Transaction, type Notification } from '@/services/dashboard';
import { useAuthStore } from '@/stores/auth';

const $q = useQuasar();
const router = useRouter();
const smsService = SmsService.getInstance();
const authStore = useAuthStore();

// State
const chartTab = ref('daily');
const activityTab = ref('recent');
const scannerDialogOpen = ref(false);
const scanFile = ref(null);
const loadingCredit = ref(false);
const loadingData = ref(true);

const stats = ref<DashboardStats>({
  clients: 0,
  newClients: 0,
  activeTransactions: 0,
  expiringResidencies: 0,
  smsCredit: 0,
  totalPassports: 0,
  totalCards: 0,
  totalContracts: 0,
  deliveredPassports: 0,
  pendingPayments: 0
});

const recentActivities = ref<Activity[]>([]);
const recentTransactions = ref<Transaction[]>([]);
const notifications = ref<Notification[]>([]);
const chartData = ref<any>(null);

// Computed
const userName = computed(() => authStore.user?.name || 'المستخدم');

// Lifecycle
onMounted(async () => {
  await loadDashboardData();
});

// Methods
const loadDashboardData = async () => {
  loadingData.value = true;
  try {
    // Load all data in parallel
    const [statsData, activities, transactions, notifs, smsCredit] = await Promise.all([
      dashboardService.getStats(),
      dashboardService.getRecentActivities(10),
      dashboardService.getRecentTransactions(10),
      dashboardService.getNotifications(),
      checkSmsCredit()
    ]);

    stats.value = { ...statsData, smsCredit };
    recentActivities.value = activities;
    recentTransactions.value = transactions;
    notifications.value = notifs;

    // Load initial chart data
    await loadChartData('daily');
  } catch (error) {
    console.error('Error loading dashboard data:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ في تحميل بيانات لوحة التحكم',
      position: 'top'
    });
  } finally {
    loadingData.value = false;
  }
};

const loadChartData = async (period: 'daily' | 'weekly' | 'monthly') => {
  try {
    chartData.value = await dashboardService.getChartData(period);
  } catch (error) {
    console.error('Error loading chart data:', error);
  }
};

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
  const now = new Date();
  return now.toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit' });
};

const getStatusColor = (status: string) => {
  const statusMap: {[key: string]: string} = {
    'مكتملة': 'positive',
    'تم التسليم': 'positive',
    'قيد التنفيذ': 'info',
    'معلقة': 'warning',
    'جاري المراجعة': 'secondary',
    'ملغية': 'negative'
  };
  return statusMap[status] || 'grey';
};

const getStatusIcon = (status: string) => {
  const iconMap: {[key: string]: string} = {
    'مكتملة': 'o_check_circle',
    'تم التسليم': 'o_check_circle',
    'قيد التنفيذ': 'o_pending',
    'معلقة': 'o_hourglass_empty',
    'جاري المراجعة': 'o_rate_review',
    'ملغية': 'o_cancel'
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
    color: 'positive',
    position: 'top'
  });
};

const refreshActivity = async () => {
  $q.loading.show({ message: 'جاري تحديث النشاطات...' });
  try {
    await loadDashboardData();
    $q.notify({
      message: 'تم تحديث النشاطات بنجاح',
      icon: 'o_refresh',
      color: 'positive',
      position: 'top'
    });
  } catch (error) {
    $q.notify({
      message: 'حدث خطأ في التحديث',
      icon: 'o_error',
      color: 'negative',
      position: 'top'
    });
  } finally {
    $q.loading.hide();
  }
};

const checkSmsCredit = async () => {
  loadingCredit.value = true;
  try {
    const credit = await smsService.checkCredit();
    return credit;
  } catch (error) {
    console.error('Error checking SMS credit:', error);
    return 0;
  } finally {
    loadingCredit.value = false;
  }
};

const navigateToActivity = (activity: Activity) => {
  if (activity.type === 'passport') {
    router.push({ name: 'PassEdit', params: { id: activity.id } });
  } else if (activity.type === 'card') {
    router.push({ name: 'PassCardEdit', params: { id: activity.id } });
  }
};

const navigateToTransaction = (transaction: Transaction) => {
  router.push({ name: 'PassEdit', params: { id: transaction.id } });
};

const onChartTabChange = async (tab: string) => {
  await loadChartData(tab as 'daily' | 'weekly' | 'monthly');
};
</script>

<style scoped>
/* Welcome Card */
.welcome-card {
  border-radius: 16px;
  background: linear-gradient(135deg, #215562 0%, #1a4450 50%, #13333d 100%);
  color: white;
  box-shadow: 0 8px 24px rgba(33, 85, 98, 0.3);
  overflow: hidden;
  position: relative;
}

.welcome-card::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -10%;
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, rgba(38, 166, 154, 0.15) 0%, transparent 70%);
  border-radius: 50%;
}

.welcome-title {
  font-size: 28px;
  font-weight: 600;
  letter-spacing: -0.5px;
}

.welcome-subtitle {
  font-size: 14px;
  opacity: 0.95;
  display: flex;
  align-items: center;
}

.welcome-btn {
  border-radius: 8px;
  font-weight: 500;
  padding: 8px 20px;
}

.welcome-btn-outline {
  border-radius: 8px;
  font-weight: 500;
  padding: 8px 20px;
  border: 1.5px solid rgba(255, 255, 255, 0.5);
}

/* Stat Cards */
.stat-card {
  border-radius: 16px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid rgba(0, 0, 0, 0.06);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  overflow: hidden;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
}

.stat-label {
  font-size: 13px;
  color: #666;
  font-weight: 500;
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-value {
  font-size: 32px;
  font-weight: 700;
  color: #1a1a1a;
  line-height: 1.2;
  margin-bottom: 8px;
}

.stat-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
}

.stat-badge.positive {
  background: rgba(33, 186, 69, 0.1);
  color: #21ba45;
}

.stat-badge.info {
  background: rgba(49, 204, 236, 0.1);
  color: #31ccec;
}

.stat-badge.warning {
  background: rgba(242, 192, 55, 0.1);
  color: #f2c037;
}

.stat-badge.accent {
  background: rgba(38, 166, 154, 0.1);
  color: #26a69a;
}

.stat-action-btn {
  font-size: 12px;
  font-weight: 600;
  text-transform: none;
  letter-spacing: 0.3px;
}

/* Color variants for stat cards */
.stat-card-primary {
  border-top: 3px solid #215562;
}

.stat-card-info {
  border-top: 3px solid #31ccec;
}

.stat-card-warning {
  border-top: 3px solid #f2c037;
}

.stat-card-accent {
  border-top: 3px solid #26a69a;
}

/* Chart Card */
.dashboard-chart-card {
  border-radius: 16px;
  height: 100%;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  border: 1px solid rgba(0, 0, 0, 0.06);
}

.chart-container {
  min-height: 250px;
  width: 100%;
}

.chart-bar-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.chart-bar {
  width: 100%;
  max-width: 60px;
  background: linear-gradient(180deg, #215562 0%, #1a4450 100%);
  border-radius: 8px 8px 0 0;
  position: relative;
  transition: all 0.3s ease;
  min-height: 20px;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding-top: 8px;
}

.chart-bar:hover {
  background: linear-gradient(180deg, #26a69a 0%, #215562 100%);
  transform: scaleY(1.05);
}

.chart-bar-value {
  color: white;
  font-size: 12px;
  font-weight: 600;
}

.chart-bar-label {
  font-size: 11px;
  color: #666;
  text-align: center;
}

/* Actions Card */
.dashboard-actions-card {
  border-radius: 16px;
  height: 100%;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  border: 1px solid rgba(0, 0, 0, 0.06);
}

.quick-action-item {
  transition: all 0.2s ease;
  border-radius: 8px;
  margin: 4px 8px;
}

.quick-action-item:hover {
  background-color: rgba(33, 85, 98, 0.04);
  transform: translateX(-4px);
}

/* Activity Items */
.activity-item, .transaction-item, .notification-item {
  transition: all 0.2s ease;
  border-radius: 8px;
  margin: 4px 8px;
}

.activity-item:hover, .transaction-item:hover {
  background-color: rgba(33, 85, 98, 0.04);
  transform: translateX(-4px);
}

.notification-item {
  padding: 12px 16px;
}

/* Avatar colors */
:deep(.q-avatar) {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Custom color classes */
:global(.bg-primary-light) {
  background-color: rgba(33, 85, 98, 0.1) !important;
}

:global(.bg-info-light) {
  background-color: rgba(49, 204, 236, 0.1) !important;
}

:global(.bg-warning-light) {
  background-color: rgba(242, 192, 55, 0.1) !important;
}

:global(.bg-accent-light) {
  background-color: rgba(38, 166, 154, 0.1) !important;
}

/* Responsive adjustments */
@media (max-width: 1023px) {
  .welcome-title {
    font-size: 24px;
  }
  
  .stat-value {
    font-size: 28px;
  }
}

@media (max-width: 599px) {
  .welcome-title {
    font-size: 20px;
  }
  
  .stat-value {
    font-size: 24px;
  }
  
  .stat-card {
    margin-bottom: 8px;
  }
}

/* Loading state */
.q-spinner {
  margin: 0 auto;
}

/* Smooth transitions */
* {
  transition-property: background-color, transform, box-shadow;
  transition-duration: 0.2s;
  transition-timing-function: ease;
}
</style>
