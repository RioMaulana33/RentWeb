<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { User, Role, Mobil } from "@/types";
import ApiService from "@/core/services/ApiService";
import { useRole } from "@/services/useRole";

const props = defineProps({
    selected: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(["close", "refresh"]);

const mobil = ref<User>({} as User);
const fileTypes = ref(["image/jpeg", "image/png", "image/jpg"]);
const foto = ref<any>([]);
const formRef = ref();

const formSchema = Yup.object().shape({
    merk: Yup.string().required("Merk Mobil harus diisi"),
    model: Yup.string().required("Model Mobil harus diisi"),
    tarif: Yup.string().required("Tarif harus diisi"),
    // foto: Yup.object().required("Foto harus diisi"),
});

function getEdit() {
    block(document.getElementById("form-mobil"));
    ApiService.get("mobil/mobil/edit", props.selected)
        .then(({ data }) => {
            mobil.value = data.data;
            foto.value = data.data.foto ? ["/storage/" + data.data.foto] : [];
        })
        .catch((err: any) => {
            toast.error(err.response.data.message);
        })
        .finally(() => {
            unblock(document.getElementById("form-mobil"));
        });
}

function submit() {
    const formData = new FormData();

    formData.append("merk", mobil.value.merk);
    formData.append("model", mobil.value.model);
    formData.append("type", mobil.value.type);
    formData.append("tahun", mobil.value.tahun);
    formData.append("tarif", mobil.value.tarif);
    formData.append("kapasitas", mobil.value.kapasitas);
    formData.append("bahan_bakar", mobil.value.bahan_bakar);
    formData.append("foto", mobil.value.foto);

    if (foto.value.length) {
        formData.append("foto", foto.value[0].file);
    }
    if (props.selected) {
        formData.append("_method", "PUT");
    }
    block(document.getElementById("form-mobil"));
    axios({
        method: "post",
        url: props.selected
            ? `/mobil/mobil/update/${props.selected}`
            : "/mobil/store",
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
            unblock(document.getElementById("form-mobil"));
        });
}

const formatRupiah = (value: number | string | null | undefined) => {
    // Handle null or undefined
    if (value === null || value === undefined) return '';

    // Convert to string and remove non-numeric characters
    const numericValue = parseFloat(String(value).replace(/[^0-9.-]+/g, ''));

    // Check if the value is a valid number
    if (isNaN(numericValue)) return '';

    // Format to Rupiah
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(numericValue);
};

// Add a computed property to handle input/display conversion
const TarifFormat = computed({
    get: () => formatRupiah(mobil.value.tarif),
    set: (newValue) => {
        // Remove non-numeric characters
        mobil.value.tarif = newValue.replace(/[^0-9]/g, '');
    }
});

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
        id="form-mobil"
        ref="formRef"
    >
        <div class="card-header align-items-center">
            <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Mobil </h2>
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
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Merk
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            name="merk"
                            autocomplete="off"
                            v-model="mobil.merk"
                            placeholder="Masukkan Merk Mobil"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="merk" />
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                </div>
                <div class="col-md-6">
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Model
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            name="model"
                            autocomplete="off"
                            v-model="mobil.model"
                            placeholder="Masukkan Model Mobil"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="model" />
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                </div>
                <div class="col-md-6">
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 ">
                            Type
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            name="type"
                            autocomplete="off"
                            v-model="mobil.type"
                            placeholder="Masukkan Model Mobil"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="type" />
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                </div>
                <div class="col-md-6">
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 ">
                            Tahun
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="number"
                            name="tahun"
                            autocomplete="off"
                            v-model="mobil.tahun"
                            placeholder="Masukkan Tahun Mobil"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="tahun" />
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                </div>
                <div class="col-md-6">
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Tarif
                        </label>
                        <Field class="form-control form-control-lg form-control-solid" type="text" name="tarif"
                        v-model="TarifFormat" placeholder="Masukkan Tarif Mobil" />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="tarif" />
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                </div>
                <div class="col-md-6">
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 ">
                            Kapasitas
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="string"
                            name="kapasitas"
                            autocomplete="off"
                            v-model="mobil.kapasitas"
                            placeholder="Masukkan Kapasitas Mobil"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="kapasitas" />
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                </div>

                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Bahan Bakar
                        </label>
                        <Field name="bahan_bakar" type="hidden" v-model="mobil.bahan_bakar">
                            <select2 placeholder="Pilih Bahan Bakar" class="form-select-solid" :options="[
                                { id: 'Bensin', text: 'Bensin'},
                                { id: 'Solar (Diesel)', text: 'Solar (Diesel)'},
                                { id: 'Hybrid', text :'Hybrid'}
                            ]" name="bahan_bakar" v-model="mobil.bahan_bakar">
                            </select2>
                        </Field>
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="bahan_bakar" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6"> Foto </label>
                        <!--begin::Input-->
                        <file-upload
                            :files="foto"
                            :accepted-file-types="fileTypes"
                            required
                            v-on:updatefiles="(file) => (foto = file)"
                        ></file-upload>
                        <!--end::Input-->
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="foto" />
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
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
