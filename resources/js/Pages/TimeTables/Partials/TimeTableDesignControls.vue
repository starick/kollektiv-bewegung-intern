<script setup lang="ts">
import { TimeTableDesignConfig } from '@/Types/time-table';
import ColorSwatch from '@/Components/Form/ColorSwatch.vue';
import InputTextSize from '@/Components/Form/InputTextSize.vue';
import useAlert from '@/Composables/use-alerts';
import FontSelect from '@/Components/Form/FontSelect.vue';

const modelValue = defineModel<TimeTableDesignConfig>({ required: true });

const emits = defineEmits(['onSave', 'onDownload']);

const alert = useAlert();

const backgroundImages = [
  { label: 'Abstract 1', value: '/img/abstract-1.png' },
  { label: 'Abstract 2', value: '/img/abstract-2.png' },
  { label: 'Soft 1', value: '/img/soft-1.png' },
  { label: 'Pastel 1', value: '/img/pastel-1.png' },
  { label: 'Münster Hafen', value: '/img/meunster-hafen.png' },
  { label: 'Münster Hafen 2', value: '/img/meunster-hafen-dark.png' }
];
</script>

<template>
  <Accordion value="0">
    <AccordionPanel value="0">
      <AccordionHeader>Background</AccordionHeader>
      <AccordionContent>
        <div class="flex items-center gap-3 mb-4">
          <img :src="modelValue.background.image" class="w-32 h-20 object-cover rounded" />
          <Dropdown
            v-model="modelValue.background.image"
            :options="backgroundImages"
            optionLabel="label"
            optionValue="value"
            placeholder="Background Image"
            class="w-full"
          />
        </div>
        <div class="col-span-2">
          <label class="block text-xs mb-1">Overlay</label>

          <div class="flex items-center gap-3 mb-4">
            <Checkbox v-model="modelValue.overlay.hasOverlay" ariaLabel="has overlay" binary />
            <div text-xs>has overlay</div>
          </div>

          <ColorSwatch
            v-model="modelValue.overlay.color"
            :disabled="!modelValue.overlay.hasOverlay"
          />
        </div>
        <div>
          <label class="block text-xs mb-1">Overlay Opacity</label>
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
      <AccordionHeader>Header</AccordionHeader>
      <AccordionContent>
        <div class="grid grid-cols-1 gap-3">
          <div>
            <label class="block text-xs mb-1">Font</label>
            <FontSelect v-model="modelValue.header.fontFamily" />
          </div>
          <div>
            <label class="block text-xs mb-1">Color</label>
            <ColorSwatch v-model="modelValue.header.color" />
          </div>
          <div>
            <label class="block text-xs mb-1">Scale</label>
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
      <AccordionHeader>Body</AccordionHeader>
      <AccordionContent>
        <div class="grid grid-cols-1 gap-3">
          <div class="col-span-2">
            <label class="block text-xs mb-1">Font Family</label>
            <FontSelect v-model="modelValue.body.fontFamily" />
          </div>
          <div class="col-span-2">
            <label class="block text-xs mb-1">Font Color</label>
            <ColorSwatch v-model="modelValue.body.color" />
          </div>
          <div class="col-span-2">
            <label class="block text-xs mb-1">Font Size</label>
            <InputTextSize v-model="modelValue.body.fontSize" />
          </div>
          <div class="col-span-2">
            <label class="block text-xs mb-1">Margin</label>
            <InputTextSize v-model="modelValue.body['margin-top']" />
          </div>

          <div class="col-span-2">
            <label class="block text-xs mb-1">Scale</label>
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
            <label class="block text-xs mb-1">Line Height</label>
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
      <AccordionHeader>Highlights</AccordionHeader>
      <AccordionContent>
        <div class="grid grid-cols-1 gap-3">
          <div class="col-span-2">
            <label class="block text-xs mb-1">Font Color</label>
            <ColorSwatch v-model="modelValue.highlight.color" />
          </div>
          <div class="col-span-2">
            <label class="block text-xs mb-1">Background Color</label>
            <ColorSwatch v-model="modelValue.highlight.background" />
          </div>
          <div class="col-span-2">
            <label class="block text-xs mb-1">Registration Note</label>
            <div class="flex items-center gap-3">
              <Checkbox v-model="modelValue.highlight.registrationNoteAsOverlay" binary ariaLabel="registration note as overlay" />
              <span class="text-xs">Show as overlay badge</span>
            </div>
          </div>
        </div>
      </AccordionContent>
    </AccordionPanel>
    <AccordionPanel value="4">
      <AccordionHeader>Debug</AccordionHeader>
      <AccordionContent>
        <pre class="mt-4 bg-gray-100 dark:bg-gray-800 p-2 text-xs">{{ modelValue }}</pre>
      </AccordionContent>
    </AccordionPanel>
  </Accordion>
</template>
