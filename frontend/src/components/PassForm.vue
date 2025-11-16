<script setup lang="ts">
import { ref, watch, computed, defineProps, defineEmits } from 'vue';
import { usePassportsStore } from '@/stores/passports';
import { smsService } from '@/services/smsService';
import moment from 'moment-jalaali';
import { useQuasar } from 'quasar';
import { QFile } from 'quasar';

// @ts-ignore
import VueSignaturePad from 'vue-signature-pad';

const passports = usePassportsStore();
const $q = useQuasar();

// Define initial data for the form
const getInitialData = (): PassportFormData => ({
  full_name: '',
  nationality: '',
  passport_number: '',
  date_of_birth: '',
  residence_expiry_date: '',
  phone_number: '',
  mobile_number: '',
  passport_status: 'قيد الانجاز',
  passport_delivery_date: '',
  transaction_type: '',
  payment_status: { label: 'في انتظار الدفع', value: 'pending' },
  address: '',
  zipcode: '',
  delivered_by: '',
  personal_photo: null,
  passport_photo: null,
  unique_code: '',
  sponsor_name: '',
  relationship: '',
  extension_reason: '',
  barcode: '',
  signature_data: '',
  no_signature: false,
  no_signature_reason: '',
  email: ''
});

const form = ref<PassportFormData>(getInitialData());
const formRef = ref<any>(null);
const fetchLoading = ref(false);
const personalPhotoInput = ref<any>(null);
const passportPhotoInput = ref<any>(null);
const signaturePad = ref<any>(null); // Commented out for now due to Vue 3 incompatibility
const barcodeInput = ref<any>(null);
const transactionTypeOptions = ref([
  'اصدار اقامه',
  'اصدار اقامه وخروج مكرر',
  'تجديد اقامه وخروج مكرر',
  'تجديد اقامه',
  'تمديد فيزا',
  'خروج مكرر',
  'خروج قطعي',
  'أخرى'
]);

// Add this helper function near the top of the script
const isFileObject = (value: any): boolean => {
  return value && typeof value === 'object' && 'type' in value && 'name' in value;
};

// Add these computed properties
const personalPhotoUrl = computed(() => {
  const photo = form.value.personal_photo;
  if (!photo) return '';
  return typeof photo === 'string' ? photo : isFileObject(photo) ? URL.createObjectURL(photo) : '';
});

const passportPhotoUrl = computed(() => {
  const photo = form.value.passport_photo;
  if (!photo) return '';
  return typeof photo === 'string' ? photo : isFileObject(photo) ? URL.createObjectURL(photo) : '';
});

// Reset the form to initial data
function onReset() {
  form.value = getInitialData();
}

// Props for the component
const props = defineProps({
  submitLoading: {
    type: Boolean,
    default: false
  },
  id: {
    type: Number,
    required: false
  },
  readonly: {
    type: Boolean,
    default: false
  },
  showDeliveryOptions: {
    type: Boolean,
    default: false
  }
});

// Fetch passport details if an ID is provided
async function fetch() {
  if (!props.id) {
    if (formRef.value) formRef.value.reset();
    return;
  }

  fetchLoading.value = true;
  form.value = await passports.fetchById(props.id);
  fetchLoading.value = false;
}

// Watch for changes to the ID prop and fetch data
watch(
  () => props.id,
  () => {
    fetch();
  },
  { immediate: true }
);

// Update the conversion functions for Persian calendar
const convertToPersian = (gregorianDate: string) => {
  if (!gregorianDate) return '';
  try {
    return moment(gregorianDate, 'YYYY-MM-DD').format('jYYYY/jMM/jDD');
  } catch (error) {
    console.error('Persian conversion error:', error);
    return '';
  }
};

const convertToGregorian = (persianDate: string) => {
  if (!persianDate) return '';
  try {
    return moment(persianDate, 'jYYYY/jMM/jDD').format('YYYY-MM-DD');
  } catch (error) {
    console.error('Gregorian conversion error:', error);
    return '';
  }
};

// Update the watch effect for residence_expiry_date
watch(
  () => form.value.residence_expiry_date,
  (newValue, oldValue) => {
    if (newValue && newValue !== oldValue && !newValue.includes('/')) {
      form.value.residence_expiry_date = convertToPersian(newValue);
    }
  }
);

// Update the handleDateChange function
const handleDateChange = (val: string, field: string) => {
  if (val) {
    try {
      if (field === 'residence_expiry_date' || field === 'passport_delivery_date') {
        // Convert to Persian for residence_expiry_date and passport_delivery_date
        if (!val.includes('/')) {
          form.value[field] = convertToPersian(val);
        }
      } else {
        // Keep Gregorian format for other dates
        form.value[field] = moment(val).format('YYYY-MM-DD');
      }
    } catch (error) {
      console.error('Date conversion error:', error);
    }
  }
};

// Add unique code generation function
const generateUniqueCode = () => {
  const timestamp = Date.now();
  const randomPart = Math.random().toString(36).substring(2, 8).toUpperCase();
  const datePart = new Date(timestamp).toISOString().slice(2, 10).replace(/-/g, '');
  const sequencePart = Math.floor(Math.random() * 10000)
    .toString()
    .padStart(4, '0');

  // Format: PC-YYMMDD-RANDOM-SEQ
  // PC: Prefix
  // YYMMDD: Date part
  // RANDOM: Random alphanumeric string
  // SEQ: Sequential number
  return `PC-${datePart}-${randomPart}-${sequencePart}`;
};

// Define the type for file inputs
type FileInputValue = File | string | null;

interface PassportFormData {
  full_name: string;
  nationality: string;
  passport_number: string;
  date_of_birth: string;
  residence_expiry_date: string;
  phone_number: string;
  mobile_number: string;
  passport_status: string;
  passport_delivery_date: string;
  transaction_type: string;
  payment_status: { label: string; value: 'pending' | 'paid' | 'cancelled' };
  address: string;
  zipcode: string;
  delivered_by: string;
  personal_photo: any;
  passport_photo: any;
  personal_photo_url?: string;
  passport_photo_url?: string;
  unique_code: string;
  sponsor_name: string;
  relationship: string;
  extension_reason: string;
  barcode: string;
  signature_data: string;
  no_signature: boolean;
  no_signature_reason: string;
  email: string;
  [key: string]: any; // Allow string indexing
}

// Update prepareFormForSubmission function
const prepareFormForSubmission = () => {
  const formData = new FormData();

  // Pre-process payment status to ensure it's a valid value
  let paymentStatusValue = 'pending'; // Default
  if (form.value.payment_status) {
    if (typeof form.value.payment_status === 'object' && form.value.payment_status !== null) {
      if ('value' in form.value.payment_status) {
        paymentStatusValue = form.value.payment_status.value;
      }
    } else if (typeof form.value.payment_status === 'string') {
      paymentStatusValue = form.value.payment_status;
    }
  }
  // Validate payment status
  if (!['pending', 'paid', 'cancelled'].includes(paymentStatusValue)) {
    console.warn(`Invalid payment_status value: ${paymentStatusValue}, defaulting to 'pending'`);
    paymentStatusValue = 'pending';
  }
  console.log(`Payment status processed: ${paymentStatusValue}`);

  // Handle all form fields - log each field for debugging
  Object.entries(form.value).forEach(([key, value]) => {
    console.log(`Processing field ${key}:`, value);

    if (value !== null && value !== undefined) {
      // Special handling for payment_status - use our pre-processed value
      if (key === 'payment_status') {
        formData.append(key, paymentStatusValue);
      }
      // Handle file fields
      else if (value instanceof File) {
        formData.append(key, value);
        console.log(`Added file ${key}:`, value.name);
      }
      // Handle dates
      else if (key === 'residence_expiry_date' || key === 'passport_delivery_date') {
        const dateValue = String(value).includes('/')
          ? convertToGregorian(String(value))
          : String(value);
        formData.append(key, dateValue);
        console.log(`Converted date ${key}:`, dateValue);
      }
      // Handle boolean fields
      else if (typeof value === 'boolean') {
        formData.append(key, value ? '1' : '0'); // Convert boolean to '1' or '0' string
      }
      // Handle other fields
      else {
        formData.append(key, String(value));
      }
    }
  });

  // Generate unique code if not present
  if (!form.value.unique_code) {
    const uniqueCode = generateUniqueCode();
    formData.append('unique_code', uniqueCode);
    form.value.unique_code = uniqueCode;
    console.log('Generated unique code:', uniqueCode);
  }

  // Log the processed data for debugging
  const formDataObj = Object.fromEntries(formData.entries());
  console.log('Form data being submitted:', formDataObj);

  // Verify required fields
  const requiredFields = [
    'full_name',
    'nationality',
    'passport_number',
    'date_of_birth',
    'residence_expiry_date',
    'mobile_number',
    'passport_delivery_date',
    'transaction_type',
    'payment_status',
    'address',
    'delivered_by'
  ];

  const missingFields = requiredFields.filter((field) => !formData.get(field));
  if (missingFields.length > 0) {
    console.error('Missing required fields:', missingFields);
  } else {
    console.log('All required fields are present');
  }

  return formData;
};

// Add the submit handler
const emit = defineEmits(['submit']);

const onSubmit = async () => {
  try {
    const formData = prepareFormForSubmission();

    // At this point the payment_status has been validated in prepareFormForSubmission
    // Log the final form data for debugging
    console.log('Final form data being submitted:', Object.fromEntries(formData.entries()));

    emit('submit', formData);
  } catch (error) {
    console.error('Form submission error:', error);
    $q.notify({
      type: 'negative',
      message: 'Error submitting form. Please try again.'
    });
  }
};

// Update setFormData method to handle unique code
const setFormData = (data: any) => {
  console.log('Setting form data:', data);

  // Create a new form data object with all fields
  const newFormData = {
    ...getInitialData(), // Start with default values
    ...data, // Spread the incoming data
    // Handle dates
    date_of_birth: data.date_of_birth ? data.date_of_birth.split('T')[0] : '',
    residence_expiry_date: data.residence_expiry_date || '',
    passport_delivery_date: data.passport_delivery_date || '',
    // Handle photos
    personal_photo: data.personal_photo_url || data.personal_photo || null,
    passport_photo: data.passport_photo_url || data.passport_photo || null,
    // Other fields
    phone_number: data.phone_number || '',
    zipcode: data.zipcode || '',
    unique_code: data.unique_code || '',
    nationality: data.nationality || '',
    passport_status: data.passport_status || 'قيد الانجاز',
    transaction_type: data.transaction_type || '',
    payment_status: data.payment_status || { label: 'في انتظار الدفع', value: 'pending' },
    address: data.address || '',
    sponsor_name: '',
    relationship: '',
    extension_reason: '',
    delivered_by: data.delivered_by || '',
    barcode: data.barcode || '',
    signature_data: data.signature_data || '',
    no_signature: data.no_signature || false,
    no_signature_reason: data.no_signature_reason || '',
    email: data.email || ''
  };

  // Update the form ref with the new data
  form.value = newFormData;

  console.log('Form data after set:', form.value);
};

// Update the handleFileUpload function
const handleFileUpload = (file: any, field: 'personal_photo' | 'passport_photo') => {
  if (form.value[field] && typeof form.value[field] === 'string') {
    // If there was a previous URL, clear it
    form.value[field] = null;
  }
  form.value[field] = file;
};

// Add this after the handleFileUpload function
const copyToClipboard = async (text: string) => {
  try {
    // Try using the modern Clipboard API
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(text);
      $q.notify({
        type: 'positive',
        message: 'تم نسخ الرمز بنجاح',
        position: 'top',
        timeout: 2000
      });
    } else {
      // Fallback for older browsers
      const textArea = document.createElement('textarea');
      textArea.value = text;
      textArea.style.position = 'fixed';
      textArea.style.left = '-999999px';
      textArea.style.top = '-999999px';
      document.body.appendChild(textArea);
      textArea.focus();
      textArea.select();

      try {
        document.execCommand('copy');
        textArea.remove();
        $q.notify({
          type: 'positive',
          message: 'تم نسخ الرمز بنجاح',
          position: 'top',
          timeout: 2000
        });
      } catch (err) {
        textArea.remove();
        throw new Error('فشل نسخ النص');
      }
    }
  } catch (error) {
    console.error('Copy failed:', error);
    $q.notify({
      type: 'negative',
      message: 'فشل نسخ الرمز',
      position: 'top',
      timeout: 2000
    });
  }
};

const startBarcodeScanner = () => {
  // Here you would integrate with your barcode scanner hardware
  // For now, we'll focus the barcode input
  barcodeInput.value?.focus();
};

const handleBarcodeEnter = async () => {
  if (!form.value.barcode) return;

  try {
    // Here you would validate the barcode and fetch passport details
    // For demonstration, we'll show a success message
    $q.notify({
      type: 'positive',
      message: 'تم قراءة الباركود بنجاح'
    });
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء قراءة الباركود'
    });
  }
};

// Commented out signature pad methods due to Vue 3 incompatibility
// const clearSignature = () => {
//   if (signaturePad.value) {
//     signaturePad.value.clearSignature();
//   }
// };

// const getSignatureData = () => {
//   if (!form.value.no_signature && signaturePad.value) {
//     return signaturePad.value.saveSignature();
//   }
//   return null;
// };

defineExpose({ fetch, setFormData, onSubmit });
</script>

<template>
  <q-form @submit.prevent="onSubmit" @reset="onReset" ref="formRef" class="q-gutter-md">
    <div class="form-wrapper q-pa-md">
      <div class="row q-col-gutter-xl">
        <!-- Left Column - Summary Card -->
        <div class="col-12 col-md-3">
          <q-card flat class="summary-card">
            <q-card-section>
              <div class="text-h6 text-weight-bold q-mb-lg">
                <q-icon name="assignment_ind" color="primary" size="28px" class="q-mr-sm" />
                معلومات الجواز
              </div>

              <!-- Passport Summary -->
              <div class="passport-summary q-pa-md">
                <div class="row q-col-gutter-y-md">
                  <!-- Passport Number -->
                  <div class="col-12">
                    <label class="field-label" for="passport_number">رقم الجواز</label>
                    <q-input
                      v-model="form.passport_number"
                      class="q-mt-sm"
                      outlined
                      dense
                      :readonly="readonly"
                      :rules="[(val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة']"
                    >
                      <template v-slot:prepend>
                        <q-icon name="badge" color="primary" />
                      </template>
                    </q-input>
                  </div>

                  <!-- Unique Code -->
                  <div class="col-12 q-mt-md" v-if="form.unique_code">
                    <q-card bordered flat class="unique-code-card q-pa-md">
                      <div class="text-subtitle2 text-weight-medium q-mb-sm">رمز التتبع الفريد</div>
                      <q-input
                        v-model="form.unique_code"
                        class="unique-code-input"
                        outlined
                        dense
                        readonly
                        bg-color="grey-1"
                      >
                        <template v-slot:prepend>
                          <q-icon name="fingerprint" color="primary" />
                        </template>
                        <template v-slot:append>
                          <q-btn
                            flat
                            dense
                            icon="content_copy"
                            color="primary"
                            @click="() => copyToClipboard(form.unique_code)"
                          >
                            <q-tooltip>نسخ الرمز</q-tooltip>
                          </q-btn>
                        </template>
                      </q-input>
                      <div class="text-caption text-grey-7 q-mt-sm">
                        يرجى الاحتفاظ بهذا الرمز للرجوع إليه في المستقبل
                      </div>
                    </q-card>
                  </div>
                </div>
              </div>

              <!-- Status Card -->
              <div class="status-section q-mt-lg">
                <q-card flat bordered class="status-card q-pa-md">
                  <div class="text-subtitle2 text-weight-medium q-mb-md">حالة المعاملة</div>
                  <div class="status-info">
                    <q-chip
                      :color="form.passport_status === 'قيد الانجاز' ? 'orange' : 'green'"
                      text-color="white"
                      icon="circle"
                      size="md"
                    >
                      {{ form.passport_status }}
                    </q-chip>
                  </div>
                </q-card>
              </div>

              <!-- Save Button -->
              <div class="q-mt-xl">
                <q-btn
                  :loading="submitLoading"
                  label="حفظ المعلومات"
                  type="submit"
                  color="primary"
                  class="full-width submit-btn"
                  unelevated
                >
                  <template v-slot:loading>
                    <q-spinner-dots />
                  </template>
                </q-btn>
              </div>
            </q-card-section>
          </q-card>
        </div>

        <!-- Right Column - Main Form -->
        <div class="col-12 col-md-9">
          <q-card flat class="main-form-card">
            <q-card-section>
              <div class="text-h6 text-weight-bold q-mb-xl">
                <q-icon name="person" color="primary" size="28px" class="q-mr-sm" />
                البيانات الشخصية
              </div>

              <!-- Form Grid -->
              <div class="row q-col-gutter-md">
                <!-- Personal Information Section -->
                <div class="col-12">
                  <div class="form-section q-mb-xl">
                    <div class="text-subtitle1 text-weight-medium q-mb-md">
                      <q-icon name="info" color="primary" size="24px" class="q-mr-sm" />
                      المعلومات الأساسية
                    </div>
                    <div class="row q-col-gutter-md">
                      <!-- Full Name -->
                      <div class="col-12 col-md-6">
                        <label class="field-label">الاسم الكامل</label>
                        <q-input
                          v-model="form.full_name"
                          class="q-mt-sm"
                          outlined
                          dense
                          :readonly="readonly"
                          :rules="[(val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة']"
                        >
                          <template v-slot:prepend>
                            <q-icon name="person" color="primary" />
                          </template>
                        </q-input>
                      </div>

                      <!-- Nationality -->
                      <div class="col-12 col-md-6">
                        <label class="field-label">الجنسیة</label>
                        <q-select
                          v-model="form.nationality"
                          class="q-mt-sm"
                          outlined
                          dense
                          :options="[
                            'البحرین',
                            'الیمن',
                            'السعودیه',
                            'الجزائر',
                            'سوریه',
                            'لبنان',
                            'بریطانیا',
                            'امریکا',
                            'کندا',
                            'العراق',
                            'اندونیسیا',
                            'فلسطین',
                            'الامارات',
                            'الكويت',
                            'اخرای'
                          ]"
                          :readonly="readonly"
                          :rules="[(val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة']"
                        >
                          <template v-slot:prepend>
                            <q-icon name="flag" color="primary" />
                          </template>
                        </q-select>
                      </div>

                      <!-- Date Fields -->
                      <div class="col-12 col-md-6">
                        <label class="field-label">تاريخ الميلاد (ميلادي)</label>
                        <q-input
                          v-model="form.date_of_birth"
                          class="q-mt-sm"
                          outlined
                          dense
                          :readonly="readonly"
                          :rules="[(val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة']"
                        >
                          <template v-slot:prepend>
                            <q-icon name="event" color="primary" />
                          </template>
                          <template v-slot:append>
                            <q-icon name="event" class="cursor-pointer">
                              <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                                <q-date
                                  v-model="form.date_of_birth"
                                  mask="YYYY-MM-DD"
                                  today-btn
                                  format24h
                                  @update:model-value="
                                    (val) => handleDateChange(val, 'date_of_birth')
                                  "
                                >
                                  <div class="row items-center justify-end q-gutter-sm">
                                    <q-btn label="تم" color="primary" flat v-close-popup />
                                  </div>
                                </q-date>
                              </q-popup-proxy>
                            </q-icon>
                          </template>
                        </q-input>
                      </div>

                      <!-- Contact Information -->
                      <div class="col-12 col-md-6">
                        <label class="field-label">رقم الجوال</label>
                        <q-input
                          v-model="form.mobile_number"
                          class="q-mt-sm"
                          outlined
                          dense
                          :readonly="readonly"
                          :rules="[(val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة']"
                        >
                          <template v-slot:prepend>
                            <q-icon name="phone" color="primary" />
                          </template>
                        </q-input>
                      </div>
                    </div>
                  </div>

                  <!-- Transaction Information Section -->
                  <div class="col-12">
                    <div class="form-section q-mb-xl">
                      <div class="text-subtitle1 text-weight-medium q-mb-md">
                        <q-icon name="receipt_long" color="primary" size="24px" class="q-mr-sm" />
                        معلومات المعاملة
                      </div>
                      <div class="row q-col-gutter-md">
                        <!-- Transaction Type -->
                        <div class="col-12 col-md-6">
                          <label class="field-label">نوع المعاملة</label>
                          <q-select
                            v-model="form.transaction_type"
                            class="q-mt-sm"
                            outlined
                            dense
                            :options="[
                              'اصدار اقامه',
                              'اصدار اقامه وخروج مكرر',
                              'تجديد اقامه وخروج مكرر',
                              'تجديد اقامه',
                              'تمديد فيزا',
                              'خروج مكرر',
                              'خروج قطعي',
                              'أخرى'
                            ]"
                            :readonly="readonly"
                            :rules="[(val) => !!val || 'يرجى اختيار نوع المعاملة']"
                            use-input
                            input-debounce="0"
                            @filter="
                              (val, update) => {
                                if (val === '') {
                                  update(() => {
                                    transactionTypeOptions = [
                                      'اصدار اقامه',
                                      'اصدار اقامه وخروج مكرر',
                                      'تجديد اقامه وخروج مكرر',
                                      'تجديد اقامه',
                                      'تمديد فيزا',
                                      'خروج مكرر',
                                      'خروج قطعي',
                                      'أخرى'
                                    ];
                                  });
                                  return;
                                }

                                update(() => {
                                  const needle = val.toLowerCase();
                                  transactionTypeOptions = [
                                    'اصدار اقامه',
                                    'اصدار اقامه وخروج مكرر',
                                    'تجديد اقامه وخروج مكرر',
                                    'تجديد اقامه',
                                    'تمديد فيزا',
                                    'خروج مكرر',
                                    'خروج قطعي',
                                    'أخرى'
                                  ].filter((v) => v.toLowerCase().indexOf(needle) > -1);
                                });
                              }
                            "
                            @new-value="
                              (val) => {
                                if (val) {
                                  transactionTypeOptions = [...transactionTypeOptions, val];
                                  form.transaction_type = val;
                                }
                              }
                            "
                          >
                            <template v-slot:prepend>
                              <q-icon name="assignment" color="primary" />
                            </template>
                          </q-select>
                        </div>

                        <!-- Payment Status -->
                        <div class="col-12 col-md-6">
                          <label class="field-label">حالة الدفع</label>
                          <q-select
                            v-model="form.payment_status"
                            class="q-mt-sm"
                            outlined
                            dense
                            :options="[
                              { label: 'في انتظار الدفع', value: 'pending' },
                              { label: 'تم الدفع', value: 'paid' },
                              { label: 'ملغي', value: 'cancelled' }
                            ]"
                            option-label="label"
                            option-value="value"
                            emit-value
                            map-options
                            :readonly="readonly"
                            :rules="[(val) => !!val || 'يرجى اختيار حالة الدفع']"
                          >
                            <template v-slot:prepend>
                              <q-icon name="payments" color="primary" />
                            </template>
                          </q-select>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Contact Information Section -->
                  <div class="col-12">
                    <div class="form-section q-mb-xl">
                      <div class="text-subtitle1 text-weight-medium q-mb-md">
                        <q-icon name="contact_phone" color="primary" size="24px" class="q-mr-sm" />
                        معلومات الاتصال
                      </div>
                      <div class="row q-col-gutter-md">
                        <!-- Phone Number -->
                        <div class="col-12 col-md-6">
                          <label class="field-label">رقم الهاتف</label>
                          <q-input
                            v-model="form.phone_number"
                            class="q-mt-sm"
                            outlined
                            dense
                            :readonly="readonly"
                            :rules="[(val) => !!val || 'يرجى إدخال رقم الهاتف']"
                          >
                            <template v-slot:prepend>
                              <q-icon name="phone" color="primary" />
                            </template>
                          </q-input>
                        </div>

                        <!-- Email (Optional) -->
                        <div class="col-12 col-md-6">
                          <label class="field-label">البريد الإلكتروني (اختياري)</label>
                          <q-input
                            v-model="form.email"
                            class="q-mt-sm"
                            outlined
                            dense
                            :readonly="readonly"
                            type="email"
                            :rules="[
                              (val) =>
                                !val ||
                                /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val) ||
                                'صيغة البريد الإلكتروني غير صحيحة'
                            ]"
                          >
                            <template v-slot:prepend>
                              <q-icon name="email" color="primary" />
                            </template>
                          </q-input>
                        </div>

                        <!-- Address -->
                        <div class="col-12">
                          <label class="field-label">العنوان</label>
                          <q-input
                            v-model="form.address"
                            class="q-mt-sm"
                            outlined
                            dense
                            type="textarea"
                            :readonly="readonly"
                            :rules="[(val) => !!val || 'يرجى إدخال العنوان']"
                          >
                            <template v-slot:prepend>
                              <q-icon name="location_on" color="primary" />
                            </template>
                          </q-input>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Passport Dates Section -->
                  <div class="col-12">
                    <div class="form-section q-mb-xl">
                      <div class="text-subtitle1 text-weight-medium q-mb-md">
                        <q-icon name="event" color="primary" size="24px" class="q-mr-sm" />
                        تواريخ الجواز
                      </div>
                      <div class="row q-col-gutter-md">
                        <!-- Residence Expiry Date -->
                        <div class="col-12 col-md-6">
                          <label class="field-label">تاريخ انتهاء الإقامة (هجري)</label>
                          <q-input
                            v-model="form.residence_expiry_date"
                            class="q-mt-sm"
                            outlined
                            dense
                            :readonly="readonly"
                            :rules="[(val) => !!val || 'يرجى إدخال تاريخ انتهاء الإقامة']"
                          >
                            <template v-slot:prepend>
                              <q-icon name="event" color="primary" />
                            </template>
                            <template v-slot:append>
                              <q-icon name="event" class="cursor-pointer">
                                <q-popup-proxy
                                  cover
                                  transition-show="scale"
                                  transition-hide="scale"
                                >
                                  <q-date
                                    v-model="form.residence_expiry_date"
                                    mask="YYYY/MM/DD"
                                    today-btn
                                    calendar="persian"
                                    @update:model-value="
                                      (val) => handleDateChange(val, 'residence_expiry_date')
                                    "
                                  >
                                    <div class="row items-center justify-end q-gutter-sm">
                                      <q-btn label="تم" color="primary" flat v-close-popup />
                                    </div>
                                  </q-date>
                                </q-popup-proxy>
                              </q-icon>
                            </template>
                          </q-input>
                        </div>

                        <!-- Passport Receipt Date -->
                        <div class="col-12 col-md-6">
                          <label class="field-label">تاريخ استلام الجواز (هجري)</label>
                          <q-input
                            v-model="form.passport_delivery_date"
                            class="q-mt-sm"
                            outlined
                            dense
                            :readonly="readonly"
                            :rules="[(val) => !!val || 'يرجى إدخال تاريخ استلام الجواز']"
                          >
                            <template v-slot:prepend>
                              <q-icon name="event" color="primary" />
                            </template>
                            <template v-slot:append>
                              <q-icon name="event" class="cursor-pointer">
                                <q-popup-proxy
                                  cover
                                  transition-show="scale"
                                  transition-hide="scale"
                                >
                                  <q-date
                                    v-model="form.passport_delivery_date"
                                    mask="YYYY/MM/DD"
                                    today-btn
                                    calendar="persian"
                                    @update:model-value="
                                      (val) => handleDateChange(val, 'passport_delivery_date')
                                    "
                                  >
                                    <div class="row items-center justify-end q-gutter-sm">
                                      <q-btn label="تم" color="primary" flat v-close-popup />
                                    </div>
                                  </q-date>
                                </q-popup-proxy>
                              </q-icon>
                            </template>
                          </q-input>
                        </div>

                        <!-- Received By -->
                        <div class="col-12 col-md-6">
                          <label class="field-label">تم الاستلام بواسطة</label>
                          <q-input
                            v-model="form.delivered_by"
                            class="q-mt-sm"
                            outlined
                            dense
                            :readonly="readonly"
                            :rules="[(val) => !!val || 'يرجى إدخال اسم المستلم']"
                          >
                            <template v-slot:prepend>
                              <q-icon name="person" color="primary" />
                            </template>
                          </q-input>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Documents Section -->
              <div class="form-section">
                <div class="text-subtitle1 text-weight-medium q-mb-md">
                  <q-icon name="photo_library" color="primary" size="24px" class="q-mr-sm" />
                  المستندات والصور
                </div>
                <div class="row q-col-gutter-xl">
                  <!-- Personal Photo -->
                  <div class="col-12 col-md-6">
                    <q-card bordered flat class="upload-card">
                      <q-card-section>
                        <div class="text-subtitle2 q-mb-md">الصورة الشخصية</div>
                        <div class="upload-area">
                          <div
                            v-if="personalPhotoUrl"
                            class="preview-container"
                          >
                            <q-img :src="personalPhotoUrl" class="preview-image" />
                            <q-btn
                              round
                              color="negative"
                              icon="close"
                              size="sm"
                              class="absolute-top-right"
                              @click="form.personal_photo = null"
                            />
                          </div>
                          <q-file
                            v-else
                            v-model="form.personal_photo"
                            class="upload-input"
                            outlined
                            accept=".jpg,.jpeg,.png,image/*"
                            label="انقر لتحميل الصورة الشخصية"
                            @update:model-value="(val) => handleFileUpload(val, 'personal_photo')"
                          >
                            <template v-slot:prepend>
                              <q-icon name="add_photo_alternate" color="primary" />
                            </template>
                          </q-file>
                        </div>
                      </q-card-section>
                    </q-card>
                  </div>

                  <!-- Passport Photo -->
                  <div class="col-12 col-md-6">
                    <q-card bordered flat class="upload-card">
                      <q-card-section>
                        <div class="text-subtitle2 q-mb-md">صورة الجواز</div>
                        <div class="upload-area">
                          <div
                            v-if="passportPhotoUrl"
                            class="preview-container"
                          >
                            <q-img :src="passportPhotoUrl" class="preview-image" />
                            <q-btn
                              round
                              color="negative"
                              icon="close"
                              size="sm"
                              class="absolute-top-right"
                              @click="form.passport_photo = null"
                            />
                          </div>
                          <q-file
                            v-else
                            v-model="form.passport_photo"
                            class="upload-input"
                            outlined
                            accept=".jpg,.jpeg,.png,image/*"
                            label="انقر لتحميل صورة الجواز"
                            @update:model-value="(val) => handleFileUpload(val, 'passport_photo')"
                          >
                            <template v-slot:prepend>
                              <q-icon name="add_photo_alternate" color="primary" />
                            </template>
                          </q-file>
                        </div>
                      </q-card-section>
                    </q-card>
                  </div>
                </div>
              </div>

              <!-- Barcode and Office Code Section -->
              <q-card flat bordered>
                <q-card-section>
                  <div class="text-h6 text-primary q-mb-md">معلومات التسجيل</div>
                  <div class="row q-col-gutter-md">
                    <div class="col-12">
                      <q-input
                        v-model="form.barcode"
                        outlined
                        label="رقم الباركود"
                        :readonly="readonly"
                        @keyup.enter="handleBarcodeEnter"
                        ref="barcodeInput"
                      >
                        <template v-slot:append>
                          <q-btn
                            round
                            flat
                            icon="qr_code_scanner"
                            @click="startBarcodeScanner"
                            :disable="readonly"
                          >
                            <q-tooltip>مسح الباركود</q-tooltip>
                          </q-btn>
                        </template>
                      </q-input>
                    </div>
                  </div>
                </q-card-section>
              </q-card>

              <!-- Delivery Confirmation Section (shown only during delivery) -->
              <q-card v-if="showDeliveryOptions" flat bordered>
                <q-card-section>
                  <div class="text-h6 text-primary q-mb-md">تأكيد التسليم</div>
                  <div class="row q-col-gutter-md">
                    <div class="col-12">
                      <div class="signature-pad-container">
                        <div class="text-subtitle2 q-mb-sm">التوقيع الإلكتروني (ميزة غير متوفرة حالياً)</div>
                        <!-- <vue-signature-pad
                          v-if="!form.no_signature"
                          ref="signaturePad"
                          :width="400"
                          :height="200"
                          :options="{ backgroundColor: '#f5f5f5' }"
                        /> -->
                        <div class="row items-center q-gutter-sm">
                          <!-- <q-btn
                            v-if="!form.no_signature"
                            flat
                            color="primary"
                            icon="refresh"
                            label="مسح التوقيع"
                            @click="clearSignature"
                          /> -->
                          <q-checkbox v-model="form.no_signature" label="استلام بدون توقيع" />
                        </div>
                      </div>
                    </div>
                    <div class="col-12" v-if="form.no_signature">
                      <q-input
                        v-model="form.no_signature_reason"
                        type="textarea"
                        outlined
                        label="سبب عدم التوقيع"
                        :rules="[
                          (val) =>
                            !form.no_signature ||
                            (val && val.length > 0) ||
                            'يرجى ذكر سبب عدم التوقيع'
                        ]"
                      />
                    </div>
                  </div>
                </q-card-section>
              </q-card>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>
  </q-form>
</template>

<style lang="scss" scoped>
.form-wrapper {
  background: #f8fafc;
  min-height: 100vh;
}

// Card Styles
.summary-card,
.main-form-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;

  &:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  }
}

// Status Card
.status-card {
  background: #f8fafc;
  border: 1px dashed rgba(var(--q-primary), 0.2);
  border-radius: 8px;

  .status-info {
    display: flex;
    justify-content: center;
  }
}

// Field Labels
.field-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #64748b;
  display: block;
  margin-bottom: 4px;
}

// Form Sections
.form-section {
  padding: 1.5rem;
  background: white;
  border-radius: 12px;

  &:not(:last-child) {
    margin-bottom: 1.5rem;
  }
}

// Upload Area
.upload-card {
  background: white;
  border-radius: 8px;
  overflow: hidden;

  .upload-area {
    .preview-container {
      position: relative;
      border-radius: 8px;
      overflow: hidden;

      .preview-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
      }
    }
  }
}

// Extension Card
.extension-card {
  background: #f8fafc;
  border: 1px solid rgba(var(--q-primary), 0.1);
}

// Input Styles
:deep(.q-field) {
  &.q-field--outlined {
    .q-field__control {
      background: white;
      border-radius: 8px;
      border-color: #e2e8f0;

      &:hover {
        border-color: var(--q-primary);
      }
    }
  }

  &.q-field--focused {
    .q-field__control {
      border-color: var(--q-primary);
      box-shadow: 0 0 0 2px rgba(var(--q-primary), 0.1);
    }
  }
}

// Submit Button
.submit-btn {
  border-radius: 8px;
  font-weight: 500;
  height: 44px;
  letter-spacing: 0.5px;
  text-transform: none;
}

// Unique Code Input
.unique-code-input {
  :deep(.q-field__control) {
    background: #fffbeb !important;
  }
}

// RTL Support
[dir='rtl'] {
  .field-label {
    text-align: right;
  }
}

// Responsive Adjustments
@media (max-width: 768px) {
  .form-wrapper {
    padding: 12px;
  }

  .form-section {
    padding: 1rem;
  }

  .summary-card,
  .main-form-card {
    margin-bottom: 16px;
  }
}

.signature-pad-container {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 16px;
  background: white;

  canvas {
    border: 1px solid #eee;
    border-radius: 4px;
    margin-bottom: 8px;
  }
}

.unique-code-card {
  background: #f8f9fa;
  border: 1px dashed var(--q-primary);
  border-radius: 8px;

  .unique-code-input {
    :deep(.q-field__control) {
      background: white !important;
    }
  }
}
</style>
