<script setup lang="ts">
import tinymce from 'tinymce';
import type { TinyMCE } from 'tinymce';

declare global {
  interface Window {
    tinymce: TinyMCE;
  }
}

window.tinymce = tinymce;
// import tinymce from 'tinymce/tinymce.js';

/* Required TinyMCE components */
import 'tinymce/themes/silver';
import 'tinymce/models/dom';

/* Default icons are required. After that, import custom icons if applicable */
import 'tinymce/icons/default';

/* Import a skin (can be a custom skin instead of the default) */

/* Import plugins */
// import "tinymce/plugins/advlist";
import 'tinymce/plugins/code';
// import "tinymce/plugins/emoticons";
// import "tinymce/plugins/emoticons/js/emojis";
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/table';
import 'tinymce/plugins/table';
import 'tinymce/plugins/image';
// import "tinymce/plugins/help";
import 'tinymce/plugins/wordcount';
import 'tinymce/plugins/directionality';

/* content UI CSS is required */
import contentUiSkinCss from 'tinymce/skins/ui/oxide/content.css?raw';

/* The default content CSS can be changed or replaced with appropriate CSS for the editor content. */
import contentCss from 'tinymce/skins/content/default/content.css?raw';

import Editor from '@tinymce/tinymce-vue';

import { useQuasar } from 'quasar';
import BaseFileManagerDialog from './BaseFileManagerDialog.vue';
import languageURL from '../assets/tinymce-ar.js?url';

const $q = useQuasar();

const initOptions = {
  // entity_encoding: "raw",
  language: 'ar',
  language_url: languageURL,
  promotion: false,
  plugins: 'lists link image table code  wordcount directionality',
  // skin: 'oxide-dark',
  // toolbar: "ltr rtl",
  toolbar:
    'undo redo | styles | bold italic | alignleft aligncenter alignright alignjustify | outdent indent | ltr rtl | image mediaembed editimage',

  file_picker_callback: function (cb: any, value: any, meta: any) {
    $q.dialog({
      component: BaseFileManagerDialog,
      componentProps: {
        type: 'post'
      }
    }).onOk((files) => {
      cb(files[0].url);
    });
  },
  directionality: 'rtl',
  skin: false,
  content_css: false,
  content_style: contentUiSkinCss.toString() + '\n' + contentCss.toString(),
  license_key: 'gpl'
};

const model = defineModel();
</script>
<style type="sass">
/*! rtl:begin:ignore */
@import 'tinymce/skins/ui/oxide/skin.css';
/*! rtl:end:ignore */
</style>
<template>
  <Editor v-model="model" :init="initOptions" />
</template>
