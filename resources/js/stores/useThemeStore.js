import { defineStore } from 'pinia';
import useAPI from '@/composables/useAPI';

const { get, post, patch, del } = useAPI();

export const useThemeStore = defineStore({
    id: 'theme',
    state: () => ({
        themes: [],
        theme: {},
        isReady: false,
        isLoading: false
    }),
    actions: {
        async getThemes() {
            const res = await get('/api/themes');
            this.themes = res.themes;
        },
        async getThemesAdmin() {
            const res = await get('/api/admin/themes');
            this.themes = res.themes;
        },
        getThemeById(id) {
            const theme = this.themes.find(theme => theme.id == id);
            
            if (theme) {
              return theme;
            }
            // const res = await get(`/api/themes/${id}`);
            // if(res.theme){
            //     this.themes.push(res.theme);
            //     return res.theme;
            // }
            this.getThemes();
            return {id, title_en: 'Unknown theme', title_fr: 'Thème inconnu', forWorkshop: true, forPost: true}
        },
        async updateTheme(theme){
            this.isLoading = true;
            const res = await patch(`/api/admin/themes/${theme.id}`, theme);
            this.isLoading = false;
        },
        async createTheme(title_en, title_fr){
            this.isLoading = true;
            const res = await post(`/api/admin/themes`, {title_en, title_fr});
            this.themes.push(res.theme);
            this.isLoading = false;
        },
        async deleteTheme(theme){
            this.isLoading = true;
            const res = await del(`/api/admin/themes/${theme.id}`);
            if(res.id){
                this.themes = this.themes.filter(t => t.id != res.id);
            }
            this.isLoading = false;
        }
    },
    getters: {
        forWorkshop() {
            return this.themes.filter(t => t.forWorkshop);
        },
        forPost() {
            return this.themes.filter(t => t.forPost);
        }
    }
});