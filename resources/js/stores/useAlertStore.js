import { defineStore } from 'pinia';

export const useAlertStore = defineStore({
    id: 'alert',
    state: () => ({
        alerts: [],
        id: 0
    }),
    actions: {
        addAlert(alert){
            this.alerts.push({
                id: this.id, 
                timer: setTimeout(this.removeAlert.bind(null, this.id), 2000),
                ...alert
            });
            
            this.id++;
        },
        removeAlert(id) {
            this.alerts = this.alerts.filter(a => a.id != id);
        },
        hold(id){
            clearTimeout((this.alerts.find(a => a.id == id)).timer);
        },
        release(id){
            (this.alerts.find(a => a.id == id)).timer = setTimeout(this.removeAlert.bind(null, id), 2000);
        }
    }
})