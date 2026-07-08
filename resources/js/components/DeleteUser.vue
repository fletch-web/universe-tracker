<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { useTemplateRef } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';

const passwordInput = useTemplateRef('passwordInput');
</script>

<template>
    <div
        class="space-y-6 rounded-2xl border border-slate-800 bg-slate-900/60 p-6 shadow"
    >
        <div class="border-b border-slate-800/60 pb-3">
            <h2
                class="text-sm font-black tracking-wider text-red-500 uppercase"
            >
                Danger Zone
            </h2>
            <p class="mt-1 text-[10px] text-slate-400">
                Delete your account and all associated simulation logs and
                metrics
            </p>
        </div>

        <div
            class="space-y-4 rounded-xl border border-red-950 bg-red-950/20 p-4 text-red-400"
        >
            <div class="relative space-y-0.5">
                <p
                    class="text-xs font-bold tracking-wider text-red-500 uppercase"
                >
                    Warning
                </p>
                <p class="text-xs text-red-400">
                    Proceed with caution, expunging account details is final and
                    cannot be reverted.
                </p>
            </div>
            <Dialog>
                <DialogTrigger as-child>
                    <button
                        type="button"
                        class="flex cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-red-900/60 bg-red-950/40 px-4 py-2.5 text-xs font-bold text-red-400 transition-all hover:bg-red-950 hover:text-red-300"
                        data-test="delete-user-button"
                    >
                        Delete Account
                    </button>
                </DialogTrigger>
                <DialogContent
                    class="rounded-2xl border-slate-800 bg-slate-900 text-slate-100"
                >
                    <Form
                        v-bind="ProfileController.destroy.form()"
                        reset-on-success
                        @error="() => passwordInput?.focus()"
                        :options="{
                            preserveScroll: true,
                        }"
                        class="space-y-6"
                        v-slot="{ errors, processing, reset, clearErrors }"
                    >
                        <DialogHeader class="space-y-3">
                            <DialogTitle
                                class="text-sm font-black tracking-wider text-white uppercase"
                                >Are you sure you want to delete your
                                account?</DialogTitle
                            >
                            <DialogDescription
                                class="text-xs leading-relaxed text-slate-400"
                            >
                                Once your account is deleted, all of its
                                resources and data will also be permanently
                                deleted. Please enter your password to confirm
                                you would like to permanently delete your
                                account.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-2">
                            <Label for="password" class="sr-only"
                                >Password</Label
                            >
                            <PasswordInput
                                id="password"
                                name="password"
                                ref="passwordInput"
                                placeholder="Enter Password"
                                class="h-10 rounded-xl border-slate-800 bg-slate-950 text-white placeholder-slate-500 focus-visible:border-amber-400 focus-visible:ring-amber-500/30"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <button
                                    type="button"
                                    class="flex cursor-pointer items-center justify-center rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-xs font-bold text-slate-200 transition duration-200 hover:bg-slate-700"
                                    @click="
                                        () => {
                                            clearErrors();
                                            reset();
                                        }
                                    "
                                >
                                    Cancel
                                </button>
                            </DialogClose>

                            <button
                                type="submit"
                                class="flex cursor-pointer items-center justify-center rounded-xl bg-red-600 px-4 py-2 text-xs font-bold text-white transition duration-200 hover:bg-red-500 disabled:opacity-50"
                                :disabled="processing"
                                data-test="confirm-delete-user-button"
                            >
                                Delete Account
                            </button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
