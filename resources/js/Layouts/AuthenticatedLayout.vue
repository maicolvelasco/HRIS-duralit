<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';
import { useDarkMode } from '@/Composables/useDarkMode';
const { isDark, toggleDarkMode } = useDarkMode();
import { SunIcon, MoonIcon } from '@heroicons/vue/24/outline';
import FlashMessage from '@/Components/FlashMessage.vue';

const user = usePage().props.auth.user;
const isManager = computed(() => usePage().props.auth.isManager || false);

const showingSidebar = ref(false);

async function logout() {
  await axios.get('/sanctum/csrf-cookie');
  router.post(route('logout'));
}
</script>

<template>
  <div>
    <div class="min-h-screen bg-gray-100 dark:bg-slate-900">
      <FlashMessage />
      <nav class="border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-slate-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="flex h-16 justify-between">
            <!-- Left: Hamburger (mobile) + Logo -->
            <div class="flex items-center">
              <!-- Hamburger -->
              <div class="sm:hidden -ms-2 me-3">
                <button
                  @click="showingSidebar = !showingSidebar"
                  class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-300 hover:text-gray-500 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-slate-700 focus:text-gray-500 transition"
                >
                  <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path
                      :class="{ hidden: showingSidebar, 'inline-flex': !showingSidebar }"
                      stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 6h16M4 12h16M4 18h16"
                    />
                    <path
                      :class="{ hidden: !showingSidebar, 'inline-flex': showingSidebar }"
                      stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M6 18L18 6M6 6l12 12"
                    />
                  </svg>
                </button>
              </div>

              <!-- Logo -->
              <div class="shrink-0 flex items-center">
                <Link :href="route('dashboard')">
                  <ApplicationLogo
                    class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-100"
                  />
                </Link>
              </div>

              <!-- Nav Links (desktop) -->
              <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                  Dashboard
                </NavLink>
                <NavLink
                  v-if="user.permissions?.includes?.('Control de Jerarquia')"
                  :href="route('rrhh.index')"
                  :active="route().current('rrhh.index')"
                >
                  Control
                </NavLink>
                <NavLink
                  v-if="user.permissions?.includes?.('Control de Asistencia') || user.permissions?.includes?.('Control de Asistencia Propio')"
                  :href="route('assistances.index')"
                  :active="route().current('assistances.index')"
                >
                  Asistencia
                </NavLink>
                <NavLink :href="route('overtimes.index')" :active="route().current('overtimes.index')">
                  Sobretiempo
                </NavLink>
                <NavLink 
                  v-if="isManager"
                  :href="route('overtimes.team')"
                  :active="route().current('overtimes.team')"
                >
                  S. Tiempo de Equipo
                </NavLink>
                <NavLink 
                  v-if="user.permissions?.includes?.('Ver Autorizaciones')"
                  :href="route('permissions.index')"
                  :active="route().current('permissions.index')"
                >
                  Permisos
                </NavLink>
                <NavLink 
                  v-if="isManager || $page.props.auth.isHrManager"
                  :href="route('permissions.team')"
                  :active="route().current('permissions.team')"
                >
                  P. de Grupo
                </NavLink>
              </div>
            </div>

            <!-- Right: User name + Dropdown (desktop) -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
              <!-- Botón modo claro / oscuro (desktop) -->
              <button
                @click="toggleDarkMode"
                class="me-3 p-2 rounded-md text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-slate-700 focus:outline-none transition"
                title="Cambiar tema"
              >
                <component :is="isDark ? SunIcon : MoonIcon" class="w-5 h-5" />
              </button>

              <div class="relative">
                <Dropdown align="right" width="48">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="inline-flex items-center gap-2 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-300 bg-white dark:bg-slate-800 hover:text-gray-700 dark:hover:text-gray-100 focus:outline-none transition ease-in-out duration-150"
                      >
                        <!-- Foto de perfil (solo si existe) -->
                        <img
                          v-if="user.foto"
                          :src="`/storage/${user.foto}`"
                          class="w-8 h-8 rounded-full object-cover"
                          alt="Foto de perfil"
                        />

                        <!-- Nombre completo -->
                        <span>{{ user.nombre }} {{ user.apellido }}</span>

                        <!-- Icono de flecha -->
                        <svg
                          class="-me-0.5 ms-1 h-4 w-4"
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path
                            fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      </button>
                    </span>
                  </template>

                  <template #content>
                    <DropdownLink :href="route('profile.edit')">Perfil</DropdownLink>
                    <DropdownLink :href="route('settings.index')">Ajustes</DropdownLink>
                    <DropdownLink as="button" @click="logout">Cerrar sesión</DropdownLink>
                  </template>
                </Dropdown>
              </div>
            </div>

            <!-- Right: Three dots dropdown (mobile only) -->
            <div class="flex items-center sm:hidden gap-2">
              <!-- Botón modo claro / oscuro (desktop) -->
              <button
                @click="toggleDarkMode"
                class="me-3 p-2 rounded-md text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-slate-700 focus:outline-none transition"
                title="Cambiar tema"
              >
                <component :is="isDark ? SunIcon : MoonIcon" class="w-5 h-5" />
              </button>

              <Dropdown align="right" width="48">
                <template #trigger>
                  <button
                    class="inline-flex items-center p-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-300 bg-white dark:bg-slate-800 hover:text-gray-700 dark:hover:text-gray-100 focus:outline-none transition"
                  >
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                    </svg>
                  </button>
                </template>
                <template #content>
                  <DropdownLink :href="route('profile.edit')">Perfil</DropdownLink>
                  <DropdownLink :href="route('settings.index')">Ajustes</DropdownLink>
                  <DropdownLink as="button" @click="logout">Cerrar sesión</DropdownLink>
                </template>
              </Dropdown>
            </div>
          </div>
        </div>
      </nav>

      <!-- Sidebar (mobile) -->
      <div
        :class="{
          'translate-x-0': showingSidebar,
          '-translate-x-full': !showingSidebar,
        }"
        class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-slate-800 shadow-lg transform transition-transform duration-300 ease-in-out sm:hidden"
      >
        <!-- Header con foto y nombre -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <div class="flex flex-col items-center">
            <!-- Foto de perfil o ícono -->
            <div class="mb-4">
              <img 
                v-if="user.foto" 
                :src="`/storage/${user.foto}`" 
                class="w-20 h-20 rounded-full object-cover border-4 border-gray-200 dark:border-gray-600"
                alt="Foto de perfil"
              />
              <Icon 
                v-else 
                name="UserCircleIcon" 
                class="w-20 h-20 text-gray-400 dark:text-gray-500" 
              />
            </div>
            
            <!-- Nombre y apellido -->
            <div class="text-center">
              <div class="font-semibold text-lg text-gray-900 dark:text-gray-100">
                {{ user.nombre }} {{ user.apellido }}
              </div>
            </div>
          </div>
        </div>
        
        <!-- Navegación -->
        <nav class="mt-3 space-y-1 px-4">
          <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
            Dashboard
          </ResponsiveNavLink>
          <ResponsiveNavLink
            v-if="user.permissions?.includes?.('Control de Jerarquia')"
            :href="route('rrhh.index')"
            :active="route().current('rrhh.index')"
          >
            Control
          </ResponsiveNavLink>
          <ResponsiveNavLink :href="route('assistances.index')" :active="route().current('assistances.index')">
            Asistencia
          </ResponsiveNavLink>
          <ResponsiveNavLink :href="route('overtimes.index')" :active="route().current('overtimes.index')">
            Sobretiempo
          </ResponsiveNavLink>
          <ResponsiveNavLink
            v-if="isManager"
            :href="route('overtimes.team')"
            :active="route().current('overtimes.team')"
          >
            S. Tiempo de Equipo
          </ResponsiveNavLink>
          <ResponsiveNavLink 
            v-if="user.permissions?.includes?.('Ver Autorizaciones')"
            :href="route('permissions.index')"
            :active="route().current('permissions.index')"
          >
            Permisos
          </ResponsiveNavLink>
          <ResponsiveNavLink 
            v-if="isManager || $page.props.auth.isHrManager"
            :href="route('permissions.team')"
            :active="route().current('permissions.team')"
          >
            P. de Grupo
          </ResponsiveNavLink>
        </nav>
      </div>

      <!-- Overlay -->
      <div
        v-if="showingSidebar"
        @click="showingSidebar = false"
        class="fixed inset-0 z-40 bg-black bg-opacity-50 sm:hidden"
      ></div>

      <!-- Page Heading -->
      <header class="bg-white dark:bg-slate-800 shadow" v-if="$slots.header">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
          <div class="text-gray-800 dark:text-gray-100">
            <slot name="header" />
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main>
        <slot />
      </main>
    </div>
  </div>
</template>