<template>
    <section class="w-100">
        <!--begin::Input group-->
        <div class="fv-row mb-7">
            <label class="form-label fw-bold text-dark fs-6">Password</label>
            <div class="position-relative mb-3">
                <!--begin::Input-->
                <Field class="form-control form-control-lg form-control-solid" type="password" name="password"
                    autocomplete="off" v-model="formData.password" />
                <!--end::Input-->

                <!--begin::Visibility toggle-->
                <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2">
                    <i class="bi bi-eye-slash fs-2" @click="togglePassword"></i>
                </span>
                <!--end::Visibility toggle-->
            </div>
            <div class="fv-plugins-message-container">
                <div class="fv-help-block">
                    <ErrorMessage name="password" />
                </div>
            </div>
        </div>
        <!--end::Input group-->

        <!--begin::Input group-->
        <div class="fv-row mb-7">
            <label class="form-label fw-bold text-dark fs-6">Konfirmasi Password</label>
            <div class="position-relative mb-3">
                <!--begin::Input-->
                <Field class="form-control form-control-lg form-control-solid" type="password" name="password_confirmation"
                    autocomplete="off" v-model="formData.password_confirmation" />
                <!--end::Input-->

                <!--begin::Visibility toggle-->
                <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2">
                    <i class="bi bi-eye-slash fs-2" @click="toggleConfirmPassword"></i>
                </span>
                <!--end::Visibility toggle-->
            </div>
            <div class="fv-plugins-message-container">
                <div class="fv-help-block">
                    <ErrorMessage name="password_confirmation" />
                </div>
            </div>
        </div>
        <!--end::Input group-->
    </section>
</template>

<script lang="ts">
import { defineComponent } from 'vue'


export default defineComponent({
    name: 'Password',
    props: {
        formData: {
            type: Object,
            required: true,
        },
    },
    setup(props) {
        return {
            formData: props.formData,
        }
    },
    methods: {
        togglePassword(ev) {
            const type = document.querySelector("[name=password]").type;

            if (type === 'password') {
                document.querySelector("[name=password]").type = 'text';
                ev.target.classList.add("bi-eye");
                ev.target.classList.remove("bi-eye-slash");
            } else {
                document.querySelector("[name=password]").type = 'password';
                ev.target.classList.remove("bi-eye");
                ev.target.classList.add("bi-eye-slash");
            }
        },
        toggleConfirmPassword(ev) {
            const type = document.querySelector("[name=password_confirmation]").type;

            if (type === 'password') {
                document.querySelector("[name=password_confirmation]").type = 'text';
                ev.target.classList.add("bi-eye");
                ev.target.classList.remove("bi-eye-slash");
            } else {
                document.querySelector("[name=password_confirmation]").type = 'password';
                ev.target.classList.remove("bi-eye");
                ev.target.classList.add("bi-eye-slash");
            }
        }
    }
})
</script>
