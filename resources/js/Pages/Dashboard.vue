<script setup lang="ts">
import Card from '@/Components/General/Card.vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import TimeTablePreview from '@/Components/TimeTables/TimeTablePreview.vue';
import { currentLocale, trans } from '@/Composables/use-translations';
import { relativeTime } from '@/Helpers/relative-time';
import { AuditEvent } from '@/Types/audit';
import { Course } from '@/Types/course';
import { TimeTable } from '@/Types/time-table';
import { Link } from '@inertiajs/vue3';
import { Tag } from 'primevue';
import { route } from 'ziggy-js';

defineProps<{
  timeTable: (TimeTable & { courses: Course[] }) | null;
  // null for people outside the team, who can't open protocols or the wiki
  protocol: {
    slug: string;
    date: string;
    description: string | null;
    topics: string[];
    participantCount: number;
    updatedAt: string;
    editor: string | null;
  } | null;
  wikiChanges: Array<{
    title: string | null;
    slug: string | null;
    event: AuditEvent;
    user: string | null;
    createdAt: string;
  }> | null;
}>();

const locale = currentLocale();
const ago = (value: string) => relativeTime(value, locale);

const formatLongDate = (value: string) =>
  new Date(value).toLocaleDateString(locale === 'de' ? 'de-DE' : locale, {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    timeZone: 'UTC'
  });

const events: Record<AuditEvent, { label: string; icon: string }> = {
  created: { label: trans('Created'), icon: 'pi pi-plus' },
  updated: { label: trans('Edited'), icon: 'pi pi-pencil' },
  deleted: { label: trans('Deleted'), icon: 'pi pi-trash' },
  restored: { label: trans('Restored'), icon: 'pi pi-replay' }
};
</script>

<template>
  <AppLayout :title="$t('Dashboard')">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ $t('Dashboard') }}
      </h2>
    </template>

    <div class="grid gap-6 lg:grid-cols-2 items-start">
      <Card>
        <section class="p-4 sm:p-6" :aria-label="$t('Timetable')">
          <div class="flex items-baseline justify-between gap-2 mb-4">
            <h3 class="text-lg font-semibold">
              {{ $t('Timetable') }}
              <span v-if="timeTable" class="font-normal text-gray-500 dark:text-gray-400">
                {{ $t('Week :week / :year', { week: timeTable.week, year: timeTable.year }) }}
              </span>
            </h3>
            <Link
              v-if="timeTable"
              :href="route('time-tables.show', { time_table: timeTable.id })"
              class="text-sm font-medium hover:underline shrink-0"
            >
              {{ $t('Open') }} <i class="pi pi-arrow-right text-xs" aria-hidden="true" />
            </Link>
          </div>

          <template v-if="timeTable">
            <Link
              :href="route('time-tables.show', { time_table: timeTable.id })"
              class="block"
              :aria-label="$t('Open')"
            >
              <TimeTablePreview :time-table="timeTable" :courses="timeTable.courses" />
            </Link>
            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
              {{ $t(':count courses', { count: timeTable.coursesCount }) }}
              <template v-if="timeTable.creator">
                · {{ $t('by') }} {{ timeTable.creator.name }}
              </template>
            </p>
          </template>
          <p v-else class="text-gray-500 dark:text-gray-400">
            {{ $t('No timetables yet.') }}
            <Link :href="route('time-tables.create')" class="font-medium underline">
              {{ $t('Create timetable') }}
            </Link>
          </p>
        </section>
      </Card>

      <div class="grid gap-6">
        <Card v-if="protocol !== null || wikiChanges !== null">
          <section class="p-4 sm:p-6" :aria-label="$t('Latest protocol')">
            <div class="flex items-baseline justify-between gap-2 mb-3">
              <h3 class="text-lg font-semibold">{{ $t('Latest protocol') }}</h3>
              <Link
                v-if="protocol"
                :href="route('protocols.show', { protocol: protocol.slug })"
                class="text-sm font-medium hover:underline shrink-0"
              >
                {{ $t('Open') }} <i class="pi pi-arrow-right text-xs" aria-hidden="true" />
              </Link>
            </div>

            <template v-if="protocol">
              <Link
                :href="route('protocols.show', { protocol: protocol.slug })"
                class="block hover:underline"
              >
                <p class="text-xl font-semibold">{{ formatLongDate(protocol.date) }}</p>
                <p v-if="protocol.description" class="text-gray-600 dark:text-gray-300">
                  {{ protocol.description }}
                </p>
              </Link>
              <div v-if="protocol.topics.length" class="flex flex-wrap gap-1.5 mt-3">
                <Tag
                  v-for="topic in protocol.topics"
                  :key="topic"
                  :value="topic"
                  severity="secondary"
                />
              </div>
              <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                <i class="pi pi-users text-xs mr-1" aria-hidden="true" />
                {{ $t(':count participants', { count: protocol.participantCount }) }}
                <template v-if="protocol.editor">
                  · {{ $t('Last Edited') }}: {{ protocol.editor }}, {{ ago(protocol.updatedAt) }}
                </template>
              </p>
            </template>
            <p v-else class="text-gray-500 dark:text-gray-400">
              {{ $t('No protocols yet.') }}
              <Link :href="route('protocols.create')" class="font-medium underline">
                {{ $t('Create protocol') }}
              </Link>
            </p>
          </section>
        </Card>

        <Card v-if="wikiChanges !== null">
          <section class="p-4 sm:p-6" :aria-label="$t('Latest changes in the wiki')">
            <div class="flex items-baseline justify-between gap-2 mb-3">
              <h3 class="text-lg font-semibold">{{ $t('Latest changes in the wiki') }}</h3>
              <Link
                :href="route('wiki.index')"
                class="text-sm font-medium hover:underline shrink-0"
              >
                {{ $t('Wiki') }} <i class="pi pi-arrow-right text-xs" aria-hidden="true" />
              </Link>
            </div>

            <ul v-if="wikiChanges.length" class="divide-y divide-gray-200 dark:divide-gray-700">
              <li
                v-for="change in wikiChanges"
                :key="`${change.slug}-${change.createdAt}-${change.title}`"
                class="flex items-start gap-3 py-2.5"
              >
                <i
                  :class="events[change.event]?.icon"
                  class="text-xs text-gray-400 mt-1.5"
                  aria-hidden="true"
                />
                <div class="min-w-0">
                  <Link
                    v-if="change.slug"
                    :href="route('wiki.show', { page: change.slug })"
                    class="font-medium hover:underline break-words"
                  >
                    {{ change.title }}
                  </Link>
                  <span v-else class="font-medium text-gray-500 line-through break-words">
                    {{ change.title }}
                  </span>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ events[change.event]?.label ?? change.event }} {{ $t('by') }}
                    {{ change.user ?? $t('Unknown user') }} · {{ ago(change.createdAt) }}
                  </p>
                </div>
              </li>
            </ul>
            <p v-else class="text-gray-500 dark:text-gray-400">{{ $t('No wiki changes yet.') }}</p>
          </section>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
