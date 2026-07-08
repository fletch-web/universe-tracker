<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
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

defineProps<{
    status?: string;
    canResetPassword: boolean;
    passwordRules: string;
}>();

const activeTab = ref<'login' | 'register'>('login');

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const tab = params.get('tab');

    if (tab === 'register' || tab === 'login') {
        activeTab.value = tab;
    }
});

const setTab = (tab: 'login' | 'register') => {
    activeTab.value = tab;
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tab);
    window.history.pushState({}, '', url.toString());
};
</script>

<template>
    <Head :title="activeTab === 'login' ? 'Log in' : 'Register'" />

    <div
        class="relative flex min-h-svh flex-col items-center justify-center gap-6 overflow-hidden bg-slate-950 p-6 font-sans text-slate-100 md:p-10"
    >
        <!-- Background decorative ambient glows -->
        <div
            class="pointer-events-none absolute -top-40 -left-40 h-96 w-96 rounded-full bg-amber-500/10 blur-[120px]"
        ></div>
        <div
            class="pointer-events-none absolute -right-40 -bottom-40 h-96 w-96 rounded-full bg-yellow-500/10 blur-[120px]"
        ></div>

        <div class="relative z-10 flex w-full max-w-md flex-col gap-6">
            <div class="flex flex-col items-center gap-2 text-center">
                <Link
                    :href="home()"
                    class="flex flex-col items-center gap-3 self-center"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-tr from-amber-500 to-yellow-400 p-2.5 text-slate-950 shadow-lg shadow-amber-500/20"
                    >
                        <AppLogoIcon class="size-7 fill-current" />
                    </div>
                </Link>
                <h1 class="mt-2 text-xl font-black tracking-tight text-white">
                    Universe Tracker
                </h1>
                <p class="text-xs text-slate-400">
                    Elite Booking & Metrics Engine
                </p>
            </div>

            <div class="flex flex-col gap-6">
                <Card
                    class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/60 shadow-xl backdrop-blur-md"
                >
                    <div class="flex border-b border-slate-800 bg-slate-950/40">
                        <button
                            type="button"
                            @click="setTab('login')"
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
                            @click="setTab('register')"
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
                        <CardDescription class="mt-1 text-xs text-slate-400">
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
                        <div v-show="activeTab === 'login'" class="space-y-6">
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
                                            :autofocus="activeTab === 'login'"
                                            :tabindex="1"
                                            autocomplete="email"
                                            placeholder="email@example.com"
                                            class="h-10 rounded-xl border-slate-800 bg-slate-950 text-white placeholder-slate-500 focus-visible:border-amber-400 focus-visible:ring-amber-500/30"
                                        />
                                        <InputError :message="errors.email" />
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
                                                :href="passwordResetRequest()"
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
                                        <InputError :message="errors.name" />
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
                                        <InputError :message="errors.email" />
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
</template>
