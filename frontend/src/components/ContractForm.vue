<script setup lang="ts">
import { ref, watch } from 'vue';
import { useContractsStore } from '@/stores/contracts';
import moment from 'moment-hijri';
import { useQuasar } from 'quasar';

interface ContractForm {
  husband_name: string;
  husband_nationality: string;
  husband_id_number: string;
  husband_birth_date: string;
  husband_address: string;
  husband_phone: string;
  husband_passport_number: string;

  wife_name: string;
  wife_nationality: string;
  wife_id_number: string;
  wife_birth_date: string;
  wife_address: string;
  wife_phone: string;
  wife_passport_number: string;

  contract_number: string;
  contract_date: string;
  contract_type: string;
  contract_place: string;
  present_dowry: string | number; // Assuming dowry can be number or string
  deferred_dowry: string | number; // Assuming dowry can be number or string
  husband_conditions_arabic: string;
  husband_conditions_persian: string;
  wife_conditions_arabic: string;
  wife_conditions_persian: string;
  first_witness: string;
  second_witness: string;
  officiant_name: string;
  notes: string;
}

const contracts = useContractsStore();
const $q = useQuasar();

// Define initial data for the form
const getInitialData = (): ContractForm => ({
  husband_name: '',
  husband_nationality: '',
  husband_id_number: '',
  husband_birth_date: '',
  husband_address: '',
  husband_phone: '',
  husband_passport_number: '',

  wife_name: '',
  wife_nationality: '',
  wife_id_number: '',
  wife_birth_date: '',
  wife_address: '',
  wife_phone: '',
  wife_passport_number: '',

  contract_number: '',
  contract_date: '',
  contract_type: '', // Added new field
  contract_place: '',
  present_dowry: '',
  deferred_dowry: '',
  husband_conditions_arabic: '',
  husband_conditions_persian: '',
  wife_conditions_arabic: '',
  wife_conditions_persian: '',
  first_witness: '',
  second_witness: '',
  officiant_name: '',
  notes: ''
});

const form = ref<ContractForm>(getInitialData());
const formRef = ref<any>(null);
const fetchLoading = ref(false);

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
  }
});

// Fetch contract details if an ID is provided
async function fetch() {
  if (!props.id) {
    if (formRef.value) formRef.value.reset();
    return;
  }

  fetchLoading.value = true;
  const fetchedData = await contracts.fetchById(String(props.id)); // Convert number to string
  if (fetchedData) {
    form.value = fetchedData;
  } else {
    onReset(); // Reset form if no data is fetched
  }
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

// Add conversion functions
const convertToHijri = (gregorianDate: string) => {
  if (!gregorianDate) return '';
  return moment(gregorianDate).format('iYYYY/iMM/iDD');
};

const convertToGregorian = (hijriDate: string) => {
  if (!hijriDate) return '';
  return moment(hijriDate, 'iYYYY/iMM/iDD').format('YYYY-MM-DD');
};

// Handle Hijri date change
const handleDateChange = (field: keyof ContractForm, val: string) => {
  if (val) {
    (form.value as any)[field] = val; // Type assertion for dynamic field assignment
  }
};

// Before submitting the form, convert back to Gregorian
const prepareFormForSubmission = () => {
  const formData = { ...form.value };
  // Inside prepareFormForSubmission
console.log('Form data before submission preparation:', form.value);
console.log('Prepared form data for submission (after date conversion):', formData);
  if (formData.contract_date) {
    formData.contract_date = moment(formData.contract_date, 'iYYYY/iMM/iDD').format('YYYY-MM-DD');
  }
  return formData;
};

const emit = defineEmits(['submit']);

// Expose a submit function that validates the form and then emits the data
const submitForm = async () => {
  if (formRef.value) {
    const isValid = await formRef.value.validate();
    if (isValid) {
      emit('submit', prepareFormForSubmission());
    } else {
      console.log('Form validation failed.');
    }
  }
};

defineExpose({ fetch, submit: submitForm });

const nationalityOptions = [
  'السعودية',
  'اليمن',
  'مصر',
  'الأردن',
  'سوريا',
  'لبنان',
  'العراق',
  'فلسطين',
  'السودان',
  'المغرب',
  'تونس',
  'الجزائر',
  'ليبيا',
  'الإمارات',
  'الكويت',
  'البحرين',
  'عمان',
  'قطر',
  'ایران',
  'افغانستان',
  'پاکستان',
  'ترکیه'
];
</script>

<template>
  <q-form
    @submit.prevent="emit('submit', prepareFormForSubmission())"
    @reset="onReset"
    ref="formRef"
  >
    <div class="form-container q-pa-md">
      <div class="row q-col-gutter-lg">
        <!-- Left Column - Contract Summary -->
        <div class="col-12 col-md-3">
          <q-card flat bordered class="contract-summary-card sticky-card">
            <q-card-section>
              <div class="text-h6 text-weight-bold q-mb-md">
                <q-icon name="description" color="primary" size="sm" class="q-mr-sm" />
                معلومات العقد 
              </div>
              <div class="row q-col-gutter-y-md">
                <!-- Contract Number -->
                <div class="col-12">
                  <label class="label-title" for="contract_number">رقم العقد / شماره قرارداد</label>
                  <q-input
                    v-model="form.contract_number"
                    class="q-mt-sm custom-input"
                    outlined
                    dense
                    :readonly="readonly"
                    :rules="[
                      (val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة / این فیلد الزامی است'
                    ]"
                  />
                </div>

                <!-- Contract Date -->
                <div class="col-12">
                  <label class="label-title" for="contract_date">تاريخ العقد / تاریخ قرارداد</label>
                  <q-input
                    v-model="form.contract_date"
                    class="q-mt-sm custom-input"
                    outlined
                    dense
                    :readonly="readonly"
                    :rules="[
                      (val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة / این فیلد الزامی است'
                    ]"
                  >
                    <template v-slot:append>
                      <q-icon name="event" class="cursor-pointer">
                        <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                          <q-date
                            v-model="form.contract_date"
                            mask="YYYY/MM/DD"
                            today-btn
                            @update:model-value="(val) => handleDateChange('contract_date', val)"
                          >
                            <div class="row items-center justify-end q-gutter-sm">
                              <q-btn label="OK" color="primary" flat v-close-popup />
                            </div>
                          </q-date>
                        </q-popup-proxy>
                      </q-icon>
                    </template>
                  </q-input>
                </div>

                <!-- Contract Type -->
                <div class="col-12">
                  <label class="label-title" for="contract_type">نوع العقد / Contract Type</label>
                  <q-select
                    v-model="form.contract_type"
                    :options="['دائم', 'موقت']"
                    class="q-mt-sm custom-input"
                    outlined
                    dense
                    :readonly="readonly"
                    :rules="[
                      (val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة / این فیلد الزامی است'
                    ]"
                  />
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>

        <!-- Right Column - Main Form -->
        <div class="col-12 col-md-9">
          <!-- Husband Information -->
          <q-card flat bordered class="main-form-card q-mb-lg">
            <q-card-section>
              <div class="section-header">
                <q-icon name="person" color="primary" size="sm" class="q-mr-sm" />
                <div class="text-h6 text-weight-bold text-primary">بيانات الزوج / اطلاعات شوهر</div>
              </div>
              <q-separator class="q-my-md" />
              <div class="row q-col-gutter-md">
                <!-- Husband fields here -->
                <div class="col-12 col-md-6">
                  <label class="field-label">الاسم الکامل </label>
                  <q-input
                    v-model="form.husband_name"
                    outlined
                    dense
                    class="field-input"
                    :readonly="readonly"
                    :rules="[
                      (val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة / این فیلد الزامی است'
                    ]"
                  />
                </div>

                <div class="col-12 col-md-6">
                  <label class="field-label">تاریخ المیلاد </label>
                  <q-input
                    v-model="form.husband_birth_date"
                    type="date"
                    outlined
                    dense
                    class="field-input"
                    :readonly="readonly"
                    :rules="[
                      (val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة / این فیلد الزامی است'
                    ]"
                  />
                </div>

                <div class="col-12 col-md-6">
                  <label class="field-label">رقم الجواز </label>
                  <q-input
                    v-model="form.husband_passport_number"
                    outlined
                    dense
                    class="field-input"
                    :readonly="readonly"
                    :rules="[
                      (val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة / این فیلد الزامی است'
                    ]"
                  />
                </div>

                <div class="col-12 col-md-6">
                  <label class="field-label">رقم الهاتف </label>
                  <q-input
                    v-model="form.husband_phone"
                    outlined
                    dense
                    class="field-input"
                    :readonly="readonly"
                    :rules="[
                      (val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة / این فیلد الزامی است'
                    ]"
                  />
                </div>

                <div class="col-12 col-md-6">
                  <label class="field-label">الجنسیه </label>
                  <q-select
                    v-model="form.husband_nationality"
                    :options="nationalityOptions"
                    outlined
                    dense
                    class="field-input"
                    :readonly="readonly"
                    :rules="[
                      (val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة / این فیلد الزامی است'
                    ]"
                  />
                </div>
              </div>
            </q-card-section>
          </q-card>

          <!-- Wife Information -->
          <q-card flat bordered class="main-form-card q-mb-lg">
            <q-card-section>
              <div class="section-header">
                <q-icon name="person_outline" color="primary" size="sm" class="q-mr-sm" />
                <div class="text-h6 text-weight-bold text-primary">
                  بيانات الزوجة / اطلاعات همسر
                </div>
              </div>
              <q-separator class="q-my-md" />
              <div class="row q-col-gutter-md">
                <!-- Wife fields here -->
                <div class="col-12 col-md-6">
                  <label class="field-label">الاسم الکامل </label>
                  <q-input
                    v-model="form.wife_name"
                    outlined
                    dense
                    class="field-input"
                    :readonly="readonly"
                    :rules="[
                      (val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة / این فیلد الزامی است'
                    ]"
                  />
                </div>

                <div class="col-12 col-md-6">
                  <label class="field-label">تاریخ المیلاد </label>
                  <q-input
                    v-model="form.wife_birth_date"
                    type="date"
                    outlined
                    dense
                    class="field-input"
                    :readonly="readonly"
                    :rules="[
                      (val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة / این فیلد الزامی است'
                    ]"
                  />
                </div>

                <div class="col-12 col-md-6">
                  <label class="field-label">رقم الجواز </label>
                  <q-input
                    v-model="form.wife_passport_number"
                    outlined
                    dense
                    class="field-input"
                    :readonly="readonly"
                    :rules="[
                      (val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة / این فیلد الزامی است'
                    ]"
                  />
                </div>

                <div class="col-12 col-md-6">
                  <label class="field-label">الجنسیه </label>
                  <q-select
                    v-model="form.wife_nationality"
                    :options="nationalityOptions"
                    outlined
                    dense
                    class="field-input"
                    :readonly="readonly"
                    :rules="[
                      (val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة / این فیلد الزامی است'
                    ]"
                  />
                </div>
              </div>
            </q-card-section>
          </q-card>

          <!-- Contract Details -->
          <q-card flat bordered class="main-form-card">
            <q-card-section>
              <div class="section-header">
                <q-icon name="assignment" color="primary" size="sm" class="q-mr-sm" />
                <div class="text-h6 text-weight-bold text-primary">
                  تفاصيل العقد / جزئیات قرارداد
                </div>
              </div>
              <q-separator class="q-my-md" />

              <!-- Dowry Section -->
              <div class="subsection q-mb-lg">
                <div class="text-subtitle1 text-weight-medium q-mb-sm">المهر / مهریه</div>
                <div class="row q-col-gutter-md">
                  <div class="col-12 col-md-6">
                    <label class="field-label">حاضر الصداق / مهریه حاضر</label>
                    <q-input
                      v-model="form.present_dowry"
                      type="number"
                      outlined
                      dense
                      class="field-input"
                      :readonly="readonly"
                      :rules="[
                        (val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة / این فیلد الزامی است'
                      ]"
                    />
                  </div>
                  <div class="col-12 col-md-6">
                    <label class="field-label">موجل الصداق / مهریه موجل</label>
                    <q-input
                      v-model="form.deferred_dowry"
                      type="number"
                      outlined
                      dense
                      class="field-input"
                      :readonly="readonly"
                      :rules="[
                        (val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة / این فیلد الزامی است'
                      ]"
                    />
                  </div>
                </div>
              </div>

              <!-- Conditions Section -->
              <div class="subsection q-mb-lg">
                <div class="text-subtitle1 text-weight-medium q-mb-sm">شروط الزوج / شرایط شوهر</div>
                <div class="row q-col-gutter-md">
                  <div class="col-12">
                    <label class="field-label">شروط الزوج (عربي) </label>
                    <q-input
                      v-model="form.husband_conditions_arabic"
                      type="textarea"
                      outlined
                      autogrow
                      class="field-input"
                      :readonly="readonly"
                      rows="4"
                    />
                  </div>
                  <div class="col-12">
                    <label class="field-label">شروط الزوج (فارسی) </label>
                    <q-input
                      v-model="form.husband_conditions_persian"
                      type="textarea"
                      outlined
                      autogrow
                      class="field-input"
                      :readonly="readonly"
                      rows="4"
                    />
                  </div>
                </div>
              </div>

              <div class="subsection q-mb-lg">
                <div class="text-subtitle1 text-weight-medium q-mb-sm">شروط الزوجة / شرایط همسر</div>
                <div class="row q-col-gutter-md">
                  <div class="col-12">
                    <label class="field-label">شروط الزوجة (عربي) </label>
                    <q-input
                      v-model="form.wife_conditions_arabic"
                      type="textarea"
                      outlined
                      autogrow
                      class="field-input"
                      :readonly="readonly"
                      rows="4"
                    />
                  </div>
                  <div class="col-12">
                    <label class="field-label">شروط الزوجة (فارسی) </label>
                    <q-input
                      v-model="form.wife_conditions_persian"
                      type="textarea"
                      outlined
                      autogrow
                      class="field-input"
                      :readonly="readonly"
                      rows="4"
                    />
                  </div>
                </div>
              </div>

              <!-- Witnesses Section -->
              <div class="subsection">
                <div class="text-subtitle1 text-weight-medium q-mb-sm">الشهود / شاهدان</div>
                <div class="row q-col-gutter-md">
                  <div class="col-12 col-md-6">
                    <label class="field-label">الشاهد الاول / شاهد اول</label>
                    <q-input
                      v-model="form.first_witness"
                      outlined
                      dense
                      class="field-input"
                      :readonly="readonly"
                      :rules="[
                        (val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة / این فیلد الزامی است'
                      ]"
                    />
                  </div>
                  <div class="col-12 col-md-6">
                    <label class="field-label">الشاهد الثانی / شاهد دوم</label>
                    <q-input
                      v-model="form.second_witness"
                      outlined
                      dense
                      class="field-input"
                      :readonly="readonly"
                      :rules="[
                        (val) => !!val || 'يرجى ملئ الحقل بالمعلومات المناسبة / این فیلد الزامی است'
                      ]"
                    />
                  </div>
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>
  </q-form>
</template>

<style lang="scss" scoped>
.form-container {
  background: #f8fafc;
}

.sticky-card {
  position: sticky;
  top: 1rem;
}

.contract-summary-card,
.main-form-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;

  &:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  }
}

.section-header {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
}

.subsection {
  background: #f8fafc;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1rem;
}

.field-label {
  font-size: 0.875rem;
  color: #64748b;
  margin-bottom: 0.5rem;
  display: block;
}

:deep(.field-input) {
  .q-field__control {
    background: white;
    border-radius: 8px;
    border-color: #e2e8f0;

    &:hover {
      border-color: var(--q-primary);
    }
  }

  &.q-field--focused {
    .q-field__control {
      border-color: var(--q-primary);
      box-shadow: 0 0 0 2px rgba(var(--q-primary), 0.1);
    }
  }
}

.label-title {
  font-size: 0.875rem;
  color: #64748b;
  font-weight: 500;
  margin-bottom: 0.25rem;
}

// Responsive adjustments
@media (max-width: 768px) {
  .sticky-card {
    position: static;
    margin-bottom: 1rem;
  }

  .subsection {
    padding: 0.75rem;
  }
}
</style>
