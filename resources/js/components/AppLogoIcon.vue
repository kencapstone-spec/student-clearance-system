<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import tpcLogo from '../../images/tpc-logo.webp';

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
        :src="tpcLogo"
        alt="Talibon Polytechnic College Logo"
        :class="className"
        v-bind="$attrs"
        class="rounded-full object-cover"
    />
</template>
