<script setup lang="ts">

import { TimeTableDesignConfig } from '@/Types/time-table';

const props = defineProps<{ footerConfig: TimeTableDesignConfig["footer"], addRegistrationNote: boolean  }>();
</script>

<template>
  <div class="absolute bottom-1 left-1 text-sm rounded overflow-hidden z-20">
    <div
      v-if="footerConfig.overlay.hasOverlay"
      class="absolute inset-0 rounded"
      :style="{ backgroundColor: footerConfig.overlay.color, opacity: footerConfig.overlay.opacity }"
    />
    <div class="relative z-10 px-2 py-1" :style="{ color: footerConfig.color }">
      <p>
        Sofern nicht anders angegeben, finden die Kurse im Bewegungsraum der B-Side (Am Mittelhafen
        42) statt.
      </p>
      <p>
        Alle Angebote sind nach Pay-What-You-Can-Prinzip.
        <span v-if="addRegistrationNote" class="font-bold">
            Bitte vorher bei den Kursleitenden anmelden!
          </span>
      </p>
    </div>
  </div>
</template>

<style scoped></style>
