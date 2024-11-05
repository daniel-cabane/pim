<template>
    <div class="text-center" style="position:relative">
          <v-img 
            max-width='90%'
            min-width='90%'
            style="margin-left:5%;cursor:pointer;"
            src="/images/google signin.png" 
            @click="signMeIn"
        />
        <v-card flat color="surface" class="overlay" v-if="loading">
            <v-progress-circular :size="50" :width="3" color="primary" indeterminate/>
        </v-card>
      </div>
</template>
<script setup>
    import { ref } from "vue";
    import useAPI from '@/composables/useAPI';
    import { useRoute } from 'vue-router';
    
    const { post } = useAPI();
    const route = useRoute();

    const loading = ref(false);

    const signMeIn = async () => {
        loading.value = true;
        const sessionRecord = await post('/api/session/currentRoute', { route: route.path });
        loading.value = false;
        if(sessionRecord == 'OK'){
            window.location.href = '/auth/google';
        }
    }
</script>
<style scoped>
    .overlay {
        position:absolute;
        top: 0;
        left: 5%;
        width: 90%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0.8;
    }
</style>