import axios from 'axios';
import { Ref, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import { trans } from '@/Composables/use-translations';

export type EditMode = 'edit' | 'preview';

/**
 * Edit/preview state for a Markdown text. Switching to preview renders the text on the
 * server, so the preview matches the saved page exactly.
 */
export function useMarkdownPreview(text: Ref<string>, mode: Ref<EditMode> = ref('edit')) {
  const previewHtml = ref('');
  const previewLoading = ref(false);
  const previewError = ref<string | null>(null);
  let previewedText: string | null = null;
  let previewRequest = 0;

  async function loadPreview() {
    if (text.value === previewedText) {
      return;
    }

    const request = ++previewRequest;
    const textToRender = text.value;
    previewLoading.value = true;
    previewError.value = null;

    try {
      const { data } = await axios.post<{ html: string }>(route('markdown.preview'), {
        text: textToRender
      });

      // Ignore responses that were overtaken by a newer request
      if (request !== previewRequest) {
        return;
      }

      previewHtml.value = data.html;
      previewedText = textToRender;
    } catch {
      if (request === previewRequest) {
        previewError.value = trans('The preview could not be loaded.');
      }
    } finally {
      if (request === previewRequest) {
        previewLoading.value = false;
      }
    }
  }

  watch(mode, (value) => {
    if (value === 'preview') {
      loadPreview();
    }
  });

  return { mode, previewHtml, previewLoading, previewError };
}
