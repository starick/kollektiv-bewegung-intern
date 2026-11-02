import { router, useForm } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { route } from 'ziggy-js';
import { trans } from '@/Composables/use-translations';

export function protocolCreateModel() {
  const formData = useForm<{
    date: Date;
    description: string;
    topics: string;
    file: File | null;
  }>({
    date: new Date(),
    description: '',
    topics: '',
    file: null
  });

  function onFileSelect(e: { files?: File[] }) {
    formData.file = e.files?.[0] ?? null;
  }

  function onFileClear() {
    formData.file = null;
  }

  function submit() {
    formData
      .transform((data) => ({
        ...data,
        // Format locally; toISOString() would shift the day for timezones ahead of UTC
        date: data.date ? format(data.date, 'yyyy-MM-dd') : null
      }))
      .post(route('protocols.store'), {
        errorBag: 'createProtocol',
        preserveScroll: true,
        forceFormData: true
      });
  }

  function cancel() {
    router.get(route('protocols.index'));
  }

  const title = trans('Create New Protocol');

  return {
    formData,
    title,
    onFileSelect,
    onFileClear,
    submit,
    cancel
  };
}
