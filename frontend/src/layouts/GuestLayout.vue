<template>
  <q-layout view="hHh lpR fFf">
    <q-page-container>
      <q-page class="q-pa-md flex flex-center">
        <q-card style="width: 100%; max-width: 400px; margin: 0 auto">
          <q-card-section class="relative-position">
            <q-btn
              @click="
                $q.dark.toggle();
                $q.localStorage.set('dark-mode', $q.dark.isActive);
              "
              :icon="$q.dark.mode ? 'o_dark_mode' : 'o_light_mode'"
              round
              dense
              flat
              style="z-index: 1000; top: 10px"
              class="text-body1 q-ml-sm absolute-top-left"
            />
            <router-view />
          </q-card-section>
        </q-card>
      </q-page>
    </q-page-container>
  </q-layout>
</template>
