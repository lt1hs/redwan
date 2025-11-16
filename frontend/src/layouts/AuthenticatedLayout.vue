<script setup lang="ts">
import { useQuasar } from 'quasar';
import { computed, ref } from 'vue';
import BaseDrawer from '@/components/BaseDrawer.vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';

const $q = useQuasar();
const router = useRouter();
const auth = useAuthStore();
const leftDrawerOpen = ref(false);
const globalSearchInput = ref('');

const menuItems = computed(() => [
  {
    icon: 'o_dashboard',
    label: 'لوحة التحكم',
    to: { name: 'dashboard' }
  },

  { label: 'الموقع' },

  {
    label: 'الجوازات',
    icon: 'o_feed',
    children: [
      {
        label: 'اضافة',
        to: { name: 'PassCreate' }
      },
      {
        label: 'قائمة الجوازات',
        to: { name: 'PassIndex' }
      },
      {
        label: 'الجوازات غير المكتملة',
        to: { name: 'UnfinishedPassportsIndex' }
      }
    ]
  },
  {
    label: 'البطاقات',
    icon: 'o_credit_card',
    children: [
      {
        label: 'اضافة بطاقة',
        to: { name: 'PassCardCreate' }
      },
      {
        label: 'قائمة البطاقات',
        to: { name: 'PassCardIndex' }
      }
    ]
  },
  {
    label: 'العقود',
    icon: 'o_article',
    children: [
      {
        label: 'اضافه',
        to: { name: 'ContractCreate' }
      },
      {
        label: 'قائمة العقود',
        to: { name: 'ContractIndex' }
      }
    ]
  },
  {
    label: 'الخطابات',
    icon: 'o_description',
    children: [
      {
        label: 'إنشاء خطاب',
        icon: 'o_add_circle_outline',
        to: { name: 'SpeechCreate' }
      },
      {
        label: 'قائمة الخطابات',
        icon: 'o_list',
        to: { name: 'SpeechIndex' }
      },
      {
        label: 'طباعة خطاب',
        icon: 'o_print',
        to: { name: 'SpeechPrint' }
      }
    ]
  },

 
  {
    label: 'نظام الرسائل النصية',
    icon: 'o_sms',
    children: [
      {
        label: 'لوحة التحكم',
        icon: 'o_dashboard',
        to: { name: 'SmsDashboard' }
      },
      {
        label: 'سجلات الرسائل',
        icon: 'o_history',
        to: { name: 'SmsLogs' }
      },
      {
        label: 'قوالب الرسائل',
        icon: 'o_format_quote',
        to: { name: 'SmsTemplates' }
      },
      {
        label: 'إعدادات النظام',
        icon: 'o_settings',
        to: { name: 'SmsSettings' }
      }
    ]
  },
  {
    label: 'سجل النشاط',
    icon: 'o_history',
    to: { name: 'ActivityLog' }
  },

  { label: 'النظام' },
  {
    label: 'المشرفين',
    icon: 'o_settings',
    children: [
      {
        label: 'السمة',
        to: { name: 'RolesIndex' }
      },
      {
        label: 'اضافة مشرف',
        to: { name: 'AdminsCreate' }
      },
      {
        label: 'قائمة المشرفين',
        to: { name: 'AdminsIndex' }
      }
    ]
  }
]);

function logout() {
  auth.logout().then(() => {
    router.push({ name: 'login' });
  });
}
</script>

<template>
  <q-layout view="rHr Lpr lff" v-if="auth.user">

    <q-header
      class="topbar"
      :style="{ paddingRight: leftDrawerOpen ? '280px' : '0' }"
    >
      <q-toolbar class="topbar-content">
        <q-btn
          flat
          dense
          round
          icon="menu"
          aria-label="Menu"
          @click="leftDrawerOpen = !leftDrawerOpen"
          class="menu-toggle"
        />

        <q-space />

        <q-input
          v-model="globalSearchInput"
          placeholder="البحث في النظام..."
          dense
          borderless
          class="search-bar"
        >
          <template v-slot:prepend>
            <q-icon name="search" size="20px" />
          </template>
        </q-input>

        <q-space />

        <div class="topbar-right">
          <q-btn
            flat
            round
            icon="notifications_none"
            size="md"
            class="topbar-btn"
          >
            <q-badge color="red" floating rounded>2</q-badge>
          </q-btn>

          <q-separator vertical inset class="q-mx-sm" />

          <div class="user-section">
            <q-avatar size="36px" class="user-avatar">
              <q-icon name="person" size="20px" />
            </q-avatar>
            <div class="user-info">
              <div class="user-name">{{ auth.user.name }}</div>
              <div class="user-role">مدير النظام</div>
            </div>
            <q-btn-dropdown flat dense class="user-dropdown">
              <q-list>
                <q-item clickable v-close-popup @click="router.push({ name: 'ProfileEdit' })">
                  <q-item-section avatar>
                    <q-icon name="person" />
                  </q-item-section>
                  <q-item-section>الملف الشخصي</q-item-section>
                </q-item>
                <q-item clickable v-close-popup>
                  <q-item-section avatar>
                    <q-icon name="settings" />
                  </q-item-section>
                  <q-item-section>الإعدادات</q-item-section>
                </q-item>
                <q-separator />
                <q-item clickable v-close-popup @click="logout">
                  <q-item-section avatar>
                    <q-icon name="logout" />
                  </q-item-section>
                  <q-item-section>تسجيل الخروج</q-item-section>
                </q-item>
              </q-list>
            </q-btn-dropdown>
          </div>
        </div>
      </q-toolbar>
    </q-header>

    <BaseDrawer
      v-model="leftDrawerOpen"
      :menuItems="menuItems"
      :userName="auth.user?.name || 'المستخدم'"
      :userRole="'مدير'"
    />

    <q-page-container>
      <router-view
        class="q-mx-auto q-px-lg"
        style="max-width: 1440px; padding-top: 32px; padding-bottom: 16px"
      />
    </q-page-container>
  </q-layout>
</template>

<style lang="scss">
.q-layout {
  background: #f5f5f5;
}

.topbar {
  background: #ffffff;
  border-bottom: 1px solid #e0e0e0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  width: 100vw !important;
  max-width: none !important;
  left: 0 !important;
  right: 0 !important;
  transition: padding-right 0.3s ease;

  .topbar-content {
    height: 70px;
    padding: 0 24px;
    max-width: none;
    width: 100%;

    .menu-toggle {
      color: #424242;
      
      &:hover {
        background: #f5f5f5;
      }
    }

    .search-bar {
      width: 350px;
      background: #f8f9fa;
      border-radius: 25px;
      padding: 8px 20px;

      :deep(.q-field__control) {
        background: transparent;
        border: none;
        padding: 0;
      }

      :deep(.q-field__native) {
        color: #424242;
        font-size: 14px;
      }

      :deep(.q-icon) {
        color: #757575;
      }
    }

    .topbar-right {
      display: flex;
      align-items: center;
      gap: 20px;

      .topbar-btn {
        color: #616161;
        
        &:hover {
          background: #f5f5f5;
        }
      }

      .user-section {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 16px;
        border-radius: 50px;
        transition: background 0.2s;

        &:hover {
          background: #f8f9fa;
        }

        .user-avatar {
          background: #2196f3;
          color: white;
          border: 2px solid #e0e0e0;
        }

        .user-info {
          text-align: right;

          .user-name {
            font-size: 14px;
            font-weight: 600;
            color: #212121;
            line-height: 1.2;
          }

          .user-role {
            font-size: 12px;
            color: #757575;
          }
        }

        .user-dropdown {
          color: #757575;
        }
      }
    }
  }
}

.q-drawer {
  .q-item {
    border-radius: 8px;
    margin: 0 8px;
  }

  .q-item--active {
    font-weight: 600;
  }
}
</style>
