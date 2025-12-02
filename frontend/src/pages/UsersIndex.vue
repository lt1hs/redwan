<template>
  <q-page>
    <base-breadcrumbs />
    <div class="q-pa-md">
      <q-card flat bordered>
        <q-card-section>
          <div class="row items-center justify-between q-mb-md">
            <div class="text-h6 text-primary">إدارة المشرفين</div>
            <q-btn 
              v-if="canCreateUsers"
              color="primary" 
              label="إضافة مشرف جديد" 
              @click="showCreateDialog = true" 
            />
          </div>

          <q-table
            :rows="usersStore.users"
            :columns="columns"
            :loading="usersStore.loading"
            row-key="id"
            flat
            bordered
          >
            <template v-slot:body-cell-roles="props">
              <q-td :props="props">
                <q-chip 
                  v-for="role in props.row.roles" 
                  :key="role.id"
                  color="primary" 
                  text-color="white" 
                  size="sm"
                >
                  {{ getRoleLabel(role.name) }}
                </q-chip>
              </q-td>
            </template>

            <template v-slot:body-cell-actions="props">
              <q-td :props="props">
                <q-btn 
                  v-if="canAssignRoles"
                  flat 
                  round 
                  color="primary" 
                  icon="edit" 
                  @click="editUser(props.row)"
                />
                <q-btn 
                  v-if="canDeleteUsers && !props.row.roles.some(r => r.name === 'super-admin')"
                  flat 
                  round 
                  color="negative" 
                  icon="delete" 
                  @click="deleteUser(props.row)"
                />
              </q-td>
            </template>
          </q-table>
        </q-card-section>
      </q-card>
    </div>

    <!-- Create User Dialog -->
    <q-dialog v-model="showCreateDialog">
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">إضافة مشرف جديد</div>
        </q-card-section>

        <q-card-section>
          <q-form @submit="createUser">
            <q-input v-model="newUser.name" label="الاسم" required />
            <q-input v-model="newUser.email" label="البريد الإلكتروني" type="email" required />
            <q-input v-model="newUser.password" label="كلمة المرور" type="password" required />
            <q-select 
              v-model="newUser.role" 
              :options="roleOptions" 
              label="الدور" 
              required 
            />
          </q-form>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="إلغاء" @click="showCreateDialog = false" />
          <q-btn color="primary" label="إنشاء" @click="createUser" :loading="usersStore.loading" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Edit User Dialog -->
    <q-dialog v-model="showEditDialog">
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">تعديل المشرف</div>
        </q-card-section>

        <q-card-section>
          <q-select 
            v-model="selectedRole" 
            :options="roleOptions" 
            label="الدور" 
            required 
          />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="إلغاء" @click="showEditDialog = false" />
          <q-btn color="primary" label="حفظ" @click="assignRole" :loading="usersStore.loading" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useUsersStore } from '@/stores/users';
import { useAuthStore } from '@/stores/auth';
import { useQuasar } from 'quasar';

const usersStore = useUsersStore();
const authStore = useAuthStore();
const $q = useQuasar();

const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const selectedUser = ref(null);
const selectedRole = ref('');

const newUser = ref({
  name: '',
  email: '',
  password: '',
  role: ''
});

const columns = [
  { name: 'name', label: 'الاسم', field: 'name', align: 'left' },
  { name: 'email', label: 'البريد الإلكتروني', field: 'email', align: 'left' },
  { name: 'roles', label: 'الدور', field: 'roles', align: 'left' },
  { name: 'actions', label: 'الإجراءات', field: 'actions', align: 'center' }
];

const roleOptions = computed(() => [
  { label: 'مدير عام', value: 'super-admin' },
  { label: 'مدير', value: 'admin' },
  { label: 'موظف', value: 'employee' }
]);

const canCreateUsers = computed(() => 
  authStore.user?.roles?.includes('super-admin')
);

const canAssignRoles = computed(() => 
  authStore.user?.roles?.includes('super-admin') || authStore.user?.roles?.includes('admin')
);

const canDeleteUsers = computed(() => 
  authStore.user?.roles?.includes('super-admin')
);

const getRoleLabel = (roleName: string) => {
  const roleMap = {
    'super-admin': 'مدير عام',
    'admin': 'مدير', 
    'employee': 'موظف'
  };
  return roleMap[roleName] || roleName;
};

const createUser = async () => {
  try {
    await usersStore.createUser(newUser.value);
    showCreateDialog.value = false;
    newUser.value = { name: '', email: '', password: '', role: '' };
    $q.notify({ type: 'positive', message: 'تم إنشاء المشرف بنجاح' });
  } catch (error) {
    $q.notify({ type: 'negative', message: 'خطأ في إنشاء المشرف' });
  }
};

const editUser = (user: any) => {
  selectedUser.value = user;
  selectedRole.value = user.roles[0]?.name || '';
  showEditDialog.value = true;
};

const assignRole = async () => {
  try {
    await usersStore.assignRole(selectedUser.value.id, selectedRole.value);
    showEditDialog.value = false;
    $q.notify({ type: 'positive', message: 'تم تحديث الدور بنجاح' });
  } catch (error) {
    $q.notify({ type: 'negative', message: 'خطأ في تحديث الدور' });
  }
};

const deleteUser = async (user: any) => {
  $q.dialog({
    title: 'تأكيد الحذف',
    message: `هل أنت متأكد من حذف المشرف ${user.name}؟`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await usersStore.deleteUser(user.id);
      $q.notify({ type: 'positive', message: 'تم حذف المشرف بنجاح' });
    } catch (error) {
      $q.notify({ type: 'negative', message: 'خطأ في حذف المشرف' });
    }
  });
};

onMounted(() => {
  usersStore.fetchUsers();
  usersStore.fetchRoles();
});
</script>
