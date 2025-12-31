<script setup lang="ts">
import { type HTMLAttributes, computed } from 'vue'

interface Props {
  class?: HTMLAttributes['class']
  variant?: 'default' | 'destructive'
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
})

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props
  return delegated
})

const textareaClass = computed(() => {
  const baseClasses = 'flex min-h-[80px] w-full rounded-md border px-3 py-2 text-sm placeholder:text-gray-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50'
  const variantClasses = props.variant === 'destructive'
    ? 'border-red-500 focus-visible:ring-red-500'
    : 'border-gray-300 focus-visible:ring-blue-500'

  return `${baseClasses} ${variantClasses} ${props.class || ''}`
})
</script>

<template>
  <textarea
    v-bind="delegatedProps"
    :class="textareaClass"
  />
</template>
