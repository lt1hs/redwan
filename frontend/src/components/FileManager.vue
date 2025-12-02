<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useQuasar } from 'quasar';

interface FileManagerProps {
  modelValue: boolean;
  onSelect?: (filePath: string) => void;
}

const props = defineProps<FileManagerProps>();
const emit = defineEmits(['update:modelValue', 'select']);

const $q = useQuasar();
const loading = ref(false);
const loadingFiles = ref(false);
const users = ref<any[]>([]);
const filteredUsers = ref<any[]>([]);
const searchQuery = ref('');
const selectedUser = ref<any>(null);
const userFiles = ref<any[]>([]);
const selectedImage = ref<string>('');
const previewImage = ref<string>('');
const showPreview = ref(false);

const uploadFiles = ref<File[]>([]);
const uploading = ref(false);

const imageTypes = [
  { label: 'صورة شخصية', value: 'personal' },
  { label: 'صورة الجواز', value: 'passport' },
  { label: 'صورة الإقامة', value: 'residence' },
  { label: 'صورة التمديد', value: 'extension' }
];

const previewImageFn = (file: any) => {
  previewImage.value = file.full_url;
  showPreview.value = true;
};

const selectFile = (file: any) => {
  emit('select', file.full_url);
};

const downloadImage = (file: any) => {
  const link = document.createElement('a');
  link.href = file.full_url;
  link.download = file.name;
  link.target = '_blank';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

const deleteImage = async (file: any) => {
  try {
    const response = await fetch(`http://91.109.114.156:8000/api/admin/unfinished-passports/${selectedUser.value.id}/delete-image`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        filename: file.name,
        type: file.type
      })
    });
    
    if (response.ok) {
      $q.notify({
        type: 'positive',
        message: 'تم حذف الملف بنجاح'
      });
      selectUser(selectedUser.value);
    } else {
      $q.notify({
        type: 'negative',
        message: 'خطأ في حذف الملف'
      });
    }
  } catch (error) {
    console.error('Error deleting image:', error);
    $q.notify({
      type: 'negative',
      message: 'خطأ في حذف الملف'
    });
  }
};

const uploadImages = async () => {
  if (!selectedUser.value || uploadFiles.value.length === 0) return;
  
  try {
    uploading.value = true;
    const formData = new FormData();
    
    uploadFiles.value.forEach((file, index) => {
      formData.append(`images[${index}]`, file);
    });
    
    const response = await fetch(`http://91.109.114.156:8000/api/admin/unfinished-passports/${selectedUser.value.id}/upload-images`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      },
      body: formData
    });
    
    if (response.ok) {
      $q.notify({
        type: 'positive',
        message: 'تم رفع الصور بنجاح'
      });
      uploadFiles.value = [];
      selectUser(selectedUser.value);
    } else {
      $q.notify({
        type: 'negative',
        message: 'خطأ في رفع الصور'
      });
    }
  } catch (error) {
    console.error('Error uploading images:', error);
    $q.notify({
      type: 'negative',
      message: 'خطأ في رفع الصور'
    });
  } finally {
    uploading.value = false;
  }
};

const changeImageType = async (file: any, newType: string) => {
  try {
    const response = await fetch(`http://91.109.114.156:8000/api/admin/unfinished-passports/${selectedUser.value.id}/change-image-type`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        filename: file.name,
        old_type: file.type,
        new_type: newType
      })
    });
    
    if (response.ok) {
      $q.notify({
        type: 'positive',
        message: 'تم تغيير نوع الصورة'
      });
      selectUser(selectedUser.value);
    }
  } catch (error) {
    console.error('Error changing image type:', error);
  }
};

const fetchUsers = async () => {
  try {
    loading.value = true;
    const token = localStorage.getItem('auth_token');
    
    if (!token) {
      $q.notify({
        type: 'negative',
        message: 'يجب تسجيل الدخول أولاً'
      });
      return;
    }
    
    const response = await fetch('http://91.109.114.156:8000/api/admin/unfinished-passports', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      users.value = Array.isArray(data) ? data : (data.data || []);
      filteredUsers.value = users.value;
    } else if (response.status === 401) {
      $q.notify({
        type: 'negative',
        message: 'انتهت صلاحية تسجيل الدخول'
      });
    } else {
      $q.notify({
        type: 'negative',
        message: 'خطأ في تحميل المستخدمين'
      });
    }
  } catch (error) {
    console.error('Error fetching users:', error);
    $q.notify({
      type: 'negative',
      message: 'خطأ في الاتصال بالخادم'
    });
  } finally {
    loading.value = false;
  }
};

const createUserFolders = async () => {
  try {
    const response = await fetch('http://91.109.114.156:8000/api/admin/unfinished-passports/create-folders', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Accept': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      $q.notify({
        type: 'positive',
        message: data.message
      });
    }
  } catch (error) {
    console.error('Error creating folders:', error);
    $q.notify({
      type: 'negative',
      message: 'خطأ في إنشاء المجلدات'
    });
  }
};

const selectUser = async (user: any) => {
  selectedUser.value = user;
  userFiles.value = [];
  loadingFiles.value = true;
  
  try {
    const token = localStorage.getItem('auth_token');
    if (!token) return;
    
    // First try to get files from the new folder structure
    const response = await fetch(`http://91.109.114.156:8000/api/admin/unfinished-passports/${user.id}/files`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      userFiles.value = data.files || [];
    }
    
    // If no files found in folder structure, fall back to database images
    if (userFiles.value.length === 0) {
      const images = [];
      if (user.personal_photo) {
        images.push({
          name: 'personal_photo',
          type: 'personal',
          url: user.personal_photo,
          full_url: user.personal_photo.startsWith('/storage/') ? 
            `http://91.109.114.156:8000${user.personal_photo}` : user.personal_photo
        });
      }
      if (user.passport_photo) {
        images.push({
          name: 'passport_photo',
          type: 'passport',
          url: user.passport_photo,
          full_url: user.passport_photo.startsWith('/storage/') ? 
            `http://91.109.114.156:8000${user.passport_photo}` : user.passport_photo
        });
      }
      if (user.residence_photo) {
        images.push({
          name: 'residence_photo',
          type: 'residence',
          url: user.residence_photo,
          full_url: user.residence_photo.startsWith('/storage/') ? 
            `http://91.109.114.156:8000${user.residence_photo}` : user.residence_photo
        });
      }
      if (user.passport_extension_photo) {
        images.push({
          name: 'passport_extension_photo',
          type: 'extension',
          url: user.passport_extension_photo,
          full_url: user.passport_extension_photo.startsWith('/storage/') ? 
            `http://91.109.114.156:8000${user.passport_extension_photo}` : user.passport_extension_photo
        });
      }
      userFiles.value = images;
    }
  } catch (error) {
    console.error('Error fetching user files:', error);
  } finally {
    loadingFiles.value = false;
  }
};

const selectImage = (file: any) => {
  selectedImage.value = file.full_url;
  emit('select', file.full_url);
  emit('update:modelValue', false);
};

const closeDialog = () => {
  emit('update:modelValue', false);
};

const getImageTypeLabel = (type: string) => {
  const labels = {
    'personal': 'صورة شخصية',
    'passport': 'صورة الجواز',
    'residence': 'صورة الإقامة',
    'extension': 'صورة التمديد'
  };
  return labels[type] || type;
};

const getImageTypeColor = (type: string) => {
  const colors = {
    'personal': 'blue',
    'passport': 'green',
    'residence': 'orange',
    'extension': 'purple'
  };
  return colors[type] || 'grey';
};

onMounted(() => {
  if (props.modelValue) {
    fetchUsers();
  }
});

watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    fetchUsers();
  }
});

watch(searchQuery, (newQuery) => {
  if (newQuery.trim() === '') {
    filteredUsers.value = users.value;
  } else {
    filteredUsers.value = users.value.filter(user => 
      user.full_name?.toLowerCase().includes(newQuery.toLowerCase()) ||
      user.phone_number?.includes(newQuery)
    );
  }
});
</script>

<template>
  <q-dialog :model-value="modelValue" @update:model-value="emit('update:modelValue', $event)" maximized>
    <q-card class="file-manager-card bg-grey-1">
      <q-card-section class="row items-center q-pb-none premium-header text-white shadow-4">
        <q-icon name="folder_open" size="md" class="q-mr-sm" />
        <div class="text-h5 text-weight-bold">مدير الملفات</div>
        <q-space />
        <q-btn
          color="white"
          flat
          icon="create_new_folder"
          @click="createUserFolders"
          title="إنشاء مجلدات المستخدمين"
          class="q-mr-sm premium-btn"
        >
          إنشاء مجلدات
        </q-btn>
        <q-btn icon="close" flat round dense @click="closeDialog" class="close-btn" />
      </q-card-section>

      <q-card-section class="q-pa-none" style="height: calc(100vh - 100px);">
        <div class="row no-wrap" style="height: 100%;">
          <!-- Users List -->
          <div class="col-4 q-pa-md users-panel">
            <q-card flat bordered class="full-height">
              <q-card-section class="q-pb-sm">
                <div class="row items-center q-mb-md">
                  <q-icon name="people" color="primary" size="sm" class="q-mr-sm" />
                  <div class="text-h6 text-weight-medium">المستخدمين</div>
                  <q-space />
                  <q-badge color="primary" class="text-weight-bold">{{ filteredUsers.length }}</q-badge>
                </div>
                
                <!-- Search Input -->
                <q-input
                  v-model="searchQuery"
                  outlined
                  dense
                  placeholder="البحث بالاسم أو رقم الهاتف"
                  class="search-input"
                >
                  <template v-slot:prepend>
                    <q-icon name="search" color="grey-6" />
                  </template>
                  <template v-slot:append>
                    <q-icon 
                      v-if="searchQuery" 
                      name="clear" 
                      class="cursor-pointer text-grey-6" 
                      @click="searchQuery = ''" 
                    />
                  </template>
                </q-input>
              </q-card-section>
              
              <q-separator />
              
              <q-card-section class="q-pa-none" style="height: calc(100vh - 220px); overflow-y: auto;">
                <q-list v-if="!loading" separator>
                  <q-item
                    v-for="user in filteredUsers"
                    :key="user.id"
                    clickable
                    @click="selectUser(user)"
                    :active="selectedUser?.id === user.id"
                    active-class="bg-blue-1 text-primary"
                    class="user-item"
                  >
                    <q-item-section avatar>
                      <q-avatar color="primary" text-color="white" size="md">
                        <q-icon name="person" />
                      </q-avatar>
                    </q-item-section>
                    <q-item-section>
                      <q-item-label class="text-weight-medium">{{ user.full_name || 'بدون اسم' }}</q-item-label>
                      <q-item-label caption class="text-grey-6">{{ user.phone_number || 'بدون رقم' }}</q-item-label>
                    </q-item-section>
                    <q-item-section side>
                      <q-badge color="grey-4" text-color="grey-8" class="text-weight-bold">{{ user.id }}</q-badge>
                    </q-item-section>
                  </q-item>
                </q-list>
                
                <div v-else class="text-center q-pa-xl text-grey-6">
                  <q-spinner size="60px" color="primary" />
                  <div class="text-h6 q-mt-md">جاري التحميل...</div>
                </div>
              </q-card-section>
            </q-card>
          </div>

          <!-- User Files -->
          <div class="col-8 q-pa-md">
            <q-card flat bordered class="full-height" v-if="selectedUser">
              <q-card-section class="q-pb-sm">
                <div class="row items-center justify-between">
                  <div class="row items-center">
                    <q-icon name="folder" color="orange" size="sm" class="q-mr-sm" />
                    <div class="text-h6 text-weight-medium">
                      ملفات المستخدم: {{ selectedUser.full_name }}
                    </div>
                    <q-badge color="secondary" class="q-ml-sm text-weight-bold">{{ userFiles.length }}</q-badge>
                  </div>
                  
                  <!-- Upload Section -->
                  <div class="row q-gutter-sm">
                    <q-file
                      v-model="uploadFiles"
                      multiple
                      accept="image/jpeg,image/jpg,image/png,application/pdf"
                      outlined
                      dense
                      label="اختر ملفات"
                      style="max-width: 200px"
                      class="upload-input"
                    >
                      <template v-slot:prepend>
                        <q-icon name="attach_file" />
                      </template>
                    </q-file>
                    
                    <q-btn
                      color="primary"
                      icon="cloud_upload"
                      :loading="uploading"
                      :disable="uploadFiles.length === 0"
                      @click="uploadImages"
                      class="upload-btn"
                    >
                      رفع الملفات
                    </q-btn>
                  </div>
                </div>
              </q-card-section>
              
              <q-separator />
              
              <q-card-section style="height: calc(100vh - 200px); overflow-y: auto;">
                <div v-if="userFiles.length > 0" class="row q-gutter-md">
                  <div
                    v-for="(file, index) in userFiles"
                    :key="index"
                    class="col-auto"
                  >
                    <q-card 
                      class="file-card cursor-pointer" 
                      @click="selectFile(file)"
                    >
                      <div class="file-preview">
                        <q-img
                          v-if="file.name.match(/\.(jpg|jpeg|png)$/i)"
                          :src="file.full_url"
                          style="height: 200px; width: 200px;"
                          fit="cover"
                          class="rounded-borders"
                        >
                          <template v-slot:error>
                            <div class="absolute-full flex flex-center bg-grey-3 text-grey-7">
                              <q-icon name="broken_image" size="50px" />
                            </div>
                          </template>
                        </q-img>
                        
                        <div v-else class="pdf-preview flex flex-center bg-red-1">
                          <q-icon name="picture_as_pdf" size="80px" color="red-6" />
                        </div>
                        
                        <div class="file-type-badge" @click.stop>
                          <q-select
                            :model-value="file.type"
                            :options="imageTypes"
                            option-label="label"
                            option-value="value"
                            emit-value
                            map-options
                            dense
                            borderless
                            :color="getImageTypeColor(file.type)"
                            @update:model-value="(newType) => changeImageType(file, newType)"
                            @click.stop
                            style="min-width: 100px"
                          >
                            <template v-slot:selected>
                              <q-badge :color="getImageTypeColor(file.type)" text-color="white" class="text-weight-bold">
                                {{ getImageTypeLabel(file.type) }}
                              </q-badge>
                            </template>
                          </q-select>
                        </div>
                      </div>
                      
                      <q-card-actions align="center" class="q-pa-sm">
                        <q-btn 
                          color="primary" 
                          flat 
                          size="sm"
                          icon="visibility"
                          @click.stop="previewImageFn(file)"
                          class="action-btn"
                        >
                          معاينة
                        </q-btn>
                        <q-btn 
                          color="green" 
                          flat 
                          size="sm"
                          icon="download"
                          @click.stop="downloadImage(file)"
                          class="action-btn"
                        >
                          تحميل
                        </q-btn>
                        <q-btn 
                          color="red" 
                          flat 
                          size="sm"
                          icon="delete"
                          @click.stop="deleteImage(file)"
                          class="action-btn"
                        >
                          حذف
                        </q-btn>
                      </q-card-actions>
                    </q-card>
                  </div>
                </div>
                
                <div v-else-if="loadingFiles" class="text-center q-pa-xl">
                  <q-spinner-dots size="50px" color="primary" />
                  <div class="text-h6 q-mt-md text-grey-7">جاري تحميل الملفات...</div>
                </div>
                
                <div v-else class="text-center q-pa-xl text-grey-6">
                  <q-icon name="image" size="100px" color="grey-4" />
                  <div class="text-h5 q-mt-md text-weight-light">لا توجد ملفات لهذا المستخدم</div>
                  <div class="text-body1 q-mt-sm">قم برفع ملفات جديدة باستخدام الزر أعلاه</div>
                </div>
              </q-card-section>
            </q-card>
            
            <q-card v-else flat bordered class="full-height flex flex-center">
              <div class="text-center text-grey-6">
                <q-icon name="folder_open" size="120px" color="grey-4" />
                <div class="text-h5 q-mt-md text-weight-light">اختر مستخدماً لعرض ملفاته</div>
                <div class="text-body1 q-mt-sm">اختر من قائمة المستخدمين على اليسار</div>
              </div>
            </q-card>
          </div>
        </div>
      </q-card-section>
    </q-card>
  </q-dialog>

  <!-- Image Preview Dialog -->
  <q-dialog v-model="showPreview" maximized>
    <q-card class="bg-black">
      <q-card-section class="row items-center q-pb-none">
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup color="white" />
      </q-card-section>
      <q-card-section class="flex flex-center" style="height: calc(100vh - 60px);">
        <q-img
          :src="previewImage"
          fit="contain"
          style="max-width: 100%; max-height: 100%;"
        />
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<style scoped>
.file-manager-card {
  height: 100vh !important;
}

.bg-gradient {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.users-panel {
  background: #fafafa !important;
}

.search-input {
  border-radius: 8px !important;
}

.user-item {
  border-radius: 8px !important;
  margin: 4px 8px !important;
  transition: all 0.2s ease !important;
}

.user-item:hover {
  background-color: #f5f5f5 !important;
  transform: translateX(4px) !important;
}

.file-card {
  transition: all 0.3s ease !important;
  border-radius: 12px !important;
  overflow: hidden !important;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
}

.file-card:hover {
  transform: translateY(-4px) !important;
  box-shadow: 0 8px 24px rgba(0,0,0,0.15) !important;
}

.file-card.selected {
  border: 3px solid #1976d2 !important;
  box-shadow: 0 0 0 2px rgba(25, 118, 210, 0.2) !important;
}

.file-preview {
  position: relative !important;
  height: 200px !important;
  width: 200px !important;
}

.pdf-preview {
  height: 200px !important;
  width: 200px !important;
  border-radius: 8px !important;
}

.file-type-badge {
  position: absolute !important;
  top: 8px !important;
  right: 8px !important;
  z-index: 10 !important;
}

.action-btn {
  border-radius: 6px !important;
  font-weight: 500 !important;
  transition: all 0.2s ease !important;
}

.action-btn:hover {
  transform: scale(1.05) !important;
}

.upload-input {
  border-radius: 8px !important;
}

.upload-btn {
  border-radius: 8px !important;
  font-weight: 600 !important;
  padding: 8px 16px !important;
}

.premium-header {
  background: linear-gradient(135deg, #215562 0%, #26a69a 100%) !important;
  color: white !important;
  padding: 16px !important;
}

.premium-header .q-icon,
.premium-header .text-h5 {
  color: white !important;
}
</style>
