<script setup lang="ts">
import { TimeTableDesignConfig } from '@/Types/time-table';
import ColorPickerWrapper from '@/Components/Form/ColorPickerWrapper.vue';
import InputTextSize from '@/Components/Form/InputTextSize.vue';

const modelValue = defineModel<TimeTableDesignConfig>({ required: true });

const emits = defineEmits(['onSave', 'onDownload']);
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
            :options="[
              { label: 'Test 1', value: 'https://wallpapercave.com/wp/wp3272711.jpg' },
              {
                label: 'Test 2s',
                value: 'https://cdn.wallpapersafari.com/48/17/0SDb2r.jpg'
              }
            ]"
            optionLabel="label"
            optionValue="value"
            placeholder="Background Image"
            class="w-60"
            showClear
          />
        </div>
      </AccordionContent>
    </AccordionPanel>
    <AccordionPanel value="1">
      <AccordionHeader>Body</AccordionHeader>
      <AccordionContent>
        <div class="grid grid-cols-1 gap-3">
          <div class="col-span-2">
            <label class="block text-xs mb-1">Font Color</label>
            <ColorPickerWrapper v-model="modelValue.body.color" />
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
                :step="0.1"
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
      <AccordionHeader>Header</AccordionHeader>
      <AccordionContent>
        <div class="grid grid-cols-1 gap-3">
          <div>
            <label class="block text-xs mb-1">Color</label>
            <ColorPickerWrapper v-model="modelValue.header.color" />
          </div>
          <div>
            <label class="block text-xs mb-1">Scale</label>
            <div class="flex items-center gap-3">
              <InputNumber
                v-model="modelValue.header.scale"
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
    <AccordionPanel value="4">
      <AccordionHeader>General</AccordionHeader>
      <AccordionContent>
        <div>
          <label class="block text-xs mb-1">Padding</label>
          <div class="flex items-center gap-3">
            <InputNumber v-model="modelValue.padding" :min="0" :max="100" :step="1" showButtons />
          </div>
        </div>
      </AccordionContent>
    </AccordionPanel>
  </Accordion>
</template>
