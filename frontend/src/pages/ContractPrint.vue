<template>
  <q-page class="q-pa-md contract-page-print-container">
    <div class="q-mx-auto screen-only-container">
      <base-breadcrumbs />
      <q-card flat bordered class="creation-card q-pa-lg">
        <q-card-section class="q-pb-none">
          <div class="text-h5 text-weight-bold q-mb-md row items-center">
            <q-icon name="o_description" color="primary" size="32px" class="q-mr-sm" />
            طباعة العقد / Print Contract
          </div>
        </q-card-section>

        <q-card-section class="q-pa-lg text-right" dir="rtl">
          <div class="contract-print-area" dir="rtl">
            <img src="/a4_template.jpg.png" class="contract-template-background" />
            <div class="contract-data-overlay">
              <!-- Header Information -->
              <span class="data-contract-number">{{ contractData?.data?.contract_number }}</span>
              <span class="data-contract-date">{{ contractData?.data?.contract_date ? new Date(contractData?.data?.contract_date).toLocaleDateString('ar-EG') : '' }}</span>
              <span class="data-contract-type">{{ contractData?.data?.contract_type }}</span>
              <span class="data-contract-place">{{ contractData?.data?.contract_place }}</span>

              <!-- Husband Data -->
              <span class="data-husband-name">{{ contractData?.data?.husband_name }}</span>
              <span class="data-husband-birth-date">{{ contractData?.data?.husband_birth_date ? new Date(contractData?.data?.husband_birth_date).toLocaleDateString('ar-EG') : '' }}</span>
              <span class="data-husband-nationality">{{ contractData?.data?.husband_nationality }}</span>
              <span class="data-husband-passport-number">{{ contractData?.data?.husband_passport_number }}</span>
              <span class="data-husband-id-number">{{ contractData?.data?.husband_id_number }}</span>

              <!-- Wife Data -->
              <span class="data-wife-name">{{ contractData?.data?.wife_name }}</span>
              <span class="data-wife-birth-date">{{ contractData?.data?.wife_birth_date ? new Date(contractData?.data?.wife_birth_date).toLocaleDateString('ar-EG') : '' }}</span
              ><span class="data-wife-nationality">{{ contractData?.data?.wife_nationality }}</span>
              <span class="data-wife-passport-number">{{ contractData?.data?.wife_passport_number }}</span>

              <!-- Dowry -->
              <span class="data-present-dowry">{{ contractData?.data?.present_dowry }}</span>
              <span class="data-deferred-dowry">{{ contractData?.data?.deferred_dowry }}</span>

              <!-- Conditions -->
              <div class="data-husband-conditions-arabic">{{ contractData?.data?.husband_conditions_arabic }}</div>
              <div class="data-husband-conditions-persian">{{ contractData?.data?.husband_conditions_persian }}</div>
              <div class="data-wife-conditions-arabic">{{ contractData?.data?.wife_conditions_arabic }}</div>
              <div class="data-wife-conditions-persian">{{ contractData?.data?.wife_conditions_persian }}</div>

              <!-- Witnesses and Officiant -->
              <span class="data-first-witness">{{ contractData?.data?.first_witness }}</span>
              <span class="data-second-witness">{{ contractData?.data?.second_witness }}</span>
              <span class="data-officiant-name">{{ contractData?.data?.officiant_name }}</span>
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right" class="q-pt-lg">
          <q-btn
            color="primary"
            label="Print"
            icon="print"
            @click="printPage"
          />
          <q-btn
            color="negative"
            flat
            label="Back"
            @click="router.go(-1)"
          />
        </q-card-actions>
      </q-card>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { defineProps, ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { api } from 'src/boot/axios'; // Import the configured API instance
import BaseBreadcrumbs from 'src/components/BaseBreadcrumbs.vue'; // Import BaseBreadcrumbs

const props = defineProps<{
  id: string;
}>();

const router = useRouter();
const contractData = ref<any>(null); // Using 'any' for now, can define a proper interface later

onMounted(async () => {
  console.log('Fetching contract data for ID:', props.id);
  try {
    const response = await api.get(`/admin/contracts/${props.id}`); // Assuming this is the endpoint
    console.log('Contract API response:', response.data);
    contractData.value = response.data;
    if (contractData.value) {
      console.log('Contract data successfully loaded:', contractData.value);
    } else {
      console.log('Contract data is null or empty after fetch.');
    }
  } catch (error) {
    console.error('Error fetching contract data:', error);
    // Handle error, e.g., show a notification
  }
});

const printPage = () => {
  window.print();
};
</script>

<style lang="scss" scoped>
.creation-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.contract-print-area {
  box-sizing: border-box;
  padding: 0;
  margin: 0;
  position: relative; /* Establish a positioning context for children */

  /* --- Styles for screen display (non-print) --- */
  width: 210mm; /* A4 width */
  height: 297mm; /* A4 height */
  margin: 40px auto; /* Center the A4 page on screen, add vertical margin */
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); /* Make it look like a physical page */
  background-color: white; /* Ensure a white background behind the image */
  overflow: hidden; /* Prevent content overflow */

  .contract-template-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover; /* Ensures the image covers the entire A4 area */
    z-index: 0;
  }

  .contract-data-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1; /* Ensure data is above the background image */
    font-family: 'Vazirmatn', sans-serif; /* Adjust font if needed, based on template */
    color: black;
    text-align: right; /* Default text alignment */
    font-size: 0.9rem; /* Slightly larger font for screen readability */
    line-height: 1.2; /* Tighter line spacing for forms */
  }

  /* --- Absolute Positioning for Data Fields (relative to .contract-data-overlay) ---
     Coordinates are now calculated as percentages based on a 300 DPI A4 image (2480px width x 3508px height).
     These values have been refined for better alignment with your template image.
     You may still need to fine-tune them slightly for pixel-perfect results on your specific setup.
  */

  /* Header Information */
  .data-contract-number { position: absolute; top: calc(550 / 3508 * 100%); left: calc(1750 / 2480 * 100%); width: calc(350 / 2480 * 100%); }
  .data-contract-date { position: absolute; top: calc(590 / 3508 * 100%); left: calc(1750 / 2480 * 100%); width: calc(350 / 2480 * 100%); }
  .data-contract-type { position: absolute; top: calc(630 / 3508 * 100%); left: calc(1750 / 2480 * 100%); width: calc(350 / 2480 * 100%); }
  .data-contract-place { position: absolute; top: calc(670 / 3508 * 100%); left: calc(1750 / 2480 * 100%); width: calc(350 / 2480 * 100%); }

  /* Husband Data (Right Column of Labels) */
  .data-husband-name { position: absolute; top: calc(915 / 3508 * 100%); left: calc(1700 / 2480 * 100%); width: calc(450 / 2480 * 100%); }
  .data-husband-birth-date { position: absolute; top: calc(915 / 3508 * 100%); left: calc(700 / 2480 * 100%); width: calc(450 / 2480 * 100%); }
  .data-husband-nationality { position: absolute; top: calc(955 / 3508 * 100%); left: calc(1700 / 2480 * 100%); width: calc(450 / 2480 * 100%); }
  .data-husband-passport-number { position: absolute; top: calc(955 / 3508 * 100%); left: calc(700 / 2480 * 100%); width: calc(450 / 2480 * 100%); }
  .data-husband-id-number { position: absolute; top: calc(995 / 3508 * 100%); left: calc(1700 / 2480 * 100%); width: calc(450 / 2480 * 100%); }

  /* Wife Data (Similar structure) */
  .data-wife-name { position: absolute; top: calc(1115 / 3508 * 100%); left: calc(1700 / 2480 * 100%); width: calc(450 / 2480 * 100%); }
  .data-wife-birth-date { position: absolute; top: calc(1115 / 3508 * 100%); left: calc(700 / 2480 * 100%); width: calc(450 / 2480 * 100%); }
  .data-wife-nationality { position: absolute; top: calc(1155 / 3508 * 100%); left: calc(1700 / 2480 * 100%); width: calc(450 / 2480 * 100%); }
  .data-wife-passport-number { position: absolute; top: calc(1155 / 3508 * 100%); left: calc(700 / 2480 * 100%); width: calc(450 / 2480 * 100%); }

  /* Dowry */
  .data-present-dowry { position: absolute; top: calc(1370 / 3508 * 100%); left: calc(1600 / 2480 * 100%); width: calc(400 / 2480 * 100%); }
  .data-deferred-dowry { position: absolute; top: calc(1370 / 3508 * 100%); left: calc(700 / 2480 * 100%); width: calc(400 / 2480 * 100%); }

  /* Conditions - Use divs for potential multi-line text */
  .data-husband-conditions-arabic { position: absolute; top: calc(1560 / 3508 * 100%); left: calc(1200 / 2480 * 100%); width: calc(800 / 2480 * 100%); height: calc(90 / 3508 * 100%); overflow: hidden; }
  .data-husband-conditions-persian { position: absolute; top: calc(1670 / 3508 * 100%); left: calc(1200 / 2480 * 100%); width: calc(800 / 2480 * 100%); height: calc(90 / 3508 * 100%); overflow: hidden; }
  .data-wife-conditions-arabic { position: absolute; top: calc(1560 / 3508 * 100%); left: calc(300 / 2480 * 100%); width: calc(800 / 2480 * 100%); height: calc(90 / 3508 * 100%); overflow: hidden; }
  .data-wife-conditions-persian { position: absolute; top: calc(1670 / 3508 * 100%); left: calc(300 / 2480 * 100%); width: calc(800 / 2480 * 100%); height: calc(90 / 3508 * 100%); overflow: hidden; }

  /* Witnesses and Officiant */
  .data-first-witness { position: absolute; top: calc(2180 / 3508 * 100%); left: calc(1150 / 2480 * 100%); width: calc(500 / 2480 * 100%); }
  .data-second-witness { position: absolute; top: calc(2220 / 3508 * 100%); left: calc(1150 / 2480 * 100%); width: calc(500 / 2480 * 100%); }
  .data-officiant-name { position: absolute; top: calc(2140 / 3508 * 100%); left: calc(1150 / 2480 * 100%); width: calc(500 / 2480 * 100%); }

  /* --- Styles for printing --- */
  @media print {
    /* Set page size and margins for A4 */
    @page {
      size: A4 portrait; /* Explicitly set to A4 portrait */
      margin: 0; /* Remove default print margins */
    }

    /* Crucial for printing backgrounds and colors */
    * {
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }

    /* Hide the entire #q-app by default to ensure no Quasar UI elements bleed through */
    #q-app {
      display: none !important;
      visibility: hidden !important;
      width: 0 !important;
      height: 0 !important;
      overflow: hidden !important;
      margin: 0 !important;
      padding: 0 !important;
    }

    /* Make the contract-page-print-container (q-page) visible for print */
    .contract-page-print-container {
      display: block !important;
      visibility: visible !important;
      position: absolute !important;
      top: 0 !important;
      left: 0 !important;
      width: 2480px !important;   /* A4 width at 300 DPI */
      height: 3508px !important;  /* A4 height at 300 DPI */
      margin: 0 !important;
      padding: 0 !important;
      background: none !important; /* No background for the page container itself */
      overflow: hidden !important;
    }

    /* Hide the screen-only-container that wraps the general UI elements */
    .screen-only-container {
        display: none !important;
        visibility: hidden !important;
    }

    /* Ensure the contract-print-area is the only visible content within the page */
    .contract-print-area {
      display: block !important;
      visibility: visible !important;
      position: absolute !important; /* Use absolute to ensure it covers the whole page */
      top: 0 !important;
      left: 0 !important;
      width: 2480px !important;   /* A4 width at 300 DPI */
      height: 3508px !important;  /* A4 height at 300 DPI */
      margin: 0 !important;
      padding: 0 !important;
      box-shadow: none !important;
      background-color: white !important; /* Ensure background for the area itself */
      overflow: hidden !important;
    }

    /* Ensure all content INSIDE the contract-print-area is visible */
    .contract-print-area * {
      visibility: visible !important;
      display: block !important;
    }

    /* Specific styles for background image and data overlay within the print area */
    .contract-template-background {
      position: absolute; /* Keep absolute relative to parent */
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      z-index: 0;
    }

    .contract-data-overlay {
      position: absolute; /* Keep absolute relative to parent */
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      font-size: 11pt; /* Consistent font size for print */
      line-height: 1.2;
      z-index: 1;
    }

    /* Reset body and layout elements for print to ensure a clean slate */
    body,
    html,
    .q-layout,
    .q-page-container {
      margin: 0 !important;
      padding: 0 !important;
      min-height: unset !important;
      height: auto !important;
      overflow: hidden !important; /* Prevent scrollbars */
    }
  }
}
</style>
