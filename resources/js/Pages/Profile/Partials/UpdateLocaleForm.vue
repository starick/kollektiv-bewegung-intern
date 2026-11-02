<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import ActionMessage from '@/Components/General/ActionMessage.vue';
import FormSection from '@/Components/Form/FormSection.vue';
import InputError from '@/Components/Form/InputError.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import PrimaryButton from '@/Components/General/PrimaryButton.vue';
import { currentLocale } from '@/Composables/use-translations';

const locales = [
  { value: 'de', label: 'Deutsch' },
  { value: 'en', label: 'English' }
];

const form = useForm({
  locale: currentLocale()
});

const updateLocale = () => {
  form.put(route('user-locale.update'), {
    preserveScroll: true,
    // Reload so that third-party components (e.g. PrimeVue) pick up the new locale as well
    onSuccess: () => window.location.reload()
  });
};
</script>

<template>
  <FormSection @submitted="updateLocale">
    <template #title> {{ $t('Language') }} </template>

    <template #description> {{ $t('Choose the language of the application.') }} </template>

    <template #form>
      <div class="col-span-6 sm:col-span-4">
        <InputLabel for="locale" :value="$t('Language')" />

        <Select
          input-id="locale"
          v-model="form.locale"
          :options="locales"
          option-label="label"
          option-value="value"
          class="mt-1 w-full"
        />

        <InputError :message="form.errors.locale" class="mt-2" />
      </div>
    </template>

    <template #actions>
      <ActionMessage :on="form.recentlySuccessful" class="me-3"> {{ $t('Saved.') }} </ActionMessage>

      <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
        {{ $t('Save') }}
      </PrimaryButton>
    </template>
  </FormSection>
</template>
