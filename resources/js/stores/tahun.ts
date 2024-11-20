import { ref, watch } from "vue";
import { defineStore } from "pinia";

export const useTahunStore = defineStore("tahun", () => {
    const tahun = ref(localStorage.getItem("app-tahun") || new Date().getFullYear());

    function setTahun(payload) {
        tahun.value = payload
    }

    watch(tahun, (newTahun) => {
        localStorage.setItem("app-tahun", newTahun)
        window.location.reload()
    })

    return {
        tahun,
        setTahun,
    };
});
