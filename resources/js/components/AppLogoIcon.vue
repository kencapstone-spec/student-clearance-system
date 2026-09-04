<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <!-- TPC Logo -->
    <img
        src="/images/tpc-logo.webp"
        alt="Talibon Polytechnic College Logo"
        :class="className"
        v-bind="$attrs"
        class="rounded-full object-cover"
    />
</template>
