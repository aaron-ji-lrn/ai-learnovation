import axios from 'axios';
// @ts-ignore
window.axios = axios;
// @ts-ignore
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

export const aiFeedback = async (data) => {
    if (!data.id) return;

    try {
        // @ts-ignore
        const response = await window.axios.post(`/aiFeedback`, data);
        const { success, message } = response.data;

        return success ? 'success' : 'error';
    } catch (err) {
        window.console.error(err);
        return null;
    }
};
