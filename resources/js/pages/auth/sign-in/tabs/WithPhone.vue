<template>
    <VForm class="form w-100" @submit="submit" :validation-schema="login">
        <!--begin::Input group-->
        <div class="fv-row mb-10">
            <!--begin::Label-->
            <label class="form-label fs-6 fw-bold text-dark">{{ $t('login.telepon') }}</label>
            <!--end::Label-->

            <!--begin::Input-->
            <Field tabindex="1" class="form-control form-control-lg form-control-solid" type="text" name="identifier"
                autocomplete="off" v-model="data.identifier" />
            <!--end::Input-->
            <div class="fv-plugins-message-container">
                <div class="fv-help-block">
                    <ErrorMessage name="identifier" />
                </div>
            </div>
        </div>
        <!--end::Input group-->

        <!--begin::Input group-->
        <Field tabindex="1" class="form-control form-control-lg form-control-solid" type="hidden" name="otp"
            autocomplete="off" v-model="data.otp" />
        <div v-if="/^08[0-9]\d{8,11}$/.test(data.identifier)" class="fv-row mb-5">
            <label class="form-label fw-bold text-dark fs-6 d-flex align-items-center justify-content-between">{{
                $t('login.otp') }}
            </label>
            <v-otp-input input-classes="form-control form-control-lg form-control-solid text-center" separator="-"
                :num-inputs="6" input-type="numeric" v-model:value="data.otp" />
            <div class="fv-plugins-message-container">
                <div class="fv-help-block">
                    <ErrorMessage name="otp" />
                </div>
            </div>
            <div v-if="otpInterval == 0" class="text-gray-400 fw-semobold fs-4 text-end mt-4">
                {{ $t('login.otp_label') }}
                <button type="button" class="btn p-0 link-primary fw-bold" @click="sendOtp">
                    {{ $t('login.otp_kirim') }}
                </button>
            </div>
            <div v-else class="text-gray-400 fw-semobold fs-4 text-end mt-4">
                {{ $t('login.otp_resend') }} <span class="fw-bold">{{ otpInterval }}</span> {{ $t('login.otp_detik') }}
            </div>
        </div>
        <!--end::Input group-->

        <div v-if="/^08[0-9]\d{8,11}$/.test(data.identifier)" class="form-check mb-10">
            <Field tabindex="3" class="form-check-input" type="checkbox" id="remember_me_hp" name="remember_me" value="1"
                v-model="data.remember_me" />
            <label class="form-check-label" for="remember_me_hp">
                {{ $t('login.remember') }}
            </label>
        </div>

        <!--begin::Actions-->
        <div class="text-center">
            <!--begin::Submit button-->
            <button tabindex="3" type="submit" ref="submitButton" class="btn btn-lg btn-primary w-100 mb-5">
                <span class="indicator-label">Login</span>

                <span class="indicator-progress">
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                </span>
            </button>
            <!--end::Submit button-->
        </div>
        <!--end::Actions-->
    </VForm>
</template>

<script lang="ts">
import { getAssetPath } from "@/core/helpers/assets";
import { defineComponent, ref } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useRouter } from "vue-router";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify"
import { blockBtn, unblockBtn } from "@/libs/utils"

export default defineComponent({
    setup() {
        const store = useAuthStore();
        const router = useRouter();

        const submitButton = ref(null);

        //Create form validation object
        const login = Yup.object().shape({
            identifier: Yup.string().matches(/^08[0-9]\d{8,11}$/, "No. Telepon tidak valid").required("Harap masukkan No. Telepon").label("No. Telepon"),
            otp: Yup.string().min(6, "Harap masukkan Kode OTP").required('Harap masukkan Kode OTP').label("Kode OTP"),
        });

        const otpInterval = ref(0);
        const timeIntv = ref<any>(null);

        return {
            login,
            submitButton,
            getAssetPath,
            store, router,
            otpInterval,
            timeIntv,
        };
    },
    data() {
        return {
            data: {
                identifier: "",
                otp: ''
            },
        }
    },
    methods: {
        submit() {
            blockBtn(this.submitButton);

            axios.post("/auth/login", { ...this.data, type: "phone" }).then(res => {
                this.store.setAuth(res.data.user, res.data.token);
                this.router.push("/dashboard");
            }).catch(error => {
                console.log(error)
                toast.error(error.response.data.message);
            }).finally(() => {
                unblockBtn(this.submitButton);
            });
        },
        sendOtp() {
            blockBtn(this.submitButton);

            axios.post('/auth/getOtp', {
                identifier: this.data.identifier,
            }).then((res) => {
                toast.success('Kode OTP berhasil dikirim ke No. Telepon Anda')
                unblockBtn(this.submitButton);

                this.otpInterval = 30;
                this.handleOtpInterval();
            }).catch((err) => {
                toast.error(err.response.data.message)
                unblockBtn(this.submitButton);
            })
        },
        handleOtpInterval() {
            clearInterval(this.timeIntv);

            this.timeIntv = setInterval(() => {
                this.otpInterval--;

                if (this.otpInterval === 0) {
                    clearInterval(this.timeIntv);
                }
            }, 1000);
        }
    },
})
</script>
