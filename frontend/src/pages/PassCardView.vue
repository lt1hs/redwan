<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useQuasar } from 'quasar';
import { useCardsStore } from '@/stores/cards';

const route = useRoute();
const $q = useQuasar();
const cardsStore = useCardsStore();

const id = ref(Number(route.params.id));
const cardData = ref<any>(null);

onMounted(async () => {
  try {
    const response = await cardsStore.get(id.value);
    cardData.value = response;
  } catch (error) {
    console.error('Error fetching card data:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء جلب بيانات البطاقة'
    });
  }
});
</script>

<template>
  <q-page>
    <base-breadcrumbs />
    <div class="q-pa-md">
      <q-card flat bordered>
        <q-card-section>
          <div class="text-h6 text-primary q-mb-md">عرض البطاقة</div>

          <div v-if="cardData" class="q-gutter-y-md">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-6">
                <q-card-section class="text-center">
                  <div class="text-subtitle1 q-mb-sm">الصورة الشخصية</div>
                  <q-img
                    :src="cardData.personal_photo_url"
                    style="max-width: 250px; height: auto; border-radius: 8px; border: 1px solid #ddd;"
                    class="q-mb-md"
                  />
                </q-card-section>
              </div>
              <div class="col-12 col-md-6">
                <q-card-section class="text-center">
                  <div class="text-subtitle1 q-mb-sm">صورة جواز السفر</div>
                  <q-img
                    :src="cardData.passport_photo_url"
                    style="max-width: 250px; height: auto; border-radius: 8px; border: 1px solid #ddd;"
                    class="q-mb-md"
                  />
                </q-card-section>
              </div>
            </div>

            <q-separator />

            <div class="row q-col-gutter-md q-mt-md">
              <div class="col-12 col-md-6">
                <q-field filled label="نام کامل" stack-label readonly class="q-mb-md">
                  <template v-slot:prepend><q-icon name="person" /></template>
                  <template v-slot:control><div class="self-center full-width no-outline">{{ cardData.full_name_fa }}</div></template>
                </q-field>
                <q-field filled label="Full Name" stack-label readonly class="q-mb-md">
                  <template v-slot:prepend><q-icon name="person" /></template>
                  <template v-slot:control><div class="self-center full-width no-outline">{{ cardData.full_name_en }}</div></template>
                </q-field>
                <q-field filled label="نام پدر" stack-label readonly class="q-mb-md">
                  <template v-slot:prepend><q-icon name="person_outline" /></template>
                  <template v-slot:control><div class="self-center full-width no-outline">{{ cardData.father_name_fa }}</div></template>
                </q-field>
                <q-field filled label="Father's Name" stack-label readonly class="q-mb-md">
                  <template v-slot:prepend><q-icon name="person_outline" /></template>
                  <template v-slot:control><div class="self-center full-width no-outline">{{ cardData.father_name_en }}</div></template>
                </q-field>
                <q-field filled label="تابعیت" stack-label readonly class="q-mb-md">
                  <template v-slot:prepend><q-icon name="flag" /></template>
                  <template v-slot:control><div class="self-center full-width no-outline">{{ cardData.citizenship_fa }}</div></template>
                </q-field>
                <q-field filled label="Citizenship" stack-label readonly class="q-mb-md">
                  <template v-slot:prepend><q-icon name="flag" /></template>
                  <template v-slot:control><div class="self-center full-width no-outline">{{ cardData.citizenship_en }}</div></template>
                </q-field>
              </div>
              <div class="col-12 col-md-6">
                <q-field filled label="شماره پاسپورت" stack-label readonly class="q-mb-md">
                  <template v-slot:prepend><q-icon name="badge" /></template>
                  <template v-slot:control><div class="self-center full-width no-outline">{{ cardData.passport_number }}</div></template>
                </q-field>
                <q-field filled label="کد ملی" stack-label readonly class="q-mb-md">
                  <template v-slot:prepend><q-icon name="fingerprint" /></template>
                  <template v-slot:control><div class="self-center full-width no-outline">{{ cardData.national_id }}</div></template>
                </q-field>
                <q-field filled label="كد پليس" stack-label readonly class="q-mb-md">
                  <template v-slot:prepend><q-icon name="local_police" /></template>
                  <template v-slot:control><div class="self-center full-width no-outline">{{ cardData.police_code }}</div></template>
                </q-field>
                <q-field filled label="نوع کارت" stack-label readonly class="q-mb-md">
                  <template v-slot:prepend><q-icon name="credit_card" /></template>
                  <template v-slot:control><div class="self-center full-width no-outline">{{ cardData.card_type }}</div></template>
                </q-field>
                <q-field filled label="وضعیت" stack-label readonly class="q-mb-md">
                  <template v-slot:prepend><q-icon name="toggle_on" /></template>
                  <template v-slot:control><div class="self-center full-width no-outline">{{ cardData.status }}</div></template>
                </q-field>
                <q-field filled label="تاريخ انقضاي كارت" stack-label readonly class="q-mb-md">
                  <template v-slot:prepend><q-icon name="event_busy" /></template>
                  <template v-slot:control><div class="self-center full-width no-outline">{{ cardData.card_expiry_date }}</div></template>
                </q-field>
              </div>
            </div>
          </div>
          <q-inner-loading :showing="!cardData">
            <q-spinner-gears size="50px" color="primary" />
          </q-inner-loading>
        </q-card-section>
      </q-card>
    </div>
  </q-page>
</template>
