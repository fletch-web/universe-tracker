<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: editProfile(),
    },
    {
        title: 'Security',
        href: editSecurity(),
    },
    {
        title: 'Appearance',
        href: editAppearance(),
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div
        class="flex min-h-screen flex-col gap-6 bg-slate-950 px-6 py-6 font-sans text-slate-100"
    >
        <!-- Settings Header -->
        <div class="flex flex-col gap-1 border-b border-slate-800 pb-4">
            <div class="flex items-center justify-between">
                <h1 class="text-base leading-tight font-black text-white uppercase tracking-wider">
                    Settings
                </h1>
                <Link
                    href="/dashboard"
                    class="text-xs font-semibold text-amber-400 hover:text-amber-300 transition-colors"
                >
                    &larr; Back to Dashboard
                </Link>
            </div>
            <p class="text-[10px] tracking-wide text-slate-400">
                Manage your profile and account settings
            </p>
        </div>

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="mb-6 w-full max-w-xl lg:mb-0 lg:w-48">
                <nav class="flex flex-col space-y-1.5" aria-label="Settings">
                    <Link
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        :href="item.href"
                        :class="[
                            'flex w-full items-center gap-1.5 rounded-xl px-4 py-2.5 text-left text-xs font-semibold transition-all duration-300',
                            isCurrentOrParentUrl(item.href)
                                ? 'bg-amber-400 font-bold text-slate-950 shadow-lg shadow-amber-400/10'
                                : 'text-slate-400 hover:bg-slate-900 hover:text-white',
                        ]"
                    >
                        <component
                            :is="item.icon"
                            class="h-4 w-4"
                            v-if="item.icon"
                        />
                        {{ item.title }}
                    </Link>
                </nav>
            </aside>

            <div
                class="hidden min-h-[400px] border-r border-slate-800 lg:block"
            ></div>

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
