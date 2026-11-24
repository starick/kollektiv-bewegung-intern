<script setup lang="ts">
import MarkdownContent from '@/Components/Markdown/MarkdownContent.vue';
import { EditMode, useMarkdownPreview } from '@/Composables/use-markdown-preview';

defineProps<{
  error?: string;
}>();

const text = defineModel<string>({ required: true });
// A model, so the parent can switch back to editing when saving fails because of the text
const mode = defineModel<EditMode>('mode', { default: 'edit' });

const { previewHtml, previewLoading, previewError } = useMarkdownPreview(text, mode);
</script>

<template>
  <div>
    <Tabs v-model:value="mode">
      <TabList>
        <Tab value="edit"> <i class="pi pi-pencil mr-2" aria-hidden="true" />{{ $t('Edit') }} </Tab>
        <Tab value="preview">
          <i class="pi pi-eye mr-2" aria-hidden="true" />{{ $t('Preview') }}
        </Tab>
      </TabList>
      <TabPanels class="px-0">
        <TabPanel value="edit">
          <Textarea
            id="text"
            v-model="text"
            class="w-full font-mono text-sm"
            rows="25"
            :aria-label="$t('Content')"
          />
        </TabPanel>
        <TabPanel value="preview">
          <div v-if="previewLoading" class="flex justify-center py-12">
            <ProgressSpinner style="width: 2.5rem; height: 2.5rem" />
          </div>
          <div v-else-if="previewError" class="text-red-600 py-4">{{ previewError }}</div>
          <MarkdownContent v-else-if="previewHtml" :html="previewHtml" />
          <p v-else class="italic text-gray-500 dark:text-gray-400 py-4">
            {{ $t('Nothing to preview yet.') }}
          </p>
        </TabPanel>
      </TabPanels>
    </Tabs>
    <div v-if="error" class="text-red-600 mt-2">
      {{ error }}
    </div>
  </div>
</template>
