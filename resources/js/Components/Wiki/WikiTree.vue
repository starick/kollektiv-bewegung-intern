<script setup lang="ts">
import { WikiCategoryNode, WikiPageLink } from '@/Types/wiki';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';

defineOptions({ name: 'WikiTree' });

defineProps<{
  categories: WikiCategoryNode[];
  pages: WikiPageLink[];
}>();

const emit = defineEmits<{ menu: [event: Event, category: WikiCategoryNode] }>();

// Categories start open, so the whole wiki is visible at a glance
const collapsed = ref<Record<string, boolean>>({});
const toggle = (id: string) => (collapsed.value[id] = !collapsed.value[id]);
</script>

<template>
  <ul class="space-y-0.5">
    <li v-for="category in categories" :key="category.id">
      <div
        class="group flex items-center gap-1 rounded-md pr-1 hover:bg-gray-100 dark:hover:bg-gray-800"
      >
        <button
          type="button"
          class="flex flex-1 min-w-0 items-center gap-2 py-1.5 pl-1 text-left font-semibold"
          :aria-expanded="!collapsed[category.id]"
          @click="toggle(category.id)"
        >
          <i
            class="pi text-xs text-gray-500"
            :class="collapsed[category.id] ? 'pi-chevron-right' : 'pi-chevron-down'"
            aria-hidden="true"
          />
          <i class="pi pi-folder text-gray-500" aria-hidden="true" />
          <span class="truncate">{{ category.name }}</span>
        </button>
        <Button
          icon="pi pi-ellipsis-v"
          text
          rounded
          size="small"
          severity="secondary"
          :aria-label="$t('Category actions')"
          aria-haspopup="true"
          @click="emit('menu', $event, category)"
        />
      </div>
      <WikiTree
        v-if="!collapsed[category.id]"
        class="ml-4 pl-2 border-l border-gray-200 dark:border-gray-700"
        :categories="category.children"
        :pages="category.pages"
        @menu="(event, child) => emit('menu', event, child)"
      />
    </li>
    <li v-for="page in pages" :key="page.slug">
      <Link
        :href="route('wiki.show', { page: page.slug })"
        class="flex items-center gap-2 rounded-md py-1.5 pl-6 pr-2 hover:bg-gray-100 dark:hover:bg-gray-800"
      >
        <i class="pi pi-file text-gray-500" aria-hidden="true" />
        <span class="truncate">{{ page.title }}</span>
      </Link>
    </li>
  </ul>
</template>
