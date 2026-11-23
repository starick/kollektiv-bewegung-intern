import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';
import { EditMode } from '@/Composables/use-markdown-preview';
import { WikiPage } from '@/Types/wiki';

// Creates a page when `page` is null, otherwise updates it
export function wikiPageFormModel(
  page: WikiPage | null,
  initial: { text: string; categoryId: string | null }
) {
  const formData = useForm<{
    title: string;
    category_id: string | null;
    pinned: boolean;
    text: string;
  }>({
    title: page?.title ?? '',
    category_id: page ? page.categoryId : initial.categoryId,
    pinned: page?.pinned ?? false,
    text: initial.text
  });

  const mode = ref<EditMode>('edit');

  function submit() {
    const options = {
      errorBag: 'wikiPage',
      preserveScroll: true,
      // Show the text field again if saving fails because of it
      onError: (errors: Record<string, string>) => {
        if (errors.text) {
          mode.value = 'edit';
        }
      }
    };

    if (page) {
      formData.put(route('wiki.update', { page: page.slug }), options);
    } else {
      formData.post(route('wiki.store'), options);
    }
  }

  function cancel() {
    router.get(page ? route('wiki.show', { page: page.slug }) : route('wiki.index'));
  }

  return { formData, mode, submit, cancel };
}
