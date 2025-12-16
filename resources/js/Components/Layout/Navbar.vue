<script setup lang="ts">
import { route } from 'ziggy-js';
import { router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Menubar, Menu, Avatar } from 'primevue';
import ApplicationLogo from '@/Components/General/ApplicationLogo.vue';
import ResponsiveNavLink from '@/Components/General/ResponsiveNavLink.vue';
import TeamSwitchModal from '@/Components/Layout/TeamSwitchModal.vue';

const pageProps = usePage().props as any;

const menu = ref();
const showTeamModal = ref(false);

const toggle = (event) => {
  menu.value.toggle(event);
};

const toggleTeamModal = () => {
  showTeamModal.value = !showTeamModal.value;
};

const logout = () => {
  router.post(route('logout'));
};

const menuItems = [
  { label: 'Dashboard', icon: 'pi pi-home', command: () => router.get(route('dashboard')) },
  {
    label: 'Timetables',
    icon: 'pi pi-table',
    command: () => router.get(route('time-tables.index'))
  }
];

const items = [
  {
    label: `Team: ${pageProps.auth.user.current_team.name}`,
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
        label: 'Switch Team',
        icon: 'pi pi-sync',
        visible: pageProps.auth.user.all_teams.length > 1,
        command: () => toggleTeamModal()
      }
    ]
  },
  {
    separator: true
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
</script>

<template>
  <div>
    <Menubar :model="menuItems" class="bg-white dark:bg-gray-800 -mt-1 -mx-1 p-2">
      <template #start>
        <ResponsiveNavLink :href="route('dashboard')">
          <ApplicationLogo class="block h-12 w-auto" />
        </ResponsiveNavLink>
      </template>
      <template #end>
        <Avatar
          :image="pageProps.auth.user.profile_photo_url"
          shape="circle"
          size="large"
          @click="toggle"
        />
        <Menu ref="menu" id="overlay_menu" :model="items" :popup="true" />
      </template>
    </Menubar>
    <TeamSwitchModal v-model:show="showTeamModal" />
  </div>
</template>
