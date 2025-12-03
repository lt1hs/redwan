<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
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
  template_type: yup.string()
});

const { handleSubmit, errors, setValues } = useForm({
  validationSchema: schema
});

const { value: title } = useField('title');
const { value: recipient } = useField('recipient');
const { value: content } = useField('content');
const { value: paper_size } = useField('paper_size');
const { value: template_type } = useField('template_type');

const loading = ref(false);
const editor = ref<Editor | null>(null);

const paperSizes = [
  { label: 'A4', value: 'A4' },
  { label: 'A3', value: 'A3' }
];

const templateOptions = computed(() => {
  return Object.entries(speechStore.templates).map(([key, template]) => ({
    label: template.name,
    value: key
  }));
});

async function fetch() {
  if (!props.id) return;

  loading.value = true;
  try {
    const speech = await speechStore.get(props.id);
    setValues({
      title: speech.title,
      recipient: speech.recipient,
      content: speech.content,
      paper_size: speech.paper_size,
      template_type: speech.template_type
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

function applyTemplate() {
  if (!template_type.value || !speechStore.templates[template_type.value]) return;
  
  const template = speechStore.templates[template_type.value];
  const templateContent = template.content_template
    .replace('{recipient}', recipient.value || '[المستلم]')
    .replace('{content}', '[محتوى الخطاب]');
  
  if (editor.value) {
    editor.value.commands.setContent(templateContent);
  }
}

onMounted(async () => {
  initEditor();
  paper_size.value = 'A4';
  
  try {
    await speechStore.fetchTemplates();
  } catch (error) {
    console.error('Error loading templates:', error);
  }

  if (props.id) {
    fetch();
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

      <div class="col-12 col-md-4">
        <q-select
          outlined
          v-model="template_type"
          :options="templateOptions"
          label="نوع القالب"
          emit-value
          map-options
          clearable
          @update:model-value="applyTemplate"
        />
      </div>

      <div class="col-12 col-md-4">
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

      <div class="col-12 col-md-4">
        <q-btn
          v-if="template_type"
          color="secondary"
          icon="o_refresh"
          label="تطبيق القالب"
          @click="applyTemplate"
          outline
        />
      </div>

      <div class="col-12">
        <div class="text-subtitle1 q-mb-sm">محتوى الخطاب *</div>
        <RichTextEditor :editor="editor" />
        <div v-if="errors.content" class="text-negative text-caption q-mt-sm">
          {{ errors.content }}
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.speech-form {
  direction: rtl;
  position: relative;
  min-height: 200px;
}
</style>
