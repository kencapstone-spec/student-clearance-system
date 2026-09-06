<script setup lang="ts">
import { Eye, EyeOff } from 'lucide-vue-next';
import { ref, useTemplateRef } from 'vue';
import type { Component, HTMLAttributes } from 'vue';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

defineOptions({ inheritAttrs: false });

const props = defineProps<{
    class?: HTMLAttributes['class'];
    icon?: Component;
}>();

const showPassword = ref(false);
const inputRef = useTemplateRef('inputRef');

defineExpose({
    $el: inputRef,
    focus: () => inputRef.value?.$el?.focus(),
});
</script>

<template>
    <div class="group relative flex items-center">
        <component
            :is="props.icon"
            v-if="props.icon"
            class="pointer-events-none absolute left-3.5 size-4 text-slate-400 transition-colors group-focus-within:text-blue-600"
        />
        <Input
            ref="inputRef"
            :type="showPassword ? 'text' : 'password'"
            :class="cn(props.icon ? 'pr-10 pl-10.5' : 'pr-10', props.class)"
            v-bind="$attrs"
        />
        <button
            type="button"
            @click="showPassword = !showPassword"
            class="absolute inset-y-0 right-0 flex items-center rounded-r-xl px-3 text-slate-400 transition-colors hover:text-slate-700 focus-visible:outline-none"
            :aria-label="showPassword ? 'Hide password' : 'Show password'"
            :tabindex="-1"
        >
            <EyeOff v-if="showPassword" class="size-4" />
            <Eye v-else class="size-4" />
        </button>
    </div>
</template>
