<script setup lang="ts">
import ApplicationLogo from '@/Components/General/ApplicationLogo.vue';
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

defineProps({
  canLogin: {
    type: Boolean
  },
  canRegister: {
    type: Boolean
  },
  laravelVersion: {
    type: String,
    required: true
  },
  phpVersion: {
    type: String,
    required: true
  }
});

function handleImageError() {
  document.getElementById('screenshot-container')?.classList.add('!hidden');
  document.getElementById('docs-card')?.classList.add('!row-span-1');
  document.getElementById('docs-card-content')?.classList.add('!flex-row');
  document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
  <Head title="Welcome" />
  <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
    <div class="relative min-h-screen w-screen flex flex-col items-center justify-between">
      <header class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3 w-screen px-12">
        <div class="flex lg:justify-center lg:col-start-2">
          <ApplicationLogo />
        </div>
        <nav v-if="canLogin" class="-mx-3 flex flex-1 justify-end">
          <Link
            v-if="($page.props.auth as any).user"
            :href="route('dashboard')"
            class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
          >
            Dashboard
          </Link>

          <template v-else>
            <Link
              :href="route('login')"
              class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
            >
              Log in
            </Link>

            <Link
              v-if="canRegister"
              :href="route('register')"
              class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
            >
              Register
            </Link>
          </template>
        </nav>
      </header>

      <main class="mt-6 w-1/2">
        <div
          class="p-6 lg:p-8 dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent"
        >
          <h1 class="mt-8 text-2xl font-medium text-gray-900 dark:text-white">
            Welcome to Kollektiv Bewegung Intern!
          </h1>

          <p class="mt-6 text-gray-500 dark:text-gray-400 leading-relaxed">
            This tool is developed to help manage internal processes and communication within
            Kollektiv:Bewegung. It's a work in progress so please be kind to it. If you encounter
            any issues or have suggestions for improvements, feel free to reach out to the
            development team.
          </p>
        </div>
      </main>

      <footer class="py-16 text-center text-sm text-black dark:text-white/70">
        Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
      </footer>
    </div>
  </div>
</template>
