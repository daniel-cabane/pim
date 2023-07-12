import { reactive } from "vue";

const processes = reactive([]);

const addProcess = p => processes.push(p);

const removeProcess = p => {
    const index = processes.findIndex(process => process == p);
    processes.splice(index, 1);
}

const clear = () => processes.splice(0, processes.length);

export default function useNotifications () {

    return { processes, addProcess, removeProcess, clear }

}
