<script setup lang="ts">
import { TimeTableDesignConfig } from '@/Types/time-table';
import ColorSwatch from '@/Components/Form/ColorSwatch.vue';
import InputTextSize from '@/Components/Form/InputTextSize.vue';
import useAlert from '@/Composables/use-alerts';
import FontSelect from '@/Components/Form/FontSelect.vue';
import { trans } from '@/Composables/use-translations';

const modelValue = defineModel<TimeTableDesignConfig>({ required: true });

const emits = defineEmits(['onSave', 'onDownload']);

const alert = useAlert();

const layoutOptions = [
  { label: trans('List'), value: 'list' },
  { label: trans('Bubble'), value: 'bubble' }
];

const backgroundImages = [
  { label: trans('Abstract 1'), value: '/img/abstract-1.png' },
  { label: trans('Abstract 2'), value: '/img/abstract-2.png' },
  { label: trans('Soft 1'), value: '/img/soft-1.png' },
  { label: trans('Pastel 1'), value: '/img/pastel-1.png' },
  { label: 'Münster Hafen', value: '/img/meunster-hafen.png' },
  { label: trans('Foliage'), value: '/img/foliage-1.png' }
];
</script>

<template>
  <Accordion value="0">
    <AccordionPanel value="0">
      <AccordionHeader>{{ $t('Theme') }}</AccordionHeader>
      <AccordionContent>
        <div class="flex items-center gap-3 mb-4">
          <img :src="modelValue.background.image" class="w-32 h-20 object-cover rounded" />
          <Dropdown
            v-model="modelValue.background.image"
            :options="backgroundImages"
            optionLabel="label"
            optionValue="value"
            :placeholder="$t('Background Image')"
            class="w-full"
          />
        </div>
        <div>
          <label class="block text-xs mb-1">{{ $t('Layout') }}</label>
          <div class="flex items-center gap-3">
            <Dropdown
              v-model="modelValue.layout"
              :options="layoutOptions"
              optionLabel="label"
              optionValue="value"
              class="w-full"
            />
          </div>
        </div>

        <div v-if="modelValue.layout === 'bubble'" class="mt-2">
          <label class="block text-xs mb-1">{{ $t('Columns') }}</label>
          <div class="flex items-center gap-3">
            <InputNumber
              v-model="modelValue.columns"
              :min="1"
              :max="6"
              :step="1"
              mode="decimal"
              class="w-full"
              showButtons
            />
          </div>
        </div>
      </AccordionContent>
    </AccordionPanel>
    <AccordionPanel value="5">
      <AccordionHeader>{{ $t('Overlay') }}</AccordionHeader>
      <AccordionContent>
        <div class="col-span-2">
          <label class="block text-xs mb-1">{{ $t('Overlay') }}</label>

          <div class="flex items-center gap-3 mb-4">
            <Checkbox
              v-model="modelValue.overlay.hasOverlay"
              :ariaLabel="$t('has overlay')"
              binary
            />
            <div text-xs>{{ $t('has overlay') }}</div>
          </div>

          <ColorSwatch
            v-model="modelValue.overlay.color"
            :disabled="!modelValue.overlay.hasOverlay"
          />
        </div>
        <div>
          <label class="block text-xs mb-1">{{ $t('Overlay Opacity') }}</label>
          <div class="flex items-center gap-3">
            <InputNumber
              v-model="modelValue.overlay.opacity"
              :disabled="!modelValue.overlay.hasOverlay"
              :min="0"
              :max="1"
              :step="0.05"
              mode="decimal"
              showButtons
            />
          </div>
        </div>
      </AccordionContent>
    </AccordionPanel>
    <AccordionPanel value="1">
      <AccordionHeader>{{ $t('Header') }}</AccordionHeader>
      <AccordionContent>
        <div class="grid grid-cols-1 gap-3">
          <div>
            <label class="block text-xs mb-1">{{ $t('Font') }}</label>
            <FontSelect v-model="modelValue.header.fontFamily" />
          </div>
          <div>
            <label class="block text-xs mb-1">{{ $t('Color') }}</label>
            <ColorSwatch v-model="modelValue.header.color" />
          </div>
          <div>
            <label class="block text-xs mb-1">{{ $t('Scale') }}</label>
            <div class="flex items-center gap-3">
              <InputNumber
                v-model="modelValue.header.scale"
                :min="0.8"
                :max="3"
                :step="0.05"
                mode="decimal"
                showButtons
              />
            </div>
          </div>
        </div>
      </AccordionContent>
    </AccordionPanel>
    <AccordionPanel value="2">
      <AccordionHeader>{{ $t('Body') }}</AccordionHeader>
      <AccordionContent>
        <div class="grid grid-cols-1 gap-3">
          <div class="col-span-2">
            <label class="block text-xs mb-1">{{ $t('Font Family') }}</label>
            <FontSelect v-model="modelValue.body.fontFamily" />
          </div>
          <div class="col-span-2">
            <label class="block text-xs mb-1">{{ $t('Font Color') }}</label>
            <ColorSwatch v-model="modelValue.body.color" />
          </div>
          <div class="col-span-2">
            <label class="block text-xs mb-1">{{ $t('Margin') }}</label>
            <InputTextSize v-model="modelValue.body['margin-top']" />
          </div>
          <div class="col-span-2">
            <label class="block text-xs mb-1">{{ $t('Scale') }}</label>
            <div class="flex items-center gap-3">
              <InputNumber
                v-model="modelValue.body.scale"
                :min="0.5"
                :max="3"
                :step="0.05"
                mode="decimal"
                showButtons
              />
            </div>
          </div>
          <div class="col-span-2">
            <label class="block text-xs mb-1">{{ $t('Line Height') }}</label>
            <div class="flex items-center gap-3">
              <InputNumber
                v-model="modelValue.body['line-height']"
                :min="0.8"
                :max="3"
                :step="0.1"
                mode="decimal"
                showButtons
              />
            </div>
          </div>
        </div>
      </AccordionContent>
    </AccordionPanel>
    <AccordionPanel value="3">
      <AccordionHeader>{{ $t('Highlights') }}</AccordionHeader>
      <AccordionContent>
        <div class="grid grid-cols-1 gap-3">
          <div class="col-span-2">
            <label class="block text-xs mb-1">{{ $t('Font Color') }}</label>
            <ColorSwatch v-model="modelValue.highlight.color" />
          </div>
          <div class="col-span-2">
            <label class="block text-xs mb-1">{{ $t('Background Color') }}</label>
            <ColorSwatch v-model="modelValue.highlight.background" />
          </div>
          <div class="col-span-2">
            <label class="block text-xs mb-1">{{ $t('Registration Note') }}</label>
            <div class="flex items-center gap-3">
              <Checkbox
                v-model="modelValue.highlight.registrationNoteAsOverlay"
                binary
                :ariaLabel="$t('registration note as overlay')"
              />
              <span class="text-xs">{{ $t('Show as overlay badge') }}</span>
            </div>
          </div>
        </div>
      </AccordionContent>
    </AccordionPanel>
    <AccordionPanel value="6">
      <AccordionHeader>{{ $t('Footer') }}</AccordionHeader>
      <AccordionContent>
        <div class="grid grid-cols-1 gap-3">
          <div>
            <label class="block text-xs mb-1">{{ $t('Text Color') }}</label>
            <ColorSwatch v-model="modelValue.footer.color" />
          </div>
          <div>
            <label class="block text-xs mb-1">{{ $t('Overlay') }}</label>
            <div class="flex items-center gap-3 mb-2">
              <Checkbox
                v-model="modelValue.footer.overlay.hasOverlay"
                binary
                :ariaLabel="$t('footer overlay')"
              />
              <span class="text-xs">{{ $t('has overlay') }}</span>
            </div>
            <ColorSwatch
              v-model="modelValue.footer.overlay.color"
              :disabled="!modelValue.footer.overlay.hasOverlay"
            />
          </div>
          <div>
            <label class="block text-xs mb-1">{{ $t('Overlay Opacity') }}</label>
            <InputNumber
              v-model="modelValue.footer.overlay.opacity"
              :disabled="!modelValue.footer.overlay.hasOverlay"
              :min="0"
              :max="1"
              :step="0.05"
              mode="decimal"
              showButtons
            />
          </div>
        </div>
      </AccordionContent>
    </AccordionPanel>
    <AccordionPanel value="4">
      <AccordionHeader>{{ $t('Debug') }}</AccordionHeader>
      <AccordionContent>
        <pre class="mt-4 bg-gray-100 dark:bg-gray-800 p-2 text-xs">{{ modelValue }}</pre>
      </AccordionContent>
    </AccordionPanel>
  </Accordion>
</template>
