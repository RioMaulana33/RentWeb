<template>
    <div class="w-100 d-flex justify-content-center">
      <div class="w-100" style="max-width: 500px;">
        <VForm class="form w-100" @submit="resetPassword" :validation-schema="passwordSchema">
          <!--begin::Heading-->
          <div class="text-center mb-10">
            <h1 class="mb-3">Reset Password</h1>
            <div class="text-gray-400 fw-bold fs-4">Masukkan email dan password baru Anda.</div>
          </div>
          <!--end::Heading-->
  
          <!--begin::Input group-->
          <div class="fv-row mb-10">
            <label class="form-label fs-6 fw-bold ">Email</label>
            <Field
              tabindex="1"
              class="form-control form-control-lg form-control-solid"
              type="email"
              name="email"
              autocomplete="off"
              v-model="email"
            />
            <div class="fv-plugins-message-container">
              <div class="fv-help-block">
                <ErrorMessage name="email" />
              </div>
            </div>
          </div>
          <!--end::Input group-->
  
          <!--begin::Input group-->
          <div class="fv-row mb-10">
            <label class="form-label fs-6 fw-bold ">Password Baru</label>
            <div class="position-relative">
              <Field
                tabindex="2"
                class="form-control form-control-lg form-control-solid"
                :type="showPassword ? 'text' : 'password'"
                name="password"
                autocomplete="off"
                v-model="password"
              />
              <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2" @click="togglePassword">
                <i :class="['bi', showPassword ? 'bi-eye' : 'bi-eye-slash', 'fs-2']"></i>
              </span>
            </div>
            <div class="fv-plugins-message-container">
              <div class="fv-help-block">
                <ErrorMessage name="password" />
              </div>
            </div>
          </div>
          <!--end::Input group-->
  
          <!--begin::Input group-->
          <div class="fv-row mb-10">
            <label class="form-label fs-6 fw-bold ">Konfirmasi Password Baru</label>
            <Field
              tabindex="3"
              class="form-control form-control-lg form-control-solid"
              :type="showPassword ? 'text' : 'password'"
              name="password_confirmation"
              autocomplete="off"
              v-model="passwordConfirmation"
            />
            <div class="fv-plugins-message-container">
              <div class="fv-help-block">
                <ErrorMessage name="password_confirmation" />
              </div>
            </div>
          </div>
          <!--end::Input group-->
  
          <!--begin::Actions-->
          <div class="text-center">
            <button tabindex="4" type="submit" ref="passwordSubmitButton" class="btn btn-lg btn-primary w-100 mb-5">
              <span class="indicator-label">Reset Password</span>
              <span class="indicator-progress">
                Mohon tunggu...
                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
              </span>
            </button>
            <router-link to="/sign-in" class="link-primary fs-6 fw-bold">
              Kembali ke Halaman Login
            </router-link>
          </div>
          <!--end::Actions-->
        </VForm>
  
        <div v-if="errorMessage" class="alert alert-danger mt-5">
          {{ errorMessage }}
        </div>
        <div v-if="successMessage" class="alert alert-success mt-5">
          {{ successMessage }}
        </div>
      </div>
    </div>
  </template>
  
  <script lang="ts">
  import { defineComponent, ref } from 'vue';
  import { useRouter } from 'vue-router';
  import * as Yup from 'yup';
  import axios from '@/libs/axios';
  import { toast } from 'vue3-toastify';
  import { blockBtn, unblockBtn } from '@/libs/utils';
  
  export default defineComponent({
    name: 'PasswordReset',
    setup() {
      const router = useRouter();
      const email = ref('');
      const password = ref('');
      const passwordConfirmation = ref('');
      const showPassword = ref(false);
      const errorMessage = ref('');
      const successMessage = ref('');
      const passwordSubmitButton = ref(null);
  
      const passwordSchema = Yup.object().shape({
        email: Yup.string().email('Email tidak valid').required('Harap masukkan email').label('Email'),
        password: Yup.string().min(8, 'Password minimal terdiri dari 8 karakter').required('Harap masukkan password baru').label('Password'),
        password_confirmation: Yup.string().oneOf([Yup.ref('password'), null], 'Passwords harus sama').required('Harap konfirmasi password baru').label('Konfirmasi Password'),
      });
  
      const resetPassword = async () => {
        errorMessage.value = '';
        blockBtn(passwordSubmitButton.value);
  
        try {
          await axios.post('/auth/reset-password', {
            email: email.value,
            password: password.value,
            password_confirmation: passwordConfirmation.value,
          });
          successMessage.value = 'Password berhasil direset.';
          toast.success(successMessage.value);
          setTimeout(() => router.push('/sign-in'), 5000);
        } catch (error) {
          errorMessage.value = error.response?.data?.message || 'Terjadi kesalahan saat mereset password.';
          toast.error(errorMessage.value);
        } finally {
          unblockBtn(passwordSubmitButton.value);
        }
      };
  
      const togglePassword = () => {
        showPassword.value = !showPassword.value;
      };
  
      return {
        email,
        password,
        passwordConfirmation,
        showPassword,
        errorMessage,
        successMessage,
        passwordSubmitButton,
        passwordSchema,
        resetPassword,
        togglePassword,
      };
    },
  });
  </script>