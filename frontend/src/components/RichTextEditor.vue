<script setup lang="ts">
import { onBeforeUnmount, watch } from 'vue';
import { EditorContent } from '@tiptap/vue-3';
import type { Editor } from '@tiptap/vue-3';

const props = defineProps<{
  editor: Editor | null;
}>();

watch(
  () => props.editor,
  (editor) => {
    if (editor) {
      editor.setOptions({
        editorProps: {
          attributes: {
            class: 'rich-text-editor__content'
          }
        }
      });
    }
  },
  { immediate: true }
);

onBeforeUnmount(() => {
  if (props.editor) {
    props.editor.destroy();
  }
});
</script>

<template>
  <div class="rich-text-editor">
    <div v-if="editor" class="rich-text-editor__toolbar">
      <q-btn-group flat>
        <q-btn
          flat
          round
          dense
          icon="format_bold"
          :color="editor.isActive('bold') ? 'primary' : 'grey'"
          @click="editor.chain().focus().toggleBold().run()"
        >
          <q-tooltip>Bold</q-tooltip>
        </q-btn>
        <q-btn
          flat
          round
          dense
          icon="format_italic"
          :color="editor.isActive('italic') ? 'primary' : 'grey'"
          @click="editor.chain().focus().toggleItalic().run()"
        >
          <q-tooltip>Italic</q-tooltip>
        </q-btn>
        <q-btn
          flat
          round
          dense
          icon="format_underlined"
          :color="editor.isActive('underline') ? 'primary' : 'grey'"
          @click="editor.chain().focus().toggleUnderline().run()"
        >
          <q-tooltip>Underline</q-tooltip>
        </q-btn>
      </q-btn-group>

      <q-separator vertical inset class="q-mx-sm" />

      <q-btn-group flat>
        <q-btn
          flat
          round
          dense
          icon="format_align_left"
          :color="editor.isActive({ textAlign: 'left' }) ? 'primary' : 'grey'"
          @click="editor.chain().focus().setTextAlign('left').run()"
        >
          <q-tooltip>Align Left</q-tooltip>
        </q-btn>
        <q-btn
          flat
          round
          dense
          icon="format_align_center"
          :color="editor.isActive({ textAlign: 'center' }) ? 'primary' : 'grey'"
          @click="editor.chain().focus().setTextAlign('center').run()"
        >
          <q-tooltip>Align Center</q-tooltip>
        </q-btn>
        <q-btn
          flat
          round
          dense
          icon="format_align_right"
          :color="editor.isActive({ textAlign: 'right' }) ? 'primary' : 'grey'"
          @click="editor.chain().focus().setTextAlign('right').run()"
        >
          <q-tooltip>Align Right</q-tooltip>
        </q-btn>
        <q-btn
          flat
          round
          dense
          icon="format_align_justify"
          :color="editor.isActive({ textAlign: 'justify' }) ? 'primary' : 'grey'"
          @click="editor.chain().focus().setTextAlign('justify').run()"
        >
          <q-tooltip>Justify</q-tooltip>
        </q-btn>
      </q-btn-group>
    </div>

    <editor-content v-if="editor" :editor="editor" class="rich-text-editor__content" />
    <div v-else class="rich-text-editor__placeholder">Loading editor...</div>
  </div>
</template>

<style lang="scss">
.rich-text-editor {
  border: 1px solid rgba(0, 0, 0, 0.12);
  border-radius: 4px;

  &__toolbar {
    display: flex;
    align-items: center;
    padding: 8px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.12);
  }

  &__content {
    padding: 16px;
    min-height: 200px;

    p {
      margin: 0;
    }
  }

  &__placeholder {
    padding: 16px;
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(0, 0, 0, 0.54);
  }
}

.body--dark {
  .rich-text-editor {
    border-color: rgba(255, 255, 255, 0.12);

    &__toolbar {
      border-bottom-color: rgba(255, 255, 255, 0.12);
    }

    &__placeholder {
      color: rgba(255, 255, 255, 0.54);
    }
  }
}
</style>
