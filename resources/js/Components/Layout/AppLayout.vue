<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import Navbar from '@/Components/Layout/Navbar.vue';
import useAlert from '@/Hooks/alert';

defineProps({
  title: String,
  noHeader: {
    type: Boolean,
    default: false
  }
});

const alert = useAlert();
</script>

<template>
  <div class="bg-slate-50 min-h-screen">
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
          <Message
            v-for="(message, uuid) in alert.messages"
            :severity="message.severity"
            @close="() => alert.removeMessage(uuid)"
            closable
          >
            {{ message }}
          </Message>
          <div class="my-2 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <slot />
          </div>
        </div>
      </main>
    </div>
  </div>
</template>
