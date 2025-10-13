<script setup lang="ts">
import { Menubar } from 'primevue';
import { MenuItem } from 'primevue/menuitem';

defineProps({
  menuItems: {
    type: Array<MenuItem>,
    default: []
  },
  border: { type: Boolean, default: false }
});
</script>

<template>
  <div
    class="bg-default-background overflow-hidden rounded-lg"
    :class="{ 'border-border border-l border-y': border }"
  >
    <slot name="header">
      <Menubar
        v-if="menuItems?.length"
        :model="menuItems.length ? menuItems : []"
        class="bg-transparent"
        style="border-radius: 0 !important; border-top: none; border-left: none; border-right: none"
      />
    </slot>
    <div class="p-4">
      <slot />
    </div>
  </div>
</template>
