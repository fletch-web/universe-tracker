<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { BookOpen, Tv } from '@lucide/vue';
import { ref, computed } from 'vue';
import type { Storyline } from '@/types';

const props = defineProps<{
    storylines: Storyline[];
    isReadOnly: boolean;
}>();

const activeStorylineId = ref<string | number>('NONE');
const storylineForm = useForm({
    name: '',
});

function route(name: string): string {
    if (name === 'storylines.store') {
        return '/storylines';
    }

    return '';
}

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

const handleCreateStoryline = () => {
    storylineForm.post(route('storylines.store'), {
        onSuccess: () => {
            storylineForm.reset();
        },
    });
};
</script>

<template>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Create narrative form -->
        <div
            class="h-fit space-y-6 rounded-2xl border border-slate-800 bg-slate-900/60 p-5"
        >
            <div v-if="!isReadOnly">
                <h3
                    class="mb-4 flex items-center gap-2 text-sm font-bold text-white"
                >
                    <BookOpen class="h-4 w-4 text-amber-400" /> Create Narrative Storyline
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
                            class="w-full rounded-xl border border-slate-800 bg-slate-955 px-4 py-2.5 text-xs text-white focus:border-amber-400 focus:outline-none"
                            placeholder="e.g., Bloodline Civil War"
                        />
                    </div>
                    <button
                        type="submit"
                        class="flex w-full items-center justify-center gap-1.5 rounded-xl bg-amber-400 px-4 py-2.5 text-xs font-bold text-slate-955 shadow transition-all hover:bg-amber-300"
                    >
                        <BookOpen class="h-4 w-4" /> Initiate Narrative Arc
                    </button>
                </form>
            </div>

            <div
                :class="isReadOnly ? '' : 'border-t border-slate-800 pt-5'"
            >
                <label
                    class="mb-1 block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                    >Select Storyline to View Timeline</label
                >
                <select
                    v-model="activeStorylineId"
                    class="w-full rounded-xl border border-slate-800 bg-slate-955 px-4 py-2.5 text-xs text-white focus:border-amber-400 focus:outline-none"
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
        <div
            class="space-y-4"
            :class="isReadOnly ? 'lg:col-span-3' : 'lg:col-span-2'"
        >
            <h3
                class="flex items-center justify-between text-sm font-bold text-white"
            >
                <span class="flex items-center gap-2"
                    ><BookOpen class="text-slate-450 h-4 w-4" /> Chronological Event Arc Timeline</span
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
                    Select or initialize a storyline arc timeline vector above to render segments logs.
                </div>
                <div
                    v-else-if="
                        !activeStoryline.events ||
                        activeStoryline.events.length === 0
                    "
                    class="py-12 text-center text-xs text-slate-500"
                >
                    This storyline holds no recorded data. Schedule bouts linked to this arc in the Booking engine tab.
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
                            class="flex-1 space-y-2 rounded-xl border border-slate-800 bg-slate-955 p-4"
                        >
                            <div
                                class="flex flex-wrap items-center justify-between text-[10px] text-slate-500"
                            >
                                <span
                                    class="flex items-center gap-1 font-bold text-white"
                                >
                                    <Tv class="h-3.5 w-3.5 text-slate-400" />
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
</template>
