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
// Define TS interfaces for our database records passed from controller
interface Show {
    id: number;
    name: string;
    color: string;
    image: string | null;
    is_ple?: boolean;
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
    division: 'Singles' | 'TagTeam' | 'TripleThreat' | 'Fatal4Way' | 'TripleThreatTag' | 'Fatal4WayTag' | 'ThreeOnThreeTag' | 'FourOnFourTag' | 'Segment';
    c1_superstar_id: number | null;
    c2_superstar_id: number | null;
    c1_team_id: number | null;
    c2_team_id: number | null;
    team1_superstar_ids?: number[] | null;
    team2_superstar_ids?: number[] | null;
    outcome: 'Decisive' | 'Draw' | 'Segment';
    winner_slot: '1' | '2' | '3' | '4' | null;
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
    is_ple: false,
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
    showForm.is_ple = !!show.is_ple;
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
    division: 'Singles' | 'TagTeam' | 'TripleThreat' | 'Fatal4Way' | 'TripleThreatTag' | 'Fatal4WayTag' | 'ThreeOnThreeTag' | 'FourOnFourTag' | 'Segment';
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

const bookingShowSelect = ref<string | number>('');
const bookingDate = ref(new Date().toISOString().split('T')[0]);
const bookingMatchType = ref<'Singles' | 'TagTeam' | 'TripleThreat' | 'Fatal4Way' | 'TripleThreatTag' | 'Fatal4WayTag' | 'ThreeOnThreeTag' | 'FourOnFourTag' | 'Segment'>('Singles');
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
const bookingStipulationType = ref<string>('Normal');
const bookingCustomStipulation = ref<string>('');

const WWE_2K26_MATCH_TYPES = [
    'Normal',
    'Steel Cage',
    'Hell in a Cell',
    'Ladder',
    'Table',
    'TLC',
    'Extreme Rules',
    'Falls Count Anywhere',
    'Iron Man',
    'Submission',
    'Last Man Standing',
    'Backstage Brawl',
    'Ambulance',
    'Casket',
    'Special Guest Referee',
    'Elimination Chamber',
    'Royal Rumble',
    'WarGames',
    'Handicap',
    'Triple Threat',
    'Fatal 4-Way',
    'CUSTOM',
];

const stagedMatches = ref<StagedMatch[]>([]);
const activeMatchPreview = ref<StagedMatch | null>(null);

// Competitors list based on selected Booking Show & Match type
const bookingCompetitors1 = computed(() => {
    if (!bookingShowSelect.value) {
        return [];
    }

    const isTeamMatch = ['TagTeam', 'TripleThreatTag', 'Fatal4WayTag', 'ThreeOnThreeTag', 'FourOnFourTag'].includes(bookingMatchType.value);
    const isFactionBased = isTeamMatch && !(['TagTeam', 'ThreeOnThreeTag', 'FourOnFourTag'].includes(bookingMatchType.value) && bookingTagTeamType.value === 'AdHoc');

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
    const isTeamMatch = ['TagTeam', 'TripleThreatTag', 'Fatal4WayTag', 'ThreeOnThreeTag', 'FourOnFourTag'].includes(bookingMatchType.value);
    const isFactionBased = isTeamMatch && !(['TagTeam', 'ThreeOnThreeTag', 'FourOnFourTag'].includes(bookingMatchType.value) && bookingTagTeamType.value === 'AdHoc');

    const isPLE = !!selectedBookingShow.value?.is_ple;

    if (isTeamMatch && isFactionBased) {
        return props.championships.filter(
            (c) => (isPLE || c.show_id === Number(bookingShowSelect.value)) && c.type === 'TagTeam'
        );
    } else if (!isTeamMatch) {
        return props.championships.filter(
            (c) => (isPLE || c.show_id === Number(bookingShowSelect.value)) && c.type === 'Singles'
        );
    }
    return [];
});

const isTeamMatch = computed(() => {
    return ['TagTeam', 'TripleThreatTag', 'Fatal4WayTag', 'ThreeOnThreeTag', 'FourOnFourTag'].includes(bookingMatchType.value);
});

const isFactionBased = computed(() => {
    return isTeamMatch.value && !(['TagTeam', 'ThreeOnThreeTag', 'FourOnFourTag'].includes(bookingMatchType.value) && bookingTagTeamType.value === 'AdHoc');
});

const getSuperstarsFromIds = (ids?: number[]) => {
    if (!ids) return [];
    return ids.map(id => props.superstars.find(s => s.id === id)).filter(Boolean);
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
        alert(
            'Initialize an active broadcast show profile context loop first.',
        );

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

    const needComp3 = ['TripleThreat', 'TripleThreatTag', 'Fatal4Way', 'Fatal4WayTag'].includes(bookingMatchType.value);
    const needComp4 = ['Fatal4Way', 'Fatal4WayTag'].includes(bookingMatchType.value);

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
    let c1Id = 0, c2Id = 0, c3Id = 0, c4Id = 0;

    if (isTeamMatchVal && !isFactionBasedVal) {
        // Ad-Hoc Tag Team / 3-on-3 Tag / 4-on-4 Tag
        const expectedCount = bookingMatchType.value === 'TagTeam' ? 2 : (bookingMatchType.value === 'ThreeOnThreeTag' ? 3 : 4);
        
        if (bookingTeam1Superstars.value.length !== expectedCount || bookingTeam2Superstars.value.length !== expectedCount) {
            alert(`Select exactly ${expectedCount} superstars for each team.`);
            return;
        }

        // Check if any selection is empty
        if (bookingTeam1Superstars.value.some(id => !id) || bookingTeam2Superstars.value.some(id => !id)) {
            alert('Verify competing entries exist within selected show roster limits.');
            return;
        }

        const selectedIds = [...bookingTeam1Superstars.value, ...bookingTeam2Superstars.value].map(Number);
        const uniqueIds = new Set(selectedIds);
        if (uniqueIds.size !== selectedIds.length) {
            alert('An element cannot fight against its exact mirror clone instance entry.');
            return;
        }

        const getSuperstarsName = (ids: (number | string)[]) => {
            return ids.map(id => props.superstars.find(s => s.id === Number(id))?.name || '').filter(Boolean).join(' & ');
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
        if (!bookingComp1.value || !bookingComp2.value || (needComp3 && !bookingComp3.value) || (needComp4 && !bookingComp4.value)) {
            alert('Verify competing entries exist within selected show roster limits.');
            return;
        }

        const selectedIds = [Number(bookingComp1.value), Number(bookingComp2.value)];
        if (needComp3) selectedIds.push(Number(bookingComp3.value));
        if (needComp4) selectedIds.push(Number(bookingComp4.value));

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
        if (needComp3) c3Id = Number(bookingComp3.value);
        if (needComp4) c4Id = Number(bookingComp4.value);

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
    if (bookingIsTitleMatch.value && bookingChampionshipId.value !== 'NONE') {
        const champ = props.championships.find((c) => c.id === Number(bookingChampionshipId.value));
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

    let stipulation = '';
    if (bookingStipulationType.value === 'CUSTOM') {
        stipulation = bookingCustomStipulation.value.trim();
    } else if (bookingStipulationType.value !== 'Normal') {
        stipulation = bookingStipulationType.value;
    }

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
        winnerSlot: bookingOutcome.value === 'Decisive' ? bookingWinner.value : null,
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
    bookingCustomStipulation.value = '';
    bookingStipulationType.value = 'Normal';
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
const logout = () => {
    router.post('/logout');
};

function route(name: string, param?: string | number): string {
    switch (name) {
        case 'shows.store':
            return '/shows';
        case 'shows.update':
            return `/shows/${param}`;
        case 'shows.destroy':
            return `/shows/${param}`;
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
        case 'championships.store':
            return '/championships';
        case 'championships.update':
            return `/championships/${param}`;
        case 'championships.destroy':
            return `/championships/${param}`;
        case 'storylines.store':
            return '/storylines';
        case 'booking.commit':
            return '/booking/commit';
        default:
            return '';
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
            class="flex flex-col gap-4 rounded-2xl border border-slate-800 bg-slate-900/40 p-4 backdrop-blur"
        >
            <div
                class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-800/60 pb-3"
            >
                <div class="flex items-center space-x-3.5 pl-2">
                    <div
                        class="flex items-center justify-center rounded-xl bg-gradient-to-tr from-amber-500 to-yellow-400 p-2 text-xl font-black tracking-wider text-slate-950 shadow-lg shadow-amber-500/10"
                    >
                        <Tv class="h-6 w-6" />
                    </div>
                    <div>
                        <h1
                            class="text-base leading-tight font-black text-white"
                        >
                            Universe Tracker
                        </h1>
                        <p class="text-[10px] tracking-wide text-slate-400">
                            Elite Booking & Metrics engine
                        </p>
                    </div>
                </div>

                <!-- User actions -->
                <div class="flex items-center gap-3">
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
                        @click="logout"
                        class="flex items-center gap-1 rounded-lg border border-red-900/60 bg-red-950/40 px-2.5 py-1.5 text-[11px] font-semibold text-red-400 transition hover:bg-red-950 hover:text-red-300"
                    >
                        <UserX class="h-3.5 w-3.5" />
                        Sign Out
                    </button>
                </div>
            </div>

            <nav class="flex flex-wrap gap-1.5 pl-2">
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
                                            <Trophy class="h-1.5 w-1.5" /> {{ m.championship.name }}
                                        </span>
                                        <span
                                            v-if="m.stipulation"
                                            class="mr-1 font-bold text-amber-400"
                                            >[{{ m.stipulation }}]</span
                                        >
                                        <template v-if="m.team1_superstar_ids && m.team1_superstar_ids.length > 0">
                                            <span class="text-slate-250 font-bold">{{
                                                getSuperstarsFromIds(m.team1_superstar_ids).map(s => s.name).join(' & ')
                                            }}</span>
                                            vs
                                            <span class="text-slate-250 font-bold">{{
                                                getSuperstarsFromIds(m.team2_superstar_ids).map(s => s.name).join(' & ')
                                            }}</span>
                                            <span
                                                v-if="m.outcome === 'Decisive'"
                                                class="ml-1 text-emerald-400"
                                                >({{
                                                    m.winner_slot === '1'
                                                        ? getSuperstarsFromIds(m.team1_superstar_ids).map(s => s.name).join(' & ')
                                                        : getSuperstarsFromIds(m.team2_superstar_ids).map(s => s.name).join(' & ')
                                                }}
                                                wins)</span
                                            >
                                            <span v-else class="ml-1 text-amber-500"
                                                >(Draw)</span
                                            >
                                        </template>
                                        <template v-else>
                                            <span class="text-slate-200">{{
                                                ['Singles', 'TripleThreat', 'Fatal4Way'].includes(m.division)
                                                    ? m.c1_superstar?.name
                                                    : m.c1_team?.name
                                            }}</span>
                                            vs
                                            <span class="text-slate-200">{{
                                                ['Singles', 'TripleThreat', 'Fatal4Way'].includes(m.division)
                                                    ? m.c2_superstar?.name
                                                    : m.c2_team?.name
                                            }}</span>
                                            <template v-if="['TripleThreat', 'Fatal4Way', 'TripleThreatTag', 'Fatal4WayTag'].includes(m.division)">
                                                vs
                                                <span class="text-slate-200">{{
                                                    ['Singles', 'TripleThreat', 'Fatal4Way'].includes(m.division)
                                                        ? m.c3_superstar?.name
                                                        : m.c3_team?.name
                                                }}</span>
                                            </template>
                                            <template v-if="['Fatal4Way', 'Fatal4WayTag'].includes(m.division)">
                                                vs
                                                <span class="text-slate-200">{{
                                                    ['Singles', 'TripleThreat', 'Fatal4Way'].includes(m.division)
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
                                            <span v-else class="ml-1 text-amber-500"
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
                    <!-- PLE Switch Toggle -->
                    <div class="flex items-center gap-2 rounded-xl border border-slate-800 bg-slate-950/60 p-3">
                        <input
                            type="checkbox"
                            id="is_ple_checkbox"
                            v-model="showForm.is_ple"
                            class="h-4 w-4 rounded border-slate-700 bg-slate-900 text-amber-500 focus:ring-amber-500 focus:ring-offset-slate-950 focus:outline-none"
                        />
                        <div class="flex flex-col">
                            <label for="is_ple_checkbox" class="text-xs font-bold text-slate-200 cursor-pointer">
                                Premium Live Event (PLE) Show
                            </label>
                            <span class="text-[9px] font-medium text-slate-505">
                                Bypasses brand roster limits to book any superstar & title.
                            </span>
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
                                <div class="flex items-center gap-2">
                                    <h4
                                        class="truncate text-sm font-bold text-white"
                                    >
                                        {{ show.name }}
                                    </h4>
                                    <span
                                        v-if="show.is_ple"
                                        class="rounded bg-amber-400/10 border border-amber-400/20 px-1.5 py-0.5 text-[8px] font-black text-amber-450 uppercase tracking-wider"
                                    >
                                        PLE
                                    </span>
                                </div>
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
                                            v-for="sh in shows.filter(s => !s.is_ple)"
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
                        Stage New Card Slot
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
                                Tag Team (2 vs 2)
                            </option>
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
                    <div v-if="['TagTeam', 'ThreeOnThreeTag', 'FourOnFourTag'].includes(bookingMatchType)">
                        <label class="mb-1.5 block text-[10px] font-bold tracking-wider text-slate-450 uppercase">Tag Team Booking Mode</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                type="button"
                                @click="bookingTagTeamType = 'Faction'"
                                :class="[
                                    'rounded-xl border px-3 py-2 text-xs font-bold transition-all',
                                    bookingTagTeamType === 'Faction'
                                        ? 'border-amber-400 bg-amber-400/10 text-amber-400'
                                        : 'border-slate-800 bg-slate-950 text-slate-450 hover:border-slate-700'
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
                                        : 'border-slate-800 bg-slate-950 text-slate-450 hover:border-slate-700'
                                ]"
                            >
                                Ad-Hoc Superstars
                            </button>
                        </div>
                    </div>

                    <!-- Match Stipulation Selector -->
                    <div v-if="bookingMatchType !== 'Segment'">
                        <label
                            class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >Match Type & Stipulation (WWE 2K26)</label
                        >
                        <select
                            v-model="bookingStipulationType"
                            class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                        >
                            <option
                                v-for="stip in WWE_2K26_MATCH_TYPES"
                                :key="stip"
                                :value="stip"
                            >
                                {{ stip }}
                            </option>
                        </select>
                    </div>

                    <!-- Freetype Custom Stipulation description input -->
                    <div v-if="bookingMatchType !== 'Segment' && bookingStipulationType === 'CUSTOM'">
                        <label
                            class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >Custom Stipulation Description</label
                        >
                        <input
                            type="text"
                            v-model="bookingCustomStipulation"
                            placeholder="e.g. Inferno Match, Submission Match, I Quit..."
                            class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                        />
                    </div>

                    <!-- Competitors Grid Inputs -->
                    <div v-if="bookingMatchType !== 'Segment'">
                        <!-- Ad-Hoc Tag Team / 3-on-3 Tag / 4-on-4 Tag Superstar Selection Grid -->
                        <div v-if="isTeamMatch && !isFactionBased" class="space-y-4">
                            <!-- Team 1 (Blue Corner) -->
                            <div class="space-y-2 rounded-xl border border-slate-800 bg-slate-950/70 p-3">
                                <h5 class="text-[9px] font-black tracking-wider text-blue-400 uppercase">Team 1 / Blue Corner</h5>
                                <div v-for="idx in (bookingMatchType === 'TagTeam' ? 2 : (bookingMatchType === 'ThreeOnThreeTag' ? 3 : 4))" :key="'t1-s-' + idx">
                                    <label class="mb-0.5 block text-[8px] font-bold text-slate-500 uppercase">Team 1 Member #{{ idx }}</label>
                                    <select
                                        v-model="bookingTeam1Superstars[idx - 1]"
                                        class="w-full rounded-lg border border-slate-850 bg-slate-900 px-3 py-1.5 text-xs text-white focus:border-amber-400 focus:outline-none"
                                    >
                                        <option value="" disabled>Select Superstar</option>
                                        <option v-for="s in bookingCompetitors1" :key="s.id" :value="s.id">{{ s.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Team 2 (Red Corner) -->
                            <div class="space-y-2 rounded-xl border border-slate-800 bg-slate-950/70 p-3">
                                <h5 class="text-[9px] font-black tracking-wider text-rose-400 uppercase">Team 2 / Red Corner</h5>
                                <div v-for="idx in (bookingMatchType === 'TagTeam' ? 2 : (bookingMatchType === 'ThreeOnThreeTag' ? 3 : 4))" :key="'t2-s-' + idx">
                                    <label class="mb-0.5 block text-[8px] font-bold text-slate-500 uppercase">Team 2 Member #{{ idx }}</label>
                                    <select
                                        v-model="bookingTeam2Superstars[idx - 1]"
                                        class="w-full rounded-lg border border-slate-850 bg-slate-900 px-3 py-1.5 text-xs text-white focus:border-amber-400 focus:outline-none"
                                    >
                                        <option value="" disabled>Select Superstar</option>
                                        <option v-for="s in bookingCompetitors1" :key="s.id" :value="s.id">{{ s.name }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Faction-based or standard superstar selections -->
                        <div v-else class="grid grid-cols-1 gap-2">
                            <!-- Competitor 1 -->
                            <div>
                                <label
                                    class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    {{
                                        isTeamMatch
                                            ? 'Faction Unit #1 (Blue Corner)'
                                            : 'Competitor #1 / Blue Corner'
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
                            <!-- Competitor 2 -->
                            <div>
                                <label
                                    class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    {{
                                        isTeamMatch
                                            ? 'Faction Unit #2 (Red Corner)'
                                            : 'Competitor #2 / Red Corner'
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
                            <!-- Competitor 3 (Triple Threat or Fatal 4-Way Factions/Superstars) -->
                            <div v-if="['TripleThreat', 'TripleThreatTag', 'Fatal4Way', 'Fatal4WayTag'].includes(bookingMatchType)">
                                <label
                                    class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    {{
                                        isTeamMatch
                                            ? 'Faction Unit #3 (Purple Corner)'
                                            : 'Competitor #3 / Purple Corner'
                                    }}
                                </label>
                                <select
                                    v-model="bookingComp3"
                                    class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                                >
                                    <option value="" disabled>
                                        Select Competitor 3
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
                            <!-- Competitor 4 (Fatal 4-Way Factions/Superstars only) -->
                            <div v-if="['Fatal4Way', 'Fatal4WayTag'].includes(bookingMatchType)">
                                <label
                                    class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    {{
                                        isTeamMatch
                                            ? 'Faction Unit #4 (Green Corner)'
                                            : 'Competitor #4 / Green Corner'
                                    }}
                                </label>
                                <select
                                    v-model="bookingComp4"
                                    class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                                >
                                    <option value="" disabled>
                                        Select Competitor 4
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
                    </div>

                    <div v-if="bookingMatchType !== 'Segment'" class="grid grid-cols-2 gap-2">
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
                                <option value="1">
                                    {{ (isTeamMatch && !isFactionBased) ? 'Team 1 (Blue Corner)' : 'Competitor 1 (Blue)' }}
                                </option>
                                <option value="2">
                                    {{ (isTeamMatch && !isFactionBased) ? 'Team 2 (Red Corner)' : 'Competitor 2 (Red)' }}
                                </option>
                                <option v-if="!isTeamMatch && ['TripleThreat', 'Fatal4Way'].includes(bookingMatchType)" value="3">
                                    Competitor 3 (Purple)
                                </option>
                                <option v-if="!isTeamMatch && bookingMatchType === 'Fatal4Way'" value="4">
                                    Competitor 4 (Green)
                                </option>
                                <option v-if="isFactionBased && ['TripleThreatTag', 'Fatal4WayTag'].includes(bookingMatchType)" value="3">
                                    Competitor 3 (Purple)
                                </option>
                                <option v-if="isFactionBased && bookingMatchType === 'Fatal4WayTag'" value="4">
                                    Competitor 4 (Green)
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Narrative & Championship link details card -->
                    <div
                        class="space-y-2 rounded-xl border border-slate-800 bg-slate-950 p-3"
                    >
                        <!-- Championship match parameters -->
                        <div v-if="bookingMatchType !== 'Segment'">
                            <div class="flex items-center gap-2 mb-1.5">
                                <input
                                    type="checkbox"
                                    id="isTitleMatch"
                                    v-model="bookingIsTitleMatch"
                                    class="rounded border-slate-800 bg-slate-900 text-amber-400 focus:ring-amber-500/30 focus:ring-offset-0 h-4 w-4"
                                />
                                <label
                                    for="isTitleMatch"
                                    class="text-[10px] font-bold tracking-wider text-amber-400 uppercase cursor-pointer"
                                    >Championship Title Match</label
                                >
                            </div>
                            <select
                                v-if="bookingIsTitleMatch"
                                v-model="bookingChampionshipId"
                                class="w-full rounded-lg border border-slate-800 bg-slate-900 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                            >
                                <option value="NONE">
                                    Select Championship Title
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
                                >{{
                                    bookingMatchType === 'Segment'
                                        ? 'Segment Script Details (Freetype)'
                                        : 'Script Narrative Booking Notes'
                                }}</label
                            >
                            <textarea
                                v-model="bookingNotes"
                                rows="2"
                                class="placeholder-slate-650 w-full rounded-lg border border-slate-800 bg-slate-900 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                                :placeholder="
                                    bookingMatchType === 'Segment'
                                        ? 'Describe promo segment, backstage video vignette, contract signing, or brawl details...'
                                        : 'Describe segments, surprise run-ins, post-match beatdowns, or title changes...'
                                "
                            ></textarea>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="addMatchToStagingCard"
                        class="flex w-full items-center justify-center gap-1.5 rounded-xl bg-amber-400 px-4 py-2.5 text-xs font-bold text-slate-950 shadow transition-all hover:bg-amber-300"
                    >
                        <PlusCircle class="h-4 w-4" /> Stage
                        {{
                            bookingMatchType === 'Segment' ? 'Segment' : 'Match'
                        }}
                        To Card
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
                                        >{{
                                            m.division === 'Segment'
                                                ? 'SEGMENT'
                                                : 'BOUT #0' + (index + 1)
                                        }}</span
                                    >
                                    <span
                                        class="text-slate-450 text-[10px] font-semibold"
                                        >{{
                                            m.division === 'Segment'
                                                ? 'Storyline Segment'
                                                : m.division + ' Match'
                                        }}</span
                                    >
                                </div>
                                <h4 class="text-xs font-black text-white">
                                    <template v-if="m.division === 'Segment'">
                                        {{ m.notes }}
                                    </template>
                                    <template v-else>
                                        <span
                                            v-if="m.championshipName"
                                            class="mr-1 inline-flex items-center gap-1 rounded bg-amber-400 px-1.5 py-0.5 text-[8px] font-black text-slate-950 uppercase tracking-wider"
                                        >
                                            <Trophy class="h-2 w-2" /> {{ m.championshipName }}
                                        </span>
                                        <span
                                            v-if="m.stipulation"
                                            class="mr-1 font-bold text-amber-400"
                                            >[{{ m.stipulation }}]</span
                                        >
                                        {{ m.comp1Name }}
                                        <span
                                            class="px-1 font-black text-amber-400"
                                            >VS</span
                                        >
                                        {{ m.comp2Name }}
                                        <template v-if="m.comp3Name">
                                            <span class="px-1 font-black text-amber-400">VS</span>
                                            {{ m.comp3Name }}
                                        </template>
                                        <template v-if="m.comp4Name">
                                            <span class="px-1 font-black text-amber-400">VS</span>
                                            {{ m.comp4Name }}
                                        </template>
                                    </template>
                                </h4>
                                <p
                                    v-if="m.division !== 'Segment'"
                                    class="text-[10px] text-slate-400"
                                >
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

                        <div
                            class="z-10 flex flex-col items-center space-y-0.5 text-center"
                        >
                            <span
                                class="rounded border border-slate-800 bg-slate-900/90 px-2 py-0.5 text-[9px] font-black tracking-widest text-slate-400 uppercase"
                                >{{
                                    activeMatchPreview.division === 'Segment'
                                        ? 'Segment Highlight'
                                        : 'Match Graphic Center'
                                }}</span
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
                            <span
                                v-if="activeMatchPreview.championshipName"
                                class="mt-1 inline-flex items-center gap-1 rounded bg-amber-400 px-2 py-0.5 text-[9px] font-black text-slate-950 uppercase tracking-widest"
                            >
                                <Trophy class="h-3 w-3" /> {{ activeMatchPreview.championshipName }} Championship Match
                            </span>
                            <span
                                v-if="activeMatchPreview.stipulation"
                                class="mt-1.5 inline-block rounded border border-amber-400/20 bg-amber-400/15 px-2 py-0.5 text-[9px] font-bold tracking-wider text-amber-400 uppercase"
                            >
                                {{ activeMatchPreview.stipulation }}
                            </span>
                        </div>

                        <!-- Segment Highlight Text -->
                        <div
                            v-if="activeMatchPreview.division === 'Segment'"
                            class="z-10 my-4 flex w-full flex-col items-center px-4 text-center"
                        >
                            <span
                                class="mb-3 rounded border border-amber-400/20 bg-amber-400/10 px-2.5 py-1 text-[10px] font-bold tracking-widest text-amber-400 uppercase"
                            >
                                Storyline Segment Promo
                            </span>
                            <p
                                class="text-slate-350 max-w-sm text-xs leading-relaxed font-medium italic"
                            >
                                "{{ activeMatchPreview.notes }}"
                            </p>
                        </div>

                        <!-- Match VS layout -->
                        <div
                            v-else
                            class="z-10 my-4 w-full"
                        >
                            <!-- Ad-Hoc / Multi-Man Tag Team Layout (6-man or 8-man) -->
                            <div v-if="activeMatchPreview.team1_superstar_ids" class="grid grid-cols-7 gap-2 items-center w-full">
                                <!-- Team 1 -->
                                <div class="col-span-3 flex flex-col items-center space-y-2">
                                    <div class="flex flex-wrap justify-center gap-1.5 max-w-[150px]">
                                        <div v-for="s in getSuperstarsFromIds(activeMatchPreview.team1_superstar_ids)" :key="s.id" class="relative group">
                                            <img
                                                :src="s.image || FALLBACK_USER_IMG"
                                                class="h-10 w-10 rounded-full border bg-slate-950 object-cover shadow-sm transition hover:scale-105"
                                                :style="{ borderColor: selectedBookingShow ? selectedBookingShow.color : '#475569' }"
                                            />
                                        </div>
                                    </div>
                                    <h3 class="text-[10px] font-black tracking-wide text-white uppercase text-center max-w-[120px] line-clamp-2">
                                        {{ activeMatchPreview.comp1Name }}
                                    </h3>
                                </div>

                                <!-- VS divider -->
                                <div class="col-span-1 flex flex-col items-center justify-center">
                                    <span class="via-yellow-450 to-amber-550 bg-gradient-to-b from-amber-300 bg-clip-text text-xl font-black tracking-tighter text-transparent italic drop-shadow sm:text-2xl">VS</span>
                                </div>

                                <!-- Team 2 -->
                                <div class="col-span-3 flex flex-col items-center space-y-2">
                                    <div class="flex flex-wrap justify-center gap-1.5 max-w-[150px]">
                                        <div v-for="s in getSuperstarsFromIds(activeMatchPreview.team2_superstar_ids)" :key="s.id" class="relative group">
                                            <img
                                                :src="s.image || FALLBACK_USER_IMG"
                                                class="h-10 w-10 rounded-full border bg-slate-950 object-cover shadow-sm transition hover:scale-105"
                                                :style="{ borderColor: selectedBookingShow ? selectedBookingShow.color : '#475569' }"
                                            />
                                        </div>
                                    </div>
                                    <h3 class="text-[10px] font-black tracking-wide text-white uppercase text-center max-w-[120px] line-clamp-2">
                                        {{ activeMatchPreview.comp2Name }}
                                    </h3>
                                </div>
                            </div>

                            <!-- Triple Threat (3-Way) Layout -->
                            <div v-else-if="['TripleThreat', 'TripleThreatTag'].includes(activeMatchPreview.division)" class="grid grid-cols-3 gap-3 items-center text-center">
                                <!-- Comp 1 -->
                                <div class="flex flex-col items-center space-y-1.5">
                                    <div class="relative">
                                        <div class="absolute inset-0 z-10 rounded-xl bg-gradient-to-t from-slate-950 to-transparent opacity-80"></div>
                                        <img
                                            :src="activeMatchPreview.comp1Img || FALLBACK_USER_IMG"
                                            class="h-16 w-16 rounded-xl border-2 bg-slate-950 object-cover shadow-md"
                                            :style="{ borderColor: selectedBookingShow ? selectedBookingShow.color : '#475569' }"
                                        />
                                    </div>
                                    <span class="text-[10px] font-bold text-white uppercase truncate max-w-[80px]">{{ activeMatchPreview.comp1Name }}</span>
                                </div>
                                <!-- Comp 2 -->
                                <div class="flex flex-col items-center space-y-1.5">
                                    <div class="relative">
                                        <div class="absolute inset-0 z-10 rounded-xl bg-gradient-to-t from-slate-950 to-transparent opacity-80"></div>
                                        <img
                                            :src="activeMatchPreview.comp2Img || FALLBACK_USER_IMG"
                                            class="h-16 w-16 rounded-xl border-2 bg-slate-950 object-cover shadow-md"
                                            :style="{ borderColor: selectedBookingShow ? selectedBookingShow.color : '#475569' }"
                                        />
                                    </div>
                                    <span class="text-[10px] font-bold text-white uppercase truncate max-w-[80px]">{{ activeMatchPreview.comp2Name }}</span>
                                </div>
                                <!-- Comp 3 -->
                                <div class="flex flex-col items-center space-y-1.5">
                                    <div class="relative">
                                        <div class="absolute inset-0 z-10 rounded-xl bg-gradient-to-t from-slate-950 to-transparent opacity-80"></div>
                                        <img
                                            :src="activeMatchPreview.comp3Img || FALLBACK_USER_IMG"
                                            class="h-16 w-16 rounded-xl border-2 bg-slate-950 object-cover shadow-md"
                                            :style="{ borderColor: selectedBookingShow ? selectedBookingShow.color : '#475569' }"
                                        />
                                    </div>
                                    <span class="text-[10px] font-bold text-white uppercase truncate max-w-[80px]">{{ activeMatchPreview.comp3Name }}</span>
                                </div>
                            </div>

                            <!-- Fatal 4-Way (4-Way) Layout -->
                            <div v-else-if="['Fatal4Way', 'Fatal4WayTag'].includes(activeMatchPreview.division)" class="grid grid-cols-4 gap-2 items-center text-center">
                                <!-- Comp 1 -->
                                <div class="flex flex-col items-center space-y-1.5">
                                    <div class="relative">
                                        <div class="absolute inset-0 z-10 rounded-xl bg-gradient-to-t from-slate-950 to-transparent opacity-80"></div>
                                        <img
                                            :src="activeMatchPreview.comp1Img || FALLBACK_USER_IMG"
                                            class="h-14 w-14 rounded-xl border-2 bg-slate-950 object-cover shadow-md"
                                            :style="{ borderColor: selectedBookingShow ? selectedBookingShow.color : '#475569' }"
                                        />
                                    </div>
                                    <span class="text-[9px] font-bold text-white uppercase truncate max-w-[70px]">{{ activeMatchPreview.comp1Name }}</span>
                                </div>
                                <!-- Comp 2 -->
                                <div class="flex flex-col items-center space-y-1.5">
                                    <div class="relative">
                                        <div class="absolute inset-0 z-10 rounded-xl bg-gradient-to-t from-slate-950 to-transparent opacity-80"></div>
                                        <img
                                            :src="activeMatchPreview.comp2Img || FALLBACK_USER_IMG"
                                            class="h-14 w-14 rounded-xl border-2 bg-slate-950 object-cover shadow-md"
                                            :style="{ borderColor: selectedBookingShow ? selectedBookingShow.color : '#475569' }"
                                        />
                                    </div>
                                    <span class="text-[9px] font-bold text-white uppercase truncate max-w-[70px]">{{ activeMatchPreview.comp2Name }}</span>
                                </div>
                                <!-- Comp 3 -->
                                <div class="flex flex-col items-center space-y-1.5">
                                    <div class="relative">
                                        <div class="absolute inset-0 z-10 rounded-xl bg-gradient-to-t from-slate-950 to-transparent opacity-80"></div>
                                        <img
                                            :src="activeMatchPreview.comp3Img || FALLBACK_USER_IMG"
                                            class="h-14 w-14 rounded-xl border-2 bg-slate-950 object-cover shadow-md"
                                            :style="{ borderColor: selectedBookingShow ? selectedBookingShow.color : '#475569' }"
                                        />
                                    </div>
                                    <span class="text-[9px] font-bold text-white uppercase truncate max-w-[70px]">{{ activeMatchPreview.comp3Name }}</span>
                                </div>
                                <!-- Comp 4 -->
                                <div class="flex flex-col items-center space-y-1.5">
                                    <div class="relative">
                                        <div class="absolute inset-0 z-10 rounded-xl bg-gradient-to-t from-slate-950 to-transparent opacity-80"></div>
                                        <img
                                            :src="activeMatchPreview.comp4Img || FALLBACK_USER_IMG"
                                            class="h-14 w-14 rounded-xl border-2 bg-slate-950 object-cover shadow-md"
                                            :style="{ borderColor: selectedBookingShow ? selectedBookingShow.color : '#475569' }"
                                        />
                                    </div>
                                    <span class="text-[9px] font-bold text-white uppercase truncate max-w-[70px]">{{ activeMatchPreview.comp4Name }}</span>
                                </div>
                            </div>

                            <!-- Standard 1v1 / 2v2 Layout -->
                            <div v-else class="grid grid-cols-7 items-center gap-2">
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
                        </div>

                        <div
                            class="z-10 flex w-full max-w-xs items-center justify-between rounded-xl border border-slate-800/80 bg-slate-950/90 px-3 py-1.5 text-[10px]"
                        >
                            <span class="font-semibold text-slate-400 uppercase"
                                >{{
                                    activeMatchPreview.division === 'Segment'
                                        ? 'Storyline Segment'
                                        : activeMatchPreview.division + ' Match'
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
