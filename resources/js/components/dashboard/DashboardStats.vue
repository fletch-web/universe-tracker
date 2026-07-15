<script setup lang="ts">
import { Tv, Users, Trophy, BookOpen, Star, History, ArrowLeft } from '@lucide/vue';
import { computed, ref } from 'vue';
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

const selectedShowLog = ref<ShowLog | null>(null);

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

const getMatchCompetitorImage = (match: any, slot: 1 | 2 | 3 | 4) => {
    const isTeam = [
        'TagTeam',
        'TripleThreatTag',
        'Fatal4WayTag',
        'ThreeOnThreeTag',
        'FourOnFourTag',
    ].includes(match.division);

    const isAdHoc = [
        'TagTeam',
        'ThreeOnThreeTag',
        'FourOnFourTag',
    ].includes(match.division) && match.team1_superstar_ids && match.team1_superstar_ids.length > 0;

    if (isAdHoc) {
        const ids = slot === 1 ? match.team1_superstar_ids : slot === 2 ? match.team2_superstar_ids : [];

        if (ids && ids.length > 0) {
            const firstS = props.superstars.find(s => s.id === ids[0]);

            return firstS ? firstS.image : null;
        }

        return null;
    } else if (!isTeam) {
        let idVal = 0;

        if (slot === 1) {
idVal = match.c1_superstar_id;
} else if (slot === 2) {
idVal = match.c2_superstar_id;
} else if (slot === 3) {
idVal = match.c3_superstar_id;
} else if (slot === 4) {
idVal = match.c4_superstar_id;
}

        if (!idVal) {
return null;
}

        const s = props.superstars.find(superstar => superstar.id === idVal);

        return s ? s.image : null;
    } else {
        let idVal = 0;

        if (slot === 1) {
idVal = match.c1_team_id;
} else if (slot === 2) {
idVal = match.c2_team_id;
} else if (slot === 3) {
idVal = match.c3_team_id;
} else if (slot === 4) {
idVal = match.c4_team_id;
}

        if (!idVal) {
return null;
}

        const t = props.teams.find(team => team.id === idVal);

        if (t && t.superstars && t.superstars.length > 0) {
            const firstS = props.superstars.find(superstar => superstar.id === t.superstars[0].id);

            return firstS ? firstS.image : t.superstars[0].image;
        }

        return null;
    }
};

const getMatchCompetitorName = (match: any, slot: 1 | 2 | 3 | 4) => {
    const isTeam = [
        'TagTeam',
        'TripleThreatTag',
        'Fatal4WayTag',
        'ThreeOnThreeTag',
        'FourOnFourTag',
    ].includes(match.division);

    const isAdHoc = [
        'TagTeam',
        'ThreeOnThreeTag',
        'FourOnFourTag',
    ].includes(match.division) && match.team1_superstar_ids && match.team1_superstar_ids.length > 0;

    if (isAdHoc) {
        const ids = slot === 1 ? match.team1_superstar_ids : slot === 2 ? match.team2_superstar_ids : [];

        return ids
            .map(id => props.superstars.find(s => s.id === Number(id))?.name || '')
            .filter(Boolean)
            .join(' & ') || `Slot #${slot}`;
    } else if (!isTeam) {
        let idVal = 0;

        if (slot === 1) {
idVal = match.c1_superstar_id;
} else if (slot === 2) {
idVal = match.c2_superstar_id;
} else if (slot === 3) {
idVal = match.c3_superstar_id;
} else if (slot === 4) {
idVal = match.c4_superstar_id;
}

        if (!idVal) {
return '';
}

        const s = props.superstars.find(superstar => superstar.id === idVal);

        return s ? s.name : '';
    } else {
        let idVal = 0;

        if (slot === 1) {
idVal = match.c1_team_id;
} else if (slot === 2) {
idVal = match.c2_team_id;
} else if (slot === 3) {
idVal = match.c3_team_id;
} else if (slot === 4) {
idVal = match.c4_team_id;
}

        if (!idVal) {
return '';
}

        const t = props.teams.find(team => team.id === idVal);

        return t ? t.name : '';
    }
};

const getMatchWinnerName = (match: any) => {
    if (match.outcome !== 'Decisive') {
return match.winner_slot === 'Draw' ? 'Stalemate No Contest (Draw)' : '';
}

    const slotNum = Number(match.winner_slot) as 1 | 2 | 3 | 4;

    return getMatchCompetitorName(match, slotNum);
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
                <div v-if="selectedShowLog">
                    <!-- Detail View Header -->
                    <div class="mb-5 flex items-center justify-between border-b border-slate-800 pb-4">
                        <button
                            @click="selectedShowLog = null"
                            class="flex items-center gap-1.5 rounded-lg border border-slate-800 bg-slate-950 px-3 py-1.5 text-xs font-bold text-slate-300 transition-all hover:bg-slate-900 hover:text-white"
                        >
                            <ArrowLeft class="h-3.5 w-3.5" /> Back to History
                        </button>
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-block h-3 w-3 rounded-full"
                                :style="{ backgroundColor: selectedShowLog.show ? selectedShowLog.show.color : '#fff' }"
                            ></span>
                            <span class="text-sm font-black text-white uppercase tracking-wider">
                                {{ selectedShowLog.show ? selectedShowLog.show.name : 'Unknown Show' }}
                            </span>
                            <span class="text-[10px] text-slate-500 font-mono">
                                ({{ selectedShowLog.date }})
                            </span>
                        </div>
                    </div>

                    <!-- Match Cards Grid -->
                    <div class="space-y-4">
                        <div
                            v-for="m in selectedShowLog.matches"
                            :key="m.id"
                            class="relative overflow-hidden rounded-2xl border border-slate-800/80 bg-slate-950 p-5 shadow-inner"
                        >
                            <div class="flex flex-col items-center space-y-4">
                                <!-- Title Match Header -->
                                <div v-if="m.championship" class="flex flex-col items-center gap-0.5">
                                    <span class="flex items-center gap-1 text-[9px] font-black tracking-widest text-amber-400 uppercase">
                                        <Trophy class="h-3 w-3" /> Championship Match
                                    </span>
                                    <span class="text-xs font-bold text-white uppercase">
                                        {{ m.championship.name }}
                                    </span>
                                </div>

                                <!-- Stipulation & Division -->
                                <div class="text-center">
                                    <span class="text-xs font-extrabold text-purple-400 uppercase tracking-wider">
                                        {{ m.stipulation || 'Normal Match' }}
                                    </span>
                                    <span class="block text-[9px] text-slate-500 font-semibold uppercase tracking-wider">
                                        {{ m.division === 'Segment' ? 'Storyline Segment' : m.division }}
                                    </span>
                                </div>

                                <!-- Competitors Display -->
                                <div class="w-full">
                                    <!-- Segment Notes -->
                                    <div v-if="m.division === 'Segment'" class="text-center italic text-slate-400 text-xs py-1">
                                        "{{ m.notes }}"
                                    </div>

                                    <!-- Grid for matches -->
                                    <div v-else
                                        :class="[
                                            'grid gap-4 items-start text-center',
                                            m.division === 'TripleThreat' ? 'grid-cols-3' : 
                                            ['Fatal4Way', 'Fatal4WayTag'].includes(m.division) ? 'grid-cols-4' : 'grid-cols-2'
                                        ]"
                                    >
                                        <!-- Competitor 1 -->
                                        <div 
                                            class="flex flex-col items-center space-y-1"
                                            :class="{ 'opacity-40 grayscale': m.outcome === 'Decisive' && m.winner_slot !== '1' }"
                                        >
                                            <div class="relative">
                                                <img
                                                    :src="getMatchCompetitorImage(m, 1) || FALLBACK_USER_IMG"
                                                    class="h-14 w-14 rounded-full border-2 bg-slate-900 object-cover shadow"
                                                    :style="{ borderColor: selectedShowLog.show ? selectedShowLog.show.color : '#6b21a8' }"
                                                />
                                                <div v-if="m.outcome === 'Decisive' && m.winner_slot === '1'" class="absolute -top-1.5 -right-1.5 bg-gradient-to-r from-amber-400 to-yellow-300 text-slate-950 text-[7px] font-black px-1 py-0.5 rounded-full shadow uppercase">
                                                    🏆 WIN
                                                </div>
                                            </div>
                                            <span class="max-w-[80px] truncate text-[10px] font-bold text-white uppercase">
                                                {{ getMatchCompetitorName(m, 1) }}
                                            </span>
                                        </div>

                                        <!-- Competitor 2 -->
                                        <div 
                                            class="flex flex-col items-center space-y-1"
                                            :class="{ 'opacity-40 grayscale': m.outcome === 'Decisive' && m.winner_slot !== '2' }"
                                        >
                                            <div class="relative">
                                                <img
                                                    :src="getMatchCompetitorImage(m, 2) || FALLBACK_USER_IMG"
                                                    class="h-14 w-14 rounded-full border-2 bg-slate-900 object-cover shadow"
                                                    :style="{ borderColor: selectedShowLog.show ? selectedShowLog.show.color : '#6b21a8' }"
                                                />
                                                <div v-if="m.outcome === 'Decisive' && m.winner_slot === '2'" class="absolute -top-1.5 -right-1.5 bg-gradient-to-r from-amber-400 to-yellow-300 text-slate-950 text-[7px] font-black px-1 py-0.5 rounded-full shadow uppercase">
                                                    🏆 WIN
                                                </div>
                                            </div>
                                            <span class="max-w-[80px] truncate text-[10px] font-bold text-white uppercase">
                                                {{ getMatchCompetitorName(m, 2) }}
                                            </span>
                                        </div>

                                        <!-- Competitor 3 -->
                                        <div 
                                            v-if="['TripleThreat', 'TripleThreatTag', 'Fatal4Way', 'Fatal4WayTag'].includes(m.division)"
                                            class="flex flex-col items-center space-y-1"
                                            :class="{ 'opacity-40 grayscale': m.outcome === 'Decisive' && m.winner_slot !== '3' }"
                                        >
                                            <div class="relative">
                                                <img
                                                    :src="getMatchCompetitorImage(m, 3) || FALLBACK_USER_IMG"
                                                    class="h-14 w-14 rounded-full border-2 bg-slate-900 object-cover shadow"
                                                    :style="{ borderColor: selectedShowLog.show ? selectedShowLog.show.color : '#6b21a8' }"
                                                />
                                                <div v-if="m.outcome === 'Decisive' && m.winner_slot === '3'" class="absolute -top-1.5 -right-1.5 bg-gradient-to-r from-amber-400 to-yellow-300 text-slate-950 text-[7px] font-black px-1 py-0.5 rounded-full shadow uppercase">
                                                    🏆 WIN
                                                </div>
                                            </div>
                                            <span class="max-w-[80px] truncate text-[10px] font-bold text-white uppercase">
                                                {{ getMatchCompetitorName(m, 3) }}
                                            </span>
                                        </div>

                                        <!-- Competitor 4 -->
                                        <div 
                                            v-if="['Fatal4Way', 'Fatal4WayTag'].includes(m.division)"
                                            class="flex flex-col items-center space-y-1"
                                            :class="{ 'opacity-40 grayscale': m.outcome === 'Decisive' && m.winner_slot !== '4' }"
                                        >
                                            <div class="relative">
                                                <img
                                                    :src="getMatchCompetitorImage(m, 4) || FALLBACK_USER_IMG"
                                                    class="h-14 w-14 rounded-full border-2 bg-slate-900 object-cover shadow"
                                                    :style="{ borderColor: selectedShowLog.show ? selectedShowLog.show.color : '#6b21a8' }"
                                                />
                                                <div v-if="m.outcome === 'Decisive' && m.winner_slot === '4'" class="absolute -top-1.5 -right-1.5 bg-gradient-to-r from-amber-400 to-yellow-300 text-slate-950 text-[7px] font-black px-1 py-0.5 rounded-full shadow uppercase">
                                                    🏆 WIN
                                                </div>
                                            </div>
                                            <span class="max-w-[80px] truncate text-[10px] font-bold text-white uppercase">
                                                {{ getMatchCompetitorName(m, 4) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Outcome summary bar -->
                                <div v-if="m.division !== 'Segment'" class="w-full border-t border-slate-800 pt-3 text-center text-xs font-bold">
                                    <span v-if="m.outcome === 'Decisive'" class="text-amber-400 uppercase tracking-wider">
                                        Winner: {{ getMatchWinnerName(m) }}
                                    </span>
                                    <span v-else class="text-slate-400 uppercase tracking-wider">
                                        Result: Draw
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else>
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
                            @click="selectedShowLog = h"
                            class="cursor-pointer space-y-2 rounded-xl border border-slate-800/80 bg-slate-950 p-4 transition-all hover:border-purple-500/50 hover:bg-slate-900/40"
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
                                            class="mr-1 inline-flex items-center gap-0.5 rounded bg-amber-400 px-1 py-0.5 text-[8px] font-black text-slate-955 uppercase"
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
    </div>
</template>
