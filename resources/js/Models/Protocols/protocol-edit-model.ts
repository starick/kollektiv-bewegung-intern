import { router, useForm } from '@inertiajs/vue3';
import { format, parseISO } from 'date-fns';
import { route } from 'ziggy-js';
import { trans } from '@/Composables/use-translations';
import { Protocol } from '@/Types/protocol';

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

  function submit() {
    formData
      .transform((data) => ({
        ...data,
        // Format locally; toISOString() would shift the day for timezones ahead of UTC
        date: data.date ? format(data.date, 'yyyy-MM-dd') : null
      }))
      .put(route('protocols.update', { protocol: protocol.id }), {
        errorBag: 'updateProtocol',
        preserveScroll: true
      });
  }

  function cancel() {
    router.get(route('protocols.show', { protocol: protocol.id }));
  }

  const title = trans('Edit Protocol');

  return {
    formData,
    title,
    submit,
    cancel
  };
}
