<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Sparkles, Tv, Users, CheckCircle2 } from '@lucide/vue';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import type { Show, Superstar, Team } from '@/types';

const props = defineProps<{
    shows: Show[];
    superstars: Superstar[];
    teams: Team[];
    isOpen: boolean;
}>();

const emit = defineEmits(['close']);

interface DraftItem {
    id: string;
    type: 'superstar' | 'faction';
    name: string;
    superstars: Superstar[];
}

const FALLBACK_USER_IMG =
    "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'><rect width='100%' height='100%' fill='%230f172a'/><circle cx='50' cy='40' r='18' fill='%23334155'/><path d='M20,80 C20,60 30,55 50,55 C70,55 80,60 80,80 Z' fill='%23334155'/></svg>";

const draftStage = ref(1); // 1: Setup, 2: Mode, 3: Execution, 4: Summary
const draftSelectedShows = ref<number[]>([]);
const draftEligibleSuperstars = ref<number[]>([]);
const draftMode = ref<'manual' | 'random'>('manual');
const draftPool = ref<DraftItem[]>([]);
const draftResults = ref<Record<number, number>>({});
const draftCurrentShowIndex = ref(0);
const draftLogs = ref<string[]>([]);

const resetDraftState = () => {
    draftStage.value = 1;
    draftSelectedShows.value = props.shows
        .filter((s) => !s.is_ple)
        .map((s) => s.id);
    draftEligibleSuperstars.value = props.superstars.map((s) => s.id);
    draftMode.value = 'manual';
    draftPool.value = [];
    draftResults.value = {};
    draftCurrentShowIndex.value = 0;
    draftLogs.value = [];
};

watch(
    () => props.isOpen,
    (newVal) => {
        if (newVal) {
            resetDraftState();
        }
    },
    { immediate: true },
);

function route(name: string): string {
    if (name === 'draft.commit') {
        return '/draft/commit';
    }

    return '';
}

const handleStartDraft = () => {
    if (draftSelectedShows.value.length < 2) {
        alert('Please select at least 2 shows to participate in the draft.');

        return;
    }

    if (draftEligibleSuperstars.value.length === 0) {
        alert('Please select at least one eligible superstar.');

        return;
    }

    // Build Draft Pool
    const eligibleSuperstars = props.superstars.filter((s) =>
        draftEligibleSuperstars.value.includes(s.id),
    );
    const groupedSuperstarIds = new Set<number>();
    const poolItems: DraftItem[] = [];

    // Factions (Teams) containing at least one eligible superstar
    props.teams.forEach((team) => {
        const members = (team.superstars || []).filter((s) =>
            draftEligibleSuperstars.value.includes(s.id),
        );

        if (members.length > 0) {
            poolItems.push({
                id: `faction-${team.id}`,
                type: 'faction',
                name: team.name,
                superstars: members,
            });
            members.forEach((s) => groupedSuperstarIds.add(s.id));
        }
    });

    // Individual Superstars not in any eligible faction
    eligibleSuperstars.forEach((s) => {
        if (!groupedSuperstarIds.has(s.id)) {
            poolItems.push({
                id: `superstar-${s.id}`,
                type: 'superstar',
                name: s.name,
                superstars: [s],
            });
        }
    });

    draftPool.value = poolItems;
    draftStage.value = 2;
};

const runDraftExecution = () => {
    draftResults.value = {};
    draftLogs.value = [];
    draftCurrentShowIndex.value = 0;

    if (draftMode.value === 'random') {
        draftStage.value = 3;
        const shuffled = [...draftPool.value].sort(() => Math.random() - 0.5);

        const showAssignmentsCount: Record<number, number> = {};
        draftSelectedShows.value.forEach((id) => {
            showAssignmentsCount[id] = 0;
        });

        shuffled.forEach((item) => {
            let minShowId = draftSelectedShows.value[0];
            let minCount = showAssignmentsCount[minShowId];

            draftSelectedShows.value.forEach((id) => {
                if (showAssignmentsCount[id] < minCount) {
                    minShowId = id;
                    minCount = showAssignmentsCount[id];
                }
            });

            const undrafted = item.superstars.filter(
                (s) => draftResults.value[s.id] === undefined,
            );

            if (undrafted.length > 0) {
                undrafted.forEach((s) => {
                    draftResults.value[s.id] = minShowId;
                });
                showAssignmentsCount[minShowId] += undrafted.length;

                const showName =
                    props.shows.find((s) => s.id === minShowId)?.name ||
                    'Unknown';

                if (item.type === 'faction') {
                    draftLogs.value.push(
                        `Drafted faction ${item.name} (${undrafted.map((s) => s.name).join(', ')}) to ${showName}`,
                    );
                } else {
                    draftLogs.value.push(`Drafted ${item.name} to ${showName}`);
                }
            }
        });

        draftStage.value = 4;
    } else {
        draftStage.value = 3;
    }
};

const draftItemManual = (item: DraftItem) => {
    const activeShowId = draftSelectedShows.value[draftCurrentShowIndex.value];
    const showName =
        props.shows.find((s) => s.id === activeShowId)?.name || 'Unknown';

    const undrafted = item.superstars.filter(
        (s) => draftResults.value[s.id] === undefined,
    );

    if (undrafted.length > 0) {
        undrafted.forEach((s) => {
            draftResults.value[s.id] = activeShowId;
        });

        if (item.type === 'faction') {
            draftLogs.value.push(
                `${showName} drafted faction ${item.name} (${undrafted.map((s) => s.name).join(', ')})`,
            );
        } else {
            draftLogs.value.push(`${showName} drafted ${item.name}`);
        }
    }

    draftPool.value = draftPool.value.filter((poolItem) => {
        if (poolItem.id === item.id) {
            return false;
        }

        if (poolItem.type === 'faction') {
            const remainingUndrafted = poolItem.superstars.filter(
                (s) => draftResults.value[s.id] === undefined,
            );

            return remainingUndrafted.length > 0;
        }

        return true;
    });

    if (draftPool.value.length === 0) {
        draftStage.value = 4;
    } else {
        draftCurrentShowIndex.value =
            (draftCurrentShowIndex.value + 1) % draftSelectedShows.value.length;
    }
};

const handleSaveDraft = () => {
    router.post(
        route('draft.commit'),
        {
            draft: draftResults.value,
        },
        {
            onSuccess: () => {
                emit('close');
                toast.success('Draft roster updates committed successfully!');
            },
            onError: (err) => {
                alert(
                    'Failed to save draft results: ' +
                        Object.values(err).join(', '),
                );
            },
        },
    );
};
</script>

<template>
    <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4 backdrop-blur-sm"
    >
        <div
            class="flex h-full max-h-[85vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/95 text-slate-100 shadow-2xl"
        >
            <!-- Modal Header -->
            <div
                class="bg-slate-950/50 flex items-center justify-between border-b border-slate-800 p-5"
            >
                <div class="flex items-center gap-2">
                    <div
                        class="flex items-center justify-center rounded-lg bg-amber-400 p-1.5 text-slate-955"
                    >
                        <Sparkles class="h-5 w-5" />
                    </div>
                    <div>
                        <h2
                            class="text-sm font-black tracking-wider text-white uppercase"
                        >
                            Superstar Draft Room
                        </h2>
                        <p class="text-[10px] text-slate-400">
                            Rearrange rosters between shows dynamically
                        </p>
                    </div>
                </div>
                <button
                    @click="emit('close')"
                    class="text-slate-450 rounded-lg p-1.5 transition hover:bg-slate-800 hover:text-white"
                >
                    Close
                </button>
            </div>

            <!-- Modal Content -->
            <div class="flex-1 space-y-6 overflow-y-auto p-6">
                <!-- STAGE 1: SETUP -->
                <div v-if="draftStage === 1" class="space-y-6">
                    <!-- Select Shows -->
                    <div class="space-y-3">
                        <h3
                            class="flex items-center gap-1.5 text-xs font-black tracking-wider text-white uppercase"
                        >
                            <Tv class="h-4 w-4 text-amber-400" />
                            1. Select Participating Brands / Shows
                        </h3>
                        <p class="text-[10px] text-slate-400">
                            At least 2 shows must be selected to swap rosters.
                        </p>
                        <div
                            class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3"
                        >
                            <div
                                v-for="show in shows.filter((s) => !s.is_ple)"
                                :key="show.id"
                                class="flex cursor-pointer items-center gap-3 rounded-xl border p-3 transition-all"
                                :class="[
                                    draftSelectedShows.includes(show.id)
                                        ? 'border-amber-400 bg-amber-400/5'
                                        : 'border-slate-800 bg-slate-950/40 hover:border-slate-700',
                                ]"
                                @click="
                                    draftSelectedShows.includes(show.id)
                                        ? (draftSelectedShows = draftSelectedShows.filter(
                                              (id) => id !== show.id,
                                          ))
                                        : draftSelectedShows.push(show.id)
                                "
                            >
                                <span
                                    class="h-3 w-3 rounded-full"
                                    :style="{ backgroundColor: show.color }"
                                ></span>
                                <span
                                    class="text-xs font-bold text-slate-200"
                                    >{{ show.name }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Select Superstars -->
                    <div class="space-y-3 border-t border-slate-800 pt-6">
                        <div class="flex items-center justify-between">
                            <h3
                                class="flex items-center gap-1.5 text-xs font-black tracking-wider text-white uppercase"
                            >
                                <Users class="h-4 w-4 text-amber-400" />
                                2. Choose Eligible Draft Candidates
                            </h3>
                            <div class="flex gap-2">
                                <button
                                    @click="
                                        draftEligibleSuperstars = superstars.map(
                                            (s) => s.id,
                                        )
                                    "
                                    class="text-[9px] font-bold text-amber-400 uppercase hover:underline"
                                >
                                    Select All
                                </button>
                                <span class="text-slate-700">|</span>
                                <button
                                    @click="draftEligibleSuperstars = []"
                                    class="text-rose-455 text-[9px] font-bold uppercase hover:underline"
                                >
                                    Deselect All
                                </button>
                            </div>
                        </div>
                        <p class="text-[10px] text-slate-400">
                            Only selected superstars (and their factions) will enter the draft pool.
                        </p>

                        <div
                            class="grid max-h-[250px] grid-cols-2 gap-2 overflow-y-auto pr-1 sm:grid-cols-3 md:grid-cols-4"
                        >
                            <div
                                v-for="s in superstars"
                                :key="s.id"
                                class="flex cursor-pointer items-center gap-2 rounded-lg border p-2 transition-all"
                                :class="[
                                    draftEligibleSuperstars.includes(s.id)
                                        ? 'border-amber-400/60 bg-amber-400/5'
                                        : 'border-slate-800 bg-slate-955/20 hover:border-slate-800',
                                ]"
                                @click="
                                    draftEligibleSuperstars.includes(s.id)
                                        ? (draftEligibleSuperstars = draftEligibleSuperstars.filter(
                                              (id) => id !== s.id,
                                          ))
                                        : draftEligibleSuperstars.push(s.id)
                                "
                            >
                                <img
                                    :src="s.image || FALLBACK_USER_IMG"
                                    class="h-6 w-6 rounded-full border object-cover"
                                    :style="{
                                        borderColor: s.show
                                            ? s.show.color
                                            : '#334155',
                                    }"
                                />
                                <span
                                    class="text-slate-350 truncate text-[10px] font-bold"
                                    >{{ s.name }}</span
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STAGE 2: MODE SELECTION -->
                <div v-if="draftStage === 2" class="space-y-6">
                    <h3
                        class="flex items-center gap-1.5 text-xs font-black tracking-wider text-white uppercase"
                    >
                        <Sparkles class="h-4 w-4 text-amber-400" />
                        3. Select Draft Engine mode
                    </h3>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Manual Mode -->
                        <div
                            class="cursor-pointer space-y-3 rounded-2xl border p-5 transition-all"
                            :class="[
                                draftMode === 'manual'
                                    ? 'shadow-amber-450/5 border-amber-400 bg-amber-400/5 shadow-lg'
                                    : 'border-slate-800 bg-slate-950/40 hover:border-slate-700',
                            ]"
                            @click="draftMode = 'manual'"
                        >
                            <div class="flex items-center justify-between">
                                <h4
                                    class="text-xs font-bold tracking-wider text-white uppercase"
                                >
                                    Manual Round-Robin Draft
                                </h4>
                                <span
                                    v-if="draftMode === 'manual'"
                                    class="h-2 w-2 rounded-full bg-amber-400"
                                ></span>
                            </div>
                            <p
                                class="text-[10px] leading-relaxed text-slate-400"
                            >
                                Shows take turns in a round-robin rotation. You manually choose which superstar or faction gets drafted to the active show. Highly strategic.
                            </p>
                        </div>

                        <!-- Random Mode -->
                        <div
                            class="cursor-pointer space-y-3 rounded-2xl border p-5 transition-all"
                            :class="[
                                draftMode === 'random'
                                    ? 'shadow-amber-455/5 border-amber-400 bg-amber-400/5 shadow-lg'
                                    : 'border-slate-800 bg-slate-950/40 hover:border-slate-700',
                            ]"
                            @click="draftMode = 'random'"
                        >
                            <div class="flex items-center justify-between">
                                <h4
                                    class="text-xs font-bold tracking-wider text-white uppercase"
                                >
                                    Algorithmic Random Draft
                                </h4>
                                <span
                                    v-if="draftMode === 'random'"
                                    class="h-2 w-2 rounded-full bg-amber-400"
                                ></span>
                            </div>
                            <p
                                class="text-[10px] leading-relaxed text-slate-400"
                            >
                                Draft engine distributes all eligible candidates and factions randomly and evenly across all selected shows in one click. Instant results.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- STAGE 3: MANUAL EXECUTION -->
                <div
                    v-if="draftStage === 3 && draftMode === 'manual'"
                    class="space-y-6"
                >
                    <!-- Turn banner -->
                    <div
                        class="flex items-center justify-between rounded-xl border px-5 py-4 shadow"
                        :style="{
                            borderColor:
                                shows.find(
                                    (s) =>
                                        s.id ===
                                        draftSelectedShows[
                                            draftCurrentShowIndex
                                        ],
                                )?.color || '#334155',
                            backgroundColor:
                                (shows.find(
                                    (s) =>
                                        s.id ===
                                        draftSelectedShows[
                                            draftCurrentShowIndex
                                        ],
                                )?.color || '#334155') + '15',
                        }"
                    >
                        <div>
                            <p
                                class="text-[9px] font-black tracking-widest uppercase"
                                :style="{
                                    color: shows.find(
                                        (s) =>
                                            s.id ===
                                            draftSelectedShows[
                                                draftCurrentShowIndex
                                            ],
                                    )?.color,
                                }"
                            >
                                Active Turn Pick
                            </p>
                            <h3 class="text-base font-black text-white">
                                {{
                                    shows.find(
                                        (s) =>
                                            s.id ===
                                            draftSelectedShows[
                                                draftCurrentShowIndex
                                            ],
                                    )?.name
                                }}'s Turn
                            </h3>
                        </div>
                        <span
                            class="font-mono text-xs font-bold text-slate-400"
                        >
                            {{ draftPool.length }} pool items left
                        </span>
                    </div>

                    <!-- Pool Choice Grid -->
                    <div class="space-y-4">
                        <h4
                            class="text-xs font-bold tracking-wider text-white uppercase"
                        >
                            Available Draft Pool
                        </h4>
                        <div
                            class="grid max-h-[350px] grid-cols-1 gap-3 overflow-y-auto pr-1 sm:grid-cols-2 md:grid-cols-3"
                        >
                            <div
                                v-for="item in draftPool"
                                :key="item.id"
                                class="group flex flex-col justify-between rounded-xl border border-slate-800 bg-slate-955 p-4 transition-all duration-200 hover:border-amber-400/50"
                            >
                                <div>
                                    <span
                                        class="mb-2 inline-block rounded px-1.5 py-0.5 text-[8px] font-black tracking-wider uppercase"
                                        :class="[
                                            item.type === 'faction'
                                                ? 'border border-amber-400/20 bg-amber-400/10 text-amber-400'
                                                : 'text-slate-350 border border-slate-700 bg-slate-850',
                                        ]"
                                    >
                                        {{ item.type }}
                                    </span>
                                    <h5
                                        class="truncate text-xs font-black text-white transition group-hover:text-amber-400"
                                    >
                                        {{ item.name }}
                                    </h5>
                                    <p
                                        class="mt-1 text-[9px] text-slate-550"
                                    >
                                        {{
                                            item.type === 'faction'
                                                ? 'Members:'
                                                : 'Individual'
                                        }}
                                        <span
                                            class="ml-0.5 font-semibold text-slate-400"
                                        >
                                            {{
                                                item.superstars
                                                    .map((s) => s.name)
                                                    .join(', ')
                                            }}
                                        </span>
                                    </p>
                                </div>
                                <button
                                    @click="draftItemManual(item)"
                                    class="mt-4 w-full cursor-pointer rounded-lg border border-slate-800 bg-slate-900 px-3 py-1.5 text-[10px] font-bold text-slate-300 transition-all hover:border-amber-400 hover:bg-amber-400 hover:text-slate-950"
                                >
                                    Draft to
                                    {{
                                        shows.find(
                                            (s) =>
                                                s.id ===
                                                draftSelectedShows[
                                                    draftCurrentShowIndex
                                                ],
                                        )?.name
                                    }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STAGE 4: SUMMARY & RESULTS -->
                <div v-if="draftStage === 4" class="space-y-6">
                    <div
                        class="flex items-center gap-3 rounded-xl border border-emerald-500/20 bg-emerald-500/5 p-4"
                    >
                        <CheckCircle2 class="h-6 w-6 text-emerald-400" />
                        <div>
                            <h4
                                class="text-xs font-black tracking-wider text-white uppercase"
                            >
                                Draft Completed successfully!
                            </h4>
                            <p class="text-[10px] text-slate-400">
                                Review results and confirm to update roster metrics.
                            </p>
                        </div>
                    </div>

                    <!-- Groups display -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div
                            v-for="showId in draftSelectedShows"
                            :key="showId"
                            class="space-y-3 rounded-xl border border-slate-800 bg-slate-955 p-4"
                        >
                            <div
                                class="flex items-center gap-2 border-b border-slate-800/80 pb-2"
                            >
                                <span
                                    class="h-2.5 w-2.5 rounded-full"
                                    :style="{
                                        backgroundColor: shows.find(
                                            (s) => s.id === showId,
                                        )?.color,
                                    }"
                                ></span>
                                <h4
                                    class="text-xs font-bold tracking-wider text-white uppercase"
                                >
                                    {{
                                        shows.find((s) => s.id === showId)
                                            ?.name
                                    }}
                                </h4>
                            </div>
                            <div
                                class="max-h-[180px] space-y-1.5 overflow-y-auto pr-1"
                            >
                                <div
                                    v-for="s in superstars.filter(
                                        (s) =>
                                            draftResults[s.id] === showId,
                                    )"
                                    :key="s.id"
                                    class="border-slate-850 flex items-center gap-2 rounded-lg border bg-slate-900/60 px-2 py-1.5"
                                >
                                    <img
                                        :src="s.image || FALLBACK_USER_IMG"
                                        class="h-5 w-5 rounded-full object-cover"
                                    />
                                    <span
                                        class="text-slate-350 text-[10px] font-bold"
                                        >{{ s.name }}</span
                                    >
                                </div>
                                <div
                                    v-if="
                                        superstars.filter(
                                            (s) =>
                                                draftResults[s.id] ===
                                                showId,
                                        ).length === 0
                                    "
                                    class="py-4 text-center text-[10px] text-slate-600"
                                >
                                    No candidates drafted.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Logs list -->
                    <div class="space-y-2 border-t border-slate-800 pt-5">
                        <h4
                            class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Draft Log Feed
                        </h4>
                        <div
                            class="border-slate-850 bg-slate-950 max-h-[130px] space-y-1 overflow-y-auto rounded-xl border p-3.5 font-mono text-[9px]"
                        >
                            <p
                                v-for="(log, idx) in draftLogs"
                                :key="idx"
                                class="text-slate-400"
                            >
                                <span class="font-bold text-amber-500"
                                    >[{{ idx + 1 }}]</span
                                >
                                {{ log }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div
                class="bg-slate-950/50 flex justify-between gap-3 border-t border-slate-800 p-5"
            >
                <button
                    v-if="draftStage > 1 && draftStage < 4"
                    @click="draftStage--"
                    class="bg-slate-850 cursor-pointer rounded-xl px-4 py-2.5 text-xs font-bold text-white transition-all hover:bg-slate-800"
                >
                    Back
                </button>
                <div v-else></div>

                <div class="flex gap-3">
                    <button
                        @click="emit('close')"
                        class="cursor-pointer rounded-xl border border-slate-800 bg-slate-900 px-4 py-2.5 text-xs font-bold text-slate-300 transition hover:bg-slate-800"
                    >
                        Cancel
                    </button>

                    <!-- Stage 1 Button -->
                    <button
                        v-if="draftStage === 1"
                        @click="handleStartDraft"
                        class="cursor-pointer rounded-xl bg-amber-400 px-5 py-2.5 text-xs font-bold text-slate-955 shadow transition hover:bg-amber-300"
                    >
                        Next: Mode Selection
                    </button>

                    <!-- Stage 2 Button -->
                    <button
                        v-if="draftStage === 2"
                        @click="runDraftExecution"
                        class="cursor-pointer rounded-xl bg-amber-400 px-5 py-2.5 text-xs font-bold text-slate-955 shadow transition hover:bg-amber-300"
                    >
                        Begin Draft
                    </button>

                    <!-- Stage 4 Button -->
                    <button
                        v-if="draftStage === 4"
                        @click="handleSaveDraft"
                        class="bg-emerald-500 cursor-pointer rounded-xl border border-emerald-500/20 px-5 py-2.5 text-xs font-bold text-white transition hover:bg-emerald-400"
                    >
                        Confirm & Save Draft
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
