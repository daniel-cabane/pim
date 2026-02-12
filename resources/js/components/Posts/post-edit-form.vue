<template>
    <div class="d-flex ga-3">
        <div style="flex:1" class="pa-2">
            <!-- <v-container class="pa-0"> -->
                <div class="d-flex ga-2">
                    <div style="min-width:350px;flex:1;">
                        <v-text-field 
                            :rules="[rules.required, rules.minLengthTitle]" 
                            max="50" 
                            counter="50"
                            v-model="post.title"
                            :label="$t('Title')" 
                            variant="outlined"
                            validate-on="blur"
                            :disabled="!post.editable"
                        />
                    </div>
                    <v-card 
                        flat
                        class="py-2 px-4 d-flex justify-center align-center font-weight-bold"
                        min-width="130"
                        height="60"
                        :color="post.cover.titleColor == 'black' ? 'white' : 'black'"
                    >
                        {{ $t('Title') }}
                        <v-menu>
                            <template v-slot:activator="{ props }">
                                <v-btn 
                                    size="small"
                                    :color="post.cover.titleColor"
                                    class="ml-2"
                                    :base-color="post.cover.titleColor == 'black' ? 'white' : 'black'"
                                    variant="tonal" icon="mdi-palette" v-bind="props" 
                                    dark
                                />
                            </template>
                            <v-list>
                                <v-list-item @click="post.cover.titleColor = 'white'">
                                    <v-list-item-title>
                                        {{ $t('Dark image') }}
                                    </v-list-item-title>
                                </v-list-item>
                                <v-list-item @click="post.cover.titleColor = 'black'">
                                    <v-list-item-title>
                                        {{ $t('Bright image') }}
                                    </v-list-item-title>
                                </v-list-item>
                            </v-list>
                        </v-menu>
                    </v-card>
                </div>
            <v-textarea 
                no-resize
                rows="3"
                :rules="[rules.required, rules.minLengthDescription]"
                counter="225"
                v-model="post.description"
                :label="$t('Description')" 
                variant="outlined"
                validate-on="blur"
                :disabled="!post.editable" 
                class="mb-2"
            />
            <div class="d-flex ga-2 align-center mb-4">
                <v-select 
                    v-model="post.themes"
                    :items="availableThemes"
                    :label="$t('Themes')"
                    multiple chips
                    hide-details
                    variant="outlined"
                    style="max-width:50%;"
                />
                <v-select 
                    v-model="post.series"
                    :items="seriesItems"
                    :label="$t('Series')"
                    multiple chips
                    hide-details
                    variant="outlined"
                    style="max-width:50%;"
                />
                <series-dialog :series="series"/>
            </div>
        </div>
        <div style="width:350px">
            <v-file-input 
                accept="image/png, image/jpeg, image/jpg"
                v-model="coverImageFile"
                ref="coverImageFileInput"
                @update:modelValue="updateCoverImage({ file: coverImageFile })" style='display:none;'
            />
            <div class="d-flex align-center mt-3">
                <span class="text-captionColor text-caption">
                    {{ $t('Cover image') }}
                </span>
                <v-dialog max-width="250">
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-icon icon="mdi-help-circle" color="captionColor" class="ml-1" size="x-small" v-bind="activatorProps" />
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
            <div style="position:relative">
                <post-cover :post="post"/>
                <v-btn size="small" color="primary" block :text="$t('Update cover')" class="mt-2" :disabled="imageLoading"  @click="pickCoverFile"/>
                <div class="overlay" v-if="imageLoading" />
                <v-progress-linear indeterminate color="primary" height="2" style="position:absolute;top:0;" v-if="imageLoading" />
            </div>
        </div>
    </div>
    <div class="px-2">
    <Editor 
        api-key="c6xujr454hv9o3u7uqat7dlla2v61j7n3syp29hhj0k4aeeu" :init="{
            plugins, toolbar, ...options
        }" 
        v-model="post.post" 
    />
    </div>
</template>
<script setup>
    import { ref, computed } from "vue";
    import { usePostStore } from '@/stores/usePostStore';
    import { useThemeStore } from '@/stores/useThemeStore';
    import { storeToRefs } from 'pinia';
    import { useI18n } from 'vue-i18n';
    import Editor from '@tinymce/tinymce-vue';

    const props = defineProps({ post: Object });

    const postStore = usePostStore();
    const { uploadPostImage, updateCoverImage } = postStore;
    const { imageLoading } = storeToRefs(postStore);

    
    const { forPost, series } = storeToRefs(useThemeStore());

    // getThemes();
    const { locale } = useI18n();
    const availableThemes = computed(() => forPost.value.map(theme => {
        return {
            title: locale == 'en' ? theme.title_en : theme.title_fr,
            value: theme.id
        }
    }));
    const seriesItems = computed(() => series.value.map(s => ({title: s.title, value: s.id})));
    
    const rules = {
        required: value => !!value || 'Required.',
        minLengthTitle: value => value.length >= 5 || 'The title must at least 5 characters long',
        minLengthDescription: value => value.length >= 10 || 'The description must at least 10 characters long',
    };

    const plugins = 'lists link image table code help wordcount media';
    const toolbar = 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | numlist bullist | forecolor backcolor removeformat | charmap emoticons | image media table link unlink'
    const options = {
        image_title: true,
        height: 800,
        convert_urls: false,
        link_assume_external_targets: 'https',
        automatic_uploads: true,
        image_dimensions: false,
        image_title: false,
        image_advtab: true,
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
                cb(res.url, { title: file.name });
            };
            input.click();
        },
        style_formats: [
            {
                title: 'Image Left',
                selector: 'img',
                styles: {
                    float: 'left',
                    margin: '0 10px 0 10px',
                    'max-width': '50%'
                }
            },
            {
                title: 'Image Right',
                selector: 'img',
                styles: {
                    float: 'right',
                    margin: '0 10px 0 10px',
                    'max-width': '50%'
                }
            }
        ]
    }

    const coverImageFileInput = ref(null);
    const coverImageFile = ref(null);
    const pickCoverFile = () => {
        coverImageFileInput.value.click()
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