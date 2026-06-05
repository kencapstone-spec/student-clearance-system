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
    <!-- Shield + checkmark — represents clearance/approval -->
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 40 44"
        :class="className"
        v-bind="$attrs"
    >
        <path
            fill="currentColor"
            fill-rule="evenodd"
            clip-rule="evenodd"
            d="M20 1 L2 8.5 L2 22 C2 33 10 41 20 44 C30 41 38 33 38 22 L38 8.5 Z
               M12.5 21.5 L10 24 L18 32 L30 19 L27.5 16.5 L18 27 Z"
        />
    </svg>
</template>
