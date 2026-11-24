<script setup lang="ts">
import { trans } from '@/Composables/use-translations';
import { WikiCategoryOption } from '@/Types/wiki';
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps<{
  // The category to rename or move, or null to create a new one
  category: { id: string; name: string; parentId: string | null } | null;
  // Preselected parent for a new category
  parentId?: string | null;
  categoryOptions: WikiCategoryOption[];
}>();

const visible = defineModel<boolean>('visible', { required: true });

const form = useForm<{ name: string; parent_id: string | null }>({ name: '', parent_id: null });

watch(visible, (open) => {
  if (open) {
    form.clearErrors();
    form.name = props.category?.name ?? '';
    form.parent_id = props.category ? props.category.parentId : (props.parentId ?? null);
  }
});

// A category can't move into itself or below itself, the server checks that as well
const parentOptions = computed(() => {
  const own = props.category
    ? props.categoryOptions.find((option) => option.id === props.category!.id)
    : undefined;

  return [
    { id: null, path: trans('Top level') },
    ...props.categoryOptions.filter(
      (option) => !own || (option.id !== own.id && !option.path.startsWith(`${own.path} › `))
    )
  ];
});

const submit = () => {
  const options = { preserveScroll: true, onSuccess: () => (visible.value = false) };

  if (props.category) {
    form.put(route('wiki-categories.update', { wiki_category: props.category.id }), options);
  } else {
    form.post(route('wiki-categories.store'), options);
  }
};
</script>

<template>
  <Dialog
    v-model:visible="visible"
    modal
    :header="category ? $t('Edit category') : $t('New category')"
    class="w-[28rem] max-w-[calc(100vw-2rem)]"
  >
    <form class="space-y-4" @submit.prevent="submit">
      <div>
        <label for="category-name" class="block mb-1 font-medium">{{ $t('Name') }}</label>
        <InputText
          id="category-name"
          v-model="form.name"
          class="w-full"
          maxlength="255"
          autofocus
        />
        <div v-if="form.errors.name" class="text-red-600 mt-2">{{ form.errors.name }}</div>
      </div>
      <div>
        <label for="category-parent" class="block mb-1 font-medium">{{
          $t('Parent category')
        }}</label>
        <Select
          input-id="category-parent"
          v-model="form.parent_id"
          :options="parentOptions"
          option-label="path"
          option-value="id"
          filter
          class="w-full"
        />
        <div v-if="form.errors.parent_id" class="text-red-600 mt-2">
          {{ form.errors.parent_id }}
        </div>
      </div>
      <div class="flex justify-end gap-2 pt-2">
        <Button
          :label="$t('Cancel')"
          severity="secondary"
          outlined
          :disabled="form.processing"
          @click="visible = false"
        />
        <Button type="submit" :label="$t('Save')" icon="pi pi-check" :loading="form.processing" />
      </div>
    </form>
  </Dialog>
</template>
