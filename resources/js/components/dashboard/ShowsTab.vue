<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3';
import { Plus, Tv, Pencil, Trash } from '@lucide/vue';
import { ref } from 'vue';
import type { Show } from '@/types';

defineProps<{
    shows: Show[];
    isReadOnly: boolean;
}>();

const FALLBACK_SHOW_IMG =
    "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'><rect width='100%' height='100%' fill='%231e293b'/><text x='50%' y='55%' font-size='12' font-family='sans-serif' font-weight='bold' fill='%2364748b' text-anchor='middle'>SHOW LOGO</text></svg>";

const editShowId = ref<number | null>(null);

const showForm = useForm({
    name: '',
    color: '#e11d48',
    image: null as string | null,
    is_ple: false,
});

function route(name: string, param?: string | number): string {
    switch (name) {
        case 'shows.store':
            return '/shows';
        case 'shows.update':
            return `/shows/${param}`;
        case 'shows.destroy':
            return `/shows/${param}`;
        default:
            return '';
    }
}

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
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Create Show Form -->
        <div
            v-if="!isReadOnly"
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
                <div
                    class="flex items-center gap-2 rounded-xl border border-slate-800 bg-slate-950/60 p-3"
                >
                    <input
                        type="checkbox"
                        id="is_ple_checkbox"
                        v-model="showForm.is_ple"
                        class="h-4 w-4 rounded border-slate-700 bg-slate-900 text-amber-500 focus:ring-amber-500 focus:ring-offset-slate-950 focus:outline-none"
                    />
                    <div class="flex flex-col">
                        <label
                            for="is_ple_checkbox"
                            class="cursor-pointer text-xs font-bold text-slate-200"
                        >
                            Premium Live Event (PLE) Show
                        </label>
                        <span class="text-slate-505 text-[9px] font-medium">
                            Bypasses brand roster limits to book any
                            superstar & title.
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
                        class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-amber-400 px-4 py-2.5 text-xs font-bold text-slate-955 shadow transition-all hover:bg-amber-300"
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
        <div
            class="space-y-4"
            :class="isReadOnly ? 'lg:col-span-3' : 'lg:col-span-2'"
        >
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
                                    class="text-amber-450 rounded border border-amber-400/20 bg-amber-400/10 px-1.5 py-0.5 text-[8px] font-black tracking-wider uppercase"
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
                        <div
                            v-if="!isReadOnly"
                            class="flex items-center space-x-1"
                        >
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
</template>
