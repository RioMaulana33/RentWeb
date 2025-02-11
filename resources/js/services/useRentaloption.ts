import { useQuery } from "@tanstack/vue-query";
import axios from "@/libs/axios";

export function useRentaloption(options = {}) {
    return useQuery({
        queryKey: ["rentaloption"],
        queryFn: async () =>
            await axios.get("/rentaloption/get").then((res: any) => res.data.data),
        ...options,
    });
}
