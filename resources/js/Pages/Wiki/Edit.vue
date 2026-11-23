<script setup lang="ts">
import Card from '@/Components/General/Card.vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import WikiPageForm from '@/Components/Wiki/WikiPageForm.vue';
import { trans } from '@/Composables/use-translations';
import { wikiPageFormModel } from '@/Models/Wiki/wiki-page-form-model';
import { WikiCategoryOption, WikiPage } from '@/Types/wiki';
import { MenuItem } from 'primevue';

const props = defineProps<{
  page: WikiPage;
  text: string;
  categoryOptions: WikiCategoryOption[];
}>();

const { formData, mode, submit, cancel } = wikiPageFormModel(props.page, {
  text: props.text,
  categoryId: props.page.categoryId
});

const menuItems: MenuItem[] = [
  { label: trans('Back to Page'), icon: 'pi pi-arrow-left', command: cancel }
];
</script>

<template>
  <AppLayout :title="$t('Edit :title', { title: page.title })">
    <Card :menu-items="menuItems">
      <template #menu-end>
        <Button
          :label="$t('Save')"
          icon="pi pi-check"
          :loading="formData.processing"
          @click="submit"
        />
      </template>
      <WikiPageForm
        v-model:mode="mode"
        :form="formData"
        :category-options="categoryOptions"
        @submit="submit"
        @cancel="cancel"
      />
    </Card>
  </AppLayout>
</template>
