import { reactive } from 'vue';

const notifications = reactive([]);

const addNotification = notification => {
    let id = 1;
    notifications.forEach(n => id = Math.max(id, n.id+1));
    notifications.push({
        id,
        timer: setTimeout(() => removeNotification(id), 2000),
        ...notification
    });
}
const removeNotification = id => {
    const index = notifications.findIndex(notif => notif.id == id);
    notifications.splice(index, 1);
}
const hold = id => {
    clearTimeout((notifications.find(a => a.id == id)).timer);
}
const release = id => {
    (notifications.find(a => a.id == id)).timer = setTimeout(() => removeNotification(id), 2000);
}

export default function useNotifications () {

    return { notifications, addNotification, removeNotification, hold, release }

}

