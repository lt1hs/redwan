import { createRouter, createWebHashHistory, createWebHistory } from 'vue-router';
import SpeechForm from '@/views/Speech/SpeechForm.vue';

import { useAuthStore } from '@/stores/auth';

const router = createRouter({
  history: createWebHashHistory(import.meta.env.BASE_URL),
  // history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      meta: { title: 'لوحة التحكم', breadCrumbTitle: 'لوحة التحكم', breadCrumbIcon: 'o_home' },
      component: () => import('@/layouts/AuthenticatedLayout.vue'),
      children: [
        {
          path: '',
          component: () => import('@/pages/DashboardPage.vue'),
          name: 'dashboard'
        },
        // Activity Log section
        {
          path: '/activity-logs',
          meta: {
            title: 'سجل النشاط',
            breadCrumbTitle: 'سجل النشاط',
            breadCrumbIcon: 'o_history'
          },
          component: () => import('@/pages/ActivityLog.vue'),
          name: 'ActivityLog'
        },
        // Speeches section
        {
          path: '/speeches',
          meta: {
            title: 'الخطابات',
            breadCrumbTitle: 'الخطابات',
            breadCrumbIcon: 'o_description'
          },
          children: [
            {
              path: '',
              component: () => import('@/pages/SpeechIndex.vue'),
              name: 'SpeechIndex',
              meta: {
                title: 'قائمة الخطابات',
                breadCrumbTitle: 'قائمة الخطابات',
                breadCrumbIcon: 'o_list'
              }
            },
            {
              path: 'create',
              component: () => import('@/pages/SpeechCreate.vue'),
              name: 'SpeechCreate',
              meta: {
                title: 'إنشاء خطاب جديد',
                breadCrumbTitle: 'إنشاء خطاب',
                breadCrumbIcon: 'o_add_circle_outline',
                requiresAuth: true
              }
            },
            {
              path: ':id/edit',
              component: () => import('@/pages/SpeechEdit.vue'),
              name: 'SpeechEdit',
              meta: {
                title: 'تعديل الخطاب',
                breadCrumbTitle: 'تعديل خطاب',
                breadCrumbIcon: 'o_edit'
              },
              props: true
            },
            {
              path: ':id/print',
              component: () => import('@/pages/SpeechPrint.vue'),
              name: 'SpeechPrint',
              meta: {
                title: 'طباعة الخطاب',
                breadCrumbTitle: 'طباعة خطاب',
                breadCrumbIcon: 'o_print'
              },
              props: true
            }
          ]
        },
        // Website section
        {
          path: '/posts',
          meta: {
            breadCrumbTitle: 'الموقع',
            breadCrumbIcon: 'o_location_city'
          },
          children: [
            {
              path: '',
              component: () => import('@/pages/PostsIndex.vue'),
              name: 'PostsIndex'
            },
            {
              path: 'create',
              meta: {
                breadCrumbTitle: 'اضافة',
                breadCrumbIcon: 'o_add_circle_outline'
              },
              component: () => import('@/pages/PostsCreate.vue'),
              name: 'PostsCreate'
            },
            {
              path: ':id/edit',
              meta: {
                breadCrumbTitle: 'تعديل',
                breadCrumbIcon: 'o_edit'
              },
              component: () => import('@/pages/PostsEdit.vue'),
              name: 'PostsEdit',
              props: true
            },

            {
              path: 'categories',
              meta: {
                breadCrumbTitle: 'تصانيف',
                breadCrumbIcon: 'o_category',
                apiUrl: '/api/admin/posts/categories'
              },
              component: () => import('@/pages/CategoriesIndex.vue'),
              name: 'PostsCategoriesIndex'
            }
          ]
        },
        // Unfinished Passports section
        {
          path: '/unfinished-passports',
          meta: {
            breadCrumbTitle: 'الجوازات غير المكتملة',
            breadCrumbIcon: 'o_draft'
          },
          children: [
            {
              path: '',
              component: () => import('@/pages/UnfinishedPassportsIndex.vue'),
              name: 'UnfinishedPassportsIndex'
            },
            {
              path: 'create',
              meta: {
                breadCrumbTitle: 'إضافة جواز غير مكتمل',
                breadCrumbIcon: 'o_add_circle_outline'
              },
              component: () => import('@/pages/UnfinishedPassportForm.vue'),
              name: 'UnfinishedPassportCreate'
            },
            {
              path: ':id/edit',
              meta: {
                breadCrumbTitle: 'تعديل جواز غير مكتمل',
                breadCrumbIcon: 'o_edit'
              },
              component: () => import('@/pages/UnfinishedPassportForm.vue'),
              name: 'UnfinishedPassportEdit',
              props: true
            }
          ]
        },
        // Passports section
        {
          path: '/passports',
          meta: {
            breadCrumbTitle: 'الجوازات',
            breadCrumbIcon: 'o_location_city'
          },
          children: [
            {
              path: '',
              component: () => import('@/pages/PassIndex.vue'),
              name: 'PassIndex'
            },
            {
              path: 'create',
              meta: {
                breadCrumbTitle: 'اضافة',
                breadCrumbIcon: 'o_add_circle_outline'
              },
              component: () => import('@/pages/PassCreate.vue'),
              name: 'PassCreate'
            },
            {
              path: ':id/edit',
              meta: {
                breadCrumbTitle: 'تعديل',
                breadCrumbIcon: 'o_edit'
              },
              component: () => import('@/pages/PassEdit.vue'),
              name: 'PassEdit',
              props: true
            },
            // Add new route for passport delivery
            {
              path: ':id/delivery',
              meta: {
                breadCrumbTitle: 'تسليم',
                breadCrumbIcon: 'o_local_shipping'
              },
              component: () => import('@/pages/PassDelivery.vue'),
              name: 'PassDelivery',
              props: true // Add this line to pass route params as props
            },
            {
              path: 'delivered',
              meta: {
                breadCrumbTitle: 'الجوازات المسلمة',
                breadCrumbIcon: 'o_check_circle'
              },
              component: () => import('@/pages/PassDeliveredIndex.vue'),
              name: 'PassDeliveredIndex'
            },
            // Add new routes for passport cards
            {
              path: 'cards',
              meta: {
                breadCrumbTitle: 'البطاقات',
                breadCrumbIcon: 'o_credit_card'
              },
              component: () => import('@/pages/PassCardIndex.vue'),
              name: 'PassCardIndex'
            },
            {
              path: 'cards/create',
              meta: {
                breadCrumbTitle: 'إنشاء بطاقة',
                breadCrumbIcon: 'o_add_card'
              },
              component: () => import('@/pages/PassCardCreate.vue'),
              name: 'PassCardCreate'
            },
            {
              path: 'cards/:id/edit',
              meta: {
                breadCrumbTitle: 'تعديل بطاقة',
                breadCrumbIcon: 'o_edit'
              },
              component: () => import('@/pages/PassCardEdit.vue'),
              name: 'PassCardEdit',
              props: true
            },
            {
              path: 'cards/:id/family/add',
              meta: {
                breadCrumbTitle: 'إضافة عضو عائلة',
                breadCrumbIcon: 'o_group_add'
              },
              component: () => import('@/pages/PassCardFamilyAdd.vue'),
              name: 'PassCardFamilyAdd',
              props: true
            },
            {
              path: 'cards/:id/print',
              meta: {
                breadCrumbTitle: 'طباعة بطاقة',
                breadCrumbIcon: 'o_print'
              },
              component: () => import('@/pages/PassCardPrint.vue'),
              name: 'PassCardPrint',
              props: true
            },
            {
              path: ':id/letter-print',
              meta: {
                breadCrumbTitle: 'طباعة خطاب رسمي',
                breadCrumbIcon: 'o_description'
              },
              component: () => import('@/pages/PassLetterPrint.vue'), // Assuming this component will be created
              name: 'PassLetterPrint',
              props: true
            }
          ]
        },
        {
          path: '/contracts',
          meta: {
            breadCrumbTitle: 'عقود الزواج',
            breadCrumbIcon: 'o_description'
          },
          children: [
            {
              path: '',
              component: () => import('@/pages/ContractIndex.vue'),
              name: 'ContractIndex'
            },
            {
              path: 'create',
              meta: {
                breadCrumbTitle: 'إنشاء عقد',
                breadCrumbIcon: 'o_add_circle_outline'
              },
              component: () => import('@/pages/ContractCreate.vue'),
              name: 'ContractCreate'
            },
            {
              path: ':id/edit',
              meta: {
                breadCrumbTitle: 'تعديل عقد',
                breadCrumbIcon: 'o_edit'
              },
              component: () => import('@/pages/ContractEdit.vue'),
              name: 'ContractEdit',
              props: true
            },
            {
              path: ':id/print',
              meta: {
                breadCrumbTitle: 'طباعة عقد',
                breadCrumbIcon: 'o_print'
              },
              component: () => import('@/pages/ContractPrint.vue'),
              name: 'ContractPrint',
              props: true
            }
          ]
        },
        {
          path: '/videos',
          meta: {
            breadCrumbTitle: 'الفيديو',
            breadCrumbIcon: 'o_videocam'
          },
          children: [
            {
              path: '',
              component: () => import('@/pages/VideosIndex.vue'),
              name: 'VideosIndex'
            },
            {
              path: 'create',
              meta: {
                breadCrumbTitle: 'اضافة',
                breadCrumbIcon: 'o_add_circle_outline'
              },
              component: () => import('@/pages/VideosCreate.vue'),
              name: 'VideosCreate'
            },
            {
              path: ':id/edit',
              meta: {
                breadCrumbTitle: 'تعديل',
                breadCrumbIcon: 'o_edit'
              },
              component: () => import('@/pages/VideosEdit.vue'),
              name: 'VideosEdit',
              props: true
            },

            {
              path: 'categories',
              meta: {
                breadCrumbTitle: 'تصانيف',
                breadCrumbIcon: 'o_category',
                apiUrl: '/api/admin/videos/categories'
              },
              component: () => import('@/pages/CategoriesIndex.vue'),
              name: 'VideosCategoriesIndex'
            }
          ]
        },
        {
          path: '/pages',
          meta: {
            breadCrumbTitle: 'الصفحات',
            breadCrumbIcon: 'o_location_city'
          },
          children: [
            {
              path: '',
              component: () => import('@/pages/PagesIndex.vue'),
              name: 'PagesIndex'
            },
            {
              path: 'create',
              meta: {
                breadCrumbTitle: 'اضافة',
                breadCrumbIcon: 'o_add_circle_outline'
              },
              component: () => import('@/pages/PagesCreate.vue'),
              name: 'PagesCreate'
            },
            {
              path: ':id/edit',
              meta: {
                breadCrumbTitle: 'تعديل',
                breadCrumbIcon: 'o_edit'
              },
              component: () => import('@/pages/PagesEdit.vue'),
              name: 'PagesEdit',
              props: true
            }
          ]
        },
        {
          path: '/sliders',
          meta: {
            breadCrumbTitle: 'السلايدر',
            breadCrumbIcon: 'o_location_city'
          },
          children: [
            {
              path: '',
              component: () => import('@/pages/SlidersIndex.vue'),
              name: 'SlidersIndex'
            },
            {
              path: 'create',
              meta: {
                breadCrumbTitle: 'اضافة',
                breadCrumbIcon: 'o_add_circle_outline'
              },
              component: () => import('@/pages/SlidersCreate.vue'),
              name: 'SlidersCreate'
            },
            {
              path: ':id/edit',
              meta: {
                breadCrumbTitle: 'تعديل',
                breadCrumbIcon: 'o_edit'
              },
              component: () => import('@/pages/SlidersEdit.vue'),
              name: 'SlidersEdit',
              props: true
            }
          ]
        },
        {
          path: '/navigations',
          meta: {
            breadCrumbTitle: 'القائمة',
            breadCrumbIcon: 'o_location_city'
          },
          children: [
            {
              path: '',
              component: () => import('@/pages/NavigationsIndex.vue'),
              name: 'NavigationsIndex'
            },
            {
              path: 'create',
              meta: {
                breadCrumbTitle: 'اضافة',
                breadCrumbIcon: 'o_add_circle_outline'
              },
              component: () => import('@/pages/NavigationsCreate.vue'),
              name: 'NavigationsCreate'
            },
            {
              path: ':id/edit',
              meta: {
                breadCrumbTitle: 'تعديل',
                breadCrumbIcon: 'o_edit'
              },
              component: () => import('@/pages/NavigationsEdit.vue'),
              name: 'NavigationsEdit',
              props: true
            }
          ]
        },
        {
          meta: {
            breadCrumbTitle: 'اعدادات الموقع',
            breadCrumbIcon: 'o_settings'
          },
          path: '/website-settings',
          component: () => import('@/pages/WebsiteSettings.vue'),
          name: 'WebsiteSettings'
        },
        {
          path: '/notifications',
          meta: {
            breadCrumbTitle: 'نظام الرسائل والإشعارات',
            breadCrumbIcon: 'o_notifications'
          },
          children: [
            {
              path: '',
              component: () => import('@/pages/NotificationsIndex.vue'),
              name: 'NotificationsIndex',
              meta: {
                breadCrumbTitle: 'قائمة الإشعارات',
                breadCrumbIcon: 'o_list'
              }
            },
            {
              path: 'templates',
              component: () => import('@/pages/NotificationTemplates.vue'),
              name: 'NotificationTemplates',
              meta: {
                breadCrumbTitle: 'قوالب الإشعارات التلقائية',
                breadCrumbIcon: 'o_template_frame'
              }
            },
            {
              path: 'residency',
              component: () => import('@/pages/ResidencyNotifications.vue'),
              name: 'ResidencyNotifications',
              meta: {
                breadCrumbTitle: 'إشعارات الإقامة',
                breadCrumbIcon: 'o_assignment'
              }
            },
            {
              path: 'history',
              component: () => import('@/pages/NotificationsHistory.vue'),
              name: 'NotificationsHistory',
              meta: {
                breadCrumbTitle: 'سجل الإشعارات',
                breadCrumbIcon: 'o_history'
              }
            },
            {
              path: 'settings',
              component: () => import('@/pages/NotificationSettings.vue'),
              name: 'NotificationSettings',
              meta: {
                breadCrumbTitle: 'إعدادات الإشعارات',
                breadCrumbIcon: 'o_settings'
              }
            },
            {
              path: 'sms-templates',
              component: () => import('@/pages/SmsTemplatesPage.vue'),
              name: 'SmsTemplateManager',
              meta: {
                breadCrumbTitle: 'إدارة قوالب الرسائل النصية',
                breadCrumbIcon: 'o_sms'
              }
            }
          ]
        },
        // New SMS Management Section
        {
          path: '/sms',
          meta: {
            breadCrumbTitle: 'نظام الرسائل النصية',
            breadCrumbIcon: 'o_sms'
          },
          children: [
            {
              path: '',
              component: () => import('@/pages/SmsDashboardPage.vue'),
              name: 'SmsDashboard',
              meta: {
                breadCrumbTitle: 'لوحة تحكم الرسائل النصية',
                breadCrumbIcon: 'o_dashboard'
              }
            },
            {
              path: 'logs',
              component: () => import('@/pages/SmsLogsPage.vue'),
              name: 'SmsLogs',
              meta: {
                breadCrumbTitle: 'سجلات الرسائل النصية',
                breadCrumbIcon: 'o_history'
              }
            },
            {
              path: 'templates',
              component: () => import('@/pages/SmsTemplatesPage.vue'),
              name: 'SmsTemplates',
              meta: {
                breadCrumbTitle: 'قوالب الرسائل النصية',
                breadCrumbIcon: 'o_format_quote'
              }
            },
            {
              path: 'settings',
              component: () => import('@/pages/NotificationSettings.vue'),
              name: 'SmsSettings',
              meta: {
                breadCrumbTitle: 'إعدادات الرسائل النصية',
                breadCrumbIcon: 'o_settings'
              }
            }
          ]
        },
        // Users Management Section
        {
          path: '/users',
          meta: {
            breadCrumbTitle: 'إدارة المشرفين',
            breadCrumbIcon: 'o_people'
          },
          component: () => import('@/pages/UsersIndex.vue'),
          name: 'UsersIndex'
        },
        {
          path: '/all-users',
          meta: {
            breadCrumbTitle: 'جميع المستخدمين',
            breadCrumbIcon: 'o_group'
          },
          component: () => import('@/pages/AllUsersIndex.vue'),
          name: 'AllUsersIndex'
        },
        {
          path: 'roles',
          meta: {
            breadCrumbTitle: 'الأدوار',
            breadCrumbIcon: 'o_workspace_premium'
          },
          children: [
            {
              path: '',
              component: () => import('@/pages/RolesIndex.vue'),
              name: 'RolesIndex'
            },
            {
              path: 'create',
              meta: {
                breadCrumbTitle: 'اضافة',
                breadCrumbIcon: 'o_add_circle_outline'
              },
              component: () => import('@/pages/RolesCreate.vue'),
              name: 'RolesCreate'
            },
            {
              path: ':id/edit',
              meta: {
                breadCrumbTitle: 'تعديل',
                breadCrumbIcon: 'o_edit'
              },
              component: () => import('@/pages/RolesEdit.vue'),
              name: 'RolesEdit',
              props: true
            }
          ]
        },
        {
          path: 'admins',
          meta: {
            breadCrumbTitle: 'المشرفين',
            breadCrumbIcon: 'o_workspace_premium'
          },
          children: [
            {
              path: '',
              component: () => import('@/pages/AdminsIndex.vue'),
              name: 'AdminsIndex'
            },
            {
              path: 'create',
              meta: {
                breadCrumbTitle: 'Add',
                breadCrumbIcon: 'o_add_circle_outline'
              },
              component: () => import('@/pages/AdminsCreate.vue'),
              name: 'AdminsCreate'
            },
            {
              path: ':id/edit',
              meta: {
                breadCrumbTitle: 'تعديل',
                breadCrumbIcon: 'o_edit'
              },
              component: () => import('@/pages/AdminsEdit.vue'),
              name: 'AdminsEdit',
              props: true
            }
          ]
        },
        {
          path: '/profile',
          meta: {
            title: 'الملف الشخصي',
            breadCrumbTitle: 'الملف الشخصي',
            breadCrumbIcon: 'o_person'
          },
          component: () => import('@/pages/ProfilePage.vue'),
          name: 'Profile'
        }
      ]
    },
    {
      path: '/',
      component: () => import('@/layouts/GuestLayout.vue'),
      meta: { guest: true },
      children: [
        {
          path: '/login',
          component: () => import('@/pages/auth/LoginPage.vue'),
          name: 'login',
          meta: { title: 'تسجيل الدخول' }
        },
        {
          path: '/forgot-password',
          name: 'forgot-password',
          component: () => import('@/pages/auth/ForgotPassword.vue'),
          meta: { title: 'نسيت كلمة المرور' }
        },
        {
          path: '/reset-password/:token',
          name: 'reset-password',
          component: () => import('@/pages/auth/ResetPassword.vue'),
          meta: { title: 'إعادة تعيين كلمة المرور' }
        }

        // {
        //   path: "/settings",
        //   name: "settings.profile",
        //   component: SettingsProfile,
        // },
      ]
    },

    /// Always leave this as last one,
    // but you can also remove it
    {
      path: '/:catchAll(.*)*',
      component: () => import('@/pages/ErrorNotFound.vue'),
      meta: { title: 'الصفحة غير موجودة' }
    }
  ]
});

// Update ALL other routes with ID params to have props: true
const routesWithIdParams = [
  'PassCardEdit',
  'PassCardFamilyAdd',
  'PassCardPrint',
  'PassEdit',
  'PassDelivery',
  'ContractEdit',
  'ContractPrint',
  'VideosEdit',
  'PagesEdit',
  'SlidersEdit',
  'NavigationsEdit',
  'RolesEdit',
  'AdminsEdit'
];

// Add navigation guard to check auth status before each route change
router.beforeEach(async (to) => {
  // Clear previous title
  document.title = 'Reality360D';

  // Update document title
  if (to.meta && to.meta.title) {
    document.title = to.meta.title as string;
  }

  // Ensure all ID parameters are strings (fixes the "discarded invalid param" warning)
  if (to.params && to.params.id && typeof to.params.id !== 'string') {
    to.params.id = String(to.params.id);
  }

  // Check if the route requires authentication
  const publicPages = ['/login', '/forgot-password', '/reset-password'];
  const authRequired = !publicPages.includes(to.path);
  const auth = useAuthStore();

  // Try to fetch user information if we have a token but no user
  if (auth.token && !auth.user) {
    await auth.fetchUser();
  }

  // If the route requires auth and the user is not logged in, redirect to login
  if (authRequired && !auth.isLoggedIn) {
    auth.returnUrl = to.fullPath;
    return '/login';
  }
});

export default router;
