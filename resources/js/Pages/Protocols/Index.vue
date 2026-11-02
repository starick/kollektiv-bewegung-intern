<script setup lang="ts">
import Card from '@/Components/General/Card.vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { route } from 'ziggy-js';
import { router } from '@inertiajs/vue3';
import { computed, ref, toRef, watch } from 'vue';
import { Protocol } from '@/Types/protocol';
import useAlert from '@/Composables/use-alerts';
import { DataTablePageEvent, DataTableSortEvent, IconField, InputIcon, MenuItem, Tag } from 'primevue';

type Filters = { search: string; sort: 'date' | 'updatedAt'; direction: 'asc' | 'desc'; perPage: number };

const props = defineProps<{
  protocols: {
    data: Protocol[];
    meta: { current_page: number; per_page: number; total: number };
  };
  filters: Filters;
}>();
const protocols = toRef(props, 'protocols');

const first = computed(() => (protocols.value.meta.current_page - 1) * protocols.value.meta.per_page);

// Loads the list with changed filters; anything but a page change starts again on page 1
const visit = (changes: Partial<Filters> & { page?: number }) => {
  const params = { ...props.filters, page: undefined, ...changes };

  router.get(
    route('protocols.index'),
    {
      search: params.search || undefined,
      sort: params.sort,
      direction: params.direction,
      per_page: params.perPage,
      page: params.page
    },
    { preserveState: true, replace: true, only: ['protocols', 'filters'] }
  );
};

const search = ref(props.filters.search);
let searchTimeout: ReturnType<typeof setTimeout> | undefined;

watch(search, (value) => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => visit({ search: value }), 300);
});

const onPage = (e: DataTablePageEvent) => {
  visit({ page: e.page + 1, perPage: e.rows });
};

const onSort = (e: DataTableSortEvent) => {
  visit({
    sort: e.sortField === 'updatedAt' ? 'updatedAt' : 'date',
    direction: e.sortOrder === 1 ? 'asc' : 'desc'
  });
};

const alert = useAlert();

const reload = async () => {
  router.reload({
    only: ['protocols'],
    onError: (e) => alert.error('Reload error:', e),
    onSuccess: () => alert.add('reload successful', 'success')
  });
};

const rowClick = async (e: any) => {
  router.get(route('protocols.show', { protocol: e.data.id }));
};

const menuItems: MenuItem[] = [
  {
    label: 'Create New',
    icon: 'pi pi-plus',
    command: () => router.get(route('protocols.create'))
  },
  {
    label: 'Refresh',
    icon: 'pi pi-refresh',
    command: reload
  }
];

const title = 'Protocols Overview';
</script>

<template>
  <AppLayout :title="title">
    <Card :menu-items="menuItems">
      <template #menu-end>
        <IconField>
          <InputIcon class="pi pi-search" />
          <InputText
            v-model="search"
            placeholder="Search protocols"
            aria-label="Search protocols"
            @keydown.esc="search = ''"
          />
          <InputIcon
            v-if="search"
            class="pi pi-times cursor-pointer"
            role="button"
            tabindex="0"
            aria-label="Clear search"
            @click="search = ''"
            @keydown.enter="search = ''"
          />
        </IconField>
      </template>
      <DataTable
        :value="protocols.data"
        lazy
        paginator
        :rows="protocols.meta.per_page"
        :rows-per-page-options="[10, 20, 50]"
        :total-records="protocols.meta.total"
        :first="first"
        responsiveLayout="scroll"
        :sort-field="filters.sort"
        :sort-order="filters.direction === 'asc' ? 1 : -1"
        selectionMode="single"
        stripedRows
        @page="onPage"
        @sort="onSort"
        @row-click="rowClick"
      >
        <template #empty>
          {{ filters.search ? 'No protocols match your search.' : 'No protocols yet.' }}
        </template>
        <Column field="date" header="Protocol" sortable>
          <template #body="{ data }: { data: Protocol }">
            <div class="font-semibold">
              {{ new Date(data.date).toLocaleDateString('de-DE', { timeZone: 'UTC' }) }}
            </div>
            <div v-if="data.description" class="line-clamp-1 text-sm text-gray-600 dark:text-gray-400">
              {{ data.description }}
            </div>
          </template>
        </Column>
        <Column field="topics" header="Topics">
          <template #body="{ data }: { data: Protocol }">
            <div class="flex flex-wrap gap-1">
              <Tag v-for="topic in data.topics" :key="topic" :value="topic" severity="secondary" />
            </div>
          </template>
        </Column>
        <Column field="editor" header="Last Edited By">
          <template #body="{ data }: { data: Protocol }">
            {{ data.editor?.name }}
          </template>
        </Column>
        <Column field="updatedAt" header="Updated At" sortable>
          <template #body="{ data }: { data: Protocol }">
            {{ new Date(data.updatedAt).toLocaleDateString('de-DE') }}
          </template>
        </Column>
      </DataTable>
    </Card>
  </AppLayout>
</template>
