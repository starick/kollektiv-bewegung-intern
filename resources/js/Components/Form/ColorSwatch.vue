<script setup lang="ts">
import { ref, computed } from 'vue';
import ColorPicker from 'primevue/colorpicker';
import OverlayPanel from 'primevue/overlaypanel';
import Button from 'primevue/button';
import SecondaryButton from '../General/SecondaryButton.vue';
import PrimaryButton from '../General/PrimaryButton.vue';

const modelValue = defineModel<string>({ required: true });

const props = withDefaults(
  defineProps<{
    columns?: number;
    swatchSize?: number;
    id?: string;
    showInput?: boolean;
    disabled?: boolean;
  }>(),
  {
    columns: 6,
    swatchSize: 28,
    showInput: true
  }
);

const presets = [
  ['#FDE4EC', '#F8BBD0', '#F48FB1', '#EC407A', '#D81B60'],
  ['#FFF3E0', '#FFE0B2', '#FFB74D', '#FB8C00', '#EF6C00'],
  ['#FFF9C4', '#FFF59D', '#FFEB3B', '#FBC02D', '#F9A825'],
  ['#E8F5E9', '#C8E6C9', '#A5D6A7', '#66BB6A', '#43A047'],
  ['#E3F2FD', '#BBDEFB', '#90CAF9', '#42A5F5', '#1E88E5'],
  ['#EDE7F6', '#D1C4E9', '#B39DDB', '#7E57C2', '#5E35B1'],
  ['#FAFAFA', '#E0E0E0', '#BDBDBD', '#757575', '#424242']
];

const normalizedValue = computed({
  get: () => modelValue.value.replace(/^#/, ''),
  set: (val: string) => {
    const clean = val.replace(/^#/, '').toUpperCase();
    modelValue.value = `#${clean}`;
  }
});

const panelRef = ref<InstanceType<typeof OverlayPanel> | null>(null);
function togglePanel(e: Event) {
  panelRef.value?.toggle(e);
}
</script>

<template>
  <div class="flex items-center gap-2" :id="props.id ?? 'color-swatch-picker'">
    <Button
      class="flex items-center gap-2"
      @click="togglePanel"
      variant="text"
      :disabled="disabled"
    >
      <span
        class="inline-block rounded border border-gray-300 w-[18px] h-[18px]"
        :style="{ backgroundColor: modelValue }"
      />
      <span class="font-mono text-sm text-black">{{ modelValue }}</span>
    </Button>

    <OverlayPanel ref="panelRef" :dismissable="true">
      <div class="grid grid-cols-2 gap-3 p-3 w-96 select-none">
        <div>
          <div class="grid gap-1 grid-cols-7">
            <div v-for="(col, index) in presets" :key="index" class="flex flex-col gap-1">
              <button
                v-for="color in col"
                :key="color"
                :style="{ backgroundColor: color, width: '24px', height: '24px' }"
                class="rounded border border-gray-200 hover:scale-105 transition-transform"
                @click="modelValue = color"
              />
            </div>
          </div>
          <div>
            <input
              v-if="showInput"
              type="text"
              class="border rounded px-2 py-1 text-sm w-full mt-2"
              v-model="modelValue"
              placeholder="#RRGGBB"
              aria-label="Color hex value"
            />
          </div>
        </div>

        <ColorPicker v-model="normalizedValue" format="hex" inline />
      </div>
    </OverlayPanel>
  </div>
</template>
