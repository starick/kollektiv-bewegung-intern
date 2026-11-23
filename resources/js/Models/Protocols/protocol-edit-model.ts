import { router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { format, parseISO } from 'date-fns';
import { computed, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import { trans } from '@/Composables/use-translations';
import { Protocol } from '@/Types/protocol';

export type EditMode = 'edit' | 'preview';

// Mirrors how the server splits the comma separated inputs, so the chips match what gets saved
function commaSeparated(value: string): string[] {
  return [
    ...new Set(
      value
        .split(',')
        .map((entry) => entry.trim())
        .filter(Boolean)
    )
  ];
}

export function protocolEditModel(protocol: Protocol, text: string) {
  const formData = useForm<{
    date: Date;
    description: string;
    topics: string;
    participants: string;
    text: string;
  }>({
    // parseISO reads a plain date as local midnight, so the calendar shows the stored day
    date: parseISO(protocol.date),
    description: protocol.description ?? '',
    topics: protocol.topics.join(', '),
    participants: protocol.participants.join(', '),
    text
  });

  const participants = computed(() => commaSeparated(formData.participants));
  const topics = computed(() => commaSeparated(formData.topics));

  const mode = ref<EditMode>('edit');
  const previewHtml = ref('');
  const previewLoading = ref(false);
  const previewError = ref<string | null>(null);
  let previewedText: string | null = null;
  let previewRequest = 0;

  // Renders on the server, so the preview matches the saved protocol exactly
  async function loadPreview() {
    if (formData.text === previewedText) {
      return;
    }

    const request = ++previewRequest;
    const textToRender = formData.text;
    previewLoading.value = true;
    previewError.value = null;

    try {
      const { data } = await axios.post<{ html: string }>(route('protocols.preview'), {
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

  function submit() {
    formData
      .transform((data) => ({
        ...data,
        // Format locally; toISOString() would shift the day for timezones ahead of UTC
        date: data.date ? format(data.date, 'yyyy-MM-dd') : null
      }))
      .put(route('protocols.update', { protocol: protocol.slug }), {
        errorBag: 'updateProtocol',
        preserveScroll: true,
        // Show the text field again if saving fails because of it
        onError: (errors) => {
          if (errors.text) {
            mode.value = 'edit';
          }
        }
      });
  }

  function cancel() {
    router.get(route('protocols.show', { protocol: protocol.slug }));
  }

  const title = trans('Edit Protocol');

  return {
    formData,
    title,
    participants,
    topics,
    mode,
    previewHtml,
    previewLoading,
    previewError,
    submit,
    cancel
  };
}
