<template>
    <!--begin::Wrapper-->
    <div class="w-lg-500px w-100">
        <!--begin::Form-->
        <main class="form w-100 fv-plugins-bootstrap5 fv-plugins-framework">
            <!--begin::Heading-->
            <div class="mb-10 text-center">
                <router-link to="/">
                    <img
                        :src="setting?.logo_depan"
                        alt="Logo Jombang"
                        class="w-100px mb-8"
                    />
                </router-link>
                <!--begin::Title-->
                <h1 class="text-dark mb-3">
                    Daftar Akun <span class="text-primary">SI-LAJANG</span>
                </h1>
                <!--end::Title-->
            </div>
            <!--end::Heading-->

            <div
                class="stepper stepper-links d-flex flex-column"
                id="kt_create_account_stepper"
                ref="horizontalWizardRef"
            >
                <!--begin::Nav-->
                <div class="stepper-nav py-5 mt-5 d-none">
                    <!--begin::Step 1-->
                    <div
                        class="stepper-item current"
                        data-kt-stepper-element="nav"
                    >
                        <h3 class="stepper-title">Akun</h3>
                    </div>
                    <!--end::Step 1-->

                    <!--begin::Step 2-->
                    <div class="stepper-item" data-kt-stepper-element="nav">
                        <h3 class="stepper-title">Verifikasi Email</h3>
                    </div>
                    <!--end::Step 2-->

                    <!--begin::Step 2-->
                    <div class="stepper-item" data-kt-stepper-element="nav">
                        <h3 class="stepper-title">Verifikasi Telepon</h3>
                    </div>
                    <!--end::Step 2-->

                    <!--begin::Step 3-->
                    <div class="stepper-item" data-kt-stepper-element="nav">
                        <h3 class="stepper-title">Password</h3>
                    </div>
                    <!--end::Step 3-->
                </div>
                <!--end::Nav-->

                <!--begin::Form-->
                <form
                    class="mx-auto mw-600px w-100 pt-15 pb-10"
                    novalidate
                    id="kt_create_account_form"
                    @submit="handleStep"
                >
                    <!--begin::Step 1-->
                    <div class="current" data-kt-stepper-element="content">
                        <Credential :formData="formData"></Credential>
                    </div>
                    <!--end::Step 1-->

                    <!--begin::Step 2-->
                    <div data-kt-stepper-element="content">
                        <VerifyEmail
                            :formData="formData"
                            @on-complete="handleOtpEmail"
                            @send-otp="sendOtpEmail"
                        ></VerifyEmail>
                    </div>
                    <!--end::Step 2-->

                    <!--begin::Step 3-->
                    <div data-kt-stepper-element="content">
                        <VerifyPhone
                            :formData="formData"
                            @on-complete="handleOtpPhone"
                            @send-otp="sendOtpPhone"
                        ></VerifyPhone>
                    </div>
                    <!--end::Step 3-->

                    <!--begin::Step 4-->
                    <div data-kt-stepper-element="content">
                        <Password :formData="formData"></Password>
                    </div>
                    <!--end::Step 4-->

                    <!--begin::Actions-->
                    <div class="d-flex flex-stack pt-15">
                        <!--begin::Wrapper-->
                        <div class="mr-2">
                            <button
                                type="button"
                                class="btn btn-lg btn-light-primary me-3"
                                data-kt-stepper-action="previous"
                                @click="previousStep"
                            >
                                <KTIcon
                                    icon-name="arrow-left"
                                    icon-class="fs-4 me-1"
                                />
                                Kembali
                            </button>
                        </div>
                        <!--end::Wrapper-->

                        <!--begin::Wrapper-->
                        <div>
                            <button
                                type="submit"
                                id="submit-form"
                                class="btn btn-lg btn-primary me-3"
                                data-kt-stepper-action="submit"
                                v-if="currentStepIndex === totalSteps - 1"
                            >
                                <span class="indicator-label">
                                    Daftar
                                    <KTIcon
                                        icon-name="arrow-right"
                                        icon-class="fs-3 ms-2 me-0"
                                    />
                                </span>
                                <span class="indicator-progress">
                                    Memproses...
                                    <span
                                        class="spinner-border spinner-border-sm align-middle ms-2"
                                    ></span>
                                </span>
                            </button>

                            <button
                                v-else
                                type="submit"
                                id="next-form"
                                class="btn btn-lg btn-primary"
                            >
                                <span class="indicator-label">
                                    Selanjutnya
                                    <KTIcon
                                        icon-name="arrow-right"
                                        icon-class="fs-4 ms-2 me-0"
                                    />
                                </span>
                                <span class="indicator-progress">
                                    <span
                                        class="spinner-border spinner-border-sm align-middle ms-2"
                                    ></span>
                                </span>
                            </button>
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Actions-->
                </form>
                <!--end::Form-->
            </div>

            <div class="border-bottom border-gray-300 w-100 mt-5 mb-10"></div>

            <!--begin::Link-->
            <div class="text-gray-400 fw-semobold fs-4 text-center">
                Sudah memiliki akun?

                <router-link to="/auth/sign-in" class="link-primary fw-bold">
                    Masuk
                </router-link>
            </div>
            <!--end::Link-->
        </main>
        <!--end::Form-->
    </div>
    <!--end::Wrapper-->
</template>

<script lang="ts">
import { getAssetPath } from "@/core/helpers/assets";
import { defineComponent, ref, onMounted, computed } from "vue";
import * as Yup from "yup";
import create from "vue-zustand";

import { StepperComponent } from "@/assets/ts/components";
import { useForm } from "vee-validate";
import Credential from "./steps/Credential.vue";
import VerifyEmail from "./steps/VerifyEmail.vue";
import VerifyPhone from "./steps/VerifyPhone.vue";
import Password from "./steps/Password.vue";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import { blockBtn, unblockBtn } from "@/libs/utils";
import router from "@/router";
import { useSetting } from "@/services";

interface ICredential {
    nama?: string;
    email?: string;
    phone?: string;
}

interface IVerifyEmail {
    otp_email?: string;
}

interface IVerifyPhone {
    otp_phone?: string;
}

interface IPassword {
    password?: string;
    password_confirmation?: string;
}

interface CreateAccount
    extends ICredential,
        IVerifyEmail,
        IVerifyPhone,
        IPassword {}

interface IOtpInterval {
    otpInterval: number;
    setOtpInterval: (otpInterval: number) => void;
}

export const useOtpIntervalStore = create<IOtpInterval>((set) => ({
    otpInterval: 0,
    setOtpInterval: (otpInterval: number) => set({ otpInterval }),
}));

export default defineComponent({
    name: "sign-up",
    components: {
        Credential,
        VerifyEmail,
        VerifyPhone,
        Password,
    },
    setup() {
        const { data: setting = {} } = useSetting();

        const _stepperObj = ref<StepperComponent | null>(null);
        const horizontalWizardRef = ref<HTMLElement | null>(null);
        const currentStepIndex = ref(0);

        const formData = ref<CreateAccount>({
            nama: "",
            email: "",
            phone: "",
            otp_email: "",
            otp_phone: "",
            password: "",
            password_confirmation: "",
        });

        onMounted(() => {
            _stepperObj.value = StepperComponent.createInsance(
                horizontalWizardRef.value as HTMLElement
            );
        });

        const createAccountSchema = [
            Yup.object({
                nama: Yup.string()
                    .required("Nama tidak boleh kosong")
                    .label("Nama"),
                email: Yup.string()
                    .email()
                    .required("Email tidak boleh kosong")
                    .label("Email"),
                phone: Yup.string()
                    .matches(/^08[0-9]\d{8,11}$/, "No. Telepon tidak valid")
                    .required("No. Telepon tidak boleh kosong")
                    .label("No. Telepon"),
            }),
            Yup.object({}),
            Yup.object({}),
            Yup.object({
                password: Yup.string()
                    .min(8, "Password minimal terdiri dari 8 karakter")
                    .required("Password tidak boleh kosong")
                    .label("Password"),
                password_confirmation: Yup.string()
                    .oneOf(
                        [Yup.ref("password")],
                        "Konfirmasi Password tidak sesuai"
                    )
                    .required("Konfirmasi Password tidak boleh kosong")
                    .label("Konfirmasi Password"),
            }),
        ];

        const currentSchema = computed(() => {
            return createAccountSchema[currentStepIndex.value];
        });

        const { resetForm, handleSubmit } = useForm<
            ICredential | IVerifyEmail | IVerifyPhone | IPassword
        >({
            validationSchema: currentSchema,
        });

        const totalSteps = computed(() => {
            if (_stepperObj.value) {
                return _stepperObj.value.totalStepsNumber;
            } else {
                return 1;
            }
        });

        const { otpInterval, setOtpInterval } = useOtpIntervalStore();

        const sendOtpEmail = (callback: any) => {
            blockBtn("#next-form");

            axios
                .post("/auth/register/get/email/otp", {
                    email: formData.value.email,
                    nama: formData.value.nama,
                })
                .then((res) => {
                    toast.success("Kode OTP berhasil dikirim ke Email Anda");
                    unblockBtn("#next-form");
                    callback && callback();

                    setOtpInterval.value(30);
                    handleOtpInterval();
                })
                .catch((err) => {
                    toast.error(err.response.data.message);
                    unblockBtn("#next-form");
                });
        };

        const checkOtpEmail = (callback: any) => {
            blockBtn("#next-form");

            axios
                .post("/auth/register/check/email/otp", {
                    email: formData.value.email,
                    otp: formData.value.otp_email,
                })
                .then((res) => {
                    toast.success("Email berhasil diverifikasi");
                    unblockBtn("#next-form");
                    callback && callback();
                })
                .catch((err) => {
                    toast.error(err.response.data.message);
                    unblockBtn("#next-form");
                });
        };

        const sendOtpPhone = (callback: any) => {
            blockBtn("#next-form");

            axios
                .post("/auth/register/get/phone/otp", {
                    phone: formData.value.phone,
                })
                .then((res) => {
                    toast.success(
                        "Kode OTP berhasil dikirim ke No. Telepon Anda"
                    );
                    unblockBtn("#next-form");
                    callback && callback();

                    setOtpInterval.value(30);
                    handleOtpInterval();
                })
                .catch((err) => {
                    toast.error(err.response.data.message);
                    unblockBtn("#next-form");
                });
        };

        const checkOtpPhone = (callback: any) => {
            blockBtn("#next-form");

            axios
                .post("/auth/register/check/phone/otp", {
                    phone: formData.value.phone,
                    otp: formData.value.otp_phone,
                })
                .then((res) => {
                    toast.success("No. Telepon berhasil diverifikasi");
                    unblockBtn("#next-form");
                    callback && callback();
                })
                .catch((err) => {
                    toast.error(err.response.data.message);
                    unblockBtn("#next-form");
                });
        };

        const handleStep = handleSubmit((values) => {
            resetForm({
                values: {
                    ...formData.value,
                },
            });

            if (currentStepIndex.value === 0) {
                sendOtpEmail(() => {
                    formData.value = { ...values };

                    currentStepIndex.value++;

                    if (!_stepperObj.value) {
                        return;
                    }

                    _stepperObj.value.goNext();
                });
            } else if (currentStepIndex.value === 1) {
                checkOtpEmail(() =>
                    sendOtpPhone(() => {
                        formData.value = { ...values };

                        currentStepIndex.value++;

                        if (!_stepperObj.value) {
                            return;
                        }

                        _stepperObj.value.goNext();
                    })
                );
            } else if (currentStepIndex.value === 2) {
                checkOtpPhone(() => {
                    formData.value = { ...values };

                    currentStepIndex.value++;

                    if (!_stepperObj.value) {
                        return;
                    }

                    _stepperObj.value.goNext();
                });
            } else if (currentStepIndex.value === 3) {
                formData.value = { ...values };

                formSubmit(values);
            } else {
                formData.value = { ...values };

                currentStepIndex.value++;

                if (!_stepperObj.value) {
                    return;
                }

                _stepperObj.value.goNext();
            }
        });

        const previousStep = () => {
            if (!_stepperObj.value) {
                return;
            }

            currentStepIndex.value--;

            _stepperObj.value.goPrev();
        };

        const formSubmit = (values: CreateAccount) => {
            blockBtn("#submit-form");

            axios
                .post("/auth/register", values)
                .then((res) => {
                    toast.success("Akun berhasil dibuat");
                    router.push({ name: "sign-in" });
                })
                .catch((err) => {
                    toast.error(err.response.data.message);
                    unblockBtn("#submit-form");
                });
        };

        const timeIntv = ref<any>(null);
        const handleOtpInterval = () => {
            clearInterval(timeIntv.value);

            timeIntv.value = setInterval(() => {
                setOtpInterval.value(otpInterval.value - 1);

                if (otpInterval.value === 0) {
                    clearInterval(timeIntv.value);
                }
            }, 1000);
        };

        return {
            horizontalWizardRef,
            previousStep,
            handleStep,
            formSubmit,
            totalSteps,
            currentStepIndex,
            getAssetPath,
            formData,
            sendOtpEmail,
            sendOtpPhone,
            resetForm,
            setting,
        };
    },
    methods: {
        handleOtpEmail(value: string) {
            this.resetForm({
                values: {
                    ...this.formData,
                    otp_email: value,
                },
            });
            this.formData.otp_email = value;
        },
        handleOtpPhone(value: string) {
            this.resetForm({
                values: {
                    ...this.formData,
                    otp_phone: value,
                },
            });
            this.formData.otp_phone = value;
        },
    },
});
</script>
