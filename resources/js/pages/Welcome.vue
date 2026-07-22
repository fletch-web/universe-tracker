<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Search, X } from '@lucide/vue';
import { ref, onMounted, computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import InputError from '@/components/InputError.vue';
import PasskeyVerify from '@/components/PasskeyVerify.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { home } from '@/routes';
import { store as loginStore } from '@/routes/login';
import { request as passwordResetRequest } from '@/routes/password';
import { store as registerStore } from '@/routes/register';

interface PublicUser {
    id: number;
    name: string;
    username: string;
}

const props = defineProps<{
    status?: string;
    canResetPassword: boolean;
    passwordRules: string;
    publicUsers?: PublicUser[];
}>();

const activeTab = ref<'login' | 'register'>('login');
const searchQuery = ref('');
const isAuthModalOpen = ref(false);

const filteredPublicUsers = computed(() => {
    if (!props.publicUsers) {
        return [];
    }

    if (searchQuery.value.trim() === '') {
        return props.publicUsers;
    }

    const query = searchQuery.value.toLowerCase();

    return props.publicUsers.filter(
        (u) =>
            u.name.toLowerCase().includes(query) ||
            u.username.toLowerCase().includes(query),
    );
});

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const tab = params.get('tab');

    if (tab === 'register' || tab === 'login') {
        activeTab.value = tab;
        isAuthModalOpen.value = true;
    }
});

const setTab = (tab: 'login' | 'register') => {
    activeTab.value = tab;
    isAuthModalOpen.value = true;
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tab);
    window.history.pushState({}, '', url.toString());
};
</script>

<template>
    <Head title="Explore Universes - Universe Tracker" />

    <div
        class="relative flex min-h-svh flex-col overflow-hidden bg-slate-955 font-sans text-slate-100"
    >
        <!-- Background decorative ambient glows -->
        <div
            class="pointer-events-none absolute -top-40 -left-40 h-96 w-96 rounded-full bg-amber-500/10 blur-[120px]"
        ></div>
        <div
            class="pointer-events-none absolute -right-40 -bottom-40 h-96 w-96 rounded-full bg-yellow-500/10 blur-[120px]"
        ></div>

        <!-- Top Navigation Bar -->
        <header
            class="relative z-10 mx-auto flex w-full max-w-7xl items-center justify-between border-b border-slate-800 bg-slate-950/40 px-6 py-5"
        >
            <Link :href="home()" class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-tr from-amber-500 to-yellow-400 p-2 text-slate-950 shadow-lg shadow-amber-500/20"
                >
                    <AppLogoIcon class="size-6 fill-current" />
                </div>
                <span
                    class="text-base font-black tracking-tight text-white uppercase"
                    >Universe Tracker</span
                >
            </Link>
            <div class="flex items-center gap-2">
                <template v-if="$page.props.auth?.user">
                    <Link
                        href="/dashboard"
                        class="rounded-xl bg-amber-400 px-4 py-2 text-xs font-bold text-slate-955 shadow transition hover:bg-amber-300"
                    >
                        Go to Dashboard
                    </Link>
                </template>
                <template v-else>
                    <button
                        @click="setTab('login')"
                        class="rounded-xl border border-slate-800 bg-slate-900/60 px-4 py-2 text-xs font-bold text-slate-200 transition hover:bg-slate-800"
                        data-test="nav-login"
                    >
                        Sign In
                    </button>
                    <button
                        @click="setTab('register')"
                        class="rounded-xl bg-amber-400 px-4 py-2 text-xs font-bold text-slate-955 shadow transition hover:bg-amber-300"
                        data-test="nav-register"
                    >
                        Register
                    </button>
                </template>
            </div>
        </header>

        <!-- Main Content Portal -->
        <main
            class="relative z-10 mx-auto flex w-full max-w-4xl flex-1 flex-col items-center px-6 py-12"
        >
            <div class="mb-12 max-w-xl text-center">
                <h1
                    class="bg-gradient-to-b from-white to-slate-300 bg-clip-text text-3xl font-black tracking-tight text-transparent text-white uppercase italic sm:text-4xl"
                >
                    Explore Booking Universes
                </h1>
                <p class="mt-2 text-sm text-slate-400">
                    Discover and browse elite custom wrestling brands, faction
                    statistics, and match tracking metrics from managers around
                    the world.
                </p>
            </div>

            <!-- Portal Search Bar -->
            <div class="relative mb-10 w-full max-w-lg">
                <Search
                    class="absolute top-3.5 left-4 h-4.5 w-4.5 text-slate-500"
                />
                <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Search managers or universes by name..."
                    class="w-full rounded-2xl border border-slate-800 bg-slate-900/40 py-3.5 pr-4 pl-12 text-sm text-white placeholder-slate-500 backdrop-blur focus:border-amber-400 focus:ring-2 focus:ring-amber-500/20 focus:outline-none"
                />
            </div>

            <!-- Public Universes Showcases -->
            <div class="w-full">
                <div
                    v-if="filteredPublicUsers.length === 0"
                    class="py-16 text-center text-slate-500"
                >
                    <p class="text-sm font-semibold">
                        No public booking universes match your search.
                    </p>
                    <p class="mt-1 text-xs text-slate-600">
                        Be the first to publish yours by editing your profile
                        settings!
                    </p>
                </div>
                <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div
                        v-for="user in filteredPublicUsers"
                        :key="user.id"
                        class="flex items-center justify-between rounded-2xl border border-slate-800/80 bg-slate-900/40 p-5 shadow backdrop-blur transition-all duration-200 hover:border-purple-500/50 hover:bg-slate-900/60"
                    >
                        <div class="min-w-0 pr-4">
                            <h3
                                class="truncate text-sm font-black tracking-wide text-white uppercase"
                            >
                                {{ user.name }}
                            </h3>
                            <p
                                class="mt-0.5 font-mono text-xs font-bold text-purple-400"
                            >
                                @{{ user.username }}
                            </p>
                        </div>
                        <Link
                            :href="`/@${user.username}`"
                            class="hover:bg-amber-450 flex-shrink-0 rounded-xl border border-amber-400/20 bg-amber-400/10 px-4 py-2 text-xs font-black text-amber-400 transition hover:text-slate-955"
                        >
                            View Universe
                        </Link>
                    </div>
                </div>
            </div>
        </main>

        <!-- Auth Modal Overlay -->
        <div
            v-if="isAuthModalOpen"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/85 p-4 backdrop-blur-md"
        >
            <div class="flex min-h-full items-center justify-center">
                <div class="relative my-8 w-full max-w-md">
                    <!-- Close Button -->
                    <button
                        @click="isAuthModalOpen = false"
                        class="absolute top-4 right-4 z-50 rounded-xl border border-slate-800 bg-slate-950 p-2 text-slate-400 transition hover:text-white"
                        data-test="close-auth-modal"
                    >
                        <X class="h-4 w-4" />
                    </button>

                    <!-- Forms Card Container -->
                    <Card
                        class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-2xl"
                    >
                        <div
                            class="flex border-b border-slate-800 bg-slate-950/40"
                        >
                            <button
                                type="button"
                                @click="activeTab = 'login'"
                                :class="[
                                    'flex-1 cursor-pointer border-b-2 py-4 text-center text-xs font-bold tracking-wider uppercase transition-all duration-200',
                                    activeTab === 'login'
                                        ? 'border-amber-400 bg-slate-900/40 text-amber-400'
                                        : 'text-slate-450 border-transparent hover:bg-slate-900/20 hover:text-slate-200',
                                ]"
                                data-test="tab-login"
                            >
                                Log In
                            </button>
                            <button
                                type="button"
                                @click="activeTab = 'register'"
                                :class="[
                                    'flex-1 cursor-pointer border-b-2 py-4 text-center text-xs font-bold tracking-wider uppercase transition-all duration-200',
                                    activeTab === 'register'
                                        ? 'border-amber-400 bg-slate-900/40 text-amber-400'
                                        : 'text-slate-450 border-transparent hover:bg-slate-900/20 hover:text-slate-200',
                                ]"
                                data-test="tab-register"
                            >
                                Create Account
                            </button>
                        </div>

                        <CardHeader class="px-8 pt-6 pb-0 text-center">
                            <CardTitle class="text-lg font-black text-white">
                                {{
                                    activeTab === 'login'
                                        ? 'Welcome Back'
                                        : 'Get Started'
                                }}
                            </CardTitle>
                            <CardDescription
                                class="mt-1 text-xs text-slate-400"
                            >
                                {{
                                    activeTab === 'login'
                                        ? 'Enter your email and password below to log in'
                                        : 'Enter your details below to create your account'
                                }}
                            </CardDescription>
                        </CardHeader>

                        <CardContent class="px-8 py-6">
                            <div
                                v-if="status"
                                class="mb-4 text-center text-sm font-medium text-green-600 dark:text-green-400"
                            >
                                {{ status }}
                            </div>

                            <!-- Login Form -->
                            <div
                                v-show="activeTab === 'login'"
                                class="space-y-6"
                            >
                                <PasskeyVerify />

                                <Form
                                    v-bind="loginStore.form()"
                                    :reset-on-success="['password']"
                                    v-slot="{ errors, processing }"
                                    class="flex flex-col gap-6"
                                >
                                    <div class="grid gap-6">
                                        <div class="grid gap-2">
                                            <Label
                                                for="email"
                                                class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                                >Email address</Label
                                            >
                                            <Input
                                                id="email"
                                                type="email"
                                                name="email"
                                                required
                                                :autofocus="
                                                    activeTab === 'login'
                                                "
                                                :tabindex="1"
                                                autocomplete="email"
                                                placeholder="email@example.com"
                                                class="h-10 rounded-xl border-slate-800 bg-slate-950 text-white placeholder-slate-500 focus-visible:border-amber-400 focus-visible:ring-amber-500/30"
                                            />
                                            <InputError
                                                :message="errors.email"
                                            />
                                        </div>

                                        <div class="grid gap-2">
                                            <div
                                                class="flex items-center justify-between"
                                            >
                                                <Label
                                                    for="password"
                                                    class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                                    >Password</Label
                                                >
                                                <Link
                                                    v-if="canResetPassword"
                                                    :href="
                                                        passwordResetRequest()
                                                    "
                                                    class="text-xs font-bold text-amber-400 transition duration-150 hover:text-amber-300"
                                                    :tabindex="5"
                                                >
                                                    Forgot your password?
                                                </Link>
                                            </div>
                                            <PasswordInput
                                                id="password"
                                                name="password"
                                                required
                                                :tabindex="2"
                                                autocomplete="current-password"
                                                placeholder="Password"
                                                class="h-10 rounded-xl border-slate-800 bg-slate-950 text-white placeholder-slate-500 focus-visible:border-amber-400 focus-visible:ring-amber-500/30"
                                            />
                                            <InputError
                                                :message="errors.password"
                                            />
                                        </div>

                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <Label
                                                for="remember"
                                                class="flex items-center space-x-3"
                                            >
                                                <Checkbox
                                                    id="remember"
                                                    name="remember"
                                                    :tabindex="3"
                                                    class="border-slate-800 text-slate-100 focus-visible:border-amber-400 focus-visible:ring-amber-500/30 data-[state=checked]:border-amber-400 data-[state=checked]:bg-amber-400 data-[state=checked]:text-slate-950"
                                                />
                                                <span
                                                    class="cursor-pointer text-xs font-bold tracking-wide text-slate-400 uppercase"
                                                    >Remember me</span
                                                >
                                            </Label>
                                        </div>

                                        <button
                                            type="submit"
                                            class="mt-4 flex w-full cursor-pointer items-center justify-center gap-1.5 rounded-xl bg-amber-400 px-4 py-2.5 text-xs font-bold text-slate-950 shadow transition-all hover:bg-amber-300 disabled:opacity-50"
                                            :tabindex="4"
                                            :disabled="processing"
                                            data-test="login-button"
                                        >
                                            <Spinner v-if="processing" />
                                            Log in
                                        </button>
                                    </div>
                                </Form>
                            </div>

                            <!-- Register Form -->
                            <div
                                v-show="activeTab === 'register'"
                                class="space-y-6"
                            >
                                <Form
                                    v-bind="registerStore.form()"
                                    :reset-on-success="[
                                        'password',
                                        'password_confirmation',
                                    ]"
                                    v-slot="{ errors, processing }"
                                    class="flex flex-col gap-6"
                                >
                                    <div class="grid gap-6">
                                        <div class="grid gap-2">
                                            <Label
                                                for="name"
                                                class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                                >Name</Label
                                            >
                                            <Input
                                                id="name"
                                                type="text"
                                                required
                                                :autofocus="
                                                    activeTab === 'register'
                                                "
                                                :tabindex="1"
                                                autocomplete="name"
                                                name="name"
                                                placeholder="Full name"
                                                class="h-10 rounded-xl border-slate-800 bg-slate-950 text-white placeholder-slate-500 focus-visible:border-amber-400 focus-visible:ring-amber-500/30"
                                            />
                                            <InputError
                                                :message="errors.name"
                                            />
                                        </div>

                                        <div class="grid gap-2">
                                            <Label
                                                for="username"
                                                class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                                >Username</Label
                                            >
                                            <Input
                                                id="username"
                                                type="text"
                                                required
                                                :tabindex="1"
                                                autocomplete="username"
                                                name="username"
                                                placeholder="username"
                                                class="h-10 rounded-xl border-slate-800 bg-slate-950 text-white placeholder-slate-500 focus-visible:border-amber-400 focus-visible:ring-amber-500/30"
                                            />
                                            <InputError
                                                :message="errors.username"
                                            />
                                        </div>

                                        <div class="grid gap-2">
                                            <Label
                                                for="register-email"
                                                class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                                >Email address</Label
                                            >
                                            <Input
                                                id="register-email"
                                                type="email"
                                                required
                                                :tabindex="2"
                                                autocomplete="email"
                                                name="email"
                                                placeholder="email@example.com"
                                                class="h-10 rounded-xl border-slate-800 bg-slate-950 text-white placeholder-slate-500 focus-visible:border-amber-400 focus-visible:ring-amber-500/30"
                                            />
                                            <InputError
                                                :message="errors.email"
                                            />
                                        </div>

                                        <div class="grid gap-2">
                                            <Label
                                                for="register-password"
                                                class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                                >Password</Label
                                            >
                                            <PasswordInput
                                                id="register-password"
                                                required
                                                :tabindex="3"
                                                autocomplete="new-password"
                                                name="password"
                                                placeholder="Password"
                                                :passwordrules="passwordRules"
                                                class="h-10 rounded-xl border-slate-800 bg-slate-950 text-white placeholder-slate-500 focus-visible:border-amber-400 focus-visible:ring-amber-500/30"
                                            />
                                            <InputError
                                                :message="errors.password"
                                            />
                                        </div>

                                        <div class="grid gap-2">
                                            <Label
                                                for="password_confirmation"
                                                class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                                >Confirm password</Label
                                            >
                                            <PasswordInput
                                                id="password_confirmation"
                                                required
                                                :tabindex="4"
                                                autocomplete="new-password"
                                                name="password_confirmation"
                                                placeholder="Confirm password"
                                                :passwordrules="passwordRules"
                                                class="h-10 rounded-xl border-slate-800 bg-slate-950 text-white placeholder-slate-500 focus-visible:border-amber-400 focus-visible:ring-amber-500/30"
                                            />
                                            <InputError
                                                :message="
                                                    errors.password_confirmation
                                                "
                                            />
                                        </div>

                                        <button
                                            type="submit"
                                            class="mt-2 flex w-full cursor-pointer items-center justify-center gap-1.5 rounded-xl bg-amber-400 px-4 py-2.5 text-xs font-bold text-slate-950 shadow transition-all hover:bg-amber-300 disabled:opacity-50"
                                            :tabindex="5"
                                            :disabled="processing"
                                            data-test="register-user-button"
                                        >
                                            <Spinner v-if="processing" />
                                            Create account
                                        </button>
                                    </div>
                                </Form>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </div>
</template>
