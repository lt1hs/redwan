<template>
  <q-page class="q-pa-md speech-page-print-container">
    <div class="q-mx-auto screen-only-container">
      <base-breadcrumbs />
      <q-card flat bordered class="creation-card q-pa-lg">
        <q-card-section class="q-pb-none">
          <div class="text-h5 text-weight-bold q-mb-md row items-center">
            <q-icon name="o_description" color="primary" size="32px" class="q-mr-sm" />
            طباعة الخطاب / Print Speech
          </div>
        </q-card-section>

        <q-card-section v-if="speech" class="q-pa-lg text-right" dir="rtl">
          <div class="speech-print-area" dir="rtl">
            <img src="/a4_template.jpg.png" class="speech-template-background" />
            <div class="speech-data-overlay">
              <div class="speech-title">{{ speech.title }}</div>
              <div class="speech-date">التاريخ: {{ currentDate }}</div>
              <div class="speech-recipient">إلى: {{ speech.recipient }}</div>
              <div class="speech-content" v-html="speech.content"></div>
              <div v-if="showSignature" class="speech-signature">التوقيع</div>
            </div>
          </div>
        </q-card-section>

        <div v-else-if="loading" class="q-pa-xl text-center">
          <q-spinner-dots size="40px" color="primary" />
        </div>

        <q-card-actions align="right" class="q-pt-lg">
          <q-btn
            color="primary"
            label="طباعة"
            icon="print"
            @click="printSpeech"
            :disable="loading || !speech"
          />
          <q-btn
            color="negative"
            flat
            label="العودة"
            @click="router.go(-1)"
          />
        </q-card-actions>
      </q-card>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useQuasar } from 'quasar';
import { useSpeechStore } from '@/stores/speech';

const route = useRoute();
const router = useRouter();
const $q = useQuasar();
const speechStore = useSpeechStore();

const id = ref(Number(route.params.id));
const speech = ref(null);
const loading = ref(true);
const currentDate = new Date().toLocaleDateString('ar-SA');
const showSignature = ref(true);

async function fetchSpeech() {
  try {
    loading.value = true;
    speech.value = await speechStore.get(id.value);
  } catch (error) {
    console.error('Error fetching speech:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء تحميل بيانات الخطاب'
    });
    router.push({ name: 'SpeechIndex' });
  } finally {
    loading.value = false;
  }
}

function printSpeech() {
  window.print();
}

onMounted(() => {
  fetchSpeech();
});
</script>

<style lang="scss" scoped>
.creation-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.speech-print-area {
  box-sizing: border-box;
  padding: 0;
  margin: 0;
  position: relative;

  width: 210mm;
  height: 297mm;
  margin: 40px auto;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
  background-color: white;
  overflow: hidden;

  .speech-template-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: 0;
  }

  .speech-data-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
    font-family: 'Vazirmatn', sans-serif;
    color: black;
    text-align: right;
    font-size: 0.9rem;
    line-height: 1.4;
  }

  .speech-title {
    position: absolute;
    top: calc(800 / 3508 * 100%);
    left: calc(300 / 2480 * 100%);
    width: calc(1880 / 2480 * 100%);
    text-align: center;
    font-size: 1.2rem;
    font-weight: bold;
  }

  .speech-date {
    position: absolute;
    top: calc(900 / 3508 * 100%);
    left: calc(1600 / 2480 * 100%);
    width: calc(580 / 2480 * 100%);
  }

  .speech-recipient {
    position: absolute;
    top: calc(1000 / 3508 * 100%);
    left: calc(300 / 2480 * 100%);
    width: calc(800 / 2480 * 100%);
    font-weight: bold;
  }

  .speech-content {
    position: absolute;
    top: calc(1150 / 3508 * 100%);
    left: calc(300 / 2480 * 100%);
    width: calc(1880 / 2480 * 100%);
    height: calc(1800 / 3508 * 100%);
    overflow: hidden;
    text-align: justify;
    line-height: 1.6;

    ::v-deep(p) {
      margin-bottom: 0.5rem;
    }
  }

  .speech-signature {
    position: absolute;
    top: calc(3000 / 3508 * 100%);
    left: calc(1600 / 2480 * 100%);
    width: calc(580 / 2480 * 100%);
    text-align: center;
  }

  @media print {
    @page {
      size: A4 portrait;
      margin: 0;
    }

    * {
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }

    #q-app {
      display: none !important;
      visibility: hidden !important;
      width: 0 !important;
      height: 0 !important;
      overflow: hidden !important;
      margin: 0 !important;
      padding: 0 !important;
    }

    .speech-page-print-container {
      display: block !important;
      visibility: visible !important;
      position: absolute !important;
      top: 0 !important;
      left: 0 !important;
      width: 2480px !important;
      height: 3508px !important;
      margin: 0 !important;
      padding: 0 !important;
      background: none !important;
      overflow: hidden !important;
    }

    .screen-only-container {
      display: none !important;
      visibility: hidden !important;
    }

    .speech-print-area {
      display: block !important;
      visibility: visible !important;
      position: absolute !important;
      top: 0 !important;
      left: 0 !important;
      width: 2480px !important;
      height: 3508px !important;
      margin: 0 !important;
      padding: 0 !important;
      box-shadow: none !important;
      background-color: white !important;
      overflow: hidden !important;
    }

    .speech-print-area * {
      visibility: visible !important;
      display: block !important;
    }

    .speech-template-background {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      z-index: 0;
    }

    .speech-data-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      font-size: 11pt;
      line-height: 1.4;
      z-index: 1;
    }

    body,
    html,
    .q-layout,
    .q-page-container {
      margin: 0 !important;
      padding: 0 !important;
      min-height: unset !important;
      height: auto !important;
      overflow: hidden !important;
    }
  }
}
</style>
