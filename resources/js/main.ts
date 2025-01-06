import "./bootstrap";

import { createApp } from "vue";
import { createPinia } from "pinia";
import { Tooltip } from "bootstrap";
import App from "./App.vue";

/*
TIP: To get started with clean router change path to @/router/clean.ts.
 */
import router from "./router";
import ElementPlus from "element-plus";
import i18n from "@/core/plugins/i18n";
import Inputmask from "inputmask";

//imports for app initialization
import ApiService from "@/core/services/ApiService";
import { initApexCharts } from "@/core/plugins/apexcharts";
import { initInlineSvg } from "@/core/plugins/inline-svg";
import { initVeeValidate } from "@/core/plugins/vee-validate";
import { initKtIcon } from "@/core/plugins/keenthemes";

import { vue3Debounce } from "vue-debounce";
import { VueQueryPlugin, QueryClient } from "@tanstack/vue-query";
import { useThemeStore } from "./stores/theme";
import Vue3Toastify, { type ToastContainerOptions } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

import "@/core/plugins/prismjs";
const app = createApp(App);

app.use(createPinia());
app.use(router);
app.use(ElementPlus);

app.directive("debounce", vue3Debounce({ lock: true }));
app.directive("mask", (el, binding) => {
    Inputmask(binding.value).mask(el);
});

import DatePicker from "@/components/DatePicker.vue";
import FileUpload from "@/components/FileUpload.vue";
import Select2 from "@/components/Select2.vue";
import Paginate from "@/components/Paginate.vue";
import Switch from "@/components/Switch.vue";

app.component("date-picker", DatePicker);
app.component("file-upload", FileUpload);
app.component("select2", Select2);
app.component("paginate", Paginate);
app.component("vswitch", Switch);

const queryClient = new QueryClient({
    defaultOptions: {
        queries: {
            refetchOnWindowFocus: false,
            refetchOnMount: false,
            retry: false,
            staleTime: 0,
            networkMode: "always",
            onError: (err: any) => {
                if (err.response) {
                    if (err.response.status === 401) {
                        window.location.href = "/auth/sign-in";
                    }
                }
            },
        },
        mutations: {
            networkMode: "always",
            onError: (err: any) => {
                if (err.response) {
                    if (err.response.status === 401) {
                        window.location.href = "/auth/sign-in";
                    }
                }
            },
        },
    },
});

app.use(VueQueryPlugin, {
    queryClient,
});

app.use(Vue3Toastify, {
    newestOnTop: true,
    pauseOnFocusLoss: false,
    pauseOnHover: false,
    theme: useThemeStore().mode,
} as ToastContainerOptions);

import CKEditor from "@ckeditor/ckeditor5-vue";
import "ckeditor5-custom-build/build/ckeditor";
app.use(CKEditor);

import { Form as VForm, Field, ErrorMessage } from "vee-validate";
app.component("VForm", VForm);
app.component("Field", Field);
app.component("ErrorMessage", ErrorMessage);

ApiService.init(app);
initApexCharts(app);
initInlineSvg(app);
initKtIcon(app);
initVeeValidate();

app.use(i18n);

app.directive("tooltip", (el) => {
    new Tooltip(el);
});

app.mount("#app");

let cssRule =
    "display:block;width:200px;border-radius: 3px 0 0 3px;padding:3px 15px;background:#108bc3;color:#FFF;font-size: 30px;font-family:Arial, Helvetica, sans-seriffont-weight: bold;";
// let cssRule2 =
//     "display:block;border-radius: 0 3px 3px 0;padding:3px 15px;background:#fff;color:#666;font-size: 30px;font-family:Arial, Helvetica, sans-serif;";
console.log(
    "%cBluCarra",
    cssRule,
    // cssRule2
);

// cssRule =
//     "border-radius: 3px 0 0 3px;padding:3px 15px;background:#35495e;color:#fff;font-size: 12px;font-weight: bold;";
// cssRule2 =
//     "border-radius:0px;padding:3px 0px;background:#35495e;color:#FF5722;padding-left:0px;font-size: 12px;font-weight: bold;";
// const cssRule3 =
//     "border-radius: 0 3px 3px 0;padding:3px 15px;background:#35495e;color:#108bc3;font-size: 12px;font-weight: bold;";
// console.log(
//     "%cThis System Development by Mcflyon Teknologi Indonesia visit" +
//         "%c@" +
//         "%chttps://www.mcflyon.co.id",
//     cssRule,
//     cssRule2,
//     cssRule3
// );
