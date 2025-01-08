<template>
  <div class="w-100 d-flex justify-content-center">
    <div class="w-100" style="max-width: 500px;">
      <!-- Fase 1: Tombol Kirim Email -->
      <div v-if="currentPhase === 'initial'" class="text-center">
        <h1 class="mb-3">Reset Password Admin</h1>
        <div class="text-gray-400 fw-bold fs-4 mb-5">
          Klik tombol dibawah untuk mengirim kode OTP ke email admin
        </div>

        <button 
          @click="sendEmail" 
          class="btn btn-lg btn-primary w-100 mb-5"
          :disabled="loading"
        >
          <span v-if="!loading">
            Kirim Kode OTP
          </span>
          <span v-else>
            Mohon tunggu...
            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
          </span>
        </button>
      </div>

      <!-- Fase 2: Form Verifikasi OTP -->
      <div v-else-if="currentPhase === 'awaiting_otp'">
        <VForm class="form w-100" @submit="verifyOTP" :validation-schema="otpSchema">
          <div class="text-center mb-10">
            <h1 class="mb-3">Verifikasi OTP</h1>
            <div class="text-gray-400 fw-bold fs-4">
              Masukkan kode OTP yang telah dikirim ke email admin
            </div>
          </div>

          <div class="fv-row mb-10">
            <label class="form-label fs-6 fw-bold">Kode OTP</label>
            <Field
              type="text"
              class="form-control form-control-lg form-control-solid text-center"
              name="otp"
              maxlength="6"
              v-model="otpCode"
              placeholder="6 Digit"
            />
            <div class="fv-plugins-message-container">
              <div class="fv-help-block">
                <ErrorMessage name="otp" />
              </div>
            </div>
          </div>

          <div class="text-center">
            <button 
              type="submit" 
              ref="otpSubmitButton" 
              class="btn btn-lg btn-primary w-100 mb-3"
              :disabled="loading"
            >
              <span v-if="!loading" class="indicator-label">
                Verifikasi OTP
              </span>
              <span v-else class="indicator-progress">
                Mohon tunggu...
                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
              </span>
            </button>

            <button 
              @click="resendOTP" 
              class="btn btn-lg btn-light w-100"
              :disabled="loading || resendTimer > 0"
            >
              <span v-if="resendTimer > 0">
                Kirim Ulang OTP ({{ resendTimer }}s)
              </span>
              <span v-else>
                Kirim Ulang OTP
              </span>
            </button>
          </div>
        </VForm>
      </div>

      <!-- Fase 3: Form Reset Password -->
      <div v-else-if="currentPhase === 'verified'">
        <VForm class="form w-100" @submit="resetPassword" :validation-schema="passwordSchema">
          <div class="text-center mb-10">
            <h1 class="mb-3">Reset Password</h1>
            <div class="text-gray-400 fw-bold fs-4">Masukkan password baru Anda</div>
          </div>

          <div class="fv-row mb-10">
            <label class="form-label fs-6 fw-bold">Password Baru</label>
            <div class="position-relative">
              <Field
                tabindex="1"
                class="form-control form-control-lg form-control-solid"
                :type="showPassword ? 'text' : 'password'"
                name="password"
                autocomplete="off"
                v-model="password"
              />
              <span 
                class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2" 
                @click="togglePassword"
              >
                <i :class="['bi', showPassword ? 'bi-eye' : 'bi-eye-slash', 'fs-2']"></i>
              </span>
            </div>
            <div class="fv-plugins-message-container">
              <div class="fv-help-block">
                <ErrorMessage name="password" />
              </div>
            </div>
          </div>

          <div class="fv-row mb-10">
            <label class="form-label fs-6 fw-bold">Konfirmasi Password Baru</label>
            <Field
              tabindex="2"
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

          <div class="text-center">
            <button 
              tabindex="3" 
              type="submit" 
              ref="submitButton" 
              class="btn btn-lg btn-primary w-100 mb-5"
            >
              <span class="indicator-label">Reset Password</span>
              <span class="indicator-progress">
                Mohon tunggu...
                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
              </span>
            </button>
          </div>
        </VForm>
      </div>

      <!-- Fase 4: Success Message -->
      <div v-else-if="currentPhase === 'success'" class="text-center">
        <div class="alert alert-success mb-5">
          Password berhasil direset!
        </div>
        <router-link to="/sign-in" class="btn btn-lg btn-primary w-100">
          Kembali ke Halaman Login
        </router-link>
      </div>

      <!-- Error Messages -->
      <!-- <div v-if="errorMessage" class="alert alert-danger mt-5">
        {{ errorMessage }}
      </div>
      <div v-if="successMessage" class="alert alert-success mt-5">
        {{ successMessage }}
      </div> -->
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import * as Yup from 'yup';
import axios from '@/libs/axios';
import { toast } from 'vue3-toastify';
import { blockBtn, unblockBtn } from '@/libs/utils';

export default defineComponent({
  name: 'PasswordReset',
  setup() {
    const router = useRouter();
    const currentPhase = ref('initial'); // initial, awaiting_otp, verified, success
    const password = ref('');
    const passwordConfirmation = ref('');
    const otpCode = ref('');
    const showPassword = ref(false);
    const errorMessage = ref('');
    const successMessage = ref('');
    const loading = ref(false);
    const submitButton = ref(null);
    const otpSubmitButton = ref(null);
    const resendTimer = ref(0);
    let resendInterval: number | null = null;

    const otpSchema = Yup.object().shape({
      otp: Yup.string()
        .required('Harap masukkan kode OTP')
        .matches(/^\d{6}$/, 'OTP harus 6 digit angka')
        .label('OTP'),
    });

    const passwordSchema = Yup.object().shape({
      password: Yup.string()
        .min(8, 'Password minimal terdiri dari 8 karakter')
        .required('Harap masukkan password baru')
        .label('Password'),
      password_confirmation: Yup.string()
        .oneOf([Yup.ref('password'), null], 'Passwords harus sama')
        .required('Harap konfirmasi password baru')
        .label('Konfirmasi Password'),
    });

    const startResendTimer = () => {
      resendTimer.value = 60; // 60 detik cooldown
      resendInterval = window.setInterval(() => {
        if (resendTimer.value > 0) {
          resendTimer.value--;
        } else {
          if (resendInterval) {
            clearInterval(resendInterval);
          }
        }
      }, 1000);
    };

    const sendEmail = async () => {
      loading.value = true;
      errorMessage.value = '';
      successMessage.value = '';

      try {
        const response = await axios.post('/auth/send-mail-admin');
        if (response.data.status) {
          currentPhase.value = 'awaiting_otp';
          successMessage.value = 'Kode OTP telah dikirim ke email admin.';
          toast.success(successMessage.value);
          startResendTimer();
        }
      } catch (error) {
        errorMessage.value = error.response?.data?.message || 'Terjadi kesalahan saat mengirim OTP.';
        toast.error(errorMessage.value);
      } finally {
        loading.value = false;
      }
    };

    const resendOTP = async () => {
      if (resendTimer.value > 0) return;
      
      await sendEmail();
    };

    const verifyOTP = async () => {
      loading.value = true;
      errorMessage.value = '';
      blockBtn(otpSubmitButton.value);

      try {
        const response = await axios.post('/auth/verify-otp', {
          otp: otpCode.value
        });

        if (response.data.status) {
          currentPhase.value = 'verified';
          successMessage.value = 'OTP berhasil diverifikasi';
          toast.success(successMessage.value);
        }
      } catch (error) {
        errorMessage.value = error.response?.data?.message || 'Kode OTP tidak valid atau telah kadaluarsa.';
        toast.error(errorMessage.value);
      } finally {
        loading.value = false;
        unblockBtn(otpSubmitButton.value);
      }
    };

    const resetPassword = async () => {
      errorMessage.value = '';
      blockBtn(submitButton.value);

      try {
        const response = await axios.post('/auth/reset-password', {
          password: password.value,
          password_confirmation: passwordConfirmation.value,
        });

        if (response.data.status) {
          currentPhase.value = 'success';
          toast.success('Password berhasil direset');
        }
      } catch (error) {
        errorMessage.value = error.response?.data?.message || 'Terjadi kesalahan saat mereset password.';
        toast.error(errorMessage.value);
      } finally {
        unblockBtn(submitButton.value);
      }
    };

    const togglePassword = () => {
      showPassword.value = !showPassword.value;
    };

    // Cleanup interval when component is destroyed
    onUnmounted(() => {
      if (resendInterval) {
        clearInterval(resendInterval);
      }
    });

    return {
      currentPhase,
      password,
      passwordConfirmation,
      otpCode,
      showPassword,
      errorMessage,
      successMessage,
      loading,
      submitButton,
      otpSubmitButton,
      resendTimer,
      passwordSchema,
      otpSchema,
      sendEmail,
      resendOTP,
      verifyOTP,
      resetPassword,
      togglePassword
    };
  },
});
</script>