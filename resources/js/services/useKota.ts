import { useQuery } from "@tanstack/vue-query";
import axios from "@/libs/axios";

export function useKota(options = {}) {
    return useQuery({
        queryKey: ["kota"],
        queryFn: async () => {
            const response = await axios.get("/kota/get-by-user");
            return response.data.data;
        },
        ...options,
    });
}