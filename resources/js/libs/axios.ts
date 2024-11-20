import Axios from "axios";
import JwtService from "@/core/services/JwtService";
import { useTahunStore } from "@/stores/tahun";
import { formDataToObject } from "./utils";

const axios = Axios.create({
    baseURL: import.meta.env.VITE_APP_API_URL,
    transformRequest: [
        (data) => {
            const store = useTahunStore();
            if (data instanceof FormData) {
                if (formDataToObject(data).tahun) return data;

                data.append("tahun", store.tahun);
                return data;
            } else {
                if (data?.tahun) return data;

                return {
                    ...data,
                    tahun: store.tahun,
                };
            }
        },
        ...Axios.defaults.transformRequest,
    ],
});

axios.interceptors.request.use((config) => {
    config.headers["Authorization"] = "Bearer " + JwtService.getToken();
    config.headers["Accept"] = "application/json";

    return config;
});

axios.interceptors.response.use((response) => {
    if (response.data == null) {
        return Promise.reject({
            error: "Error",
            message: "Error",
        });
    }

    if (response.data.code == "0") {
        return Promise.reject({
            error: "Error",
            message: response.data.msg,
        });
    }

    return response;
});

export default axios;
