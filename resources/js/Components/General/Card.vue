<script setup lang="ts">
import { Menubar } from 'primevue';
import { MenuItem } from 'primevue/menuitem';

defineProps({
  menuItems: {
    type: Array<MenuItem>,
    default: []
  }
});

const items = [{ label: 'Create New', icon: 'pi pi-plus', command: () => {} }];
</script>

<template>
  <div class="bg-white dark:bg-gray-800 overflow-hidden sm:rounded-lg">
    <slot name="header">
      <Menubar
        v-if="menuItems?.length"
        :model="menuItems.length ? menuItems : items"
        class="bg-transparent"
        style="border-radius: 0 !important; border-top: none; border-left: none; border-right: none"
      />
    </slot>
    <div class="p-4">
      <slot />
    </div>
  </div>
</template>
