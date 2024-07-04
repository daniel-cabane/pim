<template>
    <div style="display:flex; gap:15px;">
        <div style="flex:1">
            <v-container class="pa-0">
                <v-row>
                    <v-col xs="12" sm="12" md="8">
                        <v-text-field :rules="[rules.required, rules.minLengthTitle]" max="100" v-model="post.title"
                            :label="$t('Title')" variant="outlined" validate-on="blur" :disabled="!post.editable" />
                    </v-col>
                    <v-col xs="12" sm="12" md="4" class="text-h5 font-weight-bold d-flex align-center">
                        <v-card flat :theme="isDarkColor(post.cover.titleColor) ? 'customLight' : 'customDark'"
                            class="mb-5 py-2 px-4" :style="`color:${post.cover.titleColor};`">
                            {{ $t('Title color') }}
                            <v-menu>
                                <template v-slot:activator="{ props }">
                                    <v-btn size="small" :color="post.cover.titleColor" class="ml-2" base-color="black"
                                        variant="tonal" icon="mdi-palette" v-bind="props" />
                                </template>
                                <v-list>
                                    <v-list-item @click="post.cover.titleColor = '#8A8C8D'">
                                        <v-list-item-title>
                                            {{ $t('Dark') }}
                                        </v-list-item-title>
                                    </v-list-item>
                                    <v-list-item @click="post.cover.titleColor = '#F3F3F3'">
                                        <v-list-item-title>
                                            {{ $t('Bright') }}
                                        </v-list-item-title>
                                    </v-list-item>
                                    <v-dialog width="300">
                                        <template v-slot:activator="{ props: activatorProps }">
                                            <v-list-item v-bind="activatorProps">
                                                <v-list-item-title>{{ $t('Pick a color') }}...</v-list-item-title>
                                            </v-list-item>
                                        </template>
                                        <template v-slot:default="{ isActive }">
                                            <v-card>
                                                <v-color-picker v-model="newColor" elevation="0" hide-inputs
                                                    mode='rgb' />
                                                <div class="pa-3" style="display:flex;">
                                                    <v-spacer />
                                                    <v-btn variant="plain" color="error" text="Cancel"
                                                        @click="isActive.value = false" />
                                                    <v-btn color="primary" variant="tonal" text="Confirm"
                                                        @click="post.cover.titleColor = newColor;isActive.value = false" />
                                                </div>
                                            </v-card>
                                        </template>
                                    </v-dialog>
                                </v-list>
                            </v-menu>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
            <v-textarea :rules="[rules.required, rules.minLengthDescription]" max="255" v-model="post.description"
                :label="$t('Description')" variant="outlined" validate-on="blur" :disabled="!post.editable" />
        </div>
        <div style="width:310px">
            <v-file-input accept="image/png, image/jpeg, image/jpg" v-model="coverImageFile" ref="coverImageFileInput"
                @update:modelValue="updateCoverImage({ file: coverImageFile })" style='display:none;' />
            <div class="d-flex align-center">
                <span class="text-captionColor text-caption">
                    {{ $t('Cover image') }}
                </span>
                <v-dialog max-width="250">
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-icon icon="mdi-help-circle" color="captionColor" class="ml-1" size="x-small"
                            v-bind="activatorProps" />
                    </template>

                    <template v-slot:default="{ isActive }">
                        <v-card :title="$t('Cover image')">
                            <v-card-text>
                                {{ $t('The cover image is used to promote the post on the home page.') }}
                                <br />
                                {{ $t('Aspect ratio should be') }} 16/9.
                            </v-card-text>

                            <v-card-actions>
                                <v-spacer></v-spacer>

                                <v-btn :text="$t('Close')" @click="isActive.value = false"></v-btn>
                            </v-card-actions>
                        </v-card>
                    </template>
                </v-dialog>
            </div>
            <v-hover>
                <template v-slot:default="{ isHovering, props }">
                    <div style="position:relative" v-bind="props">
                        <v-img :src="post.cover.url" />
                        <v-btn size="small" color="primary" v-show="isHovering"
                            style="position:absolute;right:5px;bottom:5px;" :disabled="imageLoading"
                            @click="pickCoverFile">
                            {{ $t('Update cover') }}
                        </v-btn>
                        <div class="overlay" v-if="imageLoading" />
                        <v-progress-linear indeterminate color="primary" height="2" style="position:absolute;top:0;"
                            v-if="imageLoading" />
                    </div>
                </template>
            </v-hover>
        </div>
    </div>
    <div class="text-captionColor text-caption">
        Content
    </div>
    <Editor api-key="c6xujr454hv9o3u7uqat7dlla2v61j7n3syp29hhj0k4aeeu" :init="{
            plugins, toolbar, ...options
        }" v-model="post.post" />
</template>
<script setup>
    import { ref } from "vue";
    import { usePostStore } from '@/stores/usePostStore';
    import { storeToRefs } from 'pinia';
    import Editor from '@tinymce/tinymce-vue';

    const props = defineProps({ post: Object });

    const postStore = usePostStore();
    const { uploadPostImage, updateCoverImage } = postStore;
    const { imageLoading } = storeToRefs(postStore);
    
    const rules = {
        required: value => !!value || 'Required.',
        minLengthTitle: value => value.length >= 5 || 'The title must at least 5 characters long',
        minLengthDescription: value => value.length >= 10 || 'The description must at least 10 characters long',
    };

    const plugins = 'lists link image table code help wordcount';
    const toolbar = 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist checklist | forecolor backcolor removeformat | charmap emoticons | image media table link unlink'
    const options = {
        image_title: true,
        automatic_uploads: true,
        file_picker_types: 'image',
        file_picker_callback: (cb, value, meta) => {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.onchange = async () => {
                const file = input.files[0];
                const formData = new FormData();
                formData.append('file', file);
                const res = await uploadPostImage(formData);
                console.log(res);
                cb(res.url, { title: file.name });
            };
            input.click();
        },
    }

    const coverImageFileInput = ref(null);
    const coverImageFile = ref(null);
    const newColor = ref('#000000');
    const pickCoverFile = () => {
        coverImageFileInput.value.click()
    }
    const isDarkColor = hex => {
        const r = parseInt(hex.substring(1, 3), 16);
        const g = parseInt(hex.substring(3, 5), 16);
        const b = parseInt(hex.substring(5, 7), 16);
        return (0.2126 * r + 0.7152 * g + 0.0722 * b) / 255 < 0.8;
    }
</script>
<style scoped>
.overlay {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0px;
    background: rgba(255, 255, 255, 0.6);
}
</style>