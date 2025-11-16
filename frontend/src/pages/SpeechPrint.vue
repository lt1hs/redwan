<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
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

// Print settings
const showHeader = ref(true);
const showFooter = ref(true);
const showSignature = ref(true);
const showDate = ref(true);

const paperStyles = computed(() => {
  if (speech.value && speech.value.paper_size === 'A3') {
    return {
      width: '297mm',
      height: '420mm',
      padding: '20mm'
    };
  }
  // Default A4
  return {
    width: '210mm',
    height: '297mm',
    padding: '15mm'
  };
});

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

<template>
  <q-page>
    <div class="non-printable">
      <base-breadcrumbs />
      <div class="q-pa-md">
        <q-card flat bordered class="q-mb-md">
          <q-card-section class="q-px-lg">
            <div class="row items-center justify-between q-mb-md">
              <div class="text-h6 text-primary">معاينة وطباعة الخطاب</div>
              <div class="row q-gutter-sm">
                <q-btn
                  color="grey"
                  icon="o_arrow_back"
                  label="العودة للقائمة"
                  flat
                  :to="{ name: 'SpeechIndex' }"
                />
                <q-btn color="primary" icon="o_print" label="طباعة" @click="printSpeech" />
                <q-btn
                  color="secondary"
                  icon="o_edit"
                  label="تعديل"
                  :to="{ name: 'SpeechEdit', params: { id } }"
                />
              </div>
            </div>

            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-3">
                <q-select
                  outlined
                  dense
                  v-model="speech.paper_size"
                  :options="['A4', 'A3']"
                  label="حجم الورق"
                  disable
                />
              </div>
              <div class="col-12 col-md-9">
                <div class="row q-col-gutter-md">
                  <div class="col-12 col-sm-3">
                    <q-toggle v-model="showHeader" label="إظهار الترويسة" />
                  </div>
                  <div class="col-12 col-sm-3">
                    <q-toggle v-model="showFooter" label="إظهار التذييل" />
                  </div>
                  <div class="col-12 col-sm-3">
                    <q-toggle v-model="showSignature" label="إظهار التوقيع" />
                  </div>
                  <div class="col-12 col-sm-3">
                    <q-toggle v-model="showDate" label="إظهار التاريخ" />
                  </div>
                </div>
              </div>
            </div>
          </q-card-section>
        </q-card>

        <q-card v-if="!loading && speech" flat bordered class="print-preview">
          <q-card-section class="paper" :style="paperStyles">
            <!-- Header -->
            <div v-if="showHeader && speech.header_image_url" class="header">
              <img :src="speech.header_image_url" alt="Header" />
            </div>

            <!-- Content -->
            <div class="content">
              <div class="title">{{ speech.title }}</div>

              <div v-if="showDate" class="date">التاريخ: {{ currentDate }}</div>

              <div class="recipient">إلى: {{ speech.recipient }}</div>

              <div class="speech-content" v-html="speech.content"></div>

              <div v-if="showSignature" class="signature">
                <div class="signature-text">التوقيع</div>
                <img
                  v-if="speech.signature_image_url"
                  :src="speech.signature_image_url"
                  alt="Signature"
                />
              </div>
            </div>

            <!-- Footer -->
            <div v-if="showFooter && speech.footer_image_url" class="footer">
              <img :src="speech.footer_image_url" alt="Footer" />
            </div>
          </q-card-section>
        </q-card>

        <div v-else-if="loading" class="full-width row flex-center q-pa-xl">
          <q-spinner-dots size="40px" color="primary" />
        </div>
      </div>
    </div>
  </q-page>
</template>

<style lang="scss" scoped>
.non-printable {
  @media print {
    display: none;
  }
}

.print-preview {
  background-color: #f5f5f5;
  padding: 2rem;

  @media print {
    padding: 0;
    background: none;
  }
}

.paper {
  background-color: white;
  margin: 0 auto;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  position: relative;
  overflow: hidden;
  direction: rtl;

  @media print {
    box-shadow: none;
    margin: 0;
    padding: 0;
  }
}

.header {
  text-align: center;
  margin-bottom: 2rem;

  img {
    max-width: 100%;
    max-height: 100px;
    object-fit: contain;
  }
}

.content {
  min-height: 70%;
}

.title {
  font-size: 24px;
  font-weight: bold;
  text-align: center;
  margin-bottom: 2rem;
  color: var(--q-primary);
}

.date {
  text-align: left;
  margin-bottom: 1.5rem;
  color: var(--q-secondary);
}

.recipient {
  font-weight: bold;
  margin-bottom: 2rem;
}

.speech-content {
  margin-bottom: 3rem;
  line-height: 1.8;
  text-align: justify;

  ::v-deep(p) {
    margin-bottom: 1rem;
  }
}

.signature {
  text-align: left;
  margin-top: 3rem;

  .signature-text {
    margin-bottom: 1rem;
    color: var(--q-secondary);
  }

  img {
    max-height: 80px;
    max-width: 200px;
    object-fit: contain;
  }
}

.footer {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  text-align: center;
  padding: 1rem;

  img {
    max-width: 100%;
    max-height: 80px;
    object-fit: contain;
  }
}

@media print {
  @page {
    size: auto;
    margin: 0mm;
  }

  body {
    margin: 0;
  }
}
</style>
