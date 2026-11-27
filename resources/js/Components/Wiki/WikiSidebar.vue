<script setup lang="ts">
import ChangelogLink from '@/Components/Wiki/ChangelogLink.vue';
import WikiTree from '@/Components/Wiki/WikiTree.vue';
import { WikiCategoryNode, WikiChangelogLink, WikiPageLink } from '@/Types/wiki';
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

withDefaults(
  defineProps<{
    tree: { categories: WikiCategoryNode[]; rootPages: WikiPageLink[] };
    pinned: WikiPageLink[];
    changelog: WikiChangelogLink | null;
    // The page being read, highlighted in the navigation
    currentSlug?: string | null;
    changelogCurrent?: boolean;
  }>(),
  { currentSlug: null, changelogCurrent: false }
);
</script>

<template>
  <aside
    class="hidden lg:block w-64 shrink-0 self-start sticky top-4 max-h-[calc(100vh-2rem)] overflow-y-auto py-4 pr-4 border-r border-gray-200 dark:border-gray-700 text-sm"
    :aria-label="$t('Wiki')"
  >
    <ul
      v-if="pinned.length"
      class="space-y-0.5 pb-3 mb-3 border-b border-gray-200 dark:border-gray-700"
      :aria-label="$t('Pinned')"
    >
      <li v-for="link in pinned" :key="link.slug">
        <Link
          :href="route('wiki.show', { page: link.slug })"
          class="flex items-center gap-2 rounded-md py-1.5 px-2"
          :class="
            link.slug === currentSlug
              ? 'bg-gray-100 font-semibold dark:bg-gray-800'
              : 'hover:bg-gray-100 dark:hover:bg-gray-800'
          "
          :aria-current="link.slug === currentSlug ? 'page' : undefined"
        >
          <i class="pi pi-thumbtack text-gray-500" aria-hidden="true" />
          <span class="truncate">{{ link.title }}</span>
        </Link>
      </li>
    </ul>
    <WikiTree
      :categories="tree.categories"
      :pages="tree.rootPages"
      :current-slug="currentSlug"
      :actions="false"
    />
    <ChangelogLink v-if="changelog" :changelog="changelog" :current="changelogCurrent" />
  </aside>
</template>
