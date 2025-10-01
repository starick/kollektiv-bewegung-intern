<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import Navbar from '@/Components/Layout/Navbar.vue';
import { computed, onMounted, watch } from 'vue';
import useAlert from '@/Hooks/alert';

const page = usePage();
const alert = useAlert();

defineProps({
  title: String,
  noHeader: {
    type: Boolean,
    default: false
  }
});

const flash = computed(() => (page.props as any).flash ?? {});

onMounted(() => {
  if (flash.value?.success) {
    alert.add((page.props.flash as any).success, 'success');
  }

  if ((flash.value?.flash as any)?.error) {
    alert.error((page.props.flash as any).error);
  }
});

watch(
  () => flash.value?.success,
  (message, prevMessage) => {
    if (message && message !== prevMessage) alert.add(message, 'success');
  },
  { immediate: true, flush: 'post' }
);

watch(
  () => flash.value?.error,
  (message, prevMessage) => {
    if (message && message !== prevMessage) alert.error(message);
  },
  { immediate: true, flush: 'post' }
);
</script>

<template>
  <div class="bg-slate-50 dark:bg-slate-900 min-h-screen">
    <Head :title="title" />
    <div>
      <Navbar />
      <header v-if="!noHeader">
        <div class="m-2 ml-8">
          <slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
              {{ title }}
            </h2>
          </slot>
        </div>
      </header>
      <main>
        <div class="py-10">
          <Toast class="mt-12" />
          <div class="my-2 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <slot />
          </div>
        </div>
      </main>
    </div>
  </div>
</template>
