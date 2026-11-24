<script setup lang="ts">
import Card from '@/Components/General/Card.vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import ParticipantChips from '@/Components/Protocols/ParticipantChips.vue';
import MarkdownEditor from '@/Components/Markdown/MarkdownEditor.vue';
import ProtocolMeta from '@/Components/Protocols/ProtocolMeta.vue';
import { protocolEditModel } from '@/Models/Protocols/protocol-edit-model';
import { trans } from '@/Composables/use-translations';
import { Protocol } from '@/Types/protocol';
import { MenuItem, Tag } from 'primevue';

const props = defineProps<{
  protocol: Protocol;
  text: string;
}>();

const { formData, title, participants, topics, mode, submit, cancel } = protocolEditModel(
  props.protocol,
  props.text
);

const descriptionHint = trans(
  "For special cases like 'Neue Leute Treffen' or 'Finanz-AK'. Leave empty for regular plenaries."
);

const menuItems: MenuItem[] = [
  {
    label: trans('Back to Protocol'),
    icon: 'pi pi-arrow-left',
    command: cancel
  }
];
</script>

<template>
  <AppLayout :title="title">
    <Card :menu-items="menuItems">
      <template #menu-end>
        <Button
          :label="$t('Save')"
          icon="pi pi-check"
          :loading="formData.processing"
          @click="submit"
        />
      </template>

      <form class="max-w-4xl mx-auto py-2 sm:py-4" @submit.prevent="submit">
        <ProtocolMeta :protocol="protocol" />

        <header class="mb-6 space-y-3">
          <div>
            <label
              for="date"
              class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1"
            >
              {{ $t('date') }}
            </label>
            <Calendar
              id="date"
              v-model="formData.date"
              date-format="DD, d. MM yy"
              input-class="text-xl font-bold"
              show-icon
            />
            <div v-if="formData.errors.date" class="text-red-600 mt-2">
              {{ formData.errors.date }}
            </div>
          </div>

          <div>
            <div class="flex items-center gap-2 mb-1">
              <label
                for="description"
                class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400"
              >
                {{ $t('Description') }}
              </label>
              <span
                v-tooltip.top="descriptionHint"
                class="inline-flex items-center justify-center size-4 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs font-semibold cursor-help"
                tabindex="0"
                >?</span
              >
            </div>
            <InputText
              id="description"
              v-model="formData.description"
              class="w-full text-lg"
              maxlength="255"
            />
            <div v-if="formData.errors.description" class="text-red-600 mt-2">
              {{ formData.errors.description }}
            </div>
          </div>
        </header>

        <div
          class="mb-8 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 divide-y divide-gray-200 dark:divide-gray-700"
        >
          <section class="p-4 sm:p-5">
            <h2 class="flex items-center gap-2 text-lg font-semibold mb-3">
              <i class="pi pi-users" aria-hidden="true" />
              <label for="participants">{{ $t('Participants') }}</label>
              <span
                class="rounded-full bg-gray-200 dark:bg-gray-700 px-2 py-0.5 text-xs font-medium text-gray-700 dark:text-gray-200"
              >
                {{ participants.length }}
              </span>
            </h2>
            <InputText
              id="participants"
              v-model="formData.participants"
              class="w-full"
              :placeholder="$t('e.g. Anna, Ben, Chris')"
            />
            <small class="text-gray-600 dark:text-gray-400">{{
              $t('Separate participants with commas')
            }}</small>
            <div v-if="formData.errors.participants" class="text-red-600 mt-2">
              {{ formData.errors.participants }}
            </div>
            <ParticipantChips
              v-if="participants.length"
              class="mt-3"
              :participants="participants"
            />
          </section>

          <section class="p-4 sm:p-5">
            <h2 class="flex items-center gap-2 text-lg font-semibold mb-3">
              <i class="pi pi-tags" aria-hidden="true" />
              <label for="topics">{{ $t('Topics') }}</label>
            </h2>
            <InputText
              id="topics"
              v-model="formData.topics"
              class="w-full"
              :placeholder="$t('e.g. Budget, Events, Membership')"
            />
            <small class="text-gray-600 dark:text-gray-400">{{
              $t('Separate topics with commas')
            }}</small>
            <div v-if="formData.errors.topics" class="text-red-600 mt-2">
              {{ formData.errors.topics }}
            </div>
            <div v-if="topics.length" class="flex flex-wrap gap-1.5 mt-3">
              <Tag v-for="topic in topics" :key="topic" :value="topic" severity="secondary" />
            </div>
          </section>
        </div>

        <MarkdownEditor v-model="formData.text" v-model:mode="mode" :error="formData.errors.text" />

        <div class="flex flex-row justify-center gap-4 mt-8">
          <Button
            :label="$t('Cancel')"
            severity="secondary"
            outlined
            :disabled="formData.processing"
            @click="cancel"
          />
          <Button
            type="submit"
            :label="$t('Save')"
            icon="pi pi-check"
            :loading="formData.processing"
          />
        </div>
      </form>
    </Card>
  </AppLayout>
</template>
