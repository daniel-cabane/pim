<template>
    <div>
        <div class="d-flex align-center">
            <div v-for="i in 7" class="topBox text-captionColor text-body-2">
                <span>
                    {{ daysOfWeek[i - 1] }}
                </span>
            </div>
        </div>
        <div v-for="i in 4" style="display:flex;">
            <div v-for="j in 7" class="dayBox"/>
        </div>
    </div>
</template>
<script setup>
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
    .dayBox {
        width: 200px;
        max-width: 14.2857%;
        height: 150px;
        border: 1px solid #cccccc;
    }
</style>