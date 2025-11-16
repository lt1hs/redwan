<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useQuasar } from 'quasar';

const $q = useQuasar();

const personalImageUrl = ref(''); // Moved up
const passportImageUrl = ref(''); // Moved up

interface RelativeOption {
  value: string;
  label: string;
  label_fa: string;
}

interface FormData {
  full_name_fa: string;
  full_name_en: string;
  father_name_fa: string;
  father_name_en: string;
  passport_number: string;
  national_id: string;
  police_code: string;
  personal_photo: File | null;
  passport_photo: File | null;
  card_type: string;
  status: string;
  card_expiry_date: string;
  parent_card_id: number | string | null;
  citizenship_en: string;
  citizenship_fa: string;
  relative_relation: string | null;
  [key: string]: string | File | null | number;
}

const props = defineProps({
  submitLoading: {
    type: Boolean,
    default: false
  },
  parentCardId: {
    type: [Number, String],
    default: null
  },
  forceFamilyType: {
    type: Boolean,
    default: false
  },
  initialPersonalPhotoUrl: {
    type: String,
    default: ''
  },
  initialPassportPhotoUrl: {
    type: String,
    default: ''
  },
  formData: { // New prop for v-model
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['submit', 'cancel', 'update:formData']); // Added update:formData event

const formRef = ref<any>(null);
const ownerType = ref<'personal' | 'family'>(props.forceFamilyType ? 'family' : 'personal');

const relativeOptions = [
  { value: 'immigrant_wife', label: 'Immigrant wife', label_fa: 'همسر مهاجر' },
  { value: 'immigrant_son', label: 'Immigrant son', label_fa: 'پسر مهاجر' },
  { value: 'immigrant_daughter', label: 'Immigrant daughter', label_fa: 'دختر مهاجر' }
];

// Helper function to define default form state
function defaultFormState(): FormData {
  return {
    full_name_fa: '',
    full_name_en: '',
    father_name_fa: '',
    father_name_en: '',
    passport_number: '',
    national_id: '',
    police_code: '',
    personal_photo: null,
    passport_photo: null,
    card_type: 'personal',
    status: 'active',
    card_expiry_date: '',
    parent_card_id: props.parentCardId,
    citizenship_en: '',
    citizenship_fa: '',
    relative_relation: null
  };
}

// Initialize form with default state or from props
const form = ref<FormData>(defaultFormState());

// Function to update form state from a given data source
function updateForm(sourceData: any) {
  if (!sourceData) return;

  // Update all form fields except photos
  for (const key in sourceData) {
    if (key !== 'personal_photo' && key !== 'passport_photo') {
      (form.value as any)[key] = sourceData[key];
    }
  }

  // Handle personal photo for display
  if (sourceData.personal_photo && typeof sourceData.personal_photo === 'string') {
    personalImageUrl.value = sourceData.personal_photo;
    form.value.personal_photo = null; // Ensure q-file model is null
  } else if (sourceData.personal_photo === null) {
    personalImageUrl.value = '';
    form.value.personal_photo = null;
  }

  // Handle passport photo for display
  if (sourceData.passport_photo && typeof sourceData.passport_photo === 'string') {
    passportImageUrl.value = sourceData.passport_photo;
    form.value.passport_photo = null; // Ensure q-file model is null
  } else if (sourceData.passport_photo === null) {
    passportImageUrl.value = '';
    form.value.passport_photo = null;
  }
}

// Initialize the form with props.formData on component setup
updateForm(props.formData);

// Watch for subsequent changes in props.formData
watch(() => props.formData, (newValue) => {
  console.log('PassCardForm: formData prop changed:', newValue);
  updateForm(newValue);
}, { deep: true });

const isPersonalType = computed(() => ownerType.value === 'personal');

const cardTypeMap: Record<1 | 2, string> = {
  1: 'personal',
  2: 'wife' // Default to wife, can be changed in the form for family members
};

function handleImageUpload(file: File | null) {
  if (file) {
    form.value.personal_photo = file;
    personalImageUrl.value = URL.createObjectURL(file);
  }
}

function removeImage() {
  form.value.personal_photo = null;
  personalImageUrl.value = '';
}

function handlePassportImageUpload(file: File | null) {
  if (file) {
    form.value.passport_photo = file;
    passportImageUrl.value = URL.createObjectURL(file);
  }
}

function removePassportImage() {
  form.value.passport_photo = null;
  passportImageUrl.value = '';
}

const validateForm = async () => {
  return formRef.value?.validate();
};

const getCardTypeFromRelation = (relation: string | null) => {
  if (!relation) return Card.TYPE_PERSONAL;
  const typeMap = {
    'immigrant_wife': Card.TYPE_WIFE,
    'immigrant_son': Card.TYPE_SON,
    'immigrant_daughter': Card.TYPE_DAUGHTER
  };
  return typeMap[relation as keyof typeof typeMap] || Card.TYPE_WIFE;
};

// Add Card type constants
const Card = {
  TYPE_PERSONAL: 'personal',
  TYPE_WIFE: 'wife',
  TYPE_SON: 'son',
  TYPE_DAUGHTER: 'daughter',
  STATUS_ACTIVE: 'active',
  STATUS_EXPIRED: 'expired',
  STATUS_CANCELLED: 'cancelled'
};

// Watch for forceFamilyType changes
watch(() => props.forceFamilyType, (newValue) => {
  if (newValue) {
    ownerType.value = 'family';
  } else {
    ownerType.value = 'personal';
  }
});

// Watch for parent card ID changes
watch(() => props.parentCardId, (newValue) => {
  if (ownerType.value === 'family') {
    form.value.parent_card_id = newValue;
  }
}, { immediate: true });

// Watch for owner type changes to reset relevant fields and set defaults
watch(ownerType, (newValue) => {
  if (newValue === 'family') {
    if (!props.parentCardId) {
      $q.notify({
        type: 'negative',
        message: 'Parent card ID is required for family member cards'
      });
      ownerType.value = 'personal';
      return;
    }
    form.value.relative_relation = relativeOptions[0].value;
    form.value.parent_card_id = props.parentCardId;
  } else {
    form.value.relative_relation = null;
    form.value.parent_card_id = null;
  }
});

// Watch for relative_relation changes to update card_type
watch(() => form.value.relative_relation, (newValue) => {
  if (ownerType.value === 'family') {
    form.value.card_type = getCardTypeFromRelation(newValue);
  }
});

async function onSubmit(e?: Event) {
  e?.preventDefault();

  try {
    const isValid = await formRef.value?.validate();
    if (!isValid) {
      throw new Error('Validation failed');
    }

    // Validate required fields based on card type
    const requiredFields = [
      'full_name_fa',
      'full_name_en',
      'father_name_fa',
      'father_name_en',
      'passport_number',
      'police_code',
      'card_expiry_date',
      'citizenship_en',
      'citizenship_fa'
    ];

    const missingFields = requiredFields.filter(field => !form.value[field]);
    if (missingFields.length > 0) {
      throw new Error(`Missing required fields: ${missingFields.join(', ')}`);
    }

    if (ownerType.value === 'personal' && !form.value.national_id) {
      throw new Error('National ID is required for personal cards');
    }

    if (ownerType.value === 'family' && !props.parentCardId) {
      throw new Error('Parent card ID is required for family member cards');
    }
    // Personal and Passport photos are handled separately for requiredness on update

    const mappedForm: Record<string, string | File | null | number> = {
      full_name_fa: form.value.full_name_fa,
      full_name_en: form.value.full_name_en,
      father_name_fa: form.value.father_name_fa,
      father_name_en: form.value.father_name_en,
      passport_number: form.value.passport_number,
      police_code: form.value.police_code,
      card_expiry_date: form.value.card_expiry_date,
      status: form.value.status || Card.STATUS_ACTIVE,
      citizenship_en: form.value.citizenship_en,
      citizenship_fa: form.value.citizenship_fa
    };

    // Set card type based on owner type and relation
    if (ownerType.value === 'family') {
      const relation = form.value.relative_relation;
      mappedForm.card_type = getCardTypeFromRelation(relation);
      console.log('Setting card type for family member:', mappedForm.card_type, 'based on relation:', relation);
      
      if (props.parentCardId) {
        mappedForm.parent_card_id = props.parentCardId;
      } else {
        throw new Error('Parent card ID is required for family member cards');
      }
      
      // Ensure relative_relation is included in the form data
      if (relation) {
        mappedForm.relative_relation = relation;
      }
      
      // Add a default national_id for family members to satisfy database constraints
      mappedForm.national_id = `FAMILY-${Date.now()}`;
    } else {
      mappedForm.card_type = Card.TYPE_PERSONAL;
      if (!form.value.national_id) {
        throw new Error('National ID is required for personal cards');
      }
      mappedForm.national_id = form.value.national_id;
    }

    // Create FormData object
    const formData = new FormData();

    // Add all form fields to FormData
    Object.entries(mappedForm).forEach(([key, value]) => {
      if (value !== null && value !== undefined) {
        if (value instanceof File) {
          formData.append(key, value);
        } else {
          formData.append(key, String(value));
        }
      }
    });

    // Add all form fields to FormData, excluding photos for now
    Object.entries(form.value).forEach(([key, value]) => {
      if (key !== 'personal_photo' && key !== 'passport_photo' && value !== null && value !== undefined) {
        if (value instanceof File) {
          formData.append(key, value);
        } else {
          formData.append(key, String(value));
        }
      }
    });

    // Handle personal photo
    if (form.value.personal_photo instanceof File) {
      // New file selected
      formData.append('personal_photo', form.value.personal_photo);
    } else if (form.value.personal_photo === null && props.initialPersonalPhotoUrl) {
      // Existing photo was explicitly removed (initial URL existed, but now it's null)
      formData.append('personal_photo', ''); // Send empty string to signal removal
    }
    // If form.value.personal_photo is a string (existing URL) and not changed, do nothing. Laravel will keep the old value.

    // Handle passport photo
    if (form.value.passport_photo instanceof File) {
      // New file selected
      formData.append('passport_photo', form.value.passport_photo);
    } else if (form.value.passport_photo === null && props.initialPassportPhotoUrl) {
      // Existing photo was explicitly removed
      formData.append('passport_photo', ''); // Send empty string to signal removal
    }
    // If form.value.passport_photo is a string (existing URL) and not changed, do nothing. Laravel will keep the old value.

    // Log form data for debugging
    const formDataObj: Record<string, string | File> = {};
    formData.forEach((value, key) => {
      formDataObj[key] = value;
    });
    console.log('Form data being sent:', formDataObj);
    console.log('Selected relation:', form.value.relative_relation);
    console.log('Card type:', mappedForm.card_type);
    console.log('Parent card ID:', props.parentCardId);

    // Emit update:formData for v-model
    emit('update:formData', form.value);

    // Emit submit event with form data
    emit('submit', { form: formData });
  } catch (error) {
    console.error('Form validation error:', error);
    $q.notify({
      type: 'negative',
      message: error instanceof Error ? error.message : 'Validation failed'
    });
    throw error;
  }
}

const submit = () => {
  onSubmit();
};

defineExpose({
  validate: async () => {
    return formRef.value?.validate();
  },
  submit: async () => {
    return onSubmit();
  }
});
</script>

<template>
  <q-form
    ref="formRef"
    @submit.prevent="onSubmit"
    class="pass-card-form"
  >
    <!-- Card Owner Type -->
    <!-- <div v-if="!forceFamilyType" class="form-section q-mb-lg">
      <div class="text-subtitle1 q-mb-sm">Card Owner Type / نوع صاحب كارت</div>
      <div class="row items-center justify-end">
       
        <q-radio v-model="ownerType" val="personal" label="Personal / شخصي" />
      </div>
    </div> -->

    <!-- Personal Image -->
    <div class="form-section q-mb-lg">
      <div class="text-subtitle1 q-mb-sm"> الصورة الشخصية</div>
      <q-file
        v-model="form.personal_photo"
        label=" اختر صورة"
        outlined
        accept=".jpg,.jpeg,.png"
        class="full-width"
        @update:model-value="handleImageUpload"
      >
        <template v-slot:prepend>
          <q-icon name="attach_file" />
        </template>
      </q-file>
      <div v-if="personalImageUrl" class="q-mt-md text-center">
        <img :src="personalImageUrl" alt="Personal Photo Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; object-fit: cover;">
        <q-btn
          icon="close"
          round
          dense
          flat
          color="negative"
          @click="removeImage"
          class="q-ml-sm"
        />
      </div>
    </div>

    <!-- Passport Image -->
    <div class="form-section q-mb-lg">
      <div class="text-subtitle1 q-mb-sm"> صورة جواز السفر</div>
      <q-file
        v-model="form.passport_photo"
        label=" اختر صورة جواز السفر"
        outlined
        accept=".jpg,.jpeg,.png"
        class="full-width"
        @update:model-value="handlePassportImageUpload"
      >
        <template v-slot:prepend>
          <q-icon name="attach_file" />
        </template>
      </q-file>
      <div v-if="passportImageUrl" class="q-mt-md text-center">
        <img :src="passportImageUrl" alt="Passport Photo Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; object-fit: cover;">
        <q-btn
          icon="close"
          round
          dense
          flat
          color="negative"
          @click="removePassportImage"
          class="q-ml-sm"
        />
      </div>
    </div>

    <!-- Personal Information -->
    <div class="row q-col-gutter-md">
      <!-- English Fields (Left Side) -->
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.full_name_en"
          label="Full Name"
          outlined
          :rules="[val => !!val || 'Full name is required']"
          class="q-mb-md"
        />
        <q-input
          v-model="form.father_name_en"
          label="Father's Name"
          outlined
          :rules="[val => !!val || 'Father\'s name is required']"
          class="q-mb-md"
        />
        <q-input
          v-model="form.citizenship_en"
          label="Citizenship"
          outlined
          :rules="[val => !!val || 'Citizenship is required']"
          class="q-mb-md"
        />
      </div>

      <!-- Arabic/Persian Fields (Right Side) -->
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.full_name_fa"
          label="نام و نام خانوادگی"
          outlined
          :rules="[val => !!val || 'نام و نام خانوادگی الزامی است']"
          class="q-mb-md"
        />
        <q-input
          v-model="form.father_name_fa"
          label="نام پدر"
          outlined
          :rules="[val => !!val || 'نام پدر الزامی است']"
          class="q-mb-md"
        />
        <q-input
          v-model="form.citizenship_fa"
          label="تابعیت"
          outlined
          :rules="[val => !!val || 'تابعیت الزامی است']"
          class="q-mb-md"
        />
      </div>
    </div>

    <!-- Additional Information -->
    <div class="row q-col-gutter-md">
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.police_code"
          label="Police Code / كد پليس"
          outlined
          :rules="[val => !!val || 'Police code is required']"
          class="q-mb-md"
        />
        <q-select
          v-if="!isPersonalType"
          v-model="form.relative_relation"
          :options="relativeOptions"
          :option-label="(opt) => opt ? `${opt.label} / ${opt.label_fa}` : ''"
          option-value="value"
          label="Relative Relation / نسبت"
          outlined
          :rules="[val => !isPersonalType ? (!!val || 'Relative relation is required') : true]"
          emit-value
          map-options
          class="q-mb-md"
        />
        <q-input
          v-model="form.card_expiry_date"
          label="Card Expiry Date / تاريخ انقضاي كارت"
          outlined
          type="date"
          :rules="[val => !!val || 'Expiry date is required']"
          class="q-mb-md"
        />
      </div>
      <div class="col-12 col-md-6">
        <q-input
          v-model="form.passport_number"
          label="ID No / شماره گذرنامه"
          outlined
          :rules="[val => !!val || 'ID number is required']"
          class="q-mb-md"
        />
        <q-input
          v-if="isPersonalType"
          v-model="form.national_id"
          label="National ID / كد فراگير"
          outlined
          :rules="[val => isPersonalType ? (!!val || 'National ID is required') : true]"
          class="q-mb-md"
        />
      </div>
    </div>

    <!-- Action Buttons -->
    
  </q-form>
</template>

<style lang="scss" scoped>
.pass-card-form {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;

  .form-section {
    background: #fff;
    border-radius: 8px;
    padding: 16px;
  }

  :deep(.q-field) {
    background: white;
  }
}
</style>
