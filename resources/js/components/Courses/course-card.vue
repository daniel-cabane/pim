<template>
    <v-card width="450" class="ma-2" ripple hover @click="goToCourse">
        <v-card-title class="d-flex justify-space-between align-center">
            <span>
                {{ title }}
            </span>
            <span class="text-captionColor" v-if="user.is.teacher">
                {{ course.join_code }}
            </span>
        </v-card-title>
        <v-card-text>
            <div class="text-caption text-captionColor">
                Description
            </div>
            <div v-html="description"/>
        </v-card-text>
        <div class="d-flex justify-space-between align-center pa-2 text-h5">
            <span class="d-flex align-center" >
                <v-icon icon="mdi-account-group" class="mr-1" v-if="user.is.teacher"/>
                <span class="text-h4" v-if="user.is.teacher">
                    {{ course.students.length }}
                </span>
            </span>
            <span class="d-flex align-center">
                <span class="text-h4">
                    {{ nbObjectives }}
                </span>
                <v-icon icon="mdi-decagram" class="ml-1"/>
            </span>
            <v-btn color="primary" icon="mdi-pencil" @click.stop="editCourse" v-if="user.is.teacher"/>
        </div>
    </v-card>
</template>
<script setup>
    import { computed } from 'vue';
    import { useI18n } from 'vue-i18n';
    import { useRouter } from 'vue-router';
    import { useAuthStore } from '@/stores/useAuthStore';

    const { locale } = useI18n();
    const router = useRouter();
    const { user } = useAuthStore();
    
    const props = defineProps({
        course: {
            type: Object,
            required: true
        }
    });

    const title = computed(() => {
        if(props.course.title.fr && props.course.title.en){
            return  props.course.title[locale.value];
        } else if(props.course.title.fr){
            return props.course.title.fr;
        } else {
            return props.course.title.en;
        }
    });
    const description = computed(() => {
        if(props.course.description.fr && props.course.description.en){
            return  props.course.description[locale.value];
        } else if(props.course.description.fr){
            return props.course.description.fr;
        } else {
            return props.course.description.en;
        }
    });
    const nbObjectives = computed(() => {
        return props.course.sections
        ? props.course.sections.reduce((sum, section) => sum + (section.objectives ? section.objectives.length : 0), 0)
        : 0;
    });

    const goToCourse = () => {
        router.push(`/courses/${props.course.id}`);
    };
    const editCourse = () => {
        router.push(`/courses/${props.course.id}/edit`);
    };
</script>