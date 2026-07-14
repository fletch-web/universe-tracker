<script setup lang="ts">
import { Tv, Users, Trophy, BookOpen, Star, History } from '@lucide/vue';
import { computed } from 'vue';
import type { Show, Superstar, Championship, Storyline, ShowLog } from '@/types';

const props = defineProps<{
    shows: Show[];
    superstars: Superstar[];
    championships: Championship[];
    storylines: Storyline[];
    history: ShowLog[];
    isReadOnly: boolean;
    owner: {
        name: string;
        username: string;
    } | null;
}>();

const FALLBACK_USER_IMG =
    "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'><rect width='100%' height='100%' fill='%230f172a'/><circle cx='50' cy='40' r='18' fill='%23334155'/><path d='M20,80 C20,60 30,55 50,55 C70,55 80,60 80,80 Z' fill='%23334155'/></svg>";

const topSuperstars = computed(() => {
    return [...props.superstars].sort((a, b) => b.wins - a.wins).slice(0, 5);
});

const getSuperstarsFromIds = (ids?: number[] | null) => {
    if (!ids) {
        return [];
    }

    return ids
        .map((id) => props.superstars.find((s) => s.id === id))
        .filter((s): s is Superstar => !!s);
};
</script>

<template>
    <div class="space-y-6">
        <!-- Grid Metrics Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div
                class="flex items-center justify-between rounded-2xl border border-slate-800 bg-slate-900/60 p-5 shadow-inner"
            >
                <div>
                    <p
                        class="text-[10px] font-black tracking-wider text-slate-400 uppercase"
                    >
                        Total Shows
                    </p>
                    <h3
                        class="mt-1 text-3xl leading-none font-black text-white"
                    >
                        {{ shows.length }}
                    </h3>
                </div>
                <div
                    class="rounded-xl border border-blue-500/20 bg-blue-500/10 p-3 text-blue-400"
                >
                    <Tv class="h-5 w-5" />
                </div>
            </div>
            <div
                class="flex items-center justify-between rounded-2xl border border-slate-800 bg-slate-900/60 p-5 shadow-inner"
            >
                <div>
                    <p
                        class="text-[10px] font-black tracking-wider text-slate-400 uppercase"
                    >
                        Superstars Registered
                    </p>
                    <h3
                        class="mt-1 text-3xl leading-none font-black text-white"
                    >
                        {{ superstars.length }}
                    </h3>
                </div>
                <div
                    class="rounded-xl border border-amber-500/20 bg-amber-500/10 p-3 text-amber-400"
                >
                    <Users class="h-5 w-5" />
                </div>
            </div>
            <div
                class="flex items-center justify-between rounded-2xl border border-slate-800 bg-slate-900/60 p-5 shadow-inner"
            >
                <div>
                    <p
                        class="text-[10px] font-black tracking-wider text-slate-400 uppercase"
                    >
                        Championship Titles
                    </p>
                    <h3
                        class="mt-1 text-3xl leading-none font-black text-white"
                    >
                        {{ championships.length }}
                    </h3>
                </div>
                <div
                    class="text-yellow-450 rounded-xl border border-yellow-500/20 bg-yellow-500/10 p-3"
                >
                    <Trophy class="h-5 w-5" />
                </div>
            </div>
            <div
                class="flex items-center justify-between rounded-2xl border border-slate-800 bg-slate-900/60 p-5 shadow-inner"
            >
                <div>
                    <p
                        class="text-[10px] font-black tracking-wider text-slate-400 uppercase"
                    >
                        Active Storylines
                    </p>
                    <h3
                        class="mt-1 text-3xl leading-none font-black text-white"
                    >
                        {{ storylines.length }}
                    </h3>
                </div>
                <div
                    class="rounded-xl border border-purple-500/20 bg-purple-500/10 p-3 text-purple-400"
                >
                    <BookOpen class="h-5 w-5" />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Top Performers with coloured borders -->
            <div
                class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5 shadow lg:col-span-1"
            >
                <h3
                    class="mb-4 flex items-center gap-2 text-sm font-bold text-white"
                >
                    <Star class="h-4 w-4 fill-amber-400 text-amber-400" />
                    Top Superstars (by Wins)
                </h3>
                <div
                    v-if="topSuperstars.length === 0"
                    class="py-4 text-center text-xs text-slate-500"
                >
                    No competition records tracked yet.
                </div>
                <div
                    v-else
                    class="space-y-3.5 divide-y divide-slate-800/40"
                >
                    <div
                        v-for="(s, index) in topSuperstars"
                        :key="s.id"
                        class="flex items-center justify-between pt-3 first:pt-0"
                    >
                        <div class="flex items-center space-x-3">
                            <span
                                class="font-mono text-xs font-bold text-slate-500"
                                >#0{{ index + 1 }}</span
                            >
                            <img
                                :src="s.image || FALLBACK_USER_IMG"
                                class="h-9 w-9 rounded-full border-2 bg-slate-950 object-cover"
                                :style="{
                                    borderColor: s.show
                                        ? s.show.color
                                        : '#334155',
                                }"
                            />
                            <div>
                                <h4 class="text-xs font-bold text-white">
                                    {{ s.name }}
                                </h4>
                                <p
                                    class="text-[9px] font-semibold"
                                    :style="{
                                        color: s.show
                                            ? s.show.color
                                            : '#64748b',
                                    }"
                                >
                                    {{
                                        s.show ? s.show.name : 'Independent'
                                    }}
                                </p>
                            </div>
                        </div>
                        <span
                            class="font-mono text-xs font-semibold text-emerald-400"
                            >{{ s.wins }} W</span
                        >
                    </div>
                </div>
            </div>

            <!-- Show History -->
            <div
                class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5 shadow lg:col-span-2"
            >
                <h3
                    class="mb-4 flex items-center gap-2 text-sm font-bold text-white"
                >
                    <History class="h-4 w-4 text-slate-400" /> Recent Show
                    Booking History
                </h3>
                <div
                    v-if="history.length === 0"
                    class="py-6 text-center text-xs text-slate-500"
                >
                    Record a show from the action tabs to populate metrics.
                </div>
                <div
                    v-else
                    class="max-h-[400px] space-y-4 overflow-y-auto pr-1"
                >
                    <div
                        v-for="h in history"
                        :key="h.id"
                        class="space-y-2 rounded-xl border border-slate-800/80 bg-slate-950 p-4"
                    >
                        <div
                            class="flex items-center justify-between text-xs"
                        >
                            <span
                                class="flex items-center gap-1.5 font-black text-white"
                            >
                                <span
                                    class="inline-block h-2.5 w-2.5 rounded-full"
                                    :style="{
                                        backgroundColor: h.show
                                            ? h.show.color
                                            : '#fff',
                                    }"
                                ></span>
                                {{ h.show ? h.show.name : 'Unknown Show' }}
                            </span>
                            <span
                                class="font-mono text-[10px] text-slate-500"
                                >{{ h.date }}</span
                            >
                        </div>
                        <div
                            class="border-slate-850 space-y-1 border-l pl-4"
                        >
                            <div
                                v-for="m in h.matches"
                                :key="m.id"
                                class="text-slate-450 font-mono text-xs leading-relaxed"
                            >
                                •
                                <template v-if="m.division === 'Segment'">
                                    <span class="font-bold text-amber-400"
                                        >[Segment]</span
                                    >
                                    <span class="ml-1 text-slate-200">{{
                                        m.notes
                                    }}</span>
                                </template>
                                <template v-else>
                                    <span
                                        v-if="m.championship"
                                        class="mr-1 inline-flex items-center gap-0.5 rounded bg-amber-400 px-1 py-0.5 text-[8px] font-black text-slate-950 uppercase"
                                    >
                                        <Trophy class="h-1.5 w-1.5" />
                                        {{ m.championship.name }}
                                    </span>
                                    <span
                                        v-if="m.stipulation"
                                        class="mr-1 font-bold text-amber-400"
                                        >[{{ m.stipulation }}]</span
                                    >
                                    <template
                                        v-if="
                                            m.team1_superstar_ids &&
                                            m.team1_superstar_ids.length > 0
                                        "
                                    >
                                        <span
                                            class="text-slate-250 font-bold"
                                            >{{
                                                getSuperstarsFromIds(
                                                    m.team1_superstar_ids,
                                                )
                                                    .map((s) => s.name)
                                                    .join(' & ')
                                            }}</span
                                        >
                                        vs
                                        <span
                                            class="text-slate-250 font-bold"
                                            >{{
                                                getSuperstarsFromIds(
                                                    m.team2_superstar_ids,
                                                )
                                                    .map((s) => s.name)
                                                    .join(' & ')
                                            }}</span
                                        >
                                        <span
                                            v-if="m.outcome === 'Decisive'"
                                            class="ml-1 text-emerald-400"
                                            >({{
                                                m.winner_slot === '1'
                                                    ? getSuperstarsFromIds(
                                                          m.team1_superstar_ids,
                                                      )
                                                          .map(
                                                              (s) => s.name,
                                                          )
                                                          .join(' & ')
                                                    : getSuperstarsFromIds(
                                                          m.team2_superstar_ids,
                                                      )
                                                          .map(
                                                              (s) => s.name,
                                                          )
                                                          .join(' & ')
                                            }}
                                            wins)</span
                                        >
                                        <span
                                            v-else
                                            class="ml-1 text-amber-500"
                                            >(Draw)</span
                                        >
                                    </template>
                                    <template v-else>
                                        <span class="text-slate-200">{{
                                            [
                                                'Singles',
                                                'TripleThreat',
                                                'Fatal4Way',
                                            ].includes(m.division)
                                                ? m.c1_superstar?.name
                                                : m.c1_team?.name
                                        }}</span>
                                        vs
                                        <span class="text-slate-200">{{
                                            [
                                                'Singles',
                                                'TripleThreat',
                                                'Fatal4Way',
                                            ].includes(m.division)
                                                ? m.c2_superstar?.name
                                                : m.c2_team?.name
                                        }}</span>
                                        <template
                                            v-if="
                                                [
                                                    'TripleThreat',
                                                    'Fatal4Way',
                                                    'TripleThreatTag',
                                                    'Fatal4WayTag',
                                                ].includes(m.division)
                                            "
                                        >
                                            vs
                                            <span class="text-slate-200">{{
                                                [
                                                    'Singles',
                                                    'TripleThreat',
                                                    'Fatal4Way',
                                                ].includes(m.division)
                                                    ? m.c3_superstar?.name
                                                    : m.c3_team?.name
                                            }}</span>
                                        </template>
                                        <template
                                            v-if="
                                                [
                                                    'Fatal4Way',
                                                    'Fatal4WayTag',
                                                ].includes(m.division)
                                            "
                                        >
                                            vs
                                            <span class="text-slate-200">{{
                                                [
                                                    'Singles',
                                                    'TripleThreat',
                                                    'Fatal4Way',
                                                ].includes(m.division)
                                                    ? m.c4_superstar?.name
                                                    : m.c4_team?.name
                                            }}</span>
                                        </template>
                                        <span
                                            v-if="m.outcome === 'Decisive'"
                                            class="ml-1 text-emerald-400"
                                            >({{
                                                m.winner_superstar?.name ||
                                                m.winner_team?.name
                                            }}
                                            wins)</span
                                        >
                                        <span
                                            v-else
                                            class="ml-1 text-amber-500"
                                            >(Draw)</span
                                        >
                                    </template>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
