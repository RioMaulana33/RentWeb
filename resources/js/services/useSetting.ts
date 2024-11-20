import { useQuery } from "@tanstack/vue-query";
import axios from "@/libs/axios";

export function useSetting(options = {}) {
    return useQuery({
        queryKey: ['app', 'setting'],
        queryFn: () => axios.get("/setting").then((res) => res.data),
        ...options
    });
}