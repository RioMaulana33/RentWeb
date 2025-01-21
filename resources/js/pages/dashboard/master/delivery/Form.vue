<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { User, Role } from "@/types";
import ApiService from "@/core/services/ApiService";
import { useRole } from "@/services/useRole";

const props = defineProps({
    selected: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(["close", "refresh"]);

const user = ref<User>({} as User);

const formRef = ref();

const formSchema = Yup.object().shape({
    nama: Yup.string().required("Nama harus diisi"),
    deskripsi: Yup.string().required("Deskripsi  harus diisi"),
    biaya: Yup.string().required("Biaya harus diisi"),
});

function getEdit() {
    block(document.getElementById("form-user"));
    ApiService.get("delivery/delivery/edit", props.selected)
        .then(({ data }) => {
            user.value = data.data;
        })
        .catch((err: any) => {
            toast.error(err.response.data.message);
        })
        .finally(() => {
            unblock(document.getElementById("form-user"));
        });
}

function submit() {
    const formData = new FormData();

    formData.append("nama", user.value.nama);
    formData.append("deskripsi", user.value.deskripsi);
    formData.append("biaya", user.value.biaya);

    if (props.selected) {
        formData.append("_method", "PUT");
    }
    block(document.getElementById("form-user"));
    axios({
        method: "post",
        url: props.selected
            ? `/delivery/delivery/update/${props.selected}`
            : "/delivery/store",
        data: formData,
        headers: {
            "Content-Type": "multipart/form-data",
        },
    })
        .then(() => {
            emit("close");
            emit("refresh");
            toast.success("Data berhasil disimpan");
            formRef.value.resetForm();
        })
        .catch((err: any) => {
            formRef.value.setErrors(err.response.data.errors);
            toast.error(err.response.data.message);
        })
        .finally(() => {
            unblock(document.getElementById("form-user"));
        });
}

const formatRupiah = (value: number | string | null | undefined) => {
    if (value === null || value === undefined) return '';
    const numericValue = parseFloat(String(value).replace(/[^0-9]/g, ''));
    if (isNaN(numericValue)) return '';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(numericValue);
};

const CostFormat = computed({
    get: () => formatRupiah(user.value.cost),
    set: (newValue) => {
        const numericValue = newValue.replace(/[^0-9]/g, '');
        user.value.cost = numericValue;
    }
});

// Fungsi untuk mencegah input selain angka
const preventNonNumericInput = (e: KeyboardEvent) => {
    if (!/[0-9]/.test(e.key) && 
        e.key !== 'Backspace' && 
        e.key !== 'Delete' && 
        e.key !== 'ArrowLeft' && 
        e.key !== 'ArrowRight' && 
        e.key !== 'Tab' && 
        !e.ctrlKey) {
        e.preventDefault();
    }
};

onMounted(async () => {
    if (props.selected) {
        getEdit();
    }
});

watch(
    () => props.selected,
    () => {
        if (props.selected) {
            getEdit();
        }
    }
);
</script>

<template>
    <VForm
        class="form card mb-10"
        @submit="submit"
        :validation-schema="formSchema"
        id="form-user"
        ref="formRef"
    >
        <div class="card-header align-items-center">
            <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Delivery </h2>
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
                            Nama
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            name="nama"
                            autocomplete="off"
                            v-model="user.nama"
                            placeholder="Masukkan Nama "
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="nama" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Deskripsi
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            name="deskripsi"
                            autocomplete="off"
                            v-model="user.deskripsi"
                            placeholder="Masukkan Deskripsi"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="deskripsi" />
                            </div>
                        </div>
                    </div>
                </div>
             
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Biaya
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            name="biaya"
                            v-model="CostFormat"
                            placeholder="Masukkan Biaya"
                            @keydown="preventNonNumericInput"
                            inputmode="numeric"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="biaya" />
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
</template>