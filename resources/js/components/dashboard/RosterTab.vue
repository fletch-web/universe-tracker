<script setup lang="ts">
import { useForm, router, InfiniteScroll } from '@inertiajs/vue3';
import {
    Plus,
    Users,
    UsersRound,
    Pencil,
    UserX,
    FileSpreadsheet,
    Sparkles,
    Trash,
    Trophy,
} from '@lucide/vue';
import { ref, computed } from 'vue';
import { toast } from 'vue-sonner';
import type { Show, Superstar, Team, Championship, PaginatedData } from '@/types';
import DraftModal from './DraftModal.vue';

const props = defineProps<{
    shows: Show[];
    superstars: Superstar[];
    teams: Team[];
    paginatedSuperstars: PaginatedData<Superstar>;
    paginatedTeams: PaginatedData<Team>;
    isReadOnly: boolean;
    championships: Championship[];
}>();

const FALLBACK_USER_IMG =
    "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'><rect width='100%' height='100%' fill='%230f172a'/><circle cx='50' cy='40' r='18' fill='%23334155'/><path d='M20,80 C20,60 30,55 50,55 C70,55 80,60 80,80 Z' fill='%23334155'/></svg>";

function route(name: string, param?: string | number): string {
    switch (name) {
        case 'superstars.store':
            return '/superstars';
        case 'superstars.update':
            return `/superstars/${param}`;
        case 'superstars.destroy':
            return `/superstars/${param}`;
        case 'superstars.import':
            return '/superstars/import';
        case 'teams.store':
            return '/teams';
        case 'teams.destroy':
            return `/teams/${param}`;
        default:
            return '';
    }
}

// Roster Filter brand & Search
const filterRosterBrand = ref('ALL');
const searchQuery = ref('');

const filteredSuperstars = computed(() => {
    let result = props.superstars;

    if (filterRosterBrand.value !== 'ALL') {
        result = result.filter(
            (s) => s.show_id === Number(filterRosterBrand.value),
        );
    }

    if (searchQuery.value.trim() !== '') {
        const query = searchQuery.value.toLowerCase();
        result = result.filter((s) => s.name.toLowerCase().includes(query));
    }

    return result;
});

const filteredPaginatedSuperstars = computed(() => {
    let result = props.paginatedSuperstars.data;

    if (filterRosterBrand.value !== 'ALL') {
        result = result.filter(
            (s) => s.show_id === Number(filterRosterBrand.value),
        );
    }

    if (searchQuery.value.trim() !== '') {
        const query = searchQuery.value.toLowerCase();

        return props.superstars.filter(
            (s) => (filterRosterBrand.value === 'ALL' || s.show_id === Number(filterRosterBrand.value)) &&
                   s.name.toLowerCase().includes(query)
        );
    }

    return result;
});

const filteredTeams = computed(() => {
    let result = props.teams;

    if (searchQuery.value.trim() !== '') {
        const query = searchQuery.value.toLowerCase();
        result = result.filter((t) => t.name.toLowerCase().includes(query));
    }

    return result;
});

const filteredPaginatedTeams = computed(() => {
    const result = props.paginatedTeams.data;

    if (searchQuery.value.trim() !== '') {
        const query = searchQuery.value.toLowerCase();

        return props.teams.filter((t) => t.name.toLowerCase().includes(query));
    }

    return result;
});

// Draft Modal state
const isDraftModalOpen = ref(false);
const openDraftModal = () => {
    isDraftModalOpen.value = true;
};

// Superstar form state
const editSuperstarId = ref<number | null>(null);
const superstarForm = useForm({
    name: '',
    gender: 'Male' as 'Male' | 'Female',
    show_id: '' as string | number,
    wins: 0,
    losses: 0,
    draws: 0,
    image: null as string | null,
});

const selectSuperstarImageFile = (e: Event) => {
    const input = e.target as HTMLInputElement;

    if (input.files && input.files[0]) {
        compressAndConvertImage(input.files[0], (base64) => {
            superstarForm.image = base64;
        });
    }
};

const handleCreateOrUpdateSuperstar = () => {
    if (editSuperstarId.value) {
        superstarForm.put(route('superstars.update', editSuperstarId.value), {
            preserveScroll: true,
            onSuccess: () => {
                cancelSuperstarEdit();
            },
        });
    } else {
        superstarForm.post(route('superstars.store'), {
            preserveScroll: true,
            onSuccess: () => {
                superstarForm.reset();
            },
        });
    }
};

const startSuperstarEdit = (superstar: Superstar) => {
    editSuperstarId.value = superstar.id;
    superstarForm.name = superstar.name;
    superstarForm.gender = superstar.gender;
    superstarForm.show_id = superstar.show_id;
    superstarForm.wins = superstar.wins;
    superstarForm.losses = superstar.losses;
    superstarForm.draws = superstar.draws;
    superstarForm.image = superstar.image;
};

const cancelSuperstarEdit = () => {
    editSuperstarId.value = null;
    superstarForm.reset();
};

const deleteSuperstar = (id: number) => {
    if (
        confirm(
            'Expunge competitor entry files? Record values will disappear from rankings columns.',
        )
    ) {
        router.delete(route('superstars.destroy', id));
    }
};

// CSV Upload
const handleCsvUpload = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];

    if (!file) {
return;
}

    if (props.shows.length === 0) {
        alert('Configure at least one default Active Show track before running data migrations.');
        target.value = '';

        return;
    }

    const reader = new FileReader();
    reader.onload = (evt) => {
        const text = evt.target?.result as string;
        const lines = text.split(/\r?\n/);
        const superstarsList: Array<{ name: string; gender: string; brand: string }> = [];

        if (lines.length < 2) {
            alert('Empty file or missing headers. CSV must have Name, Gender, Brand headers.');
            target.value = '';

            return;
        }

        const headers = lines[0].split(',').map((h) => h.trim().toLowerCase());
        const nameIdx = headers.findIndex((h) => h.includes('name') || h.includes('superstar'));
        const genderIdx = headers.findIndex((h) => h.includes('gender'));
        const brandIdx = headers.findIndex((h) => h.includes('brand') || h.includes('show'));

        for (let i = 1; i < lines.length; i++) {
            const line = lines[i].trim();

            if (!line) {
continue;
}

            const cells = line.split(',').map((c) => c.trim());
            const name = nameIdx !== -1 ? cells[nameIdx] : cells[0];
            const gender = genderIdx !== -1 ? cells[genderIdx] : 'Male';
            const brand = brandIdx !== -1 ? cells[brandIdx] : '';

            if (name) {
                superstarsList.push({ name, gender, brand });
            }
        }

        router.post(
            route('superstars.import'),
            { superstars: superstarsList },
            {
                onSuccess: () => {
                    toast.success('Roster batch imported successfully!');
                    target.value = '';
                },
                onError: (errors) => {
                    toast.error('Failed to import roster: ' + Object.values(errors).join(', '));
                },
            },
        );
    };
    reader.readAsText(file);
};

// Team form state
const teamForm = useForm({
    name: '',
    members: [] as number[],
});

const handleCreateTeam = () => {
    if (teamForm.members.length < 2) {
        alert('A faction or tag team requires at least 2 rostered members selected.');

        return;
    }

    teamForm.post(route('teams.store'), {
        preserveScroll: true,
        onSuccess: () => {
            teamForm.reset();
        },
    });
};

const deleteTeam = (id: number) => {
    if (confirm('Disband tag team/faction alignment tracking frame?')) {
        router.delete(route('teams.destroy', id));
    }
};

// Championship status helper
const getSuperstarChampionships = (superstarId: number) => {
    const singles = props.championships.filter(
        (c) => c.champion_superstar_id === superstarId,
    );
    const tags = props.championships.filter((c) => {
        if (!c.champion_team_id) {
return false;
}

        const team = props.teams.find((t) => t.id === c.champion_team_id);

        if (!team || !team.superstars) {
return false;
}

        return team.superstars.some((member) => member.id === superstarId);
    });

    return [...singles, ...tags];
};

function compressAndConvertImage(
    file: File,
    callback: (base64: string | null) => void,
) {
    const reader = new FileReader();
    reader.onload = function (e) {
        const img = new Image();
        img.onload = function () {
            const canvas = document.createElement('canvas');
            const MAX_WIDTH = 160;
            const MAX_HEIGHT = 160;
            let width = img.width;
            let height = img.height;

            if (width > height) {
                if (width > MAX_WIDTH) {
                    height *= MAX_WIDTH / width;
                    width = MAX_WIDTH;
                }
            } else {
                if (height > MAX_HEIGHT) {
                    width *= MAX_HEIGHT / height;
                    height = MAX_HEIGHT;
                }
            }

            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx?.drawImage(img, 0, 0, width, height);
            callback(canvas.toDataURL('image/jpeg', 0.7));
        };
        img.src = e.target?.result as string;
    };
    reader.readAsDataURL(file);
}
</script>

<template>
    <div class="space-y-6">
        <!-- Roster Toolbar -->
        <div
            class="grid grid-cols-1 gap-6 rounded-2xl border border-slate-800 bg-slate-900/60 p-5"
            :class="isReadOnly ? 'md:grid-cols-1' : 'md:grid-cols-3'"
        >
            <!-- Excel Import -->
            <div
                v-if="!isReadOnly"
                class="border-b border-slate-800 pb-6 md:border-r md:border-b-0 md:pr-6 md:pb-0"
            >
                <h3
                    class="mb-2 flex items-center gap-1.5 text-xs font-bold text-white"
                >
                    <FileSpreadsheet class="h-4 w-4 text-emerald-400" />
                    Roster CSV Import Engine
                </h3>
                <p class="mb-4 text-[10px] text-slate-400">
                    Batch upload members directly. CSV header format required:
                    <code class="font-mono text-amber-400">Name, Gender, Brand</code>
                </p>
                <div class="flex items-center gap-3">
                    <input
                        type="file"
                        @change="handleCsvUpload"
                        accept=".csv"
                        class="cursor-pointer text-xs text-slate-400 file:mr-2 file:rounded-lg file:border-0 file:bg-slate-800 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-200 hover:file:bg-slate-700"
                    />
                </div>
            </div>

            <!-- Roster Filtering & Search -->
            <div
                class="flex flex-col justify-center space-y-4 md:space-y-0 md:flex-row md:items-center md:space-x-4"
                :class="{
                    'md:border-r md:border-slate-800 md:pr-6': !isReadOnly,
                }"
            >
                <div>
                    <h3 class="mb-2 text-xs font-bold text-white">
                        Roster Brand Filtering
                    </h3>
                    <select
                        v-model="filterRosterBrand"
                        class="w-full min-w-[150px] rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                    >
                        <option value="ALL">All Active Shows</option>
                        <option v-for="s in shows" :key="s.id" :value="s.id">
                            {{ s.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <h3 class="mb-2 text-xs font-bold text-white">
                        Search Roster
                    </h3>
                    <input
                        type="text"
                        v-model="searchQuery"
                        placeholder="Search superstars/factions..."
                        class="w-full min-w-[200px] rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white placeholder-slate-500 focus:border-amber-400 focus:outline-none"
                    />
                </div>
            </div>

            <!-- Perform Draft -->
            <div
                v-if="!isReadOnly"
                class="flex flex-col justify-center border-t border-slate-800 pt-6 md:border-t-0 md:pt-0 md:pl-2"
            >
                <h3
                    class="mb-2 flex items-center gap-1.5 text-xs font-bold text-white"
                >
                    <Sparkles class="h-4 w-4 text-amber-400" />
                    Superstar & Faction Draft
                </h3>
                <p class="mb-4 text-[10px] text-slate-400">
                    Initiate a draft cycle to shuffle superstars and factions between shows.
                </p>
                <div>
                    <button
                        @click="openDraftModal"
                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl bg-amber-400 px-4 py-2.5 text-xs font-bold text-slate-955 shadow transition-all hover:bg-amber-300"
                    >
                        <Sparkles class="h-4 w-4" />
                        Perform Draft
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Roster Area -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Registration Forms Deck -->
            <div v-if="!isReadOnly" class="h-fit space-y-6">
                <!-- Superstar Registration Form -->
                <div
                    class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5"
                >
                    <h3
                        class="mb-4 flex items-center gap-2 text-sm font-bold text-white"
                    >
                        <Plus class="text-amber-450 h-4 w-4" />
                        {{
                            editSuperstarId
                                ? 'Modify Competitor Credentials'
                                : 'Register Competitor'
                        }}
                    </h3>
                    <form
                        @submit.prevent="handleCreateOrUpdateSuperstar"
                        class="space-y-4"
                    >
                        <div>
                            <label
                                class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >Ring Name</label
                            >
                            <input
                                type="text"
                                v-model="superstarForm.name"
                                required
                                class="w-full rounded-xl border border-slate-800 bg-slate-955 px-4 py-2.5 text-xs text-white focus:border-amber-400 focus:outline-none"
                                placeholder="e.g., Cody Rhodes"
                            />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label
                                    class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                    >Gender Class</label
                                >
                                <select
                                    v-model="superstarForm.gender"
                                    class="w-full rounded-xl border border-slate-800 bg-slate-955 px-4 py-2.5 text-xs text-white focus:border-amber-400 focus:outline-none"
                                >
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                    >Assigned Show</label
                                >
                                <select
                                    v-model="superstarForm.show_id"
                                    required
                                    class="w-full rounded-xl border border-slate-800 bg-slate-955 px-4 py-2.5 text-xs text-white focus:border-amber-400 focus:outline-none"
                                >
                                    <option value="" disabled>
                                        Select Show
                                    </option>
                                    <option
                                        v-for="sh in shows.filter((s) => !s.is_ple)"
                                        :key="sh.id"
                                        :value="sh.id"
                                    >
                                        {{ sh.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Metrics editable only in Edit Mode -->
                        <div
                            v-if="editSuperstarId"
                            class="grid grid-cols-3 gap-2 rounded-xl border border-slate-800/80 bg-slate-955/60 p-3"
                        >
                            <div>
                                <label
                                    class="mb-1 block text-[9px] font-bold tracking-wider text-slate-400 uppercase"
                                    >Wins</label
                                >
                                <input
                                    type="number"
                                    v-model="superstarForm.wins"
                                    min="0"
                                    class="w-full rounded-lg border border-slate-800 bg-slate-955 px-2 py-1 text-xs text-white"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-[9px] font-bold tracking-wider text-slate-400 uppercase"
                                    >Losses</label
                                >
                                <input
                                    type="number"
                                    v-model="superstarForm.losses"
                                    min="0"
                                    class="w-full rounded-lg border border-slate-800 bg-slate-955 px-2 py-1 text-xs text-white"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-[9px] font-bold tracking-wider text-slate-400 uppercase"
                                    >Draws</label
                                >
                                <input
                                    type="number"
                                    v-model="superstarForm.draws"
                                    min="0"
                                    class="w-full rounded-lg border border-slate-800 bg-slate-955 px-2 py-1 text-xs text-white"
                                />
                            </div>
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >Profile Portrait</label
                            >
                            <input
                                type="file"
                                @change="selectSuperstarImageFile"
                                accept="image/*"
                                class="w-full cursor-pointer text-[10px] text-slate-400 file:mr-2 file:rounded-lg file:border-0 file:bg-slate-800 file:px-2.5 file:py-1.5 file:text-[10px] file:font-semibold file:text-slate-200 hover:file:bg-slate-700"
                            />
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button
                                v-if="editSuperstarId"
                                type="button"
                                @click="cancelSuperstarEdit"
                                class="w-1/3 rounded-xl bg-slate-800 px-4 py-2.5 text-xs font-bold text-white transition-all hover:bg-slate-700"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-slate-700 bg-slate-800 px-4 py-2.5 text-xs font-bold text-white transition-all hover:border-amber-400 hover:text-amber-400"
                            >
                                <Plus class="h-4 w-4" />
                                {{
                                    editSuperstarId
                                        ? 'Apply Edits'
                                        : 'Register Competitor'
                                }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Faction Assemble Form -->
                <div
                    class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5"
                >
                    <h3
                        class="mb-4 flex items-center gap-2 text-sm font-bold text-white"
                    >
                        <UsersRound class="text-amber-405 h-4 w-4" />
                        Assemble Tag Team / Faction
                    </h3>
                    <form
                        @submit.prevent="handleCreateTeam"
                        class="space-y-4"
                    >
                        <div>
                            <label
                                class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >Faction/Team Designation</label
                            >
                            <input
                                type="text"
                                v-model="teamForm.name"
                                required
                                class="w-full rounded-xl border border-slate-800 bg-slate-955 px-4 py-2.5 text-xs text-white focus:border-amber-400 focus:outline-none"
                                placeholder="e.g., The Bloodline"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >Draft Members (Hold Ctrl/Cmd)</label
                            >
                            <select
                                v-model="teamForm.members"
                                multiple
                                required
                                class="min-h-[130px] w-full rounded-xl border border-slate-800 bg-slate-955 px-4 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                            >
                                <option
                                    v-for="s in superstars"
                                    :key="s.id"
                                    :value="s.id"
                                >
                                    {{ s.name }}
                                </option>
                            </select>
                        </div>
                        <button
                            type="submit"
                            class="flex w-full items-center justify-center gap-1.5 rounded-xl border border-slate-700 bg-slate-800 px-4 py-2.5 text-xs font-bold text-white transition-all hover:border-amber-400 hover:text-amber-400"
                        >
                            <Plus class="h-4 w-4" /> Establish Faction
                        </button>
                    </form>
                </div>
            </div>

            <!-- Matrix displays -->
            <div
                class="space-y-6"
                :class="isReadOnly ? 'lg:col-span-3' : 'lg:col-span-2'"
            >
                <!-- Superstar Active Matrix with Coloured border portraits -->
                <div>
                    <h3
                        class="mb-3 flex items-center gap-2 text-sm font-bold text-white"
                    >
                        <Users class="text-slate-450 h-4 w-4" /> Active Roster & Metrics Matrix (W-D-L)
                    </h3>
                    <div
                        v-if="filteredSuperstars.length === 0"
                        class="py-6 text-center text-xs text-slate-500"
                    >
                        No active combatants registered under this brand.
                    </div>
                    <InfiniteScroll
                        v-else
                        data="paginatedSuperstars"
                        preserve-url
                        class="max-h-[300px] space-y-4 overflow-y-auto pr-1"
                    >
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div
                                v-for="s in filteredPaginatedSuperstars"
                                :key="s.id"
                                class="group relative flex items-center space-x-3.5 overflow-hidden rounded-xl border bg-slate-900/60 p-3 transition-all duration-200"
                                :class="[
                                    getSuperstarChampionships(s.id).length > 0
                                        ? 'border-amber-500/80 shadow-md shadow-amber-500/5'
                                        : 'border-slate-800',
                                ]"
                            >
                                <!-- Championship Tooltip on Hover -->
                                <div
                                    v-if="getSuperstarChampionships(s.id).length > 0"
                                    class="pointer-events-none absolute top-[-1px] left-[-1px] z-20 flex h-[calc(100%+2px)] w-[calc(100%+2px)] items-center justify-center bg-slate-950/90 opacity-0 backdrop-blur-sm transition-all duration-200 group-hover:opacity-100"
                                >
                                    <div class="px-3 text-center">
                                        <div
                                            class="mb-1.5 flex items-center justify-center gap-1 text-[10px] font-bold tracking-wider text-amber-400 uppercase"
                                        >
                                            <Trophy class="h-3 w-3" />
                                            Current Titles
                                        </div>
                                        <ul class="space-y-1">
                                            <li
                                                v-for="c in getSuperstarChampionships(s.id)"
                                                :key="c.id"
                                                class="truncate text-[10px] font-semibold text-slate-200"
                                            >
                                                {{ c.name }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <img
                                    :src="s.image || FALLBACK_USER_IMG"
                                    class="h-12 w-12 flex-shrink-0 rounded-lg border-2 bg-slate-950 object-cover shadow-sm"
                                    :style="{
                                        borderColor: s.show
                                            ? s.show.color
                                            : '#475569',
                                    }"
                                />
                                <div class="min-w-0 flex-1">
                                    <h4
                                        class="truncate text-xs font-black text-white"
                                    >
                                        {{ s.name }}
                                    </h4>
                                    <p
                                        class="mt-0.5 text-[10px] font-semibold"
                                        :style="{
                                            color: s.show
                                                ? s.show.color
                                                : '#64748b',
                                        }"
                                    >
                                        {{
                                            s.show
                                                ? s.show.name
                                                : 'Independent'
                                        }}
                                    </p>
                                    <div
                                        class="flex gap-2 pt-1 font-mono text-[9px] font-bold text-slate-400"
                                    >
                                        <span class="text-emerald-500"
                                            >W: {{ s.wins }}</span
                                        >
                                        <span class="text-rose-500"
                                            >L: {{ s.losses }}</span
                                        >
                                        <span>D: {{ s.draws }}</span>
                                    </div>
                                </div>
                                <div
                                    v-if="!isReadOnly"
                                    class="flex space-x-0.5"
                                >
                                    <button
                                        @click="startSuperstarEdit(s)"
                                        class="p-1.5 text-slate-500 transition hover:text-amber-400"
                                    >
                                        <Pencil class="h-3.5 w-3.5" />
                                    </button>
                                    <button
                                        @click="deleteSuperstar(s.id)"
                                        class="text-slate-650 p-1.5 transition hover:text-rose-400"
                                    >
                                        <UserX class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                        <template #loading>
                            <div
                                class="animate-pulse py-2 text-center text-[10px] font-bold text-slate-400"
                            >
                                Loading more combatants...
                            </div>
                        </template>
                    </InfiniteScroll>
                </div>

                <!-- Faction standings display -->
                <div class="border-slate-850 border-t pt-6">
                    <h3
                        class="mb-3 flex items-center gap-2 text-sm font-bold text-white"
                    >
                        <UsersRound class="text-slate-450 h-4 w-4" /> Tag Teams & Faction Standings
                    </h3>
                    <div
                        v-if="filteredTeams.length === 0"
                        class="py-6 text-center text-xs text-slate-500"
                    >
                        No tag team factions assembled yet.
                    </div>
                    <InfiniteScroll
                        v-else
                        data="paginatedTeams"
                        preserve-url
                        class="max-h-[300px] space-y-4 overflow-y-auto pr-1"
                    >
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div
                                v-for="t in filteredPaginatedTeams"
                                :key="t.id"
                                class="group relative space-y-1.5 rounded-xl border border-slate-800 bg-slate-900/60 p-3.5"
                            >
                                <div
                                    class="flex items-start justify-between"
                                >
                                    <h4
                                        class="truncate text-xs font-black text-amber-400"
                                    >
                                        {{ t.name }}
                                    </h4>
                                    <button
                                        v-if="!isReadOnly"
                                        @click="deleteTeam(t.id)"
                                        class="text-slate-600 transition hover:text-rose-400"
                                    >
                                        <Trash class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                                <p class="text-[10px] text-slate-300">
                                    Members:
                                    <span
                                        class="font-normal text-slate-400"
                                    >
                                        {{
                                            t.superstars
                                                ? t.superstars
                                                      .map((s) => s.name)
                                                      .join(', ')
                                                : 'No members'
                                        }}
                                    </span>
                                </p>
                                <div
                                    class="mt-1 flex gap-3 border-t border-slate-800/40 pt-1.5 font-mono text-[9px] font-bold"
                                >
                                    <span class="text-emerald-400"
                                        >W: {{ t.wins }}</span
                                    >
                                    <span class="text-rose-400"
                                        >L: {{ t.losses }}</span
                                    >
                                    <span class="text-slate-400"
                                        >D: {{ t.draws }}</span
                                    >
                                </div>
                            </div>
                        </div>
                        <template #loading>
                            <div
                                class="animate-pulse py-2 text-center text-[10px] font-bold text-slate-400"
                            >
                                Loading more alignments...
                            </div>
                        </template>
                    </InfiniteScroll>
                </div>
            </div>
        </div>

        <!-- DRAFT MODAL OVERLAY -->
        <DraftModal
            :shows="shows"
            :superstars="superstars"
            :teams="teams"
            :isOpen="isDraftModalOpen"
            @close="isDraftModalOpen = false"
        />
    </div>
</template>
