<script setup lang="ts">
import AuditTrail from '@/Components/Audits/AuditTrail.vue';
import Card from '@/Components/General/Card.vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import MarkdownContent from '@/Components/Markdown/MarkdownContent.vue';
import WikiTree from '@/Components/Wiki/WikiTree.vue';
import { trans } from '@/Composables/use-translations';
import { Audit } from '@/Types/audit';
import { WikiCategoryNode, WikiPage, WikiPageLink } from '@/Types/wiki';
import { router } from '@inertiajs/vue3';
import { MenuItem, useConfirm } from 'primevue';
import { route } from 'ziggy-js';

const props = defineProps<{
  page: WikiPage;
  breadcrumbs: { id: string; name: string }[];
  html: string;
  tree: { categories: WikiCategoryNode[]; rootPages: WikiPageLink[] };
  audits: Audit[];
  can: { update: boolean; delete: boolean };
}>();

const confirm = useConfirm();

const auditLabels = {
  title: trans('Title'),
  category: trans('Category'),
  pinned: trans('Pinned'),
  text: trans('Content')
};

const formatDateTime = (value: string) =>
  new Date(value).toLocaleString('de-DE', { dateStyle: 'medium', timeStyle: 'short' });

const edit = () => router.get(route('wiki.edit', { page: props.page.slug }));

const confirmDelete = () => {
  confirm.require({
    header: trans('Delete page'),
    message: trans('Delete the page ":title"? This cannot be undone.', { title: props.page.title }),
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: trans('Delete'),
    rejectLabel: trans('Cancel'),
    acceptClass: 'p-button-danger',
    accept: () => router.delete(route('wiki.destroy', { page: props.page.slug }))
  });
};

const menuItems: MenuItem[] = [
  {
    label: trans('Back to Wiki'),
    icon: 'pi pi-arrow-left',
    command: () => router.get(route('wiki.index'))
  }
];
</script>

<template>
  <AppLayout :title="page.title">
    <Card :menu-items="menuItems">
      <template #menu-end>
        <Button v-if="can.update" :label="$t('Edit')" icon="pi pi-pencil" text @click="edit" />
        <Button
          v-if="can.delete"
          :label="$t('Delete')"
          icon="pi pi-trash"
          severity="danger"
          text
          @click="confirmDelete"
        />
      </template>

      <div class="lg:flex lg:gap-8">
        <aside
          class="hidden lg:block w-64 shrink-0 self-start sticky top-4 max-h-[calc(100vh-2rem)] overflow-y-auto py-4 pr-4 border-r border-gray-200 dark:border-gray-700 text-sm"
          :aria-label="$t('Wiki')"
        >
          <WikiTree
            :categories="tree.categories"
            :pages="tree.rootPages"
            :current-slug="page.slug"
            :actions="false"
          />
        </aside>

        <div class="min-w-0 flex-1 max-w-4xl mx-auto py-2 sm:py-4">
          <nav
            v-if="breadcrumbs.length"
            :aria-label="$t('Category')"
            class="flex flex-wrap items-center gap-1 text-sm text-gray-500 dark:text-gray-400 mb-2"
          >
            <template v-for="(crumb, index) in breadcrumbs" :key="crumb.id">
              <i v-if="index > 0" class="pi pi-angle-right text-xs" aria-hidden="true" />
              <span>{{ crumb.name }}</span>
            </template>
          </nav>

          <h1 class="text-3xl font-bold mb-3 break-words">
            <i v-if="page.pinned" class="pi pi-thumbtack text-xl mr-1" :title="$t('Pinned')" />
            {{ page.title }}
          </h1>

          <dl
            class="flex flex-col sm:flex-row sm:flex-wrap gap-x-6 gap-y-1 pb-4 mb-6 border-b border-gray-200 dark:border-gray-700 text-sm text-gray-500 dark:text-gray-400"
          >
            <div class="flex items-center gap-2">
              <i class="pi pi-plus text-xs" aria-hidden="true" />
              <dt>{{ $t('Created') }}:</dt>
              <dd>
                <span class="font-medium text-gray-700 dark:text-gray-200">{{
                  page.creator?.name ?? '–'
                }}</span>
                · {{ formatDateTime(page.createdAt) }}
              </dd>
            </div>
            <div class="flex items-center gap-2">
              <i class="pi pi-pencil text-xs" aria-hidden="true" />
              <dt>{{ $t('Last Edited') }}:</dt>
              <dd v-if="page.editor">
                <span class="font-medium text-gray-700 dark:text-gray-200">{{
                  page.editor.name
                }}</span>
                · {{ formatDateTime(page.updatedAt) }}
              </dd>
              <dd v-else>–</dd>
            </div>
          </dl>

          <MarkdownContent v-if="html.trim()" :html="html" />
          <div
            v-else
            class="rounded-xl border border-dashed border-gray-300 dark:border-gray-600 p-8 text-center text-gray-500 dark:text-gray-400"
          >
            <i class="pi pi-file-edit text-2xl mb-2" aria-hidden="true" />
            <p>{{ $t('This page has no content yet.') }}</p>
            <Button
              v-if="can.update"
              class="mt-3"
              :label="$t('Write page')"
              icon="pi pi-pencil"
              text
              @click="edit"
            />
          </div>

          <details
            v-if="audits.length"
            class="group mt-10 rounded-xl border border-gray-200 dark:border-gray-700"
          >
            <summary
              class="flex cursor-pointer list-none items-center gap-2 p-4 sm:px-5 text-lg font-semibold"
            >
              <i class="pi pi-history" aria-hidden="true" />
              {{ $t('History') }}
              <span
                class="rounded-full bg-gray-200 dark:bg-gray-700 px-2 py-0.5 text-xs font-medium text-gray-700 dark:text-gray-200"
              >
                {{ audits.length }}
              </span>
              <i
                class="pi pi-chevron-down ml-auto text-sm transition-transform group-open:rotate-180"
                aria-hidden="true"
              />
            </summary>
            <div class="px-4 pb-5 sm:px-5 pt-2">
              <AuditTrail :audits="audits" :labels="auditLabels" />
            </div>
          </details>
        </div>
      </div>
    </Card>
  </AppLayout>
</template>
