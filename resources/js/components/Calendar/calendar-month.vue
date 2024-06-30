<template>
    <div>
        <div class="d-flex align-center">
            <div v-for="i in 7" class="topBox text-captionColor text-body-2">
                <span>
                    {{ daysOfWeek[i-1] }}
                </span>
            </div>
        </div>
        <week-row v-for="week in weeks" :week="week" :key="week.days[0].date" :currentMonth="currentMonth" />
    </div>
</template>
<script setup>
    const props = defineProps({ weeks: Array, currentMonth: Number });

const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
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