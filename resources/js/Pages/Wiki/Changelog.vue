<script setup lang="ts">
import Card from '@/Components/General/Card.vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import MarkdownContent from '@/Components/Markdown/MarkdownContent.vue';
import WikiSidebar from '@/Components/Wiki/WikiSidebar.vue';
import { trans } from '@/Composables/use-translations';
import { WikiCategoryNode, WikiChangelogLink, WikiPageLink } from '@/Types/wiki';
import { router } from '@inertiajs/vue3';
import { MenuItem } from 'primevue';
import { route } from 'ziggy-js';

defineProps<{
  version: string | null;
  html: string;
  tree: { categories: WikiCategoryNode[]; rootPages: WikiPageLink[] };
  pinned: WikiPageLink[];
  changelog: WikiChangelogLink | null;
}>();

const menuItems: MenuItem[] = [
  {
    label: trans('Back to Wiki'),
    icon: 'pi pi-arrow-left',
    command: () => router.get(route('wiki.index'))
  }
];
</script>

<template>
  <AppLayout :title="$t('Changelog')">
    <Card :menu-items="menuItems">
      <div class="lg:flex lg:gap-8">
        <WikiSidebar :tree="tree" :pinned="pinned" :changelog="changelog" changelog-current />

        <div class="min-w-0 flex-1 max-w-4xl mx-auto py-2 sm:py-4">
          <h1 class="text-3xl font-bold mb-1">{{ $t('Changelog') }}</h1>
          <p
            class="pb-4 mb-6 border-b border-gray-200 dark:border-gray-700 text-sm text-gray-500 dark:text-gray-400"
          >
            {{ version ? $t('Version :version', { version }) : $t('What changed in the app') }}
          </p>

          <MarkdownContent :html="html" />
        </div>
      </div>
    </Card>
  </AppLayout>
</template>
