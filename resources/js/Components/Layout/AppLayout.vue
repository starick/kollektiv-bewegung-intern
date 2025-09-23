<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';

import { route } from 'ziggy-js';
import Navbar from '@/Components/Layout/Navbar.vue';

defineProps({
  title: String
});

const menu = ref();

const switchToTeam = (team) => {
  router.put(
    route('current-team.update'),
    {
      team_id: team.id
    },
    {
      preserveState: false
    }
  );
};

const logout = () => {
  router.post(route('logout'));
};

const pageProps = usePage().props;

const menuItems = [
  { label: 'Dashboard', icon: 'pi pi-home', command: () => router.get(route('dashboard')) }
];

const items = [
  {
    label: 'Team',
    icon: 'pi pi-users',
    items: [
      {
        label: 'Team Settings',
        icon: 'pi pi-users',
        command: () => router.get(route('teams.show', pageProps.auth.user.current_team))
      },
      {
        label: 'Create New Team',
        icon: 'pi pi-plus-circle',
        visible: pageProps.jetstream.canCreateTeams,
        command: () => router.get(route('teams.create'))
      },
      {
        separator: true
      },
      {
        label: 'Switch Teams',
        visible: pageProps.auth.user.all_teams.length > 1,
        items: pageProps.auth.user.all_teams.map((team) => ({
          label: team.name,
          icon:
            team.id === pageProps.auth.user.current_team_id
              ? 'pi pi-check-circle text-green-400'
              : '',
          command: () => switchToTeam(team)
        }))
      }
    ]
  },
  {
    label: pageProps.auth.user.name,
    items: [
      {
        label: 'Profile',
        icon: 'pi pi-user',
        command: () => router.get(route('profile.show'))
      },
      {
        label: 'Log Out',
        icon: 'pi pi-sign-out',
        command: logout
      }
    ]
  }
];

const toggle = (event) => {
  menu.value.toggle(event);
};
</script>

<template>
  <div>
    <Head :title="title" />
    <div>
      <Navbar />
      <header v-if="$slots.header">
        <div class="m-2 ml-8">
          <slot name="header" />
        </div>
      </header>
      <main>
        <slot />
      </main>
    </div>
  </div>
</template>
