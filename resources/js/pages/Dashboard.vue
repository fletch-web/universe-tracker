<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Tv,
    Users,
    Trophy,
    BookOpen,
    Trash,
    Pencil,
    UserX,
    PlusCircle,
    History,
} from '@lucide/vue';
import { ref } from 'vue';
import BookingTab from '@/components/dashboard/BookingTab.vue';
import ChampionshipsTab from '@/components/dashboard/ChampionshipsTab.vue';
import DashboardStats from '@/components/dashboard/DashboardStats.vue';
import RosterTab from '@/components/dashboard/RosterTab.vue';
import ShowsTab from '@/components/dashboard/ShowsTab.vue';
import StorylinesTab from '@/components/dashboard/StorylinesTab.vue';
import type { Show, Superstar, Team, Championship, Storyline, ShowLog, PaginatedData } from '@/types';

withDefaults(
    defineProps<{
        shows: Show[];
        superstars: Superstar[];
        teams: Team[];
        paginatedSuperstars: PaginatedData<Superstar>;
        paginatedTeams: PaginatedData<Team>;
        championships: Championship[];
        storylines: Storyline[];
        history: ShowLog[];
        isReadOnly?: boolean;
        owner?: {
            name: string;
            username: string;
        } | null;
    }>(),
    {
        isReadOnly: false,
        owner: null,
    },
);

// Breadcrumbs configuration for starter kit layout
defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Universe Tracker',
                href: '/dashboard',
            },
        ],
    },
});

// Tab State Management
const currentTab = ref('dashboard');
const switchTab = (tab: string) => {
    currentTab.value = tab;
};

// Helpers
const logout = () => {
    router.post('/logout');
};

// Clear Data Confirmation Handler
const handleClearData = () => {
    if (
        confirm(
            'Are you absolutely sure you want to CLEAR all universe data? This will permanently delete all shows, superstars, teams, championships, storylines, and history logs. This action CANNOT be undone.',
        )
    ) {
        router.post('/universe/clear');
    }
};
</script>

<template>
    <Head :title="owner ? owner.name + '\'s Universe - Universe Tracker' : 'Booking Deck - Universe Tracker'" />

    <div
        class="flex min-h-screen flex-col gap-6 bg-slate-950 p-6 font-sans text-slate-100"
    >
        <!-- Premium Tabs Subnavigation Bar -->
        <div
            class="flex flex-col gap-4 rounded-2xl border border-slate-800 bg-slate-900/40 p-4 backdrop-blur"
        >
            <div
                class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-800/60 pb-3"
            >
                <div class="flex items-center space-x-3.5 pl-2">
                    <div
                        class="flex items-center justify-center rounded-xl bg-gradient-to-tr from-amber-500 to-yellow-400 p-2 text-xl font-black tracking-wider text-slate-955 shadow-lg shadow-amber-500/10"
                    >
                        <Tv class="h-6 w-6" />
                    </div>
                    <div>
                        <h1
                            class="text-base leading-tight font-black text-white"
                        >
                            {{
                                isReadOnly && owner
                                    ? `${owner.name}'s Universe`
                                    : 'Universe Tracker'
                            }}
                        </h1>
                        <p class="text-[10px] tracking-wide text-slate-400">
                            {{
                                isReadOnly
                                    ? 'Public Read-Only View'
                                    : 'Elite Booking & Metrics engine'
                            }}
                        </p>
                    </div>
                </div>

                <!-- User actions -->
                <div class="flex items-center gap-3">
                    <template v-if="isReadOnly">
                        <a
                            v-if="!$page.props.auth?.user"
                            href="/"
                            class="flex items-center gap-1 rounded-lg border border-slate-800 bg-slate-900 px-2.5 py-1.5 text-[11px] font-semibold text-slate-300 transition hover:bg-slate-800"
                        >
                            Sign In / Register
                        </a>
                        <a
                            v-else
                            href="/dashboard"
                            class="flex items-center gap-1 rounded-lg border border-slate-800 bg-slate-900 px-2.5 py-1.5 text-[11px] font-semibold text-slate-300 transition hover:bg-slate-800"
                        >
                            Back to My Dashboard
                        </a>
                    </template>
                    <template v-else-if="$page.props.auth?.user">
                        <div class="hidden text-right sm:block">
                            <div class="text-xs font-bold text-slate-200">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-[10px] text-slate-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>
                        <a
                            href="/settings/profile"
                            class="flex items-center gap-1 rounded-lg border border-slate-800 bg-slate-900 px-2.5 py-1.5 text-[11px] font-semibold text-slate-300 transition hover:bg-slate-800"
                        >
                            <Pencil class="h-3.5 w-3.5" />
                            Edit Profile
                        </a>
                        <button
                            @click="handleClearData"
                            class="flex items-center gap-1 rounded-lg border border-slate-800 bg-slate-900 px-2.5 py-1.5 text-[11px] font-semibold text-rose-400 transition hover:border-rose-900/60 hover:bg-rose-950/20"
                        >
                            <Trash class="h-3.5 w-3.5" />
                            Clear Data
                        </button>
                        <button
                            @click="logout"
                            class="flex items-center gap-1 rounded-lg border border-red-900/60 bg-red-950/40 px-2.5 py-1.5 text-[11px] font-semibold text-red-400 transition hover:bg-red-950 hover:text-red-300"
                        >
                            <UserX class="h-3.5 w-3.5" />
                            Sign Out
                        </button>
                    </template>
                </div>
            </div>

            <nav class="flex flex-wrap gap-1.5 pl-2">
                <button
                    @click="switchTab('dashboard')"
                    :class="[
                        'flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-semibold transition-all duration-300',
                        currentTab === 'dashboard'
                            ? 'bg-amber-400 text-slate-955 shadow-lg shadow-amber-400/10'
                            : 'text-slate-400 hover:bg-slate-900 hover:text-white',
                    ]"
                >
                    <History class="h-4 w-4" /> Dashboard
                </button>
                <button
                    @click="switchTab('shows')"
                    :class="[
                        'flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-semibold transition-all duration-300',
                        currentTab === 'shows'
                            ? 'bg-amber-400 text-slate-955 shadow-lg shadow-amber-400/10'
                            : 'text-slate-400 hover:bg-slate-900 hover:text-white',
                    ]"
                >
                    <Tv class="h-4 w-4" /> Shows
                </button>
                <button
                    @click="switchTab('roster')"
                    :class="[
                        'flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-semibold transition-all duration-300',
                        currentTab === 'roster'
                            ? 'bg-amber-400 text-slate-955 shadow-lg shadow-amber-400/10'
                            : 'text-slate-400 hover:bg-slate-900 hover:text-white',
                    ]"
                >
                    <Users class="h-4 w-4" /> Superstars & Teams
                </button>
                <button
                    @click="switchTab('titles')"
                    :class="[
                        'flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-semibold transition-all duration-300',
                        currentTab === 'titles'
                            ? 'bg-amber-400 text-slate-955 shadow-lg shadow-amber-400/10'
                            : 'text-slate-400 hover:bg-slate-900 hover:text-white',
                    ]"
                >
                    <Trophy class="h-4 w-4" /> Championships
                </button>
                <button
                    @click="switchTab('storylines')"
                    :class="[
                        'flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-semibold transition-all duration-300',
                        currentTab === 'storylines'
                            ? 'bg-amber-400 text-slate-955 shadow-lg shadow-amber-400/10'
                            : 'text-slate-400 hover:bg-slate-900 hover:text-white',
                    ]"
                >
                    <BookOpen class="h-4 w-4" /> Storylines
                </button>
                <button
                    v-if="!isReadOnly"
                    @click="switchTab('booking')"
                    :class="[
                        'flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-semibold transition-all duration-300',
                        currentTab === 'booking'
                            ? 'bg-amber-400 text-slate-955 shadow-lg shadow-amber-400/10'
                            : 'text-slate-400 hover:bg-slate-900 hover:text-white',
                    ]"
                >
                    <PlusCircle class="h-4 w-4" /> Record Show
                </button>
            </nav>
        </div>

        <!-- MAIN TAB VIEWS -->
        <DashboardStats
            v-if="currentTab === 'dashboard'"
            :shows="shows"
            :superstars="superstars"
            :championships="championships"
            :storylines="storylines"
            :history="history"
            :isReadOnly="isReadOnly"
            :owner="owner"
        />

        <ShowsTab
            v-if="currentTab === 'shows'"
            :shows="shows"
            :isReadOnly="isReadOnly"
        />

        <RosterTab
            v-if="currentTab === 'roster'"
            :shows="shows"
            :superstars="superstars"
            :teams="teams"
            :paginatedSuperstars="paginatedSuperstars"
            :paginatedTeams="paginatedTeams"
            :isReadOnly="isReadOnly"
            :championships="championships"
        />

        <ChampionshipsTab
            v-if="currentTab === 'titles'"
            :shows="shows"
            :superstars="superstars"
            :teams="teams"
            :championships="championships"
            :isReadOnly="isReadOnly"
        />

        <StorylinesTab
            v-if="currentTab === 'storylines'"
            :storylines="storylines"
            :isReadOnly="isReadOnly"
        />

        <BookingTab
            v-if="currentTab === 'booking'"
            :shows="shows"
            :superstars="superstars"
            :teams="teams"
            :championships="championships"
            :storylines="storylines"
            @switch-tab="switchTab"
        />
    </div>
</template>
