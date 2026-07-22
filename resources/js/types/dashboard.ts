export interface Show {
    id: number;
    name: string;
    color: string;
    image: string | null;
    is_ple?: boolean;
}

export interface Superstar {
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

export interface Team {
    id: number;
    name: string;
    wins: number;
    losses: number;
    draws: number;
    superstars?: Superstar[];
}

export interface Championship {
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

export interface StorylineEvent {
    id: number;
    storyline_id: number;
    date: string;
    show_name: string;
    description: string;
    notes: string | null;
}

export interface Storyline {
    id: number;
    name: string;
    events?: StorylineEvent[];
}

export interface MatchLog {
    id: number;
    show_log_id: number;
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
    championship?: Championship;
    stipulation?: string;
    c3_superstar?: Superstar;
    c4_superstar?: Superstar;
    c3_team?: Team;
    c4_team?: Team;
}

export interface ShowLog {
    id: number;
    show_id: number | null;
    date: string;
    location?: string | null;
    show?: Show;
    matches?: MatchLog[];
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    next_page_url: string | null;
    prev_page_url: string | null;
}
