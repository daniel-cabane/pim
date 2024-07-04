<template>
    <div style="display:flex;align-items:flex-start;justify-content:space-between;">
        <div>
            <div class="text-h5 font-weight-bold">
                {{ post.title }}
            </div>
            <div class="font-italic text-caption text-captionColor" v-if="post.author">
                By {{ post.author.name }}
            </div>
        </div>
        <v-chip :prepend-icon="chipDetails.icon" :color="chipDetails.color" label>
          {{ chipDetails.title }}
        </v-chip>
        <!-- <div class="text-right" v-if="post.published_at">
            <div class="text-caption">
                {{ $t('Published on') }} {{ post.published_at_formated }}
            </div>
            <div class="text-caption font-italic" v-if="post.edited">
                {{ $t('Edited on') }} {{ post.updated_at_formated }}
            </div>
        </div> -->
    </div>
</template>
<script setup>
    import { computed } from "vue";

    const props = defineProps({ post: Object });

    const chipDetails = computed(() => {
        switch(props.post.status){
            case 'submitted':
                return { title: "Submitted", icon: "mdi-file-eye", color: "primary" };
                break;
            case 'published':
                return { title: "Published", icon: "mdi-file-check", color: "success" };
                break;
            default:
                return { title: "Draft", icon: "mdi-file-hidden", color: "secondary" };
                break;
        }
    });
</script>