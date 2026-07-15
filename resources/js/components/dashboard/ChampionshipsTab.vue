<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3';
import { Trophy, Tv, Pencil, Trash, Sparkles } from '@lucide/vue';
import { ref, computed } from 'vue';
import type { Show, Superstar, Team, Championship } from '@/types';

const props = defineProps<{
    shows: Show[];
    superstars: Superstar[];
    teams: Team[];
    championships: Championship[];
    isReadOnly: boolean;
}>();

const editChampionshipId = ref<number | null>(null);

const championshipForm = useForm({
    name: '',
    show_id: '' as string | number | null,
    type: 'Singles' as 'Singles' | 'TagTeam',
    champion_id: 'VACANT' as string | number,
});

const nonPleShows = computed(() => props.shows.filter((s) => !s.is_ple));

const eligibleSuperstars = computed(() => {
    if (!championshipForm.show_id) {
        return props.superstars;
    }

    const showId = Number(championshipForm.show_id);

    return props.superstars.filter((s) => s.show_id === showId);
});

const eligibleTeams = computed(() => {
    if (!championshipForm.show_id) {
        return props.teams;
    }

    const showId = Number(championshipForm.show_id);

    return props.teams.filter((t) =>
        t.superstars && t.superstars.some((s) => s.show_id === showId)
    );
});

function route(name: string, param?: string | number): string {
    switch (name) {
        case 'championships.store':
            return '/championships';
        case 'championships.update':
            return `/championships/${param}`;
        case 'championships.destroy':
            return `/championships/${param}`;
        default:
            return '';
    }
}

const handleCreateOrUpdateChampionship = () => {
    if (editChampionshipId.value) {
        championshipForm.put(
            route('championships.update', editChampionshipId.value),
            {
                onSuccess: () => {
                    cancelChampionshipEdit();
                },
            },
        );
    } else {
        championshipForm.post(route('championships.store'), {
            onSuccess: () => {
                championshipForm.reset();
            },
        });
    }
};

const startChampionshipEdit = (ch: Championship) => {
    editChampionshipId.value = ch.id;
    championshipForm.name = ch.name;
    championshipForm.show_id = ch.show_id;
    championshipForm.type = ch.type;
    championshipForm.champion_id =
        ch.type === 'Singles'
            ? (ch.champion_superstar_id ?? 'VACANT')
            : (ch.champion_team_id ?? 'VACANT');
};

const cancelChampionshipEdit = () => {
    editChampionshipId.value = null;
    championshipForm.reset();
};

const deleteChampionship = (id: number) => {
    if (
        confirm(
            'Decommission this championship title belt tracking registration profile?',
        )
    ) {
        router.delete(route('championships.destroy', id));
    }
};
</script>

<template>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Championship Creation form -->
        <div
            v-if="!isReadOnly"
            class="h-fit rounded-2xl border border-slate-800 bg-slate-900/60 p-5"
        >
            <h3
                class="mb-4 flex items-center gap-2 text-sm font-bold text-white"
            >
                <Trophy class="text-yellow-450 h-4 w-4" />
                {{
                    editChampionshipId
                        ? 'Modify Championship Profile'
                        : 'Forge New Championship'
                }}
            </h3>
            <form
                @submit.prevent="handleCreateOrUpdateChampionship"
                class="space-y-4"
            >
                <div>
                    <label
                        class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >Championship Title Name</label
                    >
                    <input
                        type="text"
                        v-model="championshipForm.name"
                        required
                        class="w-full rounded-xl border border-slate-800 bg-slate-955 px-4 py-2.5 text-xs text-white focus:border-amber-400 focus:outline-none"
                        placeholder="e.g., WWE Universal Title"
                    />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label
                            class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >Division type</label
                        >
                        <select
                            v-model="championshipForm.type"
                            class="w-full rounded-xl border border-slate-800 bg-slate-955 px-4 py-2.5 text-xs text-white focus:border-amber-400 focus:outline-none"
                        >
                            <option value="Singles">Singles</option>
                            <option value="TagTeam">Tag Team</option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >Assigned Brand</label
                        >
                        <select
                            v-model="championshipForm.show_id"
                            class="w-full rounded-xl border border-slate-800 bg-slate-955 px-4 py-2.5 text-xs text-white focus:border-amber-400 focus:outline-none"
                        >
                            <option value="">Independent (Any Show/Superstar)</option>
                            <option
                                v-for="s in nonPleShows"
                                :key="s.id"
                                :value="s.id"
                            >
                                {{ s.name }}
                            </option>
                        </select>
                    </div>
                </div>
                <div>
                    <label
                        class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >Anoint Current Champion</label
                    >
                    <select
                        v-model="championshipForm.champion_id"
                        class="w-full rounded-xl border border-slate-800 bg-slate-955 px-4 py-2.5 text-xs text-white focus:border-amber-400 focus:outline-none"
                    >
                        <option value="VACANT">
                            -- VACANT (No Holder Designated) --
                        </option>
                        <template
                            v-if="championshipForm.type === 'Singles'"
                        >
                            <option
                                v-for="s in eligibleSuperstars"
                                :key="s.id"
                                :value="s.id"
                            >
                                [Individual] {{ s.name }}
                            </option>
                        </template>
                        <template v-else>
                            <option
                                v-for="t in eligibleTeams"
                                :key="t.id"
                                :value="t.id"
                            >
                                [Tag/Faction] {{ t.name }}
                            </option>
                        </template>
                    </select>
                </div>
                <div class="flex gap-2 pt-2">
                    <button
                        v-if="editChampionshipId"
                        type="button"
                        @click="cancelChampionshipEdit"
                        class="w-1/3 rounded-xl bg-slate-800 px-4 py-2.5 text-xs font-bold text-white transition-all hover:bg-slate-700"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-amber-400 px-4 py-2.5 text-xs font-bold text-slate-955 shadow transition-all hover:bg-amber-300"
                    >
                        <Trophy class="h-4 w-4" />
                        {{
                            editChampionshipId
                                ? 'Apply Updates'
                                : 'Mint Championship'
                        }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Active Championships status deck -->
        <div
            class="space-y-4"
            :class="isReadOnly ? 'lg:col-span-3' : 'lg:col-span-2'"
        >
            <h3
                class="flex items-center gap-2 text-sm font-bold text-white"
            >
                <Trophy class="text-slate-450 h-4 w-4" /> Active Championships Status
            </h3>
            <div
                v-if="championships.length === 0"
                class="py-6 text-center text-xs text-slate-500"
            >
                No active championship gold minted in database.
            </div>
            <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div
                    v-for="ch in championships"
                    :key="ch.id"
                    class="group relative flex flex-col justify-between rounded-2xl border border-slate-800 bg-slate-900/60 p-4 shadow-md"
                >
                    <div>
                        <span
                            class="border-amber-450/20 mb-1.5 inline-block rounded-md border bg-amber-400/10 px-2 py-0.5 text-[9px] font-bold text-amber-400"
                        >
                            {{ ch.type }} Division
                        </span>
                        <h4
                            class="text-sm leading-tight font-bold tracking-tight text-white"
                        >
                            {{ ch.name }}
                        </h4>
                        <p
                            class="mt-1 flex items-center gap-1 text-[10px] text-slate-400"
                        >
                            <Tv class="h-3 w-3" />
                            {{ ch.show ? ch.show.name : 'Independent' }}
                        </p>
                    </div>
                    <div
                        class="mt-4 flex items-center justify-between border-t border-slate-800/80 pt-3"
                    >
                        <div>
                            <p
                                class="text-[9px] font-bold tracking-wider text-slate-500 uppercase"
                            >
                                Current Title Holder
                            </p>
                            <p
                                class="mt-1 flex items-center gap-1 text-xs font-bold text-white"
                            >
                                <Sparkles
                                    class="h-3.5 w-3.5 text-yellow-400"
                                />
                                {{
                                    ch.type === 'Singles'
                                        ? ch.champion_superstar?.name ||
                                          'Vacant Title'
                                        : ch.champion_team?.name ||
                                          'Vacant Title'
                                }}
                            </p>
                        </div>
                        <div v-if="!isReadOnly" class="flex space-x-0.5">
                            <button
                                @click="startChampionshipEdit(ch)"
                                class="text-slate-505 p-1.5 transition hover:text-amber-400"
                            >
                                <Pencil class="h-3.5 w-3.5" />
                            </button>
                            <button
                                @click="deleteChampionship(ch.id)"
                                class="text-slate-650 p-1.5 transition hover:text-rose-400"
                            >
                                <Trash class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
