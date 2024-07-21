import axios from 'axios';

function initAxios() {
    // @ts-ignore
    window.axios = axios;
    // @ts-ignore
    window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
}
export const aiFeedback = async (data) => {
    // @ts-ignore
    if (window.axios === undefined) {
        initAxios();
    }
    if (!data.id) return;

    try {
        // @ts-ignore
        const response = await window.axios.get(`/feedback/${data.id}`);
        return response.data;
    } catch (err) {
        window.console.error(err);
        return null;
    }
};
