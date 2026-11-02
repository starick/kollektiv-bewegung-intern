<script setup lang="ts">
import Card from '@/Components/General/Card.vue';
import FormSection from '@/Components/Form/FormSection.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import PrimaryButton from '@/Components/General/PrimaryButton.vue';
import SecondaryButton from '@/Components/General/SecondaryButton.vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { protocolCreateModel } from '@/Models/Protocols/protocol-create-model';
import { trans } from '@/Composables/use-translations';

const { formData, title, onFileSelect, onFileClear, submit, cancel } = protocolCreateModel();

const descriptionHint = trans(
  "For special cases like 'Neue Leute Treffen' or 'Finanz-AK'. Leave empty for regular plenaries."
);
</script>

<template>
  <AppLayout :title="title">
    <Card>
      <form @submit.prevent="submit">
        <FormSection class="mb-4">
          <template #title>{{ $t('File Upload') }}</template>
          <template #description>{{ $t('Upload the protocol as Markdown (.md) file') }}</template>

          <template #form>
            <FileUpload
              name="file"
              mode="basic"
              :auto="false"
              :customUpload="false"
              :multiple="false"
              accept=".md,.markdown"
              @select="onFileSelect"
              @clear="onFileClear"
            />
            <div v-if="formData.errors.file" class="text-red-600 mt-2">
              {{ formData.errors.file }}
            </div>
          </template>
        </FormSection>

        <FormSection>
          <template #title>{{ $t('Details') }}</template>
          <template #description>{{
            $t('When the meeting took place and what it was about')
          }}</template>

          <template #form>
            <div class="col-span-6">
              <InputLabel for="date" :value="$t('Date')" />
              <Calendar id="date" v-model="formData.date" date-format="dd.mm.yy" show-icon />
              <div v-if="formData.errors.date" class="text-red-600 mt-2">
                {{ formData.errors.date }}
              </div>
            </div>

            <div class="col-span-6">
              <div class="flex items-center gap-2">
                <InputLabel for="description" :value="$t('Description')" />
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
                class="w-full"
                maxlength="255"
              />
              <div v-if="formData.errors.description" class="text-red-600 mt-2">
                {{ formData.errors.description }}
              </div>
            </div>

            <div class="col-span-6">
              <InputLabel for="topics" :value="$t('Topics')" />
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
            </div>
          </template>
        </FormSection>

        <div class="flex flex-row justify-center mt-8">
          <SecondaryButton
            :class="{ 'opacity-25': formData.processing }"
            :disabled="formData.processing"
            class="mr-4"
            @click="cancel"
          >
            {{ $t('Cancel') }}
          </SecondaryButton>
          <PrimaryButton
            :class="{ 'opacity-25': formData.processing }"
            :disabled="formData.processing"
          >
            {{ $t('Create') }}
          </PrimaryButton>
        </div>
      </form>
    </Card>
  </AppLayout>
</template>
