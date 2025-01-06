import { useQuery } from "@tanstack/vue-query";
import axios from "@/libs/axios";

export function useDelivery(options = {}) {
    return useQuery({
        queryKey: ["delivery"],
        queryFn: async () =>
            await axios.get("/delivery/get").then((res: any) => res.data.data),
        ...options,
    });
}
