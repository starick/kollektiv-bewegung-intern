<script setup lang="ts">
import { route } from 'ziggy-js';
import { router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useDarkMode } from '@/Composables/use-dark-mode';
import { Menubar, Menu, Avatar } from 'primevue';
import ApplicationLogo from '@/Components/General/ApplicationLogo.vue';
import ResponsiveNavLink from '@/Components/General/ResponsiveNavLink.vue';
import { trans } from '@/Composables/use-translations';

const pageProps = usePage().props as any;

const menu = ref();
const { isDark, toggle: toggleDark } = useDarkMode();

const toggle = (event) => {
  menu.value.toggle(event);
};

const logout = () => {
  router.post(route('logout'));
};

const menuItems = [
  { label: trans('Dashboard'), icon: 'pi pi-home', command: () => router.get(route('dashboard')) },
  {
    label: trans('Timetables'),
    icon: 'pi pi-table',
    command: () => router.get(route('time-tables.index'))
  },
  {
    label: trans('Protocols'),
    icon: 'pi pi-file',
    command: () => router.get(route('protocols.index'))
  }
];

const items = [
  {
    // Users removed from the shared team have no current team
    label: trans('Team: :name', { name: pageProps.auth.user.current_team?.name ?? '–' }),
    icon: 'pi pi-users',
    visible: !!pageProps.auth.user.current_team,
    items: [
      {
        label: trans('Team Settings'),
        icon: 'pi pi-users',
        command: () => router.get(route('teams.show', pageProps.auth.user.current_team))
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
        label: trans('Profile'),
        icon: 'pi pi-user',
        command: () => router.get(route('profile.show'))
      },
      {
        label: trans('Log Out'),
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
        <div class="flex items-center gap-2">
          <Button :icon="isDark ? 'pi pi-sun' : 'pi pi-moon'" text rounded @click="toggleDark" />
          <Avatar
            :image="pageProps.auth.user.profile_photo_url"
            shape="circle"
            size="large"
            @click="toggle"
          />
          <Menu ref="menu" id="overlay_menu" :model="items" :popup="true" />
        </div>
      </template>
    </Menubar>
  </div>
</template>
