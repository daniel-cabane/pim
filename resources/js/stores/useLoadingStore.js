import { defineStore } from 'pinia';

export const useLoadingStore = defineStore({
    id: 'loading',
    state: () => ({
        loadingProcesses: [],
    }),
    actions: {
        addProcess(process){
            this.loadingProcesses.push(process);
        },
        removeProcess(process){
            this.loadingProcesses = this.loadingProcesses.filter(p => p != process);
        },
        clearProcesses(){
            this.loadingProcesses = [];
        }
    },
    getters: {
        isLoading() {
            return this.loadingProcesses.length > 0;
        }
    }
});