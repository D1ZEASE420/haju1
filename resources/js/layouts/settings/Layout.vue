<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    { title: 'Profile', href: editProfile() },
    { title: 'Security', href: editSecurity() },
    { title: 'Appearance', href: editAppearance() },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div class="px-6 py-6 max-w-4xl">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold tracking-tight text-foreground">Settings</h1>
            <p class="text-sm text-muted-foreground mt-1">Manage your profile and account preferences</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Side nav -->
            <aside class="w-full lg:w-44 shrink-0">
                <nav class="flex flex-row lg:flex-col gap-1">
                    <Link
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        :href="item.href"
                        :class="[
                            'px-3 py-2 rounded-lg text-sm font-medium transition-all',
                            isCurrentOrParentUrl(item.href)
                                ? 'bg-primary/10 text-primary'
                                : 'text-muted-foreground hover:text-foreground hover:bg-accent'
                        ]"
                    >
                        {{ item.title }}
                    </Link>
                </nav>
            </aside>

            <Separator class="lg:hidden" />

            <!-- Content -->
            <div class="flex-1 min-w-0">
                <section class="max-w-xl space-y-8">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
