<script setup lang="ts">
import { ref } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import { block, unblock } from "@/libs/utils";

const props = defineProps({
    selected: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(["close", "refresh"]);
const formRef = ref();
const password = ref("");
const passwordConfirmation = ref("");

const formSchema = Yup.object().shape({
    password: Yup.string()
        .required("Password harus diisi")
        .min(8, "Password minimal 8 karakter"),
    password_confirmation: Yup.string()
        .required("Konfirmasi password harus diisi")
        .oneOf([Yup.ref("password")], "Konfirmasi password harus sama")
});

function submit() {
    const formData = new FormData();
    formData.append("password", password.value);
    formData.append("password_confirmation", passwordConfirmation.value);

    block(document.getElementById("form-password"));
    
    axios({
        method: "post",
        url: `/auth/form-password`,
        data: {
            uuid: props.selected,
            password: password.value,
            password_confirmation: passwordConfirmation.value
        }
    })
        .then(() => {
            emit("close");
            emit("refresh");
            toast.success("Password berhasil diubah");
            formRef.value.resetForm();
        })
        .catch((err: any) => {
            if (err.response?.data?.errors) {
                formRef.value.setErrors(err.response.data.errors);
            }
            toast.error(err.response?.data?.message || "Gagal mengubah password");
        })
        .finally(() => {
            unblock(document.getElementById("form-password"));
        });
}
</script>

<template>
    <div class="card mb-5">
        <VForm
            class="form"
            @submit="submit"
            :validation-schema="formSchema"
            id="form-password"
            ref="formRef"
        >
            <div class="card-header align-items-center">
                <h2 class="mb-0">Ubah Password</h2>
                <button
                    type="button"
                    class="btn btn-sm btn-light-danger ms-auto"
                    @click="emit('close')"
                >
                    Batal
                    <i class="la la-times-circle p-0"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="fv-row mb-7">
                            <label class="form-label fw-bold fs-6 required">
                                Password Baru
                            </label>
                            <Field
                                class="form-control form-control-lg form-control-solid"
                                type="password"
                                name="password"
                                autocomplete="off"
                                v-model="password"
                                placeholder="Masukkan password baru"
                            />
                            <div class="fv-plugins-message-container">
                                <div class="fv-help-block">
                                    <ErrorMessage name="password" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="fv-row mb-7">
                            <label class="form-label fw-bold fs-6 required">
                                Konfirmasi Password
                            </label>
                            <Field
                                class="form-control form-control-lg form-control-solid"
                                type="password"
                                name="password_confirmation"
                                autocomplete="off"
                                v-model="passwordConfirmation"
                                placeholder="Konfirmasi password baru"
                            />
                            <div class="fv-plugins-message-container">
                                <div class="fv-help-block">
                                    <ErrorMessage name="password_confirmation" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex">
                <button type="submit" class="btn btn-primary btn-sm ms-auto">
                    Simpan
                </button>
            </div>
        </VForm>
    </div>
</template>