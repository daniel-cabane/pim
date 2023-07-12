import useLoading from '@/composables/useLoading';
import useNotifications from '@/composables/useNotifications';

const { addProcess, removeProcess } = useLoading();
const { addNotification } = useNotifications();

export default function useAPI () {
    const get = async (url, globalLoading = false) => {
        if(globalLoading){
            addProcess(`get-${url}`);
        }
        try {
            const res = await axios.get(url);
            removeProcess(`get-${url}`);
            if(res.data.message){
                addNotification({text: res.data.message.text, type: res.data.message.type});
            }
            return res.data;
        } catch (err) {
            console.log(err);
            if(err.response){
                addNotification({ text: err.response.data.message, type: 'error' });
            } else {
                addNotification({ text: err, type: 'error' });
            }
        }
        removeProcess(`get-${url}`);
    }

    const post = async (url, payload = {}, globalLoading = false) => {
        if(globalLoading){
            addProcess(`post-${url}`);
        }
        try {
            const res = await axios.post(url, payload);
            removeProcess(`post-${url}`);
            if(res.data.message){
                addNotification({text: res.data.message.text, type: res.data.message.type});
            }
            return res.data;
        } catch (err) {
            console.log(err);
            if(err.response){
                addNotification({ text: err.response.data.message, type: 'error' });
            } else {
                addNotification({ text: err, type: 'error' });
            }
        }
        removeProcess(`post-${url}`);
    }

    const patch = async (url, payload = {}, globalLoading = false) => {
        if(globalLoading){
            addProcess(`patch-${url}`);
        }
        try {
            const res = await axios.patch(url, payload);
            removeProcess(`patch-${url}`);
            if(res.data.message){
                addNotification({text: res.data.message.text, type: res.data.message.type});
            }
            return res.data;
        } catch (err) {
            console.log(err);
            if(err.response){
                addNotification({ text: err.response.data.message, type: 'error' });
            } else {
                addNotification({ text: err, type: 'error' });
            }
        }
        removeProcess(`patch-${url}`);
    }

    const del = async (url, payload = {}, globalLoading = false) => {
        if(globalLoading){
            addProcess(`delete-${url}`);
        }
        try {
            const res = await axios.delete(url, payload);
            removeProcess(`delete-${url}`);
            if(res.data.message){
                addNotification({text: res.data.message.text, type: res.data.message.type});
            }
            return res.data;
        } catch (err) {
            console.log(err);
            if(err.response){
                addNotification({ text: err.response.data.message, type: 'error' });
            } else {
                addNotification({ text: err, type: 'error' });
            }
        }
        removeProcess(`delete-${url}`);
    }

    return {get, post, patch, del}
}