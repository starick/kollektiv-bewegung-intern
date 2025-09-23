<script setup lang="ts">
import DialogModal from '@/Components/General/DialogModal.vue';
import SecondaryButton from '@/Components/General/SecondaryButton.vue';
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import PrimaryButton from '@/Components/General/PrimaryButton.vue';

const emits = defineEmits(['update:show']);

const props = defineProps({
  show: {
    type: Boolean
  }
});

const user = usePage().props.auth.user;

const selectedTeam = ref(null);

const showValue = computed({
  get() {
    return props.show;
  },
  set(value) {
    emits('update:show', value);
  }
});

const selectTeam = (team) => {
  selectedTeam.value = team;
};

const switchToTeam = () => {
  if (!selectedTeam.value.id) {
    return;
  }
  router.put(
    route('current-team.update'),
    {
      team_id: selectedTeam.value.id
    },
    {
      preserveState: false
    }
  );
};

const createTeam = () => {
  router.get(route('teams.create'));
};

const closeTeamSwitchModal = () => (showValue.value = false);
</script>

<template>
  <DialogModal :show="showValue" @close="closeTeamSwitchModal">
    <template #title>
      <div class="bg-red">Switch Teams</div>
    </template>

    <template #content>
      <div
        class="relative z-0 mt-1 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer"
      >
        <button
          v-for="(team, i) in user.all_teams"
          :key="team.id"
          type="button"
          class="relative px-4 py-3 inline-flex w-full rounded-lg focus:z-10 focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600"
          :class="{
            'border-t border-gray-200 dark:border-gray-700 focus:border-none rounded-t-none': i > 0,
            'rounded-b-none': i !== Object.keys(user.all_teams).length - 1
          }"
          @click="() => selectTeam(team)"
        >
          <div :class="{ 'opacity-50': team.id !== user.current_team_id }">
            <!-- Role Name -->
            <div class="flex items-center">
              <div
                class="text-sm text-gray-600 dark:text-gray-400"
                :class="{ 'font-semibold': team.id === user.current_team_id }"
              >
                {{ team.name }}
              </div>

              <svg
                v-if="team.id === user.current_team_id"
                class="ms-2 size-5 text-green-400"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
            </div>
          </div>
        </button>
      </div>
    </template>
    <template #footer>
      <div class="flex justify-between w-full">
        <SecondaryButton @click="closeTeamSwitchModal">Cancel</SecondaryButton>
        <SecondaryButton
          v-if="usePage().props.jetstream.canCreateTeams"
          @click="createTeam"
          class="mx-4"
        >
          Create New Team
        </SecondaryButton>
        <PrimaryButton @click="switchToTeam" :disabled="!selectedTeam">Switch Team</PrimaryButton>
      </div>
    </template>
  </DialogModal>
</template>

<style scoped></style>
