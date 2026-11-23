<script setup lang="ts">
import { Audit, AuditChange, AuditEvent, DiffLine } from '@/Types/audit';
import { trans } from '@/Composables/use-translations';

const props = defineProps<{
  audits: Audit[];
  // Display names per attribute; attributes without a label are not shown
  labels: Record<string, string>;
}>();

const events: Record<AuditEvent, { label: string; icon: string; color: string }> = {
  created: {
    label: trans('Created'),
    icon: 'pi pi-plus',
    color: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-200'
  },
  updated: {
    label: trans('Edited'),
    icon: 'pi pi-pencil',
    color: 'bg-sky-100 text-sky-700 dark:bg-sky-900 dark:text-sky-200'
  },
  deleted: {
    label: trans('Deleted'),
    icon: 'pi pi-trash',
    color: 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200'
  },
  restored: {
    label: trans('Restored'),
    icon: 'pi pi-replay',
    color: 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200'
  }
};

const formatDateTime = (value: string) =>
  new Date(value).toLocaleString('de-DE', { dateStyle: 'medium', timeStyle: 'short' });

const formatValue = (value: unknown): string => {
  if (value === null || value === undefined || value === '') {
    return '–';
  }
  if (typeof value === 'boolean') {
    return value ? trans('Yes') : trans('No');
  }
  if (Array.isArray(value)) {
    return value.length ? value.join(', ') : '–';
  }
  if (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(value)) {
    return new Date(value).toLocaleDateString('de-DE', { timeZone: 'UTC' });
  }
  return String(value);
};

const diffLineClass: Record<DiffLine['type'], string> = {
  context: 'text-gray-600 dark:text-gray-400',
  added: 'bg-emerald-50 text-emerald-900 dark:bg-emerald-950 dark:text-emerald-200',
  removed: 'bg-red-50 text-red-900 dark:bg-red-950 dark:text-red-200'
};

// Screen readers get the meaning from the prefix, colour alone isn't enough
const diffLinePrefix: Record<DiffLine['type'], string> = {
  context: ' ',
  added: '+',
  removed: '−'
};

// Creation shows no field list, the current values are on the page anyway
const visibleChanges = (audit: Audit): AuditChange[] =>
  audit.event === 'updated'
    ? audit.changes.filter((change) => change.attribute in props.labels)
    : [];
</script>

<template>
  <ol class="relative border-l border-gray-200 dark:border-gray-700 ml-3.5 space-y-6">
    <li v-for="audit in audits" :key="audit.id" class="ml-6">
      <span
        class="absolute -left-3.5 flex size-7 items-center justify-center rounded-full ring-4 ring-white dark:ring-gray-900"
        :class="events[audit.event]?.color"
        aria-hidden="true"
      >
        <i :class="events[audit.event]?.icon" class="text-xs" />
      </span>

      <p class="text-sm">
        <span class="font-semibold">{{ events[audit.event]?.label ?? audit.event }}</span>
        {{ $t('by') }}
        <span class="font-medium">{{ audit.user?.name ?? $t('Unknown user') }}</span>
        <time
          :datetime="audit.createdAt"
          class="block sm:inline sm:ml-2 text-gray-500 dark:text-gray-400"
        >
          {{ formatDateTime(audit.createdAt) }}
        </time>
      </p>

      <dl v-if="visibleChanges(audit).length" class="mt-2 space-y-1 text-sm">
        <div
          v-for="change in visibleChanges(audit)"
          :key="change.attribute"
          class="flex flex-col sm:flex-row sm:gap-2"
        >
          <dt class="font-medium text-gray-600 dark:text-gray-400 shrink-0">
            {{ labels[change.attribute] }}:
          </dt>
          <dd v-if="change.type === 'diff'" class="min-w-0 flex-1">
            <details class="group/diff">
              <summary class="cursor-pointer list-none inline-flex items-center gap-2">
                <span class="font-mono text-xs">
                  <span class="text-emerald-700 dark:text-emerald-400">+{{ change.added }}</span>
                  <span class="ml-1 text-red-700 dark:text-red-400">−{{ change.removed }}</span>
                </span>
                <span class="text-gray-600 dark:text-gray-400 underline decoration-dotted">
                  <span class="group-open/diff:hidden">{{ $t('Show changes') }}</span>
                  <span class="hidden group-open/diff:inline">{{ $t('Hide changes') }}</span>
                </span>
              </summary>
              <div
                class="mt-2 max-h-96 overflow-auto rounded-lg border border-gray-200 dark:border-gray-700 font-mono text-xs"
              >
                <template v-for="(hunk, hunkIndex) in change.hunks" :key="hunkIndex">
                  <div
                    v-if="hunkIndex > 0"
                    class="px-3 py-0.5 text-center text-gray-400 bg-gray-50 dark:bg-gray-800/50"
                    aria-hidden="true"
                  >
                    ⋯
                  </div>
                  <div
                    v-for="(line, lineIndex) in hunk"
                    :key="lineIndex"
                    class="flex whitespace-pre-wrap break-words"
                    :class="diffLineClass[line.type]"
                  >
                    <span class="w-6 shrink-0 select-none text-center opacity-60">{{
                      diffLinePrefix[line.type]
                    }}</span>
                    <span class="flex-1 pr-3">{{ line.text || ' ' }}</span>
                  </div>
                </template>
              </div>
            </details>
          </dd>
          <dd v-else class="break-words">
            <span class="line-through text-gray-500 dark:text-gray-400">{{
              formatValue(change.old)
            }}</span>
            <i class="pi pi-arrow-right text-xs mx-2 text-gray-400" aria-hidden="true" />
            <span>{{ formatValue(change.new) }}</span>
          </dd>
        </div>
      </dl>
    </li>
  </ol>
</template>
