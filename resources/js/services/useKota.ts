import { useQuery } from "@tanstack/vue-query";
import axios from "@/libs/axios";

export function useKota(options = {}) {
    return useQuery({
        queryKey: ["kota"],
        queryFn: async () =>
            await axios.get("/kota/get").then((res: any) => res.data.data),
        ...options,
    });
}
