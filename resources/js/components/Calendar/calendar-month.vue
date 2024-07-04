<template>
    <div>
        <div class="d-flex align-center">
            <div v-for="i in 7" class="topBox text-captionColor text-body-2">
                <span>
                    {{ $t(daysOfWeek[i-1]) }}
                </span>
            </div>
        </div>
        <week-row v-for="week in weeks" :week="week" :key="week.days[0].date" :currentMonth="currentMonth"
            :focusedId="focusedId" @updateFocusedId="updateFocusedId" @seeWeek="seeWeek" />
    </div>
</template>
<script setup>
    import { ref, onMounted, onUnmounted } from "vue";

    const props = defineProps({ weeks: Array, currentMonth: Number });

    const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    const focusedId = ref('0');
    const updateFocusedId = id => focusedId.value = id;

    const emit = defineEmits(['seeWeek']);
    const seeWeek = week => emit('seeWeek', week);

    onMounted(() => {
        document.addEventListener("click", resetFocusedId);
    });

    onUnmounted(() => {
        document.removeEventListener("click", resetFocusedId);
    });
    const resetFocusedId = () => focusedId.value = '0';
</script>
<style scoped>
    .topBox {
        width: 200px;
        height: 40px;
        border: 1px solid #cccccc;
        display: flex;
        justify-content: center;
        align-items: center;
    }
</style>