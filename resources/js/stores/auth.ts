import { ref } from "vue";
import { defineStore } from "pinia";
import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";

export interface User {
    id: number;
    uuid: string;
    name: string;
    email: string;
    phone: string;
    photo?: string;
    password: string;
    permission: Array<string>;
    role?: {
        name: string;
        full_name: string;
    };
}

export const useAuthStore = defineStore("auth", () => {
    const error = ref<null | string>(null);
    const user = ref<User>({} as User);
    const isAuthenticated = ref(false);

    function setAuth(authUser: User, token = "") {
        isAuthenticated.value = true;
        user.value = authUser;
        error.value = null;

        if (token) {
            JwtService.saveToken(token);
        }
    }

    function purgeAuth() {
        isAuthenticated.value = false;
        user.value = {} as User;
        error.value = null;
        JwtService.destroyToken();
    }

    async function login(credentials: User) {
        return ApiService.post("auth/login", credentials)
            .then(({ data }) => {
                setAuth(data.user, data.token);
            })
            .catch(({ response }) => {
                error.value = response.data.message;
            });
    }

    async function logout() {
        if (JwtService.getToken()) {
            ApiService.setHeader();
            await ApiService.delete("auth/logout");
            purgeAuth();
        } else {
            purgeAuth();
        }
    }

    async function register(credentials: User) {
        return ApiService.post("auth/register", credentials)
            .then(({ data }) => {
                setAuth(data.user, data.token);
            })
            .catch(({ response }) => {
                error.value = response.data.message;
            });
    }

    async function forgotPassword(email: string) {
        return ApiService.post("auth/forgot_password", email)
            .then(() => {
                error.value = null;
            })
            .catch(({ response }) => {
                error.value = response.data.message;
            });
    }

    async function verifyAuth() {
        if (JwtService.getToken()) {
            ApiService.setHeader();
            await ApiService.get("auth/me")
                .then(({ data }) => {
                    setAuth(data.user);
                })
                .catch(({ response }) => {
                    error.value = response.data.message;
                    purgeAuth();
                });
        } else {
            purgeAuth();
        }
    }

    return {
        error,
        user,
        isAuthenticated,
        login,
        logout,
        register,
        forgotPassword,
        verifyAuth,
        setAuth,
        purgeAuth,
    };
});
