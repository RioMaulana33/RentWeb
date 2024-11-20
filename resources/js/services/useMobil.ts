import { useQuery } from "@tanstack/vue-query";
import axios from "@/libs/axios";

export function useMobil(options = {}) {
    return useQuery({
        queryKey: ["mobil"],
        queryFn: async () =>
            await axios.get("/mobil/get").then((res: any) => res.data.data),
        ...options,
    });
}
