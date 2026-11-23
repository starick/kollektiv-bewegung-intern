<script setup lang="ts">
import MarkdownEditor from '@/Components/Markdown/MarkdownEditor.vue';
import { trans } from '@/Composables/use-translations';
import { WikiCategoryOption } from '@/Types/wiki';
import { InertiaForm } from '@inertiajs/vue3';
import { ToggleSwitch } from 'primevue';
import { computed } from 'vue';
import { EditMode } from '@/Composables/use-markdown-preview';

const props = defineProps<{
  form: InertiaForm<{ title: string; category_id: string | null; pinned: boolean; text: string }>;
  categoryOptions: WikiCategoryOption[];
}>();

const mode = defineModel<EditMode>('mode', { required: true });

defineEmits<{ submit: []; cancel: [] }>();

const options = computed(() => [{ id: null, path: trans('Top level') }, ...props.categoryOptions]);

const labelClass =
  'block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1';
</script>

<template>
  <form class="max-w-4xl mx-auto py-2 sm:py-4" @submit.prevent="$emit('submit')">
    <header class="mb-6 space-y-4">
      <div>
        <label for="title" :class="labelClass">{{ $t('Title') }}</label>
        <InputText
          id="title"
          v-model="form.title"
          class="w-full text-xl font-bold"
          maxlength="255"
          autofocus
        />
        <div v-if="form.errors.title" class="text-red-600 mt-2">{{ form.errors.title }}</div>
      </div>

      <div class="flex flex-col sm:flex-row sm:items-end gap-4">
        <div class="flex-1 min-w-0">
          <label for="category" :class="labelClass">{{ $t('Category') }}</label>
          <Select
            input-id="category"
            v-model="form.category_id"
            :options="options"
            option-label="path"
            option-value="id"
            filter
            class="w-full"
          />
          <div v-if="form.errors.category_id" class="text-red-600 mt-2">
            {{ form.errors.category_id }}
          </div>
        </div>
        <label class="flex items-center gap-2 sm:pb-2 cursor-pointer">
          <ToggleSwitch v-model="form.pinned" input-id="pinned" />
          <span>{{ $t('Pin to the top of the wiki') }}</span>
        </label>
      </div>
    </header>

    <MarkdownEditor v-model="form.text" v-model:mode="mode" :error="form.errors.text" />

    <div class="flex flex-row justify-center gap-4 mt-8">
      <Button
        :label="$t('Cancel')"
        severity="secondary"
        outlined
        :disabled="form.processing"
        @click="$emit('cancel')"
      />
      <Button type="submit" :label="$t('Save')" icon="pi pi-check" :loading="form.processing" />
    </div>
  </form>
</template>
