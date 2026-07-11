<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Profile settings',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user as any);
</script>

<template>
    <Head title="Profile settings" />

    <div class="flex flex-col space-y-6">
        <!-- Profile Card Panel -->
        <div
            class="space-y-6 rounded-2xl border border-slate-800 bg-slate-900/60 p-6 shadow"
        >
            <div class="border-b border-slate-800/60 pb-3">
                <h2
                    class="text-sm font-black tracking-wider text-white uppercase"
                >
                    Profile Information
                </h2>
                <p class="mt-1 text-[10px] text-slate-400">
                    Update your account name and email address
                </p>
            </div>

            <Form
                v-bind="ProfileController.update.form()"
                class="space-y-4"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-1">
                    <Label
                        for="name"
                        class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >Name</Label
                    >
                    <Input
                        id="name"
                        class="mt-1 block h-10 w-full rounded-xl border-slate-800 bg-slate-950 text-white placeholder-slate-500 focus-visible:border-amber-400 focus-visible:ring-amber-500/30"
                        name="name"
                        :default-value="user.name"
                        required
                        autocomplete="name"
                        placeholder="Full name"
                    />
                    <InputError class="mt-1" :message="errors.name" />
                </div>

                <div class="grid gap-1">
                    <Label
                        for="email"
                        class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >Email address</Label
                    >
                    <Input
                        id="email"
                        type="email"
                        class="mt-1 block h-10 w-full rounded-xl border-slate-800 bg-slate-950 text-white placeholder-slate-500 focus-visible:border-amber-400 focus-visible:ring-amber-500/30"
                        name="email"
                        :default-value="user.email"
                        required
                        autocomplete="username"
                        placeholder="Email address"
                    />
                    <InputError class="mt-1" :message="errors.email" />
                </div>

                <div class="grid gap-1">
                    <Label
                        for="username"
                        class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >Username</Label
                    >
                    <Input
                        id="username"
                        class="mt-1 block h-10 w-full rounded-xl border-slate-800 bg-slate-950 text-white placeholder-slate-500 focus-visible:border-amber-400 focus-visible:ring-amber-500/30"
                        name="username"
                        :default-value="user.username"
                        required
                        autocomplete="username"
                        placeholder="username"
                    />
                    <InputError class="mt-1" :message="errors.username" />
                </div>

                <div class="flex items-center gap-2 py-2">
                    <input type="hidden" name="is_public" value="0" />
                    <input
                        id="is_public"
                        type="checkbox"
                        name="is_public"
                        value="1"
                        :checked="user.is_public"
                        class="h-4 w-4 cursor-pointer rounded border-slate-800 bg-slate-950 text-amber-400 focus:border-amber-400 focus:ring-amber-500/30"
                    />
                    <Label
                        for="is_public"
                        class="cursor-pointer text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >Make Roster Public (read-only access via /@{
                        user.username || 'username' })</Label
                    >
                    <InputError class="mt-1" :message="errors.is_public" />
                </div>

                <div
                    v-if="page.props.mustVerifyEmail && !user.email_verified_at"
                    class="rounded-xl border border-amber-500/25 bg-amber-500/5 p-3"
                >
                    <p class="text-xs font-medium text-amber-400">
                        Your email address is unverified.
                        <Link
                            :href="send()"
                            class="ml-1 font-bold text-amber-400 underline hover:text-amber-300"
                        >
                            Click here to re-send the verification email.
                        </Link>
                    </p>

                    <div
                        v-if="page.props.status === 'verification-link-sent'"
                        class="mt-1.5 text-xs font-semibold text-emerald-400"
                    >
                        A new verification link has been sent to your email
                        address.
                    </div>
                </div>

                <div class="flex items-center pt-2">
                    <button
                        type="submit"
                        class="flex cursor-pointer items-center justify-center gap-1.5 rounded-xl bg-amber-400 px-4 py-2.5 text-xs font-bold text-slate-950 shadow transition-all hover:bg-amber-300 disabled:opacity-50"
                        :disabled="processing"
                        data-test="update-profile-button"
                    >
                        Save Settings
                    </button>
                </div>
            </Form>
        </div>

        <!-- Delete account section -->
        <DeleteUser />
    </div>
</template>
