import axios from 'axios';

function initAxios() {
    // @ts-ignore
    window.axios = axios;
    // @ts-ignore
    window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
}
// @ts-ignore
export const aiFeedback = async (activityId: string, sessionId: string) => {
    // @ts-ignore
    if (window.axios === undefined) {
        initAxios();
    }
    if (!sessionId || !activityId) return;

    try {
        // @ts-ignore
        const response = await window.axios.get(
            `/feedback/${activityId}/${sessionId}`,
        );
        return response.data;
    } catch (err) {
        window.console.error(err);
        return null;
    }
};
