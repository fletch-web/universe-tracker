<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Tv,
    Users,
    Trophy,
    BookOpen,
    Plus,
    Trash,
    Pencil,
    FileSpreadsheet,
    Star,
    History,
    Sparkles,
    UserX,
    UsersRound,
    PlusCircle,
    CheckCircle2,
} from '@lucide/vue';
import { ref, computed } from 'vue';
import { toast } from 'vue-sonner';

// Define TS interfaces for our database records passed from controller
interface Show {
    id: number;
    name: string;
    color: string;
    image: string | null;
}

interface Superstar {
    id: number;
    name: string;
    gender: 'Male' | 'Female';
    show_id: number;
    wins: number;
    losses: number;
    draws: number;
    image: string | null;
    show?: Show;
}

interface Team {
    id: number;
    name: string;
    wins: number;
    losses: number;
    draws: number;
    superstars?: Superstar[];
}

interface Championship {
    id: number;
    name: string;
    show_id: number;
    type: 'Singles' | 'TagTeam';
    champion_superstar_id: number | null;
    champion_team_id: number | null;
    show?: Show;
    champion_superstar?: Superstar;
    champion_team?: Team;
}

interface StorylineEvent {
    id: number;
    storyline_id: number;
    date: string;
    show_name: string;
    description: string;
    notes: string | null;
}

interface Storyline {
    id: number;
    name: string;
    events?: StorylineEvent[];
}

interface MatchLog {
    id: number;
    show_log_id: number;
    division: 'Singles' | 'TagTeam';
    c1_superstar_id: number | null;
    c2_superstar_id: number | null;
    c1_team_id: number | null;
    c2_team_id: number | null;
    outcome: 'Decisive' | 'Draw';
    winner_slot: '1' | '2' | null;
    winner_superstar_id: number | null;
    winner_team_id: number | null;
    storyline_id: number | null;
    notes: string | null;
    c1_superstar?: Superstar;
    c2_superstar?: Superstar;
    c1_team?: Team;
    c2_team?: Team;
    winner_superstar?: Superstar;
    winner_team?: Team;
    storyline?: Storyline;
}

interface ShowLog {
    id: number;
    show_id: number | null;
    date: string;
    show?: Show;
    matches?: MatchLog[];
}

const props = defineProps<{
    shows: Show[];
    superstars: Superstar[];
    teams: Team[];
    championships: Championship[];
    storylines: Storyline[];
    history: ShowLog[];
}>();

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

// Fallback images matching original HTML logic
const FALLBACK_SHOW_IMG =
    "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'><rect width='100%' height='100%' fill='%231e293b'/><text x='50%' y='55%' font-size='12' font-family='sans-serif' font-weight='bold' fill='%2364748b' text-anchor='middle'>SHOW LOGO</text></svg>";
const FALLBACK_USER_IMG =
    "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'><rect width='100%' height='100%' fill='%230f172a'/><circle cx='50' cy='40' r='18' fill='%23334155'/><path d='M20,80 C20,60 30,55 50,55 C70,55 80,60 80,80 Z' fill='%23334155'/></svg>";

// Tab State Management
const currentTab = ref('dashboard');
const switchTab = (tab: string) => {
    currentTab.value = tab;
};

// --- Show Tab Forms & Actions ---
const editShowId = ref<number | null>(null);
const showForm = useForm({
    name: '',
    color: '#e11d48',
    image: null as string | null,
});

const selectShowImageFile = (e: Event) => {
    const input = e.target as HTMLInputElement;

    if (input.files && input.files[0]) {
        compressAndConvertImage(input.files[0], (base64) => {
            showForm.image = base64;
        });
    }
};

const handleCreateOrUpdateShow = () => {
    if (editShowId.value) {
        showForm.put(route('shows.update', editShowId.value), {
            onSuccess: () => {
                cancelShowEdit();
            },
        });
    } else {
        showForm.post(route('shows.store'), {
            onSuccess: () => {
                showForm.reset();
            },
        });
    }
};

const startShowEdit = (show: Show) => {
    editShowId.value = show.id;
    showForm.name = show.name;
    showForm.color = show.color;
    showForm.image = show.image;
};

const cancelShowEdit = () => {
    editShowId.value = null;
    showForm.reset();
};

const deleteShow = (id: number) => {
    if (
        confirm(
            'Are you sure you want to delete this show profile? Superstars belonging to it will lose their show reference.',
        )
    ) {
        router.delete(route('shows.destroy', id));
    }
};

// --- Superstar Tab Forms & Actions ---
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
            onSuccess: () => {
                cancelSuperstarEdit();
            },
        });
    } else {
        superstarForm.post(route('superstars.store'), {
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

// CSV Batch Import Engine
const handleCsvUpload = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];

    if (!file) {
        return;
    }

    if (props.shows.length === 0) {
        alert(
            'Configure at least one default Active Show track before running data migrations.',
        );
        target.value = '';

        return;
    }

    const reader = new FileReader();
    reader.onload = (evt) => {
        const text = evt.target?.result as string;
        const lines = text.split(/\r?\n/);
        const superstars: Array<{
            name: string;
            gender: string;
            brand: string;
        }> = [];

        if (lines.length < 2) {
            alert(
                'Empty file or missing headers. CSV must have Name, Gender, Brand headers.',
            );
            target.value = '';

            return;
        }

        const headers = lines[0].split(',').map((h) => h.trim().toLowerCase());
        const nameIdx = headers.findIndex(
            (h) => h.includes('name') || h.includes('superstar'),
        );
        const genderIdx = headers.findIndex((h) => h.includes('gender'));
        const brandIdx = headers.findIndex(
            (h) => h.includes('brand') || h.includes('show'),
        );

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
                superstars.push({ name, gender, brand });
            }
        }

        router.post(
            route('superstars.import'),
            { superstars },
            {
                onSuccess: () => {
                    toast.success('Roster batch imported successfully!');
                    target.value = '';
                },
                onError: (errors) => {
                    toast.error(
                        'Failed to import roster: ' +
                            Object.values(errors).join(', '),
                    );
                },
            },
        );
    };
    reader.readAsText(file);
};

// Roster Filter brand
const filterRosterBrand = ref('ALL');
const filteredSuperstars = computed(() => {
    if (filterRosterBrand.value === 'ALL') {
        return props.superstars;
    }

    return props.superstars.filter(
        (s) => s.show_id === Number(filterRosterBrand.value),
    );
});

// --- Team Form & Actions ---
const teamForm = useForm({
    name: '',
    members: [] as number[],
});

const handleCreateTeam = () => {
    if (teamForm.members.length < 2) {
        alert(
            'A faction or tag team requires at least 2 rostered members selected.',
        );

        return;
    }

    teamForm.post(route('teams.store'), {
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

// --- Championships Form & Actions ---
const editChampionshipId = ref<number | null>(null);
const championshipForm = useForm({
    name: '',
    show_id: '' as string | number,
    type: 'Singles' as 'Singles' | 'TagTeam',
    champion_id: 'VACANT' as string | number,
});

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

// --- Storyline Form & Actions ---
const storylineForm = useForm({
    name: '',
});

const handleCreateStoryline = () => {
    storylineForm.post(route('storylines.store'), {
        onSuccess: () => {
            storylineForm.reset();
        },
    });
};

// Timeline view storyline
const activeStorylineId = ref<string | number>('NONE');
const activeStoryline = computed(() => {
    if (activeStorylineId.value === 'NONE') {
        return null;
    }

    return (
        props.storylines.find(
            (st) => st.id === Number(activeStorylineId.value),
        ) || null
    );
});

// --- Booking Engine Deck ---
interface StagedMatch {
    id: string;
    division: 'Singles' | 'TagTeam';
    c1Id: number;
    c2Id: number;
    comp1Name: string;
    comp2Name: string;
    comp1Img: string | null;
    comp2Img: string | null;
    outcome: 'Decisive' | 'Draw';
    winnerSlot: '1' | '2' | null;
    winningId: number | 'DRAW';
    winnerName: string;
    storylineId: string | number;
    notes: string;
}

const bookingShowSelect = ref<string | number>('');
const bookingDate = ref(new Date().toISOString().split('T')[0]);
const bookingMatchType = ref<'Singles' | 'TagTeam'>('Singles');
const bookingComp1 = ref<string | number>('');
const bookingComp2 = ref<string | number>('');
const bookingOutcome = ref<'Decisive' | 'Draw'>('Decisive');
const bookingWinner = ref<'1' | '2'>('1');
const bookingStoryline = ref<string | number>('NONE');
const bookingNotes = ref('');

const stagedMatches = ref<StagedMatch[]>([]);
const activeMatchPreview = ref<StagedMatch | null>(null);

// Competitors list based on selected Booking Show & Match type
const bookingCompetitors1 = computed(() => {
    if (!bookingShowSelect.value) {
        return [];
    }

    if (bookingMatchType.value === 'Singles') {
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

const handleBookingShowChange = () => {
    bookingComp1.value = '';
    bookingComp2.value = '';
};

const addMatchToStagingCard = () => {
    if (!bookingShowSelect.value) {
        alert(
            'Initialize an active broadcast show profile context loop first.',
        );

        return;
    }

    if (!bookingComp1.value || !bookingComp2.value) {
        alert(
            'Verify competing entries exist within selected show roster limits.',
        );

        return;
    }

    if (bookingComp1.value === bookingComp2.value) {
        alert(
            'An element cannot fight against its exact mirror clone instance entry.',
        );

        return;
    }

    let comp1Name = '',
        comp2Name = '',
        comp1Img = null as string | null,
        comp2Img = null as string | null;

    if (bookingMatchType.value === 'Singles') {
        const s1 = props.superstars.find(
            (s) => s.id === Number(bookingComp1.value),
        );
        const s2 = props.superstars.find(
            (s) => s.id === Number(bookingComp2.value),
        );
        comp1Name = s1 ? s1.name : '';
        comp2Name = s2 ? s2.name : '';
        comp1Img = s1 ? s1.image : null;
        comp2Img = s2 ? s2.image : null;
    } else {
        const t1 = props.teams.find((t) => t.id === Number(bookingComp1.value));
        const t2 = props.teams.find((t) => t.id === Number(bookingComp2.value));
        comp1Name = t1 ? t1.name : '';
        comp2Name = t2 ? t2.name : '';
    }

    let winnerName = 'Stalemate No Contest (Draw)';
    let winningId = 'DRAW' as number | 'DRAW';

    if (bookingOutcome.value === 'Decisive') {
        winnerName = bookingWinner.value === '1' ? comp1Name : comp2Name;
        winningId =
            bookingWinner.value === '1'
                ? Number(bookingComp1.value)
                : Number(bookingComp2.value);
    }

    const newStagedMatch: StagedMatch = {
        id: 'm-staged-' + Date.now() + Math.floor(Math.random() * 100),
        division: bookingMatchType.value,
        c1Id: Number(bookingComp1.value),
        c2Id: Number(bookingComp2.value),
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
    };

    stagedMatches.value.push(newStagedMatch);
    activeMatchPreview.value = newStagedMatch;

    // Reset notes
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
            matches: stagedMatches.value,
        },
        {
            onSuccess: () => {
                stagedMatches.value = [];
                activeMatchPreview.value = null;
                toast.success(
                    'Show card finalized, ranks metrics compiled and logged!',
                );
                switchTab('dashboard');
            },
            onError: (err) => {
                alert(
                    'Failed to commit show: ' + Object.values(err).join(', '),
                );
            },
        },
    );
};

const selectedBookingShow = computed(() => {
    return (
        props.shows.find((s) => s.id === Number(bookingShowSelect.value)) ||
        null
    );
});

// Image helper function
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

// Helpers
function route(name: string, param?: string | number): string {
    switch (name) {
        case 'shows.store': return '/shows';
        case 'shows.update': return `/shows/${param}`;
        case 'shows.destroy': return `/shows/${param}`;
        case 'superstars.store': return '/superstars';
        case 'superstars.update': return `/superstars/${param}`;
        case 'superstars.destroy': return `/superstars/${param}`;
        case 'superstars.import': return '/superstars/import';
        case 'teams.store': return '/teams';
        case 'teams.destroy': return `/teams/${param}`;
        case 'championships.store': return '/championships';
        case 'championships.update': return `/championships/${param}`;
        case 'championships.destroy': return `/championships/${param}`;
        case 'storylines.store': return '/storylines';
        case 'booking.commit': return '/booking/commit';
        default: return '';
    }
}

const topSuperstars = computed(() => {
    return [...props.superstars].sort((a, b) => b.wins - a.wins).slice(0, 5);
});
</script>

<template>
    <Head title="Universe Tracker - Booking Deck" />

    <div
        class="flex min-h-screen flex-col gap-6 bg-slate-950 p-6 font-sans text-slate-100"
    >
        <!-- Premium Tabs Subnavigation Bar -->
        <div
            class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-800 bg-slate-900/40 p-2.5 backdrop-blur"
        >
            <div class="flex items-center space-x-3.5 pl-2">
                <div
                    class="flex items-center justify-center rounded-xl bg-gradient-to-tr from-amber-500 to-yellow-400 p-2 text-xl font-black tracking-wider text-slate-950 shadow-lg shadow-amber-500/10"
                >
                    <Tv class="h-6 w-6" />
                </div>
                <div>
                    <h1 class="text-base leading-tight font-black text-white">
                        Universe Tracker
                    </h1>
                    <p class="text-[10px] tracking-wide text-slate-400">
                        Elite Booking & Metrics engine
                    </p>
                </div>
            </div>

            <nav class="flex flex-wrap gap-1.5">
                <button
                    @click="switchTab('dashboard')"
                    :class="[
                        'flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-semibold transition-all duration-300',
                        currentTab === 'dashboard'
                            ? 'bg-amber-400 text-slate-950 shadow-lg shadow-amber-400/10'
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
                            ? 'bg-amber-400 text-slate-950 shadow-lg shadow-amber-400/10'
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
                            ? 'bg-amber-400 text-slate-950 shadow-lg shadow-amber-400/10'
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
                            ? 'bg-amber-400 text-slate-950 shadow-lg shadow-amber-400/10'
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
                            ? 'bg-amber-400 text-slate-950 shadow-lg shadow-amber-400/10'
                            : 'text-slate-400 hover:bg-slate-900 hover:text-white',
                    ]"
                >
                    <BookOpen class="h-4 w-4" /> Storylines
                </button>
                <button
                    @click="switchTab('booking')"
                    :class="[
                        'flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-semibold transition-all duration-300',
                        currentTab === 'booking'
                            ? 'bg-amber-400 text-slate-950 shadow-lg shadow-amber-400/10'
                            : 'text-slate-400 hover:bg-slate-900 hover:text-white',
                    ]"
                >
                    <PlusCircle class="h-4 w-4" /> Record Show
                </button>
            </nav>
        </div>

        <!-- MAIN TAB VIEWS -->

        <!-- TAB: DASHBOARD -->
        <div v-if="currentTab === 'dashboard'" class="space-y-6">
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
                                    <span class="text-slate-200">{{
                                        m.division === 'Singles'
                                            ? m.c1_superstar?.name
                                            : m.c1_team?.name
                                    }}</span>
                                    vs
                                    <span class="text-slate-200">{{
                                        m.division === 'Singles'
                                            ? m.c2_superstar?.name
                                            : m.c2_team?.name
                                    }}</span>
                                    <span
                                        v-if="m.outcome === 'Decisive'"
                                        class="ml-1 text-emerald-400"
                                        >({{
                                            m.winner_superstar?.name ||
                                            m.winner_team?.name
                                        }}
                                        wins)</span
                                    >
                                    <span v-else class="ml-1 text-amber-500"
                                        >(Draw)</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: SHOWS -->
        <div
            v-if="currentTab === 'shows'"
            class="grid grid-cols-1 gap-6 lg:grid-cols-3"
        >
            <!-- Create Show Form -->
            <div
                class="h-fit rounded-2xl border border-slate-800 bg-slate-900/60 p-5"
            >
                <h3
                    class="mb-4 flex items-center gap-2 text-sm font-bold text-white"
                >
                    <Plus class="h-4 w-4 text-amber-400" />
                    {{ editShowId ? 'Modify Show Profile' : 'Create New Show' }}
                </h3>
                <form
                    @submit.prevent="handleCreateOrUpdateShow"
                    class="space-y-4"
                >
                    <div>
                        <label
                            class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >Show Name</label
                        >
                        <input
                            type="text"
                            v-model="showForm.name"
                            required
                            class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-2.5 text-xs text-white focus:border-amber-400 focus:outline-none"
                            placeholder="e.g., Monday Night Raw"
                        />
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-1">
                            <label
                                class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >Theme Color</label
                            >
                            <input
                                type="color"
                                v-model="showForm.color"
                                class="h-9 w-full cursor-pointer rounded-xl border border-slate-800 bg-slate-950 p-1"
                            />
                        </div>
                        <div class="col-span-2">
                            <label
                                class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >Logo Graphic</label
                            >
                            <input
                                type="file"
                                @change="selectShowImageFile"
                                accept="image/*"
                                class="w-full cursor-pointer text-[10px] text-slate-400 file:mr-2 file:rounded-lg file:border-0 file:bg-slate-800 file:px-2.5 file:py-1.5 file:text-[10px] file:font-semibold file:text-slate-200 hover:file:bg-slate-700"
                            />
                        </div>
                    </div>
                    <div class="flex gap-2 pt-2">
                        <button
                            v-if="editShowId"
                            type="button"
                            @click="cancelShowEdit"
                            class="w-1/3 rounded-xl bg-slate-800 px-4 py-2.5 text-xs font-bold text-white transition-all hover:bg-slate-700"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-amber-400 px-4 py-2.5 text-xs font-bold text-slate-950 shadow transition-all hover:bg-amber-300"
                        >
                            <Tv class="h-4 w-4" />
                            {{
                                editShowId
                                    ? 'Save Adjustments'
                                    : 'Initialize Show'
                            }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Configuration Grid -->
            <div class="space-y-4 lg:col-span-2">
                <h3
                    class="flex items-center gap-2 text-sm font-bold text-white"
                >
                    <Tv class="text-slate-450 h-4 w-4" /> Configured Brands /
                    Shows
                </h3>
                <div
                    v-if="shows.length === 0"
                    class="py-6 text-center text-xs text-slate-500"
                >
                    No brands initialized yet.
                </div>
                <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div
                        v-for="show in shows"
                        :key="show.id"
                        class="group relative overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/60"
                    >
                        <div
                            class="h-1.5"
                            :style="{ backgroundColor: show.color }"
                        ></div>
                        <div class="flex items-center space-x-4 p-4">
                            <img
                                :src="show.image || FALLBACK_SHOW_IMG"
                                class="h-12 w-12 rounded-xl border border-slate-800 bg-slate-950 object-cover shadow-inner"
                            />
                            <div class="min-w-0 flex-1">
                                <h4
                                    class="truncate text-sm font-bold text-white"
                                >
                                    {{ show.name }}
                                </h4>
                                <p
                                    class="mt-0.5 flex items-center font-mono text-[10px] text-slate-400"
                                >
                                    <span
                                        class="mr-1.5 inline-block h-2.5 w-2.5 rounded-full border border-slate-950"
                                        :style="{ backgroundColor: show.color }"
                                    ></span>
                                    {{ show.color.toUpperCase() }}
                                </p>
                            </div>
                            <div class="flex items-center space-x-1">
                                <button
                                    @click="startShowEdit(show)"
                                    class="p-2 text-slate-500 transition hover:text-amber-400"
                                >
                                    <Pencil class="h-3.5 w-3.5" />
                                </button>
                                <button
                                    @click="deleteShow(show.id)"
                                    class="p-2 text-slate-600 transition hover:text-rose-400"
                                >
                                    <Trash class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: SUPERSTARS & TEAMS -->
        <div v-if="currentTab === 'roster'" class="space-y-6">
            <!-- Roster Toolbar -->
            <div
                class="grid grid-cols-1 gap-6 rounded-2xl border border-slate-800 bg-slate-900/60 p-5 md:grid-cols-2"
            >
                <!-- Excel Import -->
                <div
                    class="border-b border-slate-800 pb-6 md:border-r md:border-b-0 md:pr-6 md:pb-0"
                >
                    <h3
                        class="mb-2 flex items-center gap-1.5 text-xs font-bold text-white"
                    >
                        <FileSpreadsheet class="h-4 w-4 text-emerald-400" />
                        Roster CSV Import Engine
                    </h3>
                    <p class="mb-4 text-[10px] text-slate-400">
                        Batch upload members directly. CSV header format
                        required:
                        <code class="font-mono text-amber-400"
                            >Name, Gender, Brand</code
                        >
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

                <!-- Roster Filtering -->
                <div class="flex flex-col justify-center">
                    <h3 class="mb-2 text-xs font-bold text-white">
                        Roster Brand Filtering
                    </h3>
                    <select
                        v-model="filterRosterBrand"
                        class="max-w-[200px] rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                    >
                        <option value="ALL">All Active Shows</option>
                        <option v-for="s in shows" :key="s.id" :value="s.id">
                            {{ s.name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Main Roster Area -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Registration Forms Deck -->
                <div class="h-fit space-y-6">
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
                                    class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-2.5 text-xs text-white focus:border-amber-400 focus:outline-none"
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
                                        class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-2.5 text-xs text-white focus:border-amber-400 focus:outline-none"
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
                                        class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-2.5 text-xs text-white focus:border-amber-400 focus:outline-none"
                                    >
                                        <option value="" disabled>
                                            Select Show
                                        </option>
                                        <option
                                            v-for="sh in shows"
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
                                class="grid grid-cols-3 gap-2 rounded-xl border border-slate-800/80 bg-slate-950/60 p-3"
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
                                        class="w-full rounded-lg border border-slate-800 bg-slate-950 px-2 py-1 text-xs text-white"
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
                                        class="w-full rounded-lg border border-slate-800 bg-slate-950 px-2 py-1 text-xs text-white"
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
                                        class="w-full rounded-lg border border-slate-800 bg-slate-950 px-2 py-1 text-xs text-white"
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
                                    class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-2.5 text-xs text-white focus:border-amber-400 focus:outline-none"
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
                                    class="min-h-[130px] w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
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
                <div class="space-y-6 lg:col-span-2">
                    <!-- Superstar Active Matrix with Coloured border portraits -->
                    <div>
                        <h3
                            class="mb-3 flex items-center gap-2 text-sm font-bold text-white"
                        >
                            <Users class="text-slate-450 h-4 w-4" /> Active
                            Roster & Metrics Matrix (W-D-L)
                        </h3>
                        <div
                            v-if="filteredSuperstars.length === 0"
                            class="py-6 text-center text-xs text-slate-500"
                        >
                            No active combatants registered under this brand.
                        </div>
                        <div
                            v-else
                            class="grid grid-cols-1 gap-4 sm:grid-cols-2"
                        >
                            <div
                                v-for="s in filteredSuperstars"
                                :key="s.id"
                                class="group relative flex items-center space-x-3.5 rounded-xl border border-slate-800 bg-slate-900/60 p-3"
                            >
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
                                            s.show ? s.show.name : 'Independent'
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
                                <div class="flex space-x-0.5">
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
                    </div>

                    <!-- Faction standings display -->
                    <div class="border-slate-850 border-t pt-6">
                        <h3
                            class="mb-3 flex items-center gap-2 text-sm font-bold text-white"
                        >
                            <UsersRound class="text-slate-450 h-4 w-4" /> Tag
                            Teams & Faction Standings
                        </h3>
                        <div
                            v-if="teams.length === 0"
                            class="py-6 text-center text-xs text-slate-500"
                        >
                            No tag team factions assembled yet.
                        </div>
                        <div
                            v-else
                            class="grid grid-cols-1 gap-4 sm:grid-cols-2"
                        >
                            <div
                                v-for="t in teams"
                                :key="t.id"
                                class="group relative space-y-1.5 rounded-xl border border-slate-800 bg-slate-900/60 p-3.5"
                            >
                                <div class="flex items-start justify-between">
                                    <h4
                                        class="truncate text-xs font-black text-amber-400"
                                    >
                                        {{ t.name }}
                                    </h4>
                                    <button
                                        @click="deleteTeam(t.id)"
                                        class="text-slate-600 transition hover:text-rose-400"
                                    >
                                        <Trash class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                                <p class="text-[10px] text-slate-300">
                                    Members:
                                    <span class="font-normal text-slate-400">
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
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: CHAMPIONSHIPS -->
        <div
            v-if="currentTab === 'titles'"
            class="grid grid-cols-1 gap-6 lg:grid-cols-3"
        >
            <!-- Championship Creation form -->
            <div
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
                            class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-2.5 text-xs text-white focus:border-amber-400 focus:outline-none"
                            placeholder="e.g., WWE Championship"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >Assigned Show Track</label
                        >
                        <select
                            v-model="championshipForm.show_id"
                            required
                            class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-2.5 text-xs text-white focus:border-amber-400 focus:outline-none"
                        >
                            <option value="" disabled>Select Show</option>
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
                            >Championship Classification</label
                        >
                        <select
                            v-model="championshipForm.type"
                            class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-2.5 text-xs text-white focus:border-amber-400 focus:outline-none"
                        >
                            <option value="Singles">Singles Division</option>
                            <option value="TagTeam">
                                Tag Team / Faction Division
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >Anoint Current Champion</label
                        >
                        <select
                            v-model="championshipForm.champion_id"
                            class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-2.5 text-xs text-white focus:border-amber-400 focus:outline-none"
                        >
                            <option value="VACANT">
                                -- VACANT (No Holder Designated) --
                            </option>
                            <template
                                v-if="championshipForm.type === 'Singles'"
                            >
                                <option
                                    v-for="s in superstars"
                                    :key="s.id"
                                    :value="s.id"
                                >
                                    [Individual] {{ s.name }}
                                </option>
                            </template>
                            <template v-else>
                                <option
                                    v-for="t in teams"
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
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-amber-400 px-4 py-2.5 text-xs font-bold text-slate-950 shadow transition-all hover:bg-amber-300"
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
            <div class="space-y-4 lg:col-span-2">
                <h3
                    class="flex items-center gap-2 text-sm font-bold text-white"
                >
                    <Trophy class="text-slate-450 h-4 w-4" /> Active
                    Championships Status
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
                            <div class="flex space-x-0.5">
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

        <!-- TAB: STORYLINES -->
        <div
            v-if="currentTab === 'storylines'"
            class="grid grid-cols-1 gap-6 lg:grid-cols-3"
        >
            <!-- Create narrative form -->
            <div
                class="h-fit space-y-6 rounded-2xl border border-slate-800 bg-slate-900/60 p-5"
            >
                <div>
                    <h3
                        class="mb-4 flex items-center gap-2 text-sm font-bold text-white"
                    >
                        <BookOpen class="h-4 w-4 text-amber-400" /> Create
                        Narrative Storyline
                    </h3>
                    <form
                        @submit.prevent="handleCreateStoryline"
                        class="space-y-4"
                    >
                        <div>
                            <label
                                class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >Storyline Arc Title Name</label
                            >
                            <input
                                type="text"
                                v-model="storylineForm.name"
                                required
                                class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-2.5 text-xs text-white focus:border-amber-400 focus:outline-none"
                                placeholder="e.g., Bloodline Civil War"
                            />
                        </div>
                        <button
                            type="submit"
                            class="flex w-full items-center justify-center gap-1.5 rounded-xl bg-amber-400 px-4 py-2.5 text-xs font-bold text-slate-950 shadow transition-all hover:bg-amber-300"
                        >
                            <BookOpen class="h-4 w-4" /> Initiate Narrative Arc
                        </button>
                    </form>
                </div>

                <div class="border-t border-slate-800 pt-5">
                    <label
                        class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >Select Storyline to View Timeline</label
                    >
                    <select
                        v-model="activeStorylineId"
                        class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-2.5 text-xs text-white focus:border-amber-400 focus:outline-none"
                    >
                        <option value="NONE">No Arc Inspected</option>
                        <option
                            v-for="st in storylines"
                            :key="st.id"
                            :value="st.id"
                        >
                            {{ st.name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Timeline visualization -->
            <div class="space-y-4 lg:col-span-2">
                <h3
                    class="flex items-center justify-between text-sm font-bold text-white"
                >
                    <span class="flex items-center gap-2"
                        ><BookOpen class="text-slate-450 h-4 w-4" />
                        Chronological Event Arc Timeline</span
                    >
                    <span
                        v-if="activeStoryline"
                        class="rounded-lg border border-amber-400/20 bg-amber-400/10 px-2.5 py-1 text-xs font-semibold text-amber-400"
                    >
                        {{ activeStoryline.name }}
                    </span>
                </h3>
                <div
                    class="relative min-h-[300px] rounded-2xl border border-slate-800 bg-slate-900/60 p-6 shadow"
                >
                    <div
                        v-if="!activeStoryline"
                        class="py-12 text-center text-xs text-slate-500"
                    >
                        Select or initialize a storyline arc timeline vector
                        above to render segments logs.
                    </div>
                    <div
                        v-else-if="
                            !activeStoryline.events ||
                            activeStoryline.events.length === 0
                        "
                        class="py-12 text-center text-xs text-slate-500"
                    >
                        This storyline holds no recorded data. Schedule bouts
                        linked to this arc in the Booking engine tab.
                    </div>
                    <div v-else class="relative space-y-6">
                        <!-- Timeline Line connector -->
                        <div
                            class="absolute top-2 bottom-2 left-3 z-0 w-0.5 bg-slate-800"
                        ></div>

                        <div
                            v-for="ev in activeStoryline.events"
                            :key="ev.id"
                            class="relative z-10 flex items-start space-x-4 pl-8"
                        >
                            <div
                                class="absolute top-1.5 left-1 h-4 w-4 rounded-full border-4 border-slate-900 bg-amber-400 shadow"
                            ></div>
                            <div
                                class="flex-1 space-y-2 rounded-xl border border-slate-800 bg-slate-950 p-4"
                            >
                                <div
                                    class="flex flex-wrap items-center justify-between text-[10px] text-slate-500"
                                >
                                    <span
                                        class="flex items-center gap-1 font-bold text-white"
                                    >
                                        <Tv
                                            class="h-3.5 w-3.5 text-slate-400"
                                        />
                                        Broadcasted: {{ ev.show_name }}
                                    </span>
                                    <span
                                        class="rounded border border-slate-800/80 bg-slate-900 px-2 py-0.5 font-mono"
                                        >{{ ev.date }}</span
                                    >
                                </div>
                                <h5 class="text-xs font-black text-amber-400">
                                    {{ ev.description }}
                                </h5>
                                <p
                                    v-if="ev.notes"
                                    class="rounded-r-lg border-l-2 border-amber-400/40 bg-slate-900/40 p-2.5 text-xs leading-relaxed text-slate-300 italic"
                                >
                                    {{ ev.notes }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: RECORD SHOW (BOOKING ENGINE) -->
        <div
            v-if="currentTab === 'booking'"
            class="grid grid-cols-1 gap-6 lg:grid-cols-3"
        >
            <!-- Stage new match form deck -->
            <div
                class="h-fit space-y-5 rounded-2xl border border-slate-800 bg-slate-900/60 p-5 shadow"
            >
                <div>
                    <h3
                        class="mb-1.5 flex items-center gap-2 text-sm font-bold text-white"
                    >
                        <PlusCircle class="h-4 w-4 text-amber-400" /> Show
                        Booking Manager
                    </h3>
                    <p class="text-[10px] text-slate-400">
                        Configure parameters, stage individual match records,
                        and lock cards down to compile metrics output.
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
                </div>

                <div class="border-slate-850 space-y-4 border-t pt-2">
                    <h4
                        class="text-[10px] font-black tracking-widest text-slate-300 uppercase"
                    >
                        Stage New Card Match Slot
                    </h4>

                    <div>
                        <label
                            class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >Booking Classification</label
                        >
                        <select
                            v-model="bookingMatchType"
                            class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                        >
                            <option value="Singles">
                                1 vs 1 Singles Action
                            </option>
                            <option value="TagTeam">
                                Tag Team / Faction Confrontation
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        <div>
                            <label
                                class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >
                                {{
                                    bookingMatchType === 'Singles'
                                        ? 'Competitor / Blue Corner (Blue Brand)'
                                        : 'Faction Unit #1 (Blue Corner)'
                                }}
                            </label>
                            <select
                                v-model="bookingComp1"
                                class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                            >
                                <option value="" disabled>
                                    Select Competitor 1
                                </option>
                                <option
                                    v-for="c in bookingCompetitors1"
                                    :key="c.id"
                                    :value="c.id"
                                >
                                    {{ c.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >
                                {{
                                    bookingMatchType === 'Singles'
                                        ? 'Competitor / Red Corner (Red Brand)'
                                        : 'Faction Unit #2 (Red Corner)'
                                }}
                            </label>
                            <select
                                v-model="bookingComp2"
                                class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                            >
                                <option value="" disabled>
                                    Select Competitor 2
                                </option>
                                <option
                                    v-for="c in bookingCompetitors2"
                                    :key="c.id"
                                    :value="c.id"
                                >
                                    {{ c.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label
                                class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >Match Outcome Verdict</label
                            >
                            <select
                                v-model="bookingOutcome"
                                class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                            >
                                <option value="Decisive">
                                    Decisive Pinfall / Submission
                                </option>
                                <option value="Draw">
                                    Draw / No Contest / Chaos
                                </option>
                            </select>
                        </div>
                        <div v-if="bookingOutcome === 'Decisive'">
                            <label
                                class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >Declared Winner Element</label
                            >
                            <select
                                v-model="bookingWinner"
                                class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                            >
                                <option value="1">Competitor 1 (Blue)</option>
                                <option value="2">Competitor 2 (Red)</option>
                            </select>
                        </div>
                    </div>

                    <div
                        class="space-y-2 rounded-xl border border-slate-800 bg-slate-950 p-3"
                    >
                        <div>
                            <label
                                class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >Link to Narrative Storyline Arc</label
                            >
                            <select
                                v-model="bookingStoryline"
                                class="w-full rounded-lg border border-slate-800 bg-slate-900 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                            >
                                <option value="NONE">
                                    -- Standalone Exhibition Match --
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
                                >Script Narrative Booking Notes</label
                            >
                            <textarea
                                v-model="bookingNotes"
                                rows="2"
                                class="placeholder-slate-650 w-full rounded-lg border border-slate-800 bg-slate-900 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                                placeholder="Describe segments, surprise run-ins, post-match beatdowns, or title changes..."
                            ></textarea>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="addMatchToStagingCard"
                        class="flex w-full items-center justify-center gap-1.5 rounded-xl bg-amber-400 px-4 py-2.5 text-xs font-bold text-slate-950 shadow transition-all hover:bg-amber-300"
                    >
                        <PlusCircle class="h-4 w-4" /> Stage Match To Card
                    </button>
                </div>
            </div>

            <!-- Staged matches lists & previews -->
            <div class="space-y-6 lg:col-span-2">
                <!-- Staged matches checklist -->
                <div
                    class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5 shadow"
                >
                    <div
                        class="mb-4 flex items-center justify-between border-b border-slate-800 pb-3"
                    >
                        <h3
                            class="flex items-center gap-2 text-sm font-bold text-white"
                        >
                            <Users class="text-slate-450 h-4 w-4" /> Staged
                            Match Event Card Structure
                        </h3>
                        <button
                            @click="commitShowToDatabaseLogs"
                            :disabled="stagedMatches.length === 0"
                            class="flex items-center gap-1 rounded-lg bg-amber-400 px-3.5 py-1.5 text-xs font-bold text-slate-950 transition-all disabled:bg-slate-800 disabled:text-slate-500"
                        >
                            <CheckCircle2 class="h-4 w-4" /> Finalize Show &
                            Commit Metrics
                        </button>
                    </div>
                    <div
                        v-if="stagedMatches.length === 0"
                        class="py-6 text-center text-xs text-slate-500"
                    >
                        No staged bouts are on the match card interface. Build
                        matches via the configuration deck tool.
                    </div>
                    <div v-else class="space-y-3.5">
                        <div
                            v-for="(m, index) in stagedMatches"
                            :key="m.id"
                            @click="activeMatchPreview = m"
                            :class="[
                                'flex cursor-pointer flex-wrap items-center justify-between gap-4 rounded-xl border bg-slate-950 p-4 transition-all',
                                activeMatchPreview?.id === m.id
                                    ? 'border-amber-400'
                                    : 'border-slate-850 hover:border-amber-400/40',
                            ]"
                        >
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="rounded bg-slate-800 px-1.5 py-0.5 font-mono text-[9px] font-bold text-slate-400"
                                        >BOUT #0{{ index + 1 }}</span
                                    >
                                    <span
                                        class="text-slate-450 text-[10px] font-semibold"
                                        >{{ m.division }} Match</span
                                    >
                                </div>
                                <h4 class="text-xs font-black text-white">
                                    {{ m.comp1Name }}
                                    <span class="px-1 font-black text-amber-400"
                                        >VS</span
                                    >
                                    {{ m.comp2Name }}
                                </h4>
                                <p class="text-[10px] text-slate-400">
                                    Verdict Outcome:
                                    <span class="font-bold text-emerald-400">{{
                                        m.winnerName
                                    }}</span>
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span
                                    v-if="m.storylineId !== 'NONE'"
                                    class="rounded-lg border border-purple-500/20 bg-purple-500/10 px-2 py-0.5 text-[9px] font-bold text-purple-400"
                                >
                                    Storyline
                                </span>
                                <button
                                    @click.stop="removeStagedMatch(m.id)"
                                    class="text-slate-550 p-1 transition hover:text-rose-500"
                                >
                                    <Trash class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Match Graphic Live Canvas preview deck -->
                <div
                    class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5 shadow"
                >
                    <h3
                        class="mb-4 flex items-center gap-2 text-sm font-bold text-white"
                    >
                        <Tv class="h-4 w-4 text-amber-400" /> Programmatic
                        Matchup Display Graphic (Preview)
                    </h3>
                    <div
                        v-if="!activeMatchPreview"
                        class="py-6 text-center text-xs text-slate-500"
                    >
                        Stage or click a specific match layout item card to
                        preview the broadcasting promo graphic.
                    </div>
                    <div
                        v-else
                        class="relative flex min-h-[220px] flex-col items-center justify-between overflow-hidden rounded-2xl border bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 p-6"
                        :style="{
                            borderColor: selectedBookingShow
                                ? selectedBookingShow.color + '40'
                                : '#334155',
                        }"
                    >
                        <div
                            class="absolute inset-x-0 top-0 h-1.5"
                            :style="{
                                backgroundColor: selectedBookingShow
                                    ? selectedBookingShow.color
                                    : '#fff',
                            }"
                        ></div>

                        <div class="z-10 space-y-0.5 text-center">
                            <span
                                class="rounded border border-slate-800 bg-slate-900/90 px-2 py-0.5 text-[9px] font-black tracking-widest text-slate-400 uppercase"
                                >Match Graphic Center</span
                            >
                            <h2
                                class="mt-1 text-sm font-black tracking-tight text-white uppercase drop-shadow-md"
                            >
                                {{
                                    selectedBookingShow
                                        ? selectedBookingShow.name
                                        : 'Special Exhibition'
                                }}
                            </h2>
                        </div>

                        <div
                            class="z-10 my-4 grid w-full grid-cols-7 items-center gap-2"
                        >
                            <!-- Competitor 1 with border indicating show brand color -->
                            <div
                                class="col-span-3 flex flex-col items-center space-y-2 text-center"
                            >
                                <div class="relative">
                                    <div
                                        class="absolute inset-0 z-10 rounded-xl bg-gradient-to-t from-slate-950 to-transparent opacity-80"
                                    ></div>
                                    <img
                                        :src="
                                            activeMatchPreview.comp1Img ||
                                            FALLBACK_USER_IMG
                                        "
                                        class="h-20 w-20 rounded-xl border-4 bg-slate-950 object-cover shadow-lg sm:h-24 sm:w-24"
                                        :style="{
                                            borderColor: selectedBookingShow
                                                ? selectedBookingShow.color
                                                : '#475569',
                                        }"
                                    />
                                </div>
                                <h3
                                    class="text-xs font-black tracking-wide text-white uppercase drop-shadow"
                                >
                                    {{ activeMatchPreview.comp1Name }}
                                </h3>
                            </div>

                            <div
                                class="col-span-1 flex flex-col items-center justify-center"
                            >
                                <span
                                    class="via-yellow-450 to-amber-550 bg-gradient-to-b from-amber-300 bg-clip-text text-xl font-black tracking-tighter text-transparent italic drop-shadow sm:text-2xl"
                                    >VS</span
                                >
                            </div>

                            <!-- Competitor 2 with border indicating show brand color -->
                            <div
                                class="col-span-3 flex flex-col items-center space-y-2 text-center"
                            >
                                <div class="relative">
                                    <div
                                        class="absolute inset-0 z-10 rounded-xl bg-gradient-to-t from-slate-950 to-transparent opacity-80"
                                    ></div>
                                    <img
                                        :src="
                                            activeMatchPreview.comp2Img ||
                                            FALLBACK_USER_IMG
                                        "
                                        class="h-20 w-20 rounded-xl border-4 bg-slate-950 object-cover shadow-lg sm:h-24 sm:w-24"
                                        :style="{
                                            borderColor: selectedBookingShow
                                                ? selectedBookingShow.color
                                                : '#475569',
                                        }"
                                    />
                                </div>
                                <h3
                                    class="text-xs font-black tracking-wide text-white uppercase drop-shadow"
                                >
                                    {{ activeMatchPreview.comp2Name }}
                                </h3>
                            </div>
                        </div>

                        <div
                            class="z-10 flex w-full max-w-xs items-center justify-between rounded-xl border border-slate-800/80 bg-slate-950/90 px-3 py-1.5 text-[10px]"
                        >
                            <span class="font-semibold text-slate-400 uppercase"
                                >{{
                                    activeMatchPreview.division
                                }}
                                Showcase</span
                            >
                            <span
                                class="flex items-center gap-1 font-bold text-amber-400"
                            >
                                <Sparkles class="h-3.5 w-3.5 animate-pulse" />
                                Live Broadcast
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
