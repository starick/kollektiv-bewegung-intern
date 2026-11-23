import { router, useForm } from '@inertiajs/vue3';
import { format, parseISO } from 'date-fns';
import { computed, ref } from 'vue';
import { EditMode } from '@/Composables/use-markdown-preview';
import { route } from 'ziggy-js';
import { trans } from '@/Composables/use-translations';
import { Protocol } from '@/Types/protocol';

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
    submit,
    cancel
  };
}
