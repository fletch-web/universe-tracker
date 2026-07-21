<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    PlusCircle,
    Plus,
    Trash,
    Sparkles,
} from '@lucide/vue';
import { ref, computed, watch } from 'vue';
import { toast } from 'vue-sonner';
import type { Show, Superstar, Team, Championship, Storyline } from '@/types';

const props = defineProps<{
    shows: Show[];
    superstars: Superstar[];
    teams: Team[];
    championships: Championship[];
    storylines: Storyline[];
}>();

const emit = defineEmits(['switch-tab']);

interface StagedMatch {
    id: string;
    division:
        | 'Singles'
        | 'TagTeam'
        | 'TripleThreat'
        | 'Fatal4Way'
        | 'TripleThreatTag'
        | 'Fatal4WayTag'
        | 'ThreeOnThreeTag'
        | 'FourOnFourTag'
        | 'Segment';
    c1Id: number;
    c2Id: number;
    c3Id?: number;
    c4Id?: number;
    team1_superstar_ids?: number[];
    team2_superstar_ids?: number[];
    comp1Name: string;
    comp2Name: string;
    comp3Name?: string;
    comp4Name?: string;
    comp1Img: string | null;
    comp2Img: string | null;
    comp3Img?: string | null;
    comp4Img?: string | null;
    outcome: 'Decisive' | 'Draw' | 'Segment';
    winnerSlot: '1' | '2' | '3' | '4' | null;
    winningId: number | 'DRAW';
    winnerName: string;
    storylineId: string | number;
    championshipId?: string | number;
    championshipName?: string | null;
    notes: string;
    stipulation?: string;
}

const FALLBACK_USER_IMG =
    "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'><rect width='100%' height='100%' fill='%230f172a'/><circle cx='50' cy='40' r='18' fill='%23334155'/><path d='M20,80 C20,60 30,55 50,55 C70,55 80,60 80,80 Z' fill='%23334155'/></svg>";

const bookingShowSelect = ref<string | number>('');
const bookingDate = ref(new Date().toISOString().split('T')[0]);
const bookingLocation = ref<string>('');
const bookingMatchType = ref<
    | 'Singles'
    | 'TagTeam'
    | 'TripleThreat'
    | 'Fatal4Way'
    | 'TripleThreatTag'
    | 'Fatal4WayTag'
    | 'ThreeOnThreeTag'
    | 'FourOnFourTag'
    | 'Segment'
>('Singles');
const bookingTagTeamType = ref<'Faction' | 'AdHoc'>('Faction');
const bookingTeam1Superstars = ref<(number | string)[]>([]);
const bookingTeam2Superstars = ref<(number | string)[]>([]);
const bookingComp1 = ref<string | number>('');
const bookingComp2 = ref<string | number>('');
const bookingComp3 = ref<string | number>('');
const bookingComp4 = ref<string | number>('');
const bookingIsTitleMatch = ref(false);
const bookingChampionshipId = ref<string | number>('NONE');
const bookingOutcome = ref<'Decisive' | 'Draw'>('Decisive');
const bookingWinner = ref<'1' | '2' | '3' | '4'>('1');
const bookingStoryline = ref<string | number>('NONE');
const bookingNotes = ref('');
const bookingStipulation = ref<string>('');

watch([bookingMatchType, bookingTagTeamType], () => {
    bookingIsTitleMatch.value = false;
    bookingChampionshipId.value = 'NONE';
});

watch(bookingIsTitleMatch, (newVal) => {
    if (!newVal) {
        bookingChampionshipId.value = 'NONE';
    }
});

const stagedMatches = ref<StagedMatch[]>([]);
const activeMatchPreview = ref<StagedMatch | null>(null);

function route(name: string): string {
    if (name === 'booking.commit') {
        return '/booking/commit';
    }

    return '';
}

// Competitors list based on selected Booking Show & Match type
const selectedBookingShow = computed(() => {
    return (
        props.shows.find((s) => s.id === Number(bookingShowSelect.value)) ||
        null
    );
});

const bookingCompetitors1 = computed(() => {
    if (!bookingShowSelect.value) {
        return [];
    }

    const isTeamMatch = [
        'TagTeam',
        'TripleThreatTag',
        'Fatal4WayTag',
        'ThreeOnThreeTag',
        'FourOnFourTag',
    ].includes(bookingMatchType.value);
    const isFactionBased =
        isTeamMatch &&
        !(
            ['TagTeam', 'ThreeOnThreeTag', 'FourOnFourTag'].includes(
                bookingMatchType.value,
            ) && bookingTagTeamType.value === 'AdHoc'
        );

    if (!isFactionBased) {
        if (selectedBookingShow.value?.is_ple) {
            return props.superstars;
        }

        return props.superstars.filter(
            (s) => s.show_id === Number(bookingShowSelect.value),
        );
    } else {
        return props.teams;
    }
});

const bookingCompetitors2 = computed(() => {
    return bookingCompetitors1.value;
});

const bookingChampionships = computed(() => {
    if (!bookingShowSelect.value) {
        return [];
    }

    const isTeamMatch = [
        'TagTeam',
        'TripleThreatTag',
        'Fatal4WayTag',
        'ThreeOnThreeTag',
        'FourOnFourTag',
    ].includes(bookingMatchType.value);

    const isPLE = !!selectedBookingShow.value?.is_ple;

    return props.championships.filter((c) => {
        const matchesShow =
            isPLE || !c.show_id || c.show_id === Number(bookingShowSelect.value);
        const matchesType = isTeamMatch
            ? c.type === 'TagTeam'
            : c.type === 'Singles';

        return matchesShow && matchesType;
    });
});

const isTeamMatch = computed(() => {
    return [
        'TagTeam',
        'TripleThreatTag',
        'Fatal4WayTag',
        'ThreeOnThreeTag',
        'FourOnFourTag',
    ].includes(bookingMatchType.value);
});

const isFactionBased = computed(() => {
    return (
        isTeamMatch.value &&
        !(
            ['TagTeam', 'ThreeOnThreeTag', 'FourOnFourTag'].includes(
                bookingMatchType.value,
            ) && bookingTagTeamType.value === 'AdHoc'
        )
    );
});

const getCompetitorNameBySlot = (slot: '1' | '2' | '3' | '4') => {
    const isTeamMatchVal = isTeamMatch.value;
    const isFactionBasedVal = isFactionBased.value;

    if (isTeamMatchVal && !isFactionBasedVal) {
        const list = slot === '1' ? bookingTeam1Superstars.value : bookingTeam2Superstars.value;

        if (!list || list.length === 0) {
return `Slot #${slot}`;
}

        return list
            .map(id => props.superstars.find(s => s.id === Number(id))?.name || '')
            .filter(Boolean)
            .join(' & ') || `Slot #${slot}`;
    } else {
        let idVal = '';

        if (slot === '1') {
idVal = bookingComp1.value;
} else if (slot === '2') {
idVal = bookingComp2.value;
} else if (slot === '3') {
idVal = bookingComp3.value;
} else if (slot === '4') {
idVal = bookingComp4.value;
}

        if (!idVal) {
return `Slot #${slot}`;
}

        if (!isTeamMatchVal) {
            const s = props.superstars.find(superstar => superstar.id === Number(idVal));

            return s ? s.name : `Slot #${slot}`;
        } else {
            const t = props.teams.find(team => team.id === Number(idVal));

            return t ? t.name : `Slot #${slot}`;
        }
    }
};

const getCompetitorImage = (match: StagedMatch | null, slot: 1 | 2 | 3 | 4) => {
    if (!match) {
return null;
}

    const isTeamMatchVal = [
        'TagTeam',
        'TripleThreatTag',
        'Fatal4WayTag',
        'ThreeOnThreeTag',
        'FourOnFourTag',
    ].includes(match.division);

    const isFactionBasedVal = isTeamMatchVal && !(
        ['TagTeam', 'ThreeOnThreeTag', 'FourOnFourTag'].includes(match.division) &&
        match.team1_superstar_ids
    );

    if (isTeamMatchVal && !isFactionBasedVal) {
        const superstarIds = slot === 1 ? match.team1_superstar_ids : slot === 2 ? match.team2_superstar_ids : undefined;

        if (superstarIds && superstarIds.length > 0) {
            const firstSuperstar = props.superstars.find(s => s.id === superstarIds[0]);

            return firstSuperstar ? firstSuperstar.image : null;
        }

        return null;
    } else {
        let idVal = 0;

        if (slot === 1) {
idVal = match.c1Id;
} else if (slot === 2) {
idVal = match.c2Id;
} else if (slot === 3) {
idVal = match.c3Id;
} else if (slot === 4) {
idVal = match.c4Id;
}

        if (!idVal) {
return null;
}

        if (!isTeamMatchVal) {
            const s = props.superstars.find(superstar => superstar.id === idVal);

            return s ? s.image : null;
        } else {
            const t = props.teams.find(team => team.id === idVal);

            if (t && t.superstars && t.superstars.length > 0) {
                const firstS = props.superstars.find(superstar => superstar.id === t.superstars[0].id);

                return firstS ? firstS.image : t.superstars[0].image;
            }

            return null;
        }
    }
};

const handleBookingShowChange = () => {
    bookingComp1.value = '';
    bookingComp2.value = '';
    bookingComp3.value = '';
    bookingComp4.value = '';
    bookingTagTeamType.value = 'Faction';
    bookingTeam1Superstars.value = [];
    bookingTeam2Superstars.value = [];
    bookingIsTitleMatch.value = false;
    bookingChampionshipId.value = 'NONE';
};

const addMatchToStagingCard = () => {
    if (!bookingShowSelect.value) {
        alert('Initialize an active broadcast show profile context loop first.');

        return;
    }

    if (bookingMatchType.value === 'Segment') {
        if (!bookingNotes.value.trim()) {
            alert('Please describe the segment or promo content.');

            return;
        }

        const newStagedMatch: StagedMatch = {
            id: 'm-staged-' + Date.now() + Math.floor(Math.random() * 100),
            division: 'Segment',
            c1Id: 0,
            c2Id: 0,
            comp1Name: '',
            comp2Name: '',
            comp1Img: null,
            comp2Img: null,
            outcome: 'Segment',
            winnerSlot: null,
            winningId: 0,
            winnerName: 'Segment',
            storylineId: bookingStoryline.value,
            notes: bookingNotes.value.trim(),
            stipulation: '',
        };

        stagedMatches.value.push(newStagedMatch);
        activeMatchPreview.value = newStagedMatch;
        bookingNotes.value = '';

        return;
    }

    const isTeamMatchVal = isTeamMatch.value;
    const isFactionBasedVal = isFactionBased.value;

    const needComp3 = [
        'TripleThreat',
        'TripleThreatTag',
        'Fatal4Way',
        'Fatal4WayTag',
    ].includes(bookingMatchType.value);
    const needComp4 = ['Fatal4Way', 'Fatal4WayTag'].includes(
        bookingMatchType.value,
    );

    let comp1Name = '',
        comp2Name = '',
        comp3Name = '',
        comp4Name = '',
        comp1Img = null as string | null,
        comp2Img = null as string | null,
        comp3Img = null as string | null,
        comp4Img = null as string | null;
    let winningId: number | 'DRAW' = 'DRAW';
    let winnerName = 'Stalemate No Contest (Draw)';
    let team1_superstar_ids: number[] | undefined = undefined;
    let team2_superstar_ids: number[] | undefined = undefined;
    let c1Id = 0,
        c2Id = 0,
        c3Id = 0,
        c4Id = 0;

    if (isTeamMatchVal && !isFactionBasedVal) {
        // Ad-Hoc Tag Team / 3-on-3 Tag / 4-on-4 Tag
        const expectedCount =
            bookingMatchType.value === 'TagTeam'
                ? 2
                : bookingMatchType.value === 'ThreeOnThreeTag'
                  ? 3
                  : 4;

        if (
            bookingTeam1Superstars.value.length !== expectedCount ||
            bookingTeam2Superstars.value.length !== expectedCount
        ) {
            alert(`Select exactly ${expectedCount} superstars for each team.`);

            return;
        }

        // Check if any selection is empty
        if (
            bookingTeam1Superstars.value.some((id) => !id) ||
            bookingTeam2Superstars.value.some((id) => !id)
        ) {
            alert('Verify competing entries exist within selected show roster limits.');

            return;
        }

        const selectedIds = [
            ...bookingTeam1Superstars.value,
            ...bookingTeam2Superstars.value,
        ].map(Number);
        const uniqueIds = new Set(selectedIds);

        if (uniqueIds.size !== selectedIds.length) {
            alert('An element cannot fight against its exact mirror clone instance entry.');

            return;
        }

        const getSuperstarsName = (ids: (number | string)[]) => {
            return ids
                .map(
                    (id) =>
                        props.superstars.find((s) => s.id === Number(id))
                            ?.name || '',
                )
                .filter(Boolean)
                .join(' & ');
        };

        comp1Name = getSuperstarsName(bookingTeam1Superstars.value);
        comp2Name = getSuperstarsName(bookingTeam2Superstars.value);

        team1_superstar_ids = bookingTeam1Superstars.value.map(Number);
        team2_superstar_ids = bookingTeam2Superstars.value.map(Number);

        if (bookingOutcome.value === 'Decisive') {
            winnerName = bookingWinner.value === '1' ? comp1Name : comp2Name;
            winningId = bookingWinner.value === '1' ? 1 : 2; // Team index
        }
    } else {
        // Faction-based Team or Superstar Singles/Multi-man
        if (
            !bookingComp1.value ||
            !bookingComp2.value ||
            (needComp3 && !bookingComp3.value) ||
            (needComp4 && !bookingComp4.value)
        ) {
            alert('Verify competing entries exist within selected show roster limits.');

            return;
        }

        const selectedIds = [
            Number(bookingComp1.value),
            Number(bookingComp2.value),
        ];

        if (needComp3) {
            selectedIds.push(Number(bookingComp3.value));
        }

        if (needComp4) {
            selectedIds.push(Number(bookingComp4.value));
        }

        const uniqueIds = new Set(selectedIds);

        if (uniqueIds.size !== selectedIds.length) {
            alert('An element cannot fight against its exact mirror clone instance entry.');

            return;
        }

        if (!isTeamMatchVal) {
            const s1 = props.superstars.find((s) => s.id === Number(bookingComp1.value));
            const s2 = props.superstars.find((s) => s.id === Number(bookingComp2.value));
            comp1Name = s1 ? s1.name : '';
            comp2Name = s2 ? s2.name : '';
            comp1Img = s1 ? s1.image : null;
            comp2Img = s2 ? s2.image : null;

            if (needComp3) {
                const s3 = props.superstars.find((s) => s.id === Number(bookingComp3.value));
                comp3Name = s3 ? s3.name : '';
                comp3Img = s3 ? s3.image : null;
            }

            if (needComp4) {
                const s4 = props.superstars.find((s) => s.id === Number(bookingComp4.value));
                comp4Name = s4 ? s4.name : '';
                comp4Img = s4 ? s4.image : null;
            }
        } else {
            const t1 = props.teams.find((t) => t.id === Number(bookingComp1.value));
            const t2 = props.teams.find((t) => t.id === Number(bookingComp2.value));
            comp1Name = t1 ? t1.name : '';
            comp2Name = t2 ? t2.name : '';

            if (needComp3) {
                const t3 = props.teams.find((t) => t.id === Number(bookingComp3.value));
                comp3Name = t3 ? t3.name : '';
            }

            if (needComp4) {
                const t4 = props.teams.find((t) => t.id === Number(bookingComp4.value));
                comp4Name = t4 ? t4.name : '';
            }
        }

        c1Id = Number(bookingComp1.value);
        c2Id = Number(bookingComp2.value);

        if (needComp3) {
            c3Id = Number(bookingComp3.value);
        }

        if (needComp4) {
            c4Id = Number(bookingComp4.value);
        }

        if (bookingOutcome.value === 'Decisive') {
            if (bookingWinner.value === '1') {
                winnerName = comp1Name;
                winningId = c1Id;
            } else if (bookingWinner.value === '2') {
                winnerName = comp2Name;
                winningId = c2Id;
            } else if (bookingWinner.value === '3' && needComp3) {
                winnerName = comp3Name;
                winningId = c3Id;
            } else if (bookingWinner.value === '4' && needComp4) {
                winnerName = comp4Name;
                winningId = c4Id;
            }
        }
    }

    let championshipId: string | number = 'NONE';
    let championshipName: string | null = null;

    if (bookingIsTitleMatch.value) {
        if (bookingChampionshipId.value === 'NONE') {
            alert('Please select a championship title belt.');

            return;
        }

        const champ = props.championships.find(
            (c) => c.id === Number(bookingChampionshipId.value),
        );

        if (champ) {
            const expectedType = isTeamMatchVal ? 'TagTeam' : 'Singles';

            if (champ.type !== expectedType) {
                alert(`Championship Title type mismatch: Select a ${expectedType} title for this match.`);

                return;
            }

            if (isTeamMatchVal && !isFactionBasedVal) {
                alert('Championship Title matches require pre-existing Factions (Teams) to challenge.');

                return;
            }

            championshipId = champ.id;
            championshipName = champ.name;
        }
    }

    const stipulation = bookingStipulation.value.trim();

    const newStagedMatch: StagedMatch = {
        id: 'm-staged-' + Date.now() + Math.floor(Math.random() * 100),
        division: bookingMatchType.value,
        c1Id,
        c2Id,
        comp1Name,
        comp2Name,
        comp1Img,
        comp2Img,
        outcome: bookingOutcome.value,
        winnerSlot:
            bookingOutcome.value === 'Decisive' ? bookingWinner.value : null,
        winningId,
        winnerName,
        storylineId: bookingStoryline.value,
        notes: bookingNotes.value.trim(),
        stipulation,
    };

    if (needComp3) {
        newStagedMatch.c3Id = c3Id;
        newStagedMatch.comp3Name = comp3Name;
        newStagedMatch.comp3Img = comp3Img;
    }

    if (needComp4) {
        newStagedMatch.c4Id = c4Id;
        newStagedMatch.comp4Name = comp4Name;
        newStagedMatch.comp4Img = comp4Img;
    }

    if (team1_superstar_ids && team2_superstar_ids) {
        newStagedMatch.team1_superstar_ids = team1_superstar_ids;
        newStagedMatch.team2_superstar_ids = team2_superstar_ids;
    }

    if (championshipId !== 'NONE') {
        newStagedMatch.championshipId = championshipId;
        newStagedMatch.championshipName = championshipName;
    }

    stagedMatches.value.push(newStagedMatch);
    activeMatchPreview.value = newStagedMatch;

    // Reset fields
    bookingStipulation.value = '';
    bookingComp1.value = '';
    bookingComp2.value = '';
    bookingComp3.value = '';
    bookingComp4.value = '';
    bookingTagTeamType.value = 'Faction';
    bookingTeam1Superstars.value = [];
    bookingTeam2Superstars.value = [];
    bookingIsTitleMatch.value = false;
    bookingChampionshipId.value = 'NONE';
    bookingNotes.value = '';
};

const removeStagedMatch = (id: string) => {
    stagedMatches.value = stagedMatches.value.filter((m) => m.id !== id);

    if (activeMatchPreview.value?.id === id) {
        activeMatchPreview.value = stagedMatches.value[0] || null;
    }
};

const commitShowToDatabaseLogs = () => {
    if (stagedMatches.value.length === 0) {
        return;
    }

    router.post(
        route('booking.commit'),
        {
            show_id: bookingShowSelect.value,
            date: bookingDate.value,
            location: bookingLocation.value.trim(),
            matches: stagedMatches.value,
        },
        {
            onSuccess: () => {
                stagedMatches.value = [];
                activeMatchPreview.value = null;
                bookingLocation.value = '';
                toast.success('Show card finalized, ranks metrics compiled and logged!');
                emit('switch-tab', 'dashboard');
            },
            onError: (err) => {
                alert('Failed to commit show: ' + Object.values(err).join(', '));
            },
        },
    );
};
</script>

<template>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Stage new match form deck -->
        <div
            class="h-fit space-y-5 rounded-2xl border border-slate-800 bg-slate-900/60 p-5 shadow"
        >
            <div>
                <h3
                    class="mb-1.5 flex items-center gap-2 text-sm font-bold text-white"
                >
                    <PlusCircle class="h-4 w-4 text-amber-400" /> Show Booking Manager
                </h3>
                <p class="text-[10px] text-slate-400">
                    Configure parameters, stage individual match records, and lock cards down to compile metrics output.
                </p>
            </div>

            <div
                class="space-y-3 rounded-xl border border-slate-800 bg-slate-950 p-3.5"
            >
                <div>
                    <label
                        class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >Active Event Host Show</label
                    >
                    <select
                        v-model="bookingShowSelect"
                        @change="handleBookingShowChange"
                        class="w-full rounded-lg border border-slate-800 bg-slate-900 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                    >
                        <option value="" disabled>Select Host Show</option>
                        <option
                            v-for="sh in shows"
                            :key="sh.id"
                            :value="sh.id"
                        >
                            {{ sh.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label
                        class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >Show Date Tracking</label
                    >
                    <input
                        type="date"
                        v-model="bookingDate"
                        class="w-full rounded-lg border border-slate-800 bg-slate-900 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                    />
                </div>
                <div>
                    <label
                        class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >Show Location</label
                    >
                    <input
                        type="text"
                        v-model="bookingLocation"
                        placeholder="e.g., Madison Square Garden"
                        class="w-full rounded-lg border border-slate-800 bg-slate-900 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                    />
                </div>
            </div>

            <div class="border-slate-850 space-y-4 border-t pt-2">
                <h4
                    class="text-[10px] font-black tracking-widest text-slate-300 uppercase"
                >
                    Stage New Card Slot
                </h4>
                <div>
                    <label
                        class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >Booking Classification</label
                    >
                    <select
                        v-model="bookingMatchType"
                        class="w-full rounded-xl border border-slate-800 bg-slate-955 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                    >
                        <option value="Singles">
                            1 vs 1 Singles Action
                        </option>
                        <option value="TagTeam">Tag Team (2 vs 2)</option>
                        <option value="TripleThreat">
                            Triple Threat (3-Way Singles)
                        </option>
                        <option value="Fatal4Way">
                            Fatal 4-Way (4-Way Singles)
                        </option>
                        <option value="TripleThreatTag">
                            Triple Threat Tag (3-Way Tag)
                        </option>
                        <option value="Fatal4WayTag">
                            Fatal 4-Way Tag (4-Way Tag)
                        </option>
                        <option value="ThreeOnThreeTag">
                            3 vs 3 Tag (6-Man Tag)
                        </option>
                        <option value="FourOnFourTag">
                            4 vs 4 Tag (8-Man Tag)
                        </option>
                        <option value="Segment">
                            Storyline Segment (Promo/Vignette)
                        </option>
                    </select>
                </div>

                <!-- Team Type Selector (only for TagTeam, ThreeOnThreeTag, FourOnFourTag) -->
                <div
                    v-if="
                        [
                            'TagTeam',
                            'ThreeOnThreeTag',
                            'FourOnFourTag',
                        ].includes(bookingMatchType)
                    "
                >
                    <label
                        class="text-slate-450 mb-1.5 block text-[10px] font-bold tracking-wider uppercase"
                        >Tag Team Booking Mode</label
                    >
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            @click="bookingTagTeamType = 'Faction'"
                            :class="[
                                'rounded-xl border px-3 py-2 text-xs font-bold transition-all',
                                bookingTagTeamType === 'Faction'
                                    ? 'border-amber-400 bg-amber-400/10 text-amber-400'
                                    : 'text-slate-450 border-slate-800 bg-slate-950 hover:border-slate-700',
                            ]"
                        >
                            Factions (Teams)
                        </button>
                        <button
                            type="button"
                            @click="bookingTagTeamType = 'AdHoc'"
                            :class="[
                                'rounded-xl border px-3 py-2 text-xs font-bold transition-all',
                                bookingTagTeamType === 'AdHoc'
                                    ? 'border-amber-400 bg-amber-400/10 text-amber-400'
                                    : 'text-slate-450 border-slate-800 bg-slate-950 hover:border-slate-700',
                            ]"
                        >
                            Ad-Hoc Team
                        </button>
                    </div>
                </div>

                <!-- Competitors dropdown selections -->
                <div
                    v-if="bookingMatchType !== 'Segment'"
                    class="space-y-3"
                >
                    <!-- Competitor 1 / Team 1 selection -->
                    <div>
                        <label
                            class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            {{ isFactionBased ? 'Faction 1' : 'Competitor 1' }}
                        </label>

                        <!-- Ad-Hoc Team 1 selections -->
                        <div
                            v-if="isTeamMatch && !isFactionBased"
                            class="space-y-1.5"
                        >
                            <select
                                v-for="idx in (bookingMatchType === 'TagTeam' ? 2 : bookingMatchType === 'ThreeOnThreeTag' ? 3 : 4)"
                                :key="'team1-s-' + idx"
                                v-model="bookingTeam1Superstars[idx - 1]"
                                class="w-full rounded-lg border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                            >
                                <option value="" disabled>Select Superstar</option>
                                <option
                                    v-for="s in superstars"
                                    :key="s.id"
                                    :value="s.id"
                                >
                                    {{ s.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Faction / Superstar 1 Selection -->
                        <select
                            v-else
                            v-model="bookingComp1"
                            class="w-full rounded-lg border border-slate-800 bg-slate-955 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                        >
                            <option value="" disabled>Select Entry</option>
                            <option
                                v-for="c in bookingCompetitors1"
                                :key="c.id"
                                :value="c.id"
                            >
                                {{ c.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Competitor 2 / Team 2 selection -->
                    <div>
                        <label
                            class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            {{ isFactionBased ? 'Faction 2' : 'Competitor 2' }}
                        </label>

                        <!-- Ad-Hoc Team 2 selections -->
                        <div
                            v-if="isTeamMatch && !isFactionBased"
                            class="space-y-1.5"
                        >
                            <select
                                v-for="idx in (bookingMatchType === 'TagTeam' ? 2 : bookingMatchType === 'ThreeOnThreeTag' ? 3 : 4)"
                                :key="'team2-s-' + idx"
                                v-model="bookingTeam2Superstars[idx - 1]"
                                class="w-full rounded-lg border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                            >
                                <option value="" disabled>Select Superstar</option>
                                <option
                                    v-for="s in superstars"
                                    :key="s.id"
                                    :value="s.id"
                                >
                                    {{ s.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Faction / Superstar 2 Selection -->
                        <select
                            v-else
                            v-model="bookingComp2"
                            class="w-full rounded-lg border border-slate-800 bg-slate-955 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                        >
                            <option value="" disabled>Select Entry</option>
                            <option
                                v-for="c in bookingCompetitors2"
                                :key="c.id"
                                :value="c.id"
                            >
                                {{ c.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Competitor 3 / Team 3 selection (TripleThreat, TripleThreatTag, Fatal4Way, Fatal4WayTag) -->
                    <div
                        v-if="
                            [
                                'TripleThreat',
                                'TripleThreatTag',
                                'Fatal4Way',
                                'Fatal4WayTag',
                            ].includes(bookingMatchType)
                        "
                    >
                        <label
                            class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            {{ isFactionBased ? 'Faction 3' : 'Competitor 3' }}
                        </label>
                        <select
                            v-model="bookingComp3"
                            class="w-full rounded-lg border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                        >
                            <option value="" disabled>Select Entry</option>
                            <option
                                v-for="c in bookingCompetitors1"
                                :key="c.id"
                                :value="c.id"
                            >
                                {{ c.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Competitor 4 / Team 4 selection (Fatal4Way, Fatal4WayTag) -->
                    <div
                        v-if="
                            ['Fatal4Way', 'Fatal4WayTag'].includes(
                                bookingMatchType,
                            )
                        "
                    >
                        <label
                            class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            {{ isFactionBased ? 'Faction 4' : 'Competitor 4' }}
                        </label>
                        <select
                            v-model="bookingComp4"
                            class="w-full rounded-lg border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                        >
                            <option value="" disabled>Select Entry</option>
                            <option
                                v-for="c in bookingCompetitors1"
                                :key="c.id"
                                :value="c.id"
                            >
                                {{ c.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Championship line -->
                    <div class="space-y-2 rounded-xl border border-slate-800 bg-slate-950/80 p-3">
                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                id="booking_is_title_match"
                                v-model="bookingIsTitleMatch"
                                class="h-4 w-4 rounded border-slate-700 bg-slate-900 text-amber-500 focus:ring-amber-500 focus:ring-offset-slate-950 focus:outline-none"
                            />
                            <label
                                for="booking_is_title_match"
                                class="cursor-pointer text-xs font-bold text-slate-200"
                            >
                                Championship Title Match
                            </label>
                        </div>
                        <div v-if="bookingIsTitleMatch">
                            <label
                                class="mb-1 block text-[9px] font-bold tracking-wider text-slate-400 uppercase"
                                >Select Title Belt</label
                            >
                            <select
                                v-model="bookingChampionshipId"
                                class="w-full rounded-lg border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                            >
                                <option value="NONE">
                                    -- Select Title --
                                </option>
                                <option
                                    v-for="ch in bookingChampionships"
                                    :key="ch.id"
                                    :value="ch.id"
                                >
                                    {{ ch.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Stipulation Line -->
                    <div>
                        <label
                            class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >Stipulation</label
                        >
                        <input
                            type="text"
                            v-model="bookingStipulation"
                            class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                            placeholder="e.g., Hell in a Cell, Steel Cage, or leave empty for Normal Match"
                        />
                    </div>

                    <!-- Outcome winner slot selection -->
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label
                                class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >Outcome</label
                            >
                            <select
                                v-model="bookingOutcome"
                                class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                            >
                                <option value="Decisive">Decisive</option>
                                <option value="Draw">Draw</option>
                            </select>
                        </div>
                        <div v-if="bookingOutcome === 'Decisive'">
                            <label
                                class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >Winner Slot</label
                            >
                            <select
                                v-model="bookingWinner"
                                class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                            >
                                <option value="1">{{ getCompetitorNameBySlot('1') }}</option>
                                <option value="2">{{ getCompetitorNameBySlot('2') }}</option>
                                <option
                                    v-if="
                                        [
                                            'TripleThreat',
                                            'TripleThreatTag',
                                            'Fatal4Way',
                                            'Fatal4WayTag',
                                        ].includes(bookingMatchType)
                                    "
                                    value="3"
                                >
                                    {{ getCompetitorNameBySlot('3') }}
                                </option>
                                <option
                                    v-if="
                                        ['Fatal4Way', 'Fatal4WayTag'].includes(
                                            bookingMatchType,
                                        )
                                    "
                                    value="4"
                                >
                                    {{ getCompetitorNameBySlot('4') }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Storyline selection & Notes -->
                <div class="space-y-3">
                    <div>
                        <label
                            class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >Link to Storyline Arc</label
                        >
                        <select
                            v-model="bookingStoryline"
                            class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                        >
                            <option value="NONE">
                                -- Independent (No Storyline Arc) --
                            </option>
                            <option
                                v-for="st in storylines"
                                :key="st.id"
                                :value="st.id"
                            >
                                {{ st.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            {{
                                bookingMatchType === 'Segment'
                                    ? 'Describe Segment Content'
                                    : 'Match Booking Notes / Summary'
                            }}
                        </label>
                        <textarea
                            v-model="bookingNotes"
                            rows="2"
                            class="w-full rounded-xl border border-slate-800 bg-slate-955 px-4 py-2.5 text-xs text-white focus:border-amber-400 focus:outline-none"
                            placeholder="Describe match finishes, promos, run-ins, etc."
                        ></textarea>
                    </div>
                </div>

                <button
                    type="button"
                    @click="addMatchToStagingCard"
                    class="flex w-full items-center justify-center gap-1.5 rounded-xl bg-amber-400 px-4 py-2.5 text-xs font-bold text-slate-955 shadow transition-all hover:bg-amber-300"
                >
                    <Plus class="h-4 w-4" /> Stage Match
                </button>
            </div>
        </div>

        <!-- Staged matches card and preview -->
        <div class="space-y-4 lg:col-span-2">
            <!-- Staged matches list -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5">
                <h3
                    class="mb-4 flex items-center justify-between text-sm font-bold text-white"
                >
                    <span>Staged Show Card Match Slots</span>
                    <span
                        class="rounded-lg bg-slate-950 px-2.5 py-1 font-mono text-xs font-bold text-amber-400 shadow-inner"
                    >
                        {{ stagedMatches.length }} Slots Staged
                    </span>
                </h3>

                <div
                    v-if="stagedMatches.length === 0"
                    class="py-12 text-center text-xs text-slate-500"
                >
                    Configure parameter options and stage matches above to compile the broadcast card.
                </div>
                <div v-else class="space-y-3">
                    <div
                        v-for="(match, index) in stagedMatches"
                        :key="match.id"
                        class="flex items-center justify-between rounded-xl border border-slate-800/80 bg-slate-950 p-4 transition-all hover:border-slate-700"
                        :class="{
                            'border-amber-400/50 bg-amber-450/5':
                                activeMatchPreview?.id === match.id,
                        }"
                    >
                        <div
                            @click="activeMatchPreview = match"
                            class="min-w-0 flex-1 cursor-pointer"
                        >
                            <p
                                class="font-mono text-[9px] font-bold text-slate-500"
                            >
                                SLOT #0{{ index + 1 }} •
                                <span class="text-amber-500 uppercase">{{
                                    match.division
                                }}</span>
                            </p>
                            <h4
                                class="mt-0.5 truncate text-xs font-bold text-slate-200"
                            >
                                <template v-if="match.division === 'Segment'">
                                    [Promo Segment] {{ match.notes }}
                                </template>
                                <template v-else>
                                    {{ match.comp1Name }} vs
                                    {{ match.comp2Name }}
                                    <template v-if="match.comp3Name">
                                        vs {{ match.comp3Name }}
                                    </template>
                                    <template v-if="match.comp4Name">
                                        vs {{ match.comp4Name }}
                                    </template>
                                    <span
                                        v-if="match.stipulation"
                                        class="ml-1 text-[10px] text-amber-400"
                                        >({{ match.stipulation }})</span
                                    >
                                </template>
                            </h4>
                        </div>
                        <div class="flex items-center space-x-3 pl-4">
                            <button
                                @click="removeStagedMatch(match.id)"
                                class="p-1.5 text-slate-600 transition hover:text-rose-400"
                            >
                                <Trash class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>

                    <!-- Finalize card button -->
                    <div class="border-t border-slate-800/60 pt-4">
                        <button
                            @click="commitShowToDatabaseLogs"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-xs font-bold text-emerald-400 transition-all hover:bg-emerald-500 hover:text-white"
                        >
                            <Sparkles class="h-4 w-4" /> Commit Broadcast Card to Logs
                        </button>
                    </div>
                </div>
            </div>            <!-- Active match preview slot showcase -->
            <div
                v-if="activeMatchPreview"
                class="relative overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/60 p-6 shadow-inner"
            >
                <div class="relative z-10 flex flex-col items-center space-y-6">
                    <!-- Title Match Header -->
                    <div v-if="activeMatchPreview.championshipName" class="flex flex-col items-center gap-1">
                        <span class="flex items-center gap-1.5 text-xs font-black tracking-widest text-amber-400 uppercase">
                            <Trophy class="h-4 w-4" /> CHAMPIONSHIP MATCH
                        </span>
                        <span class="text-sm font-black text-white italic tracking-wide uppercase drop-shadow">
                            {{ activeMatchPreview.championshipName }}
                        </span>
                    </div>

                    <!-- Match Stipulation & Division Banner -->
                    <div class="flex flex-col items-center text-center">
                        <span class="text-xs font-extrabold text-purple-400 uppercase tracking-widest">
                            {{ activeMatchPreview.stipulation || 'Normal Match' }}
                        </span>
                        <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">
                            {{ activeMatchPreview.division === 'Segment' ? 'Storyline Segment' : activeMatchPreview.division }}
                        </span>
                    </div>

                    <!-- Competitors Layout -->
                    <div class="w-full py-2">
                        <!-- Segment Notes -->
                        <div v-if="activeMatchPreview.division === 'Segment'" class="text-center">
                            <p class="rounded-xl border border-slate-800 bg-slate-950 p-4 font-mono text-xs leading-relaxed text-slate-300 italic">
                                "{{ activeMatchPreview.notes }}"
                            </p>
                        </div>

                        <!-- Match Competitors Grid -->
                        <div v-else>
                            <!-- Grid for Triple Threat or Fatal 4-Way or Multi-man matches -->
                            <div 
                                :class="[
                                    'grid gap-4 items-start text-center',
                                    activeMatchPreview.division === 'TripleThreat' ? 'grid-cols-3' : 
                                    ['Fatal4Way', 'Fatal4WayTag'].includes(activeMatchPreview.division) ? 'grid-cols-4' : 'grid-cols-2'
                                ]"
                            >
                                <!-- Competitor 1 -->
                                <div 
                                    class="flex flex-col items-center space-y-2"
                                    :class="{ 'opacity-40 grayscale': activeMatchPreview.outcome === 'Decisive' && activeMatchPreview.winnerSlot !== '1' }"
                                >
                                    <div class="relative">
                                        <img
                                            :src="getCompetitorImage(activeMatchPreview, 1) || FALLBACK_USER_IMG"
                                            class="h-16 w-16 rounded-full border-2 bg-slate-950 object-cover shadow-lg"
                                            :style="{ borderColor: selectedBookingShow ? selectedBookingShow.color : '#6b21a8' }"
                                        />
                                        <!-- Winner Badge -->
                                        <div v-if="activeMatchPreview.outcome === 'Decisive' && activeMatchPreview.winnerSlot === '1'" class="absolute -top-2 -right-2 bg-gradient-to-r from-amber-400 to-yellow-300 text-slate-950 text-[8px] font-black px-1.5 py-0.5 rounded-full shadow uppercase tracking-wider">
                                            🏆 WINNER
                                        </div>
                                    </div>
                                    <span class="max-w-[100px] truncate text-xs font-bold text-white uppercase tracking-wide">
                                        {{ activeMatchPreview.comp1Name }}
                                    </span>
                                </div>

                                <!-- Competitor 2 -->
                                <div 
                                    class="flex flex-col items-center space-y-2"
                                    :class="{ 'opacity-40 grayscale': activeMatchPreview.outcome === 'Decisive' && activeMatchPreview.winnerSlot !== '2' }"
                                >
                                    <div class="relative">
                                        <img
                                            :src="getCompetitorImage(activeMatchPreview, 2) || FALLBACK_USER_IMG"
                                            class="h-16 w-16 rounded-full border-2 bg-slate-950 object-cover shadow-lg"
                                            :style="{ borderColor: selectedBookingShow ? selectedBookingShow.color : '#6b21a8' }"
                                        />
                                        <!-- Winner Badge -->
                                        <div v-if="activeMatchPreview.outcome === 'Decisive' && activeMatchPreview.winnerSlot === '2'" class="absolute -top-2 -right-2 bg-gradient-to-r from-amber-400 to-yellow-300 text-slate-950 text-[8px] font-black px-1.5 py-0.5 rounded-full shadow uppercase tracking-wider">
                                            🏆 WINNER
                                        </div>
                                    </div>
                                    <span class="max-w-[100px] truncate text-xs font-bold text-white uppercase tracking-wide">
                                        {{ activeMatchPreview.comp2Name }}
                                    </span>
                                </div>

                                <!-- Competitor 3 (if applicable) -->
                                <div 
                                    v-if="['TripleThreat', 'TripleThreatTag', 'Fatal4Way', 'Fatal4WayTag'].includes(activeMatchPreview.division)"
                                    class="flex flex-col items-center space-y-2"
                                    :class="{ 'opacity-40 grayscale': activeMatchPreview.outcome === 'Decisive' && activeMatchPreview.winnerSlot !== '3' }"
                                >
                                    <div class="relative">
                                        <img
                                            :src="getCompetitorImage(activeMatchPreview, 3) || FALLBACK_USER_IMG"
                                            class="h-16 w-16 rounded-full border-2 bg-slate-950 object-cover shadow-lg"
                                            :style="{ borderColor: selectedBookingShow ? selectedBookingShow.color : '#6b21a8' }"
                                        />
                                        <!-- Winner Badge -->
                                        <div v-if="activeMatchPreview.outcome === 'Decisive' && activeMatchPreview.winnerSlot === '3'" class="absolute -top-2 -right-2 bg-gradient-to-r from-amber-400 to-yellow-300 text-slate-950 text-[8px] font-black px-1.5 py-0.5 rounded-full shadow uppercase tracking-wider">
                                            🏆 WINNER
                                        </div>
                                    </div>
                                    <span class="max-w-[100px] truncate text-xs font-bold text-white uppercase tracking-wide">
                                        {{ activeMatchPreview.comp3Name }}
                                    </span>
                                </div>

                                <!-- Competitor 4 (if applicable) -->
                                <div 
                                    v-if="['Fatal4Way', 'Fatal4WayTag'].includes(activeMatchPreview.division)"
                                    class="flex flex-col items-center space-y-2"
                                    :class="{ 'opacity-40 grayscale': activeMatchPreview.outcome === 'Decisive' && activeMatchPreview.winnerSlot !== '4' }"
                                >
                                    <div class="relative">
                                        <img
                                            :src="getCompetitorImage(activeMatchPreview, 4) || FALLBACK_USER_IMG"
                                            class="h-16 w-16 rounded-full border-2 bg-slate-950 object-cover shadow-lg"
                                            :style="{ borderColor: selectedBookingShow ? selectedBookingShow.color : '#6b21a8' }"
                                        />
                                        <!-- Winner Badge -->
                                        <div v-if="activeMatchPreview.outcome === 'Decisive' && activeMatchPreview.winnerSlot === '4'" class="absolute -top-2 -right-2 bg-gradient-to-r from-amber-400 to-yellow-300 text-slate-950 text-[8px] font-black px-1.5 py-0.5 rounded-full shadow uppercase tracking-wider">
                                            🏆 WINNER
                                        </div>
                                    </div>
                                    <span class="max-w-[100px] truncate text-xs font-bold text-white uppercase tracking-wide">
                                        {{ activeMatchPreview.comp4Name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Outcome Summary Bar -->
                    <div class="w-full border-t border-slate-800 pt-4 flex flex-col items-center space-y-2 text-center">
                        <div class="text-xs font-bold">
                            <span v-if="activeMatchPreview.outcome === 'Decisive'" class="text-amber-400 uppercase tracking-wider">
                                Winner: {{ activeMatchPreview.winnerName }}
                            </span>
                            <span v-else-if="activeMatchPreview.outcome === 'Draw'" class="text-slate-400 uppercase tracking-wider">
                                Result: {{ activeMatchPreview.winnerName }}
                            </span>
                            <span v-else class="text-slate-400">
                                Segment Complete
                            </span>
                        </div>
                        <div class="text-[10px] text-slate-500 font-medium">
                            Broadcast: {{ selectedBookingShow ? selectedBookingShow.name : 'Unknown' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
