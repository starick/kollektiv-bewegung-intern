<script setup lang="ts">
import Card from '@/Components/General/Card.vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import CategoryDialog from '@/Components/Wiki/CategoryDialog.vue';
import ChangelogLink from '@/Components/Wiki/ChangelogLink.vue';
import WikiTree from '@/Components/Wiki/WikiTree.vue';
import useAlert from '@/Composables/use-alerts';
import { trans } from '@/Composables/use-translations';
import {
  WikiCategoryNode,
  WikiCategoryOption,
  WikiChangelogLink,
  WikiPageLink
} from '@/Types/wiki';
import { Link, router } from '@inertiajs/vue3';
import { IconField, InputIcon, Menu, MenuItem, useConfirm } from 'primevue';
import { computed, ref, watch } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps<{
  categories: WikiCategoryNode[];
  rootPages: WikiPageLink[];
  pinned: WikiPageLink[];
  changelog: WikiChangelogLink | null;
  results: WikiPageLink[] | null;
  filters: { search: string };
  categoryOptions: WikiCategoryOption[];
  can: { create: boolean };
}>();

const confirm = useConfirm();
const alert = useAlert();

const search = ref(props.filters.search);
let searchTimeout: ReturnType<typeof setTimeout> | undefined;

watch(search, (value) => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(
    () =>
      router.get(
        route('wiki.index'),
        { search: value || undefined },
        { preserveState: true, replace: true, only: ['results', 'filters'] }
      ),
    300
  );
});

const formatDate = (value: string) => new Date(value).toLocaleDateString('de-DE');

const isEmpty = computed(() => !props.categories.length && !props.rootPages.length);

// Category dialog: editing an existing category, or creating one below `dialogParentId`
const dialogVisible = ref(false);
const dialogCategory = ref<WikiCategoryNode | null>(null);
const dialogParentId = ref<string | null>(null);

const openCategoryDialog = (category: WikiCategoryNode | null, parentId: string | null = null) => {
  dialogCategory.value = category;
  dialogParentId.value = parentId;
  dialogVisible.value = true;
};

const deleteCategory = (category: WikiCategoryNode) => {
  confirm.require({
    header: trans('Delete category'),
    message: trans('Delete the category ":name"?', { name: category.name }),
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: trans('Delete'),
    rejectLabel: trans('Cancel'),
    acceptClass: 'p-button-danger',
    accept: () =>
      router.delete(route('wiki-categories.destroy', { wiki_category: category.id }), {
        preserveScroll: true,
        onError: (errors) => alert.add(errors.category ?? trans('Something went wrong.'), 'error')
      })
  });
};

const categoryMenu = ref();
const menuCategory = ref<WikiCategoryNode | null>(null);
const categoryMenuItems = computed<MenuItem[]>(() => {
  const category = menuCategory.value;
  if (!category) {
    return [];
  }

  return [
    {
      label: trans('New page here'),
      icon: 'pi pi-file-plus',
      command: () => router.get(route('wiki.create', { category: category.id }))
    },
    {
      label: trans('New subcategory'),
      icon: 'pi pi-folder-plus',
      command: () => openCategoryDialog(null, category.id)
    },
    {
      label: trans('Rename or move'),
      icon: 'pi pi-pencil',
      command: () => openCategoryDialog(category)
    },
    {
      label: trans('Delete'),
      icon: 'pi pi-trash',
      command: () => deleteCategory(category)
    }
  ];
});

const showCategoryMenu = (event: Event, category: WikiCategoryNode) => {
  menuCategory.value = category;
  categoryMenu.value.toggle(event);
};

const menuItems = computed<MenuItem[]>(() =>
  props.can.create
    ? [
        {
          label: trans('New page'),
          icon: 'pi pi-plus',
          command: () => router.get(route('wiki.create'))
        },
        {
          label: trans('New category'),
          icon: 'pi pi-folder-plus',
          command: () => openCategoryDialog(null)
        }
      ]
    : []
);
</script>

<template>
  <AppLayout :title="$t('Wiki')">
    <Card :menu-items="menuItems">
      <template #menu-end>
        <IconField>
          <InputIcon class="pi pi-search" />
          <InputText
            v-model="search"
            :placeholder="$t('Search wiki')"
            :aria-label="$t('Search wiki')"
            @keydown.esc="search = ''"
          />
          <InputIcon
            v-if="search"
            class="pi pi-times cursor-pointer"
            role="button"
            tabindex="0"
            :aria-label="$t('Clear search')"
            @click="search = ''"
            @keydown.enter="search = ''"
          />
        </IconField>
      </template>

      <div class="max-w-4xl mx-auto py-2 sm:py-4 space-y-6">
        <section v-if="results === null && pinned.length" class="grid gap-3 sm:grid-cols-2">
          <Link
            v-for="page in pinned"
            :key="page.slug"
            :href="route('wiki.show', { page: page.slug })"
            class="flex items-center gap-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-4 hover:border-primary"
          >
            <i class="pi pi-thumbtack text-lg" aria-hidden="true" />
            <div class="min-w-0">
              <div class="font-semibold truncate">{{ page.title }}</div>
              <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ $t('Updated :date', { date: formatDate(page.updatedAt) }) }}
              </div>
            </div>
          </Link>
        </section>

        <section v-if="results !== null" :aria-label="$t('Search results')">
          <ul v-if="results.length" class="divide-y divide-gray-200 dark:divide-gray-700">
            <li v-for="page in results" :key="page.slug">
              <Link
                :href="route('wiki.show', { page: page.slug })"
                class="block py-3 px-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800"
              >
                <div class="font-semibold">{{ page.title }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                  {{ page.path ?? $t('Top level') }}
                </div>
              </Link>
            </li>
          </ul>
          <p v-else class="italic text-gray-500 dark:text-gray-400 py-4">
            {{ $t('No pages match your search.') }}
          </p>
        </section>

        <section v-else>
          <div
            v-if="isEmpty"
            class="rounded-xl border border-dashed border-gray-300 dark:border-gray-600 p-8 text-center text-gray-500 dark:text-gray-400"
          >
            <i class="pi pi-book text-2xl mb-2" aria-hidden="true" />
            <p>{{ $t('No pages yet.') }}</p>
            <Button
              v-if="can.create"
              class="mt-3"
              :label="$t('New page')"
              icon="pi pi-plus"
              text
              @click="router.get(route('wiki.create'))"
            />
          </div>
          <WikiTree v-else :categories="categories" :pages="rootPages" @menu="showCategoryMenu" />

          <ChangelogLink v-if="changelog" :changelog="changelog" />
        </section>
      </div>
    </Card>

    <Menu ref="categoryMenu" :model="categoryMenuItems" popup />
    <CategoryDialog
      v-model:visible="dialogVisible"
      :category="dialogCategory"
      :parent-id="dialogParentId"
      :category-options="categoryOptions"
    />
  </AppLayout>
</template>
