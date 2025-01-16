<template>
    <!--begin::Form-->
    <div class="w-100">
        <!--begin::Heading-->
        <div class="text-center mb-10">
            <router-link to="/">
                <img
                    :src="setting?.inlog"
                    :alt="setting?.app"
                    class="w-100px mb-8"
                />
            </router-link>
            <!--begin::Title-->
            <h1 class="mb-3">
                Masuk ke <span class="text-primary">{{ setting?.app }}</span>
            </h1>
            <!--end::Title-->
        </div>
        <!--end::Heading-->

        <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
            <li class="nav-item">
                <a
                    class="nav-link active"
                    data-bs-toggle="tab"
                    href="#with-email"
                >Email</a>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
            <div
                class="tab-pane fade show active"
                id="with-email"
                role="tabpanel"
            >
                <WithEmail /> <!-- Memanggil komponen WithEmail -->
            </div>
        </div>

        <div class="border-bottom border-gray-300 w-100 mt-5 mb-10"></div>

        <!--begin::Forgot Password Link-->
        <div class="text-center mt-4">
            <!-- <router-link to="/password-reset" class="link-primary fs-6 fw-bold">
                Reset Password?
            </router-link> -->
        </div>
        <!--end::Forgot Password Link-->

        <!--begin::Link-->
        <!--
        <div class="text-gray-400 fw-semobold fs-4 text-center">
            {{ $t('login.daftar_label') }}

            <router-link to="/auth/sign-up" class="link-primary fw-bold">
                {{ $t('login.daftar') }}
            </router-link>
        </div>
        -->
        <!--end::Link-->
    </div>
    <!--end::Form-->
</template>

<script>
import { defineComponent, ref } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useRouter } from "vue-router";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import { blockBtn, unblockBtn } from "@/libs/utils";
import WithEmail from "./tabs/WithEmail.vue"; // Pastikan jalur ini benar
import { useSetting } from "@/services";

export default defineComponent({
    name: "sign-in",
    components: {
        WithEmail, // Daftarkan komponen di sini
    },
    setup() {
        const store = useAuthStore();
        const router = useRouter();
        const { data: setting = {} } = useSetting();

        const submitButton = ref(null);

        const login = Yup.object().shape({
            identifier: Yup.string()
                .email("Email/No. Telepon tidak valid")
                .required("Harap masukkan Email/No. Telepon")
                .label("Email"),
            password: Yup.string()
                .min(8, "Password minimal terdiri dari 8 karakter")
                .required("Harap masukkan password")
                .label("Password"),
        });

        return {
            login,
            submitButton,
            store,
            router,
            setting,
        };
    },
    data() {
        return {
            data: {
                identifier: null,
                password: null,
            },
            check: {
                type: "",
                error: "",
            },
        };
    },
    methods: {
        submit() {
            blockBtn(this.submitButton);

            axios
                .post("/auth/login", { ...this.data, type: this.check.type })
                .then((res) => {
                    this.store.setAuth(res.data.user, res.data.token);
                    this.router.push("/dashboard");
                })
                .catch((error) => {
                    toast.error(error.response.data.message);
                })
                .finally(() => {
                    unblockBtn(this.submitButton);
                });
        },
        checkInput(value) {
            this.check.type = "";
            if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(value)) {
                this.check.type = "email";
            } else {
                this.check.type = "phone";
                if (isNaN(this.data.identifier)) {
                    this.check.type =
                        "Masukkan Email / No. Telepon Yang Valid!";
                }
            }
        },
    },
});
</script>

