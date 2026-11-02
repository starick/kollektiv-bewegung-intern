<script setup lang="ts">
import Card from '@/Components/General/Card.vue';
import FormSection from '@/Components/Form/FormSection.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import PrimaryButton from '@/Components/General/PrimaryButton.vue';
import SecondaryButton from '@/Components/General/SecondaryButton.vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { protocolCreateModel } from '@/Models/Protocols/protocol-create-model';

const { formData, title, onFileSelect, onFileClear, submit, cancel } = protocolCreateModel();
</script>

<template>
  <AppLayout :title="title">
    <Card>
      <form @submit.prevent="submit">
        <FormSection class="mb-4">
          <template #title>File Upload</template>
          <template #description>Upload the protocol as Markdown (.md) file</template>

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
          <template #title>Details</template>
          <template #description>When the meeting took place and what it was about</template>

          <template #form>
            <div class="col-span-6">
              <InputLabel for="date" value="Date" />
              <Calendar id="date" v-model="formData.date" date-format="dd.mm.yy" show-icon />
              <div v-if="formData.errors.date" class="text-red-600 mt-2">
                {{ formData.errors.date }}
              </div>
            </div>

            <div class="col-span-6">
              <InputLabel for="description" value="Description" />
              <InputText id="description" v-model="formData.description" class="w-full" maxlength="255" />
              <div v-if="formData.errors.description" class="text-red-600 mt-2">
                {{ formData.errors.description }}
              </div>
            </div>

            <div class="col-span-6">
              <InputLabel for="topics" value="Topics" />
              <InputText
                id="topics"
                v-model="formData.topics"
                class="w-full"
                placeholder="e.g. Budget, Events, Membership"
              />
              <small class="text-gray-600 dark:text-gray-400">Separate topics with commas</small>
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
            Cancel
          </SecondaryButton>
          <PrimaryButton
            :class="{ 'opacity-25': formData.processing }"
            :disabled="formData.processing"
          >
            Create
          </PrimaryButton>
        </div>
      </form>
    </Card>
  </AppLayout>
</template>
