<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-10 items-center justify-center rounded-2xl bg-blue-700 text-white shadow-lg shadow-blue-700/20"
    >
        <AppLogoIcon class="size-6 fill-current text-white" />
    </div>

    <div class="ml-2 grid flex-1 text-left text-sm">
        <span class="truncate text-sm font-black leading-tight text-slate-950">
            Student Clearance
        </span>
        <span class="truncate text-xs font-semibold text-slate-500">
            TPC System
        </span>
    </div>
</template>