import { useQuery } from "@tanstack/vue-query";
import axios from "@/libs/axios";

export function useRole(options = {}) {
    return useQuery({
        queryKey: ["roles"],
        queryFn: async () =>
            await axios.get("/master/roles").then((res: any) => res.data.data),
        ...options,
    });
}
