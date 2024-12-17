<template>
    <div class="text-center pa-2">
        <div class="d-flex align-center">
            <v-img :src="`/images/flag ${workshop.language}.png`" :width="30" class="mr-2" />
            <v-chip variant="elevated" size="small" :color="workshop.campus == 'BPR' ? 'blue' : 'red'">
                {{ workshop.campus }}
            </v-chip>
        </div>
        <v-chip 
            label
            size="small"
            :variant="details.type"
            class="mt-1"
            :color="details.color" 
            :text="$t(details.text)"
            style="width:100%;display:flex;justify-content:center;"
            v-if="workshop.editable || !['draft', 'submitted'].includes(workshop.status)"
        />
    </div>
</template>
<script setup>
    import { computed } from 'vue';
    import { useAuthStore } from '@/stores/useAuthStore';
    const props = defineProps({ workshop: Object });

    const { user } = useAuthStore();

    const details = computed(() => {
        if(user && user.is.student){
            switch(props.workshop.status){
                case 'confirmed': 
                    if(props.workshop.acceptingStudents){
                        return {text: 'Open|adj', type: 'elevated', color: 'success'}
                    }
                    return {text: 'In preparation', type: 'tonal', color: 'primary'}
                    break;
                case 'launched':
                    return {text: 'Open|adj', type: 'elevated', color: 'success'}
                    break;
                case 'progress':
                    return {text: 'In progress', type: 'flat', color: 'primary'}
                    break;
                case 'finished':
                    return {text: 'Finished', type: 'tonal', color: 'grey'}
                    break;
            }
        }
        switch(props.workshop.status){
                case 'draft':
                    return {text: 'Draft', type: 'tonal', color: 'secondary'}
                    break;
                case 'submitted':
                    return {text: 'Submitted', type: 'tonal', color: 'warning'}
                    break;    
                case 'confirmed': 
                    if(props.workshop.acceptingStudents){
                        return {text: 'Confirmed', type: 'elevated', color: 'primary'}
                    }
                    return {text: 'Confirmed', type: 'tonal', color: 'primary'}
                    break;
                case 'launched':
                    return {text: 'Launched', type: 'elevated', color: 'success'}
                    break;
                case 'progress':
                    return {text: 'In progress', type: 'flat', color: 'primary'}
                    break;
                case 'finished':
                    return {text: 'Finished', type: 'tonal', color: 'grey'}
                    break;
            } 
    });

</script>