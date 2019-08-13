import {LOGIN, LOGOUT, SIGNUP, RESET_PASSWORD, FETCH_CURRENT_USER} from './types/actions';
import {
    SET_AUTHENTICATED_USER,
    USER_LOGIN,
    USER_LOGOUT,
} from "./types/mutations";
import {authorize, getAuthUser, registerUser} from '@/api/auth';
import {HAS_TOKEN} from "./types/getters";

export default {
    [LOGIN]: (context, user) => {
        return authorize(user)
            .then(response => {
                context.commit(USER_LOGIN, response.data);

                return getAuthUser()
                    .then(response => {
                        const user = response.data;
                        context.commit(SET_AUTHENTICATED_USER, user);

                        return user;
                    });
            });
    },

    [LOGOUT]: (context) => {
        return new Promise(resolve => {
            context.commit(USER_LOGOUT);
            resolve();
        });
    },

    [SIGNUP]: (context, newUser) => {
        return registerUser(newUser);
    },

    [RESET_PASSWORD]: () => {
        return new Promise((resolve, reject) => {
            const fakeResponse = 201;
            switch (fakeResponse) {
                case 201:
                    resolve("Your reset password link was created. Check your email, please.");
                    break;
                case 500:
                    reject("Sorry, something wrong happened. Please, try again.");
                    break;
                default:
                    reject("User with this email does not exist. Please, check if the password is correct.");
            }
        })
    },

    [FETCH_CURRENT_USER]: (context) => {
        if (!context.getters[HAS_TOKEN]) {
            return;
        }
        return getAuthUser().then(response => {
            context.commit(SET_AUTHENTICATED_USER, response.data);
        }).catch(() => {
            context.commit(USER_LOGOUT);
        });
    },

}