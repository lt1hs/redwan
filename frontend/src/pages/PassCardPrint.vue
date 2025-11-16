<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useQuasar } from 'quasar';
import { useCardsStore } from '@/stores/cards';

// Accept the id as a prop instead of from route.params
const props = defineProps({
  id: {
    type: [String, Number],
    required: true
  }
});

const route = useRoute();
const $q = useQuasar();
const cardsStore = useCardsStore();

const passport = ref<any>(null);
const loading = ref(false);
const parentCardId = ref<number | null>(null);
const photoLoadFailed = ref(false);

onMounted(async () => {
  loading.value = true;
  try {
    // Use the id prop directly instead of route.params.id
    const cardId = typeof props.id === 'string' ? parseInt(props.id, 10) : props.id;
    
    passport.value = await cardsStore.get(cardId);
    console.log('Loaded card data:', passport.value);
    
    // If this is a family member card, get the parent card ID
    if (passport.value.parent_card_id) {
      parentCardId.value = passport.value.parent_card_id;
      console.log('Family member card detected with parent ID:', parentCardId.value);
    }
  } catch (error) {
    console.error('Error fetching card:', error);
    $q.notify({
      type: 'negative',
      message: 'خطا في جلب بيانات البطاقة',
      position: 'top'
    });
  } finally {
    loading.value = false;
  }
});

// Function to handle image load errors
function handleImageError(event: Event) {
  const target = event.target as HTMLImageElement;
  console.error('Failed to load image:', target.src);
  
  // Use a data URL placeholder which doesn't require network access
  target.src = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMjgiIGhlaWdodD0iMTI4IiB2aWV3Qm94PSIwIDAgMjQgMjQiIGZpbGw9Im5vbmUiIHN0cm9rZT0iIzMzMyIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiIGNsYXNzPSJmZWF0aGVyIGZlYXRoZXItdXNlciI+PHBhdGggZD0iTTIwIDIxdi0yYTQgNCAwIDAgMC00LTRINGE0IDQgMCAwIDAtNCA0djIiPjwvcGF0aD48Y2lyY2xlIGN4PSIxMiIgY3k9IjciIHI9IjQiPjwvY2lyY2xlPjwvc3ZnPg==';
  console.log('Using placeholder user icon due to image load failure');
}

function printCard() {
  window.print();
}

function formatDate(date: string | Date) {
  if (!date) return 'غير محدد';
  const d = new Date(date);
  
  // Convert to numeric date format
  const formatter = new Intl.DateTimeFormat('fa-IR', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    numberingSystem: 'latn'
  });
  
  // Get the parts of the date
  const parts = formatter.formatToParts(d);
  const year = parts.find(part => part.type === 'year')?.value || '';
  const month = parts.find(part => part.type === 'month')?.value || '';
  const day = parts.find(part => part.type === 'day')?.value || '';
  
  // Format as DD/MM/YYYY
  return `${year}/${month}/${day}`;
}

// Helper to determine if card is a family member
function isFamilyMember() {
  return passport.value && passport.value.parent_card_id !== null;
}

// Helper function to get relationship type in Persian
function getRelativeType(cardType: string): string {
  switch (cardType) {
    case 'wife':
      return 'همسر';
    case 'son':
      return 'پسر';
    case 'daughter':
      return 'دختر';
    default:
      return cardType;
  }
}

// Helper function to get relationship type in English
function getRelativeTypeEn(cardType: string): string {
  switch (cardType) {
    case 'wife':
      return 'Wife';
    case 'son':
      return 'Son';
    case 'daughter':
      return 'Daughter';
    default:
      return cardType;
  }
}

// Generate an image URL with a fallback to a data URL placeholder
function getDirectImageUrl(photo: string): string {
  if (!photo) return '';

  // Extract just the filename, regardless of the path structure
  const filename = photo.split('/').pop();
  
  // In Laravel, storage/app/public files are accessible through /storage
  // The correct URL for Laravel's storage symlink is:
  const imageUrl = `http://localhost:8000/storage/photos/cards/${filename}`;
  console.log('Generated image URL:', imageUrl);
  return imageUrl;
}
</script>

<template>
  <q-page class="print-container">
    <!-- Screen only controls -->
    <div class="screen-only q-pa-md">
      <div class="row items-center justify-between">
        <base-breadcrumbs />
        <q-btn color="primary" icon="print" label="طباعة" @click="printCard" />
      </div>
    </div>

    <div v-if="loading" class="flex flex-center">
      <q-spinner size="3em" />
    </div>

    <!-- Card Preview -->
    <div v-else-if="passport" class="cards-wrapper">
      <!-- Conditional template selection based on card type -->
      <template v-if="isFamilyMember()">
        <!-- Family Member Card - Front -->
        <div class="card-container family-member-card">
          <div class="card-content">
            <img src="../assets/card-temp-fm-front.png" class="card-bg" />
            <div class="photo-area fm-photo-area">
              <template v-if="passport.personal_photo">
                <!-- Use direct URL to the file -->
                <img 
                  :src="getDirectImageUrl(passport.personal_photo)" 
                  @error="handleImageError" 
                  class="photo"
                />
              </template>
              <div v-else class="no-photo-placeholder">No Photo</div>
            </div>
            <div class="data-area">
              <div class="data-text">{{ passport.full_name_fa || '' }}</div>
              <div class="data-text">{{ passport.father_name_fa || '' }}</div>
              <div class="data-text">{{ passport.passport_number || '' }}</div>
              <div class="data-text">{{ passport.citizenship_fa || '' }}</div>
              <div class="data-text relative-row">{{ getRelativeType(passport.card_type) }}</div>
              <div class="data-text">{{ passport.police_code || '' }}</div>
            </div>
            <div class="expiry-date">
              (اعتبار کارت: {{ formatDate(passport.card_expiry_date) }})
            </div>
          </div>
        </div>

        <!-- Family Member Card - Back -->
        <div class="card-container family-member-card">
          <div class="card-content">
            <img src="../assets/card-temp-fm-back.png" class="card-bg" />
            <div class="data-area">
              <div class="data-text">{{ passport.full_name_en || '' }}</div>
              <div class="data-text">{{ passport.father_name_en || '' }}</div>
              <div class="data-text">{{ passport.passport_number || '' }}</div>
              <div class="data-text">{{ passport.citizenship_en || '' }}</div>
              <div class="data-text relative-row">{{ getRelativeTypeEn(passport.card_type) }}</div>
              <div class="data-text">{{ passport.police_code || '' }}</div>
            </div>
          </div>
        </div>
      </template>
      
      <template v-else>
        <!-- Original Personal Card - Front -->
        <div class="card-container">
          <div class="card-content">
            <img src="../assets/card-temp-front.png" class="card-bg" />
            <div class="photo-area">
              <template v-if="passport.personal_photo">
                <!-- Use direct URL to the file -->
                <img 
                  :src="getDirectImageUrl(passport.personal_photo)" 
                  @error="handleImageError" 
                  class="photo"
                />
              </template>
              <div v-else class="no-photo-placeholder">No Photo</div>
            </div>
            <div class="data-area">
              <div class="data-text">{{ passport.full_name_fa || '' }}</div>
              <div class="data-text">{{ passport.father_name_fa || '' }}</div>
              <div class="data-text">{{ passport.passport_number || '' }}</div>
              <div class="data-text">{{ passport.citizenship_fa || '' }}</div>
              <div class="data-text">{{ passport.national_id || '' }}</div>
              <div class="data-text">{{ passport.police_code || '' }}</div>
            </div>
            <div class="expiry-date">
              (اعتبار کارت: {{ formatDate(passport.card_expiry_date) }})
            </div>
          </div>
        </div>

        <!-- Original Personal Card - Back -->
        <div class="card-container">
          <div class="card-content">
            <img src="../assets/card-temp-back.png" class="card-bg" />
            <div class="data-area">
              <div class="data-text">{{ passport.full_name_en || '' }}</div>
              <div class="data-text">{{ passport.father_name_en || '' }}</div>
              <div class="data-text">{{ passport.passport_number || '' }}</div>
              <div class="data-text">{{ passport.citizenship_en || '' }}</div>
              <div class="data-text">{{ passport.national_id || '' }}</div>
              <div class="data-text">{{ passport.police_code || '' }}</div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </q-page>
</template>

<style lang="scss">
@font-face {
  font-family: 'Yekan';
  src: url('../assets/W_yekan Bold_mypsdshop.ir.ttf') format('truetype');
  font-weight: bold;
  font-style: normal;
}

@font-face {
  font-family: 'Source Sans Pro';
  src: url('../assets/SourceSansPro-Bold.otf') format('opentype');
  font-weight: bold;
  font-style: normal;
}

/* Print styles */
@media print {
  @page {
    size: 85.6mm 53.98mm;
    margin: 0;
  }

  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  /* Hide UI elements */
  .q-header,
  .q-drawer,
  .q-footer,
  .screen-only,
  .q-toolbar,
  nav,
  header,
  footer {
    display: none !important;
  }

  /* Reset body and layout */
  body,
  .q-layout,
  .q-page-container {
    margin: 0 !important;
    padding: 0 !important;
    min-height: unset !important;
    height: auto !important;
    overflow: visible !important;
  }

  .print-container {
    margin: 0 !important;
    padding: 0 !important;
    min-height: unset !important;
  }

  .cards-wrapper {
    display: block !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  .card-container {
    width: 85.6mm !important;
    height: 53.98mm !important;
    margin: 0 !important;
    padding: 0 !important;
    page-break-after: always !important;
    background: white !important;
    position: relative !important;
    overflow: hidden !important;
  }

  .card-content {
    width: 85.6mm !important;
    height: 53.98mm !important;
    position: relative !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  .card-bg {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 85.6mm !important;
    height: 53.98mm !important;
    object-fit: fill !important;
  }

  .photo-area {
    position: absolute !important;
    width: 19mm !important;
    height: 26mm !important;
    right: 7.8mm !important;
    top: 14.5mm !important;
    z-index: 2 !important;
    border-radius: 5px;
    overflow: hidden !important;

    .photo {
      width: 100% !important;
      height: 100% !important;
      object-fit: cover !important;
    }
  }

  .data-area {
    position: absolute !important;
    left: 19.7mm !important;
    top: 19mm !important;
    width: 40.7mm !important;
    height: 34.4mm !important;
    z-index: 2 !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 0.75mm !important;
  }
  

  .data-text {
    font-size: 3.2mm !important;
    line-height: 1 !important;
    color: rgba(0, 0, 0, 0.75) !important;
    font-weight: bold !important;
    text-align: center !important;
    direction: rtl !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    margin-bottom: 0.3mm !important;
    font-family: 'Yekan', sans-serif !important;
  }
  
  .expiry-date {
    position: absolute !important;
    bottom: 4.3mm !important;
    right: 34% !important;
    transform: translateX(-50%) !important;
    font-size: 3.5mm !important;
    color: rgba(0, 0, 0, 0.65) !important;
    font-weight: bold !important;
    text-align: right !important;
    z-index: 2 !important;
    white-space: nowrap !important;
    font-family: 'Yekan', sans-serif !important;
  }

  /* Front card text styles */
  .card-container:first-child {
    .data-text, .expiry-date {
      font-family: 'Yekan', sans-serif !important;
    }
  }

  /* Back card text styles */
  .card-container:last-child {
    .data-text {
      font-family: 'Source Sans Pro', sans-serif !important;
      margin-bottom: 0.1mm !important;
      line-height: 1.3 !important;
      font-size: 2.8mm !important;
      color: rgba(0, 0, 0, 0.65) !important;
    }

    .data-area {
      gap: 0.4mm !important;
      top: 18mm !important;
      line-height: 1 !important;
    }
  }

  /* Family Member card specific styles */
  .relative-row {
    // color: #b71c1c !important;
    font-weight: bolder !important;
    text-transform: uppercase;
  }

  /* Special print styles for family member cards */
  .family-member-card {
    .fm-photo-area {
      position: absolute !important;
      width: 22mm !important;
      height: 28mm !important;
      right: 5mm !important;
      top: 13mm !important;
      z-index: 10 !important;
      border: 1px solid rgba(0, 0, 0, 0.1) !important;
      background-color: #f5f5f5 !important;
    }
  }
}

/* Screen styles */
.print-container {
  background: white;
  min-height: 100vh;
}

.cards-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
  padding: 20px;
}

.card-container {
  width: 85.6mm;
  height: 53.98mm;
  position: relative;
  background: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.card-content {
  width: 100%;
  height: 100%;
  position: relative;
}

.card-bg {
  width: 100%;
  height: 100%;
  position: absolute;
  top: 0;
  left: 0;
  object-fit: fill;
}

.photo-area {
  position: absolute;
  width: 19mm;
  height: 26mm;
  right: 7.8mm;
  top: 14.5mm;
  overflow: hidden;
  background: #f5f5f5;
  border-radius: 5px;

  .photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
}

.data-area {
  position: absolute;
  left: 19.7mm;
  top: 19mm;
  width: 40.7mm;
  height: 34.4mm;
  display: flex;
  flex-direction: column;
  gap: 0.5mm;
}

.data-text {
  font-size: 3.2mm;
  line-height: 1;
  color: rgba(0, 0, 0, 0.75);
  font-weight: bold;
  text-align: center;
  direction: rtl;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 0.5mm;
  font-family: Arial, sans-serif;
}

.expiry-date {
  position: absolute;
  bottom: 4.3mm;
  right: 34%;
  transform: translateX(-50%);
  font-size: 3.5mm;
  color: rgba(0, 0, 0, 0.65);
  font-weight: bold;
  text-align: right;
  z-index: 2;
  white-space: nowrap;
  font-family: 'Yekan', sans-serif;
}

/* Front card text styles */
.card-container:first-child {
  .data-text, .expiry-date {
    font-family: 'Yekan', sans-serif;
    
  }
}

/* Back card text styles */
.card-container:last-child {
  .data-text {
      font-family: 'Source Sans Pro', sans-serif;
      margin-bottom: 0.3mm;
      line-height: 1.3;
      font-size: 2.8mm;
      color: rgba(0, 0, 0, 0.65);
    }

  .data-area {
    gap: 0mm;
    line-height: 1;
  }
}

.relative-row {
  // color: #b71c1c !important;
  font-weight: bolder !important;
}

/* Add specific styles for family member cards */
.family-member-card {
  .fm-photo-area {
    position: absolute !important;
    width: 22mm !important;
    height: 28mm !important;
    right: 5mm !important;
    top: 13mm !important;
    z-index: 10 !important;
    border: 1px solid rgba(0, 0, 0, 0.1) !important;
    background-color: #f5f5f5 !important;
  }
  
  .no-photo-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f0f0f0;
    color: #999;
    font-size: 14px;
  }
}

/* Add no-photo placeholder style to all card types */
.no-photo-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f0f0f0;
  color: #999;
  font-size: 14px;
  text-align: center;
}
</style>
