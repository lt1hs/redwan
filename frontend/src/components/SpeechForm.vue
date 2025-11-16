<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { useQuasar } from 'quasar';
import { useSpeechStore } from '@/stores/speech';
import { useField, useForm } from 'vee-validate';
import * as yup from 'yup';
import { Editor } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import TextAlign from '@tiptap/extension-text-align';
import RichTextEditor from '@/components/RichTextEditor.vue';

const props = defineProps({
  id: {
    type: Number,
    default: null
  },
  submitLoading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['submit']);
const $q = useQuasar();
const speechStore = useSpeechStore();

const schema = yup.object({
  title: yup.string().required('العنوان مطلوب'),
  recipient: yup.string().required('المستلم مطلوب'),
  content: yup.string().required('محتوى الخطاب مطلوب'),
  paper_size: yup.string().required('حجم الورق مطلوب'),
  template_image: yup.mixed(),
  header_image: yup.mixed(),
  footer_image: yup.mixed(),
  signature_image: yup.mixed()
});

const { handleSubmit, errors, setValues } = useForm({
  validationSchema: schema
});

const { value: title } = useField('title');
const { value: recipient } = useField('recipient');
const { value: content } = useField('content');
const { value: paper_size } = useField('paper_size');
const { value: template_image } = useField('template_image');
const { value: header_image } = useField('header_image');
const { value: footer_image } = useField('footer_image');
const { value: signature_image } = useField('signature_image');

const loading = ref(false);
const editor = ref<Editor | null>(null);
const templatePreviewUrl = ref('');
const showPreviewDialog = ref(false);

const paperSizes = [
  { label: 'A4', value: 'A4' },
  { label: 'A3', value: 'A3' }
];

async function fetch() {
  if (!props.id) return;

  loading.value = true;
  try {
    const speech = await speechStore.get(props.id);

    setValues({
      title: speech.title,
      recipient: speech.recipient,
      content: speech.content,
      paper_size: speech.paper_size
    });

    if (editor.value) {
      editor.value.commands.setContent(speech.content);
    }
  } catch (error) {
    console.error('Error fetching speech:', error);
    $q.notify({
      type: 'negative',
      message: 'حدث خطأ أثناء تحميل بيانات الخطاب'
    });
  } finally {
    loading.value = false;
  }
}

const submit = handleSubmit((values) => {
  emit('submit', { form: values });
});

function initEditor() {
  if (editor.value) {
    editor.value.destroy();
  }

  editor.value = new Editor({
    extensions: [
      StarterKit,
      Underline,
      TextAlign.configure({
        types: ['heading', 'paragraph'],
        alignments: ['left', 'center', 'right', 'justify']
      })
    ],
    content: '',
    onUpdate: ({ editor }) => {
      content.value = editor.getHTML();
    }
  });
}

function onTemplateUpload(file: File) {
  if (file) {
    templatePreviewUrl.value = URL.createObjectURL(file);
  }
}

function openPreview() {
  showPreviewDialog.value = true;
}

onMounted(() => {
  // Initialize editor
  initEditor();

  // Set default values
  paper_size.value = 'A4';

  if (props.id) {
    fetch();
  }
});

onBeforeUnmount(() => {
  if (templatePreviewUrl.value) {
    URL.revokeObjectURL(templatePreviewUrl.value);
  }
  if (editor.value) {
    editor.value.destroy();
    editor.value = null;
  }
});

defineExpose({
  submit,
  fetch
});
</script>

<template>
  <div class="speech-form">
    <q-inner-loading :showing="loading">
      <q-spinner-dots size="50px" color="primary" />
    </q-inner-loading>

    <div class="row q-col-gutter-md">
      <div class="col-12 col-md-6">
        <q-input
          outlined
          v-model="title"
          label="عنوان الخطاب *"
          :error="!!errors.title"
          :error-message="errors.title"
        />
      </div>

      <div class="col-12 col-md-6">
        <q-input
          outlined
          v-model="recipient"
          label="المستلم *"
          :error="!!errors.recipient"
          :error-message="errors.recipient"
        />
      </div>

      <div class="col-12">
        <div class="row q-col-gutter-md">
          <div class="col-12 col-md-6">
            <q-file
              outlined
              v-model="template_image"
              label="قالب الصفحة (A4)"
              accept=".jpg,.jpeg,.png"
              :error="!!errors.template_image"
              :error-message="errors.template_image"
              @update:model-value="onTemplateUpload"
            >
              <template v-slot:prepend>
                <q-icon name="o_file_upload" />
              </template>
              <template v-slot:after>
                <q-btn
                  v-if="templatePreviewUrl"
                  flat
                  round
                  color="primary"
                  icon="o_preview"
                  @click="openPreview"
                >
                  <q-tooltip>معاينة القالب</q-tooltip>
                </q-btn>
              </template>
            </q-file>
          </div>

          <div class="col-12 col-md-6">
            <q-select
              outlined
              v-model="paper_size"
              :options="paperSizes"
              label="حجم الورق *"
              :error="!!errors.paper_size"
              :error-message="errors.paper_size"
              emit-value
              map-options
            />
          </div>
        </div>
      </div>

      <!-- <div class="col-12 col-md-6">
        <q-file
          outlined
          v-model="header_image"
          label="صورة الترويسة"
          accept=".jpg,.jpeg,.png"
          :error="!!errors.header_image"
          :error-message="errors.header_image"
        >
          <template v-slot:prepend>
            <q-icon name="attach_file" />
          </template>
        </q-file>
      </div> -->

      <!-- <div class="col-12 col-md-6">
        <q-file
          outlined
          v-model="footer_image"
          label="صورة التذييل"
          accept=".jpg,.jpeg,.png"
          :error="!!errors.footer_image"
          :error-message="errors.footer_image"
        >
          <template v-slot:prepend>
            <q-icon name="attach_file" />
          </template>
        </q-file>
      </div> -->

      <!-- <div class="col-12 col-md-6">
        <q-file
          outlined
          v-model="signature_image"
          label="صورة التوقيع"
          accept=".jpg,.jpeg,.png"
          :error="!!errors.signature_image"
          :error-message="errors.signature_image"
        >
          <template v-slot:prepend>
            <q-icon name="attach_file" />
          </template>
        </q-file>
      </div> -->

      <div class="col-12">
        <div class="text-subtitle1 q-mb-sm">محتوى الخطاب *</div>
        <RichTextEditor :editor="editor" />
        <div v-if="errors.content" class="text-negative text-caption q-mt-sm">
          {{ errors.content }}
        </div>
      </div>

      <q-dialog v-model="showPreviewDialog" maximized>
        <q-card class="column full-height">
          <q-card-section class="row items-center q-pb-none">
            <div class="text-h6">معاينة قالب الصفحة</div>
            <q-space />
            <q-btn icon="close" flat round dense v-close-popup />
          </q-card-section>

          <q-card-section class="col q-pa-lg">
            <div class="row justify-center">
              <div class="col-auto">
                <div class="template-preview">
                  <img :src="templatePreviewUrl" alt="قالب الصفحة" />
                </div>
              </div>
            </div>
          </q-card-section>

          <q-card-actions align="right" class="bg-white">
            <q-btn flat label="إغلاق" color="primary" v-close-popup />
            <q-btn
              color="primary"
              label="طباعة"
              icon="o_print"
              @click="$q.dialog({ message: 'سيتم تنفيذ الطباعة قريباً' })"
            />
          </q-card-actions>
        </q-card>
      </q-dialog>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.speech-form {
  direction: rtl;
  position: relative;
  min-height: 200px;
}

.template-preview {
  max-width: 100%;
  overflow: auto;
  background: #f5f5f5;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);

  img {
    width: 21cm; // A4 width
    height: 29.7cm; // A4 height
    background: white;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
  }
}

@media print {
  .template-preview {
    padding: 0;
    box-shadow: none;

    img {
      width: 100%;
      height: auto;
      box-shadow: none;
    }
  }
}
</style>
