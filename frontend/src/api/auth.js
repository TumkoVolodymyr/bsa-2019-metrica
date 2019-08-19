import requestService from "@/services/requestService";
import config from "@/config";

const resourceUrl = config.getApiUrl() + '/auth';

const authorize = params => requestService.create(resourceUrl + '/login', params);
const getAuthUser = () => requestService.get(resourceUrl + '/me');
const registerUser = params => requestService.create(resourceUrl + '/register', params);
const resetPassword = params => requestService.create(resourceUrl + '/reset-password', params);

export {
    authorize,
    getAuthUser,
    registerUser,
    resetPassword
};