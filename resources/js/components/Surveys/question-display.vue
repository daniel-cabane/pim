<template>
    <div>
        {{ question.question }}
    </div>
</template>
<script setup>
    const props = defineProps({ question: Object });
</script>