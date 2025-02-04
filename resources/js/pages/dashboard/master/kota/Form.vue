<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { User, Role } from "@/types";
import ApiService from "@/core/services/ApiService";

const props = defineProps({
    selected: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(["close", "refresh"]);

const user = ref<User>({} as User);
const fileTypes = ref(["image/jpeg", "image/png", "image/jpg"]);
const foto = ref<any>([]);
const formRef = ref();

const formSchema = Yup.object().shape({
    nama: Yup.string()
        .required("Nama harus diisi")
        .matches(/^[a-zA-Z\s]+$/, "Nama kota hanya boleh berisi huruf"),
    alamat: Yup.string().required("Alamat harus diisi"),
    
});


function getEdit() {
    block(document.getElementById("form-user"));
    ApiService.get("kota/kota/edit", props.selected)
        .then(({ data }) => {
            user.value = data.data;
            foto.value = data.data.foto ? ["/storage/" + data.data.foto] : [];
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
    formData.append("alamat", user.value.alamat);
    formData.append("deskripsi", user.value.deskripsi);
    formData.append("foto", user.value.foto);

    if (foto.value.length) {
        formData.append("foto", foto.value[0].file);
    }
    if (props.selected) {
        formData.append("_method", "PUT");
    }
    block(document.getElementById("form-user"));
    axios({
        method: "post",
        url: props.selected
            ? `/kota/kota/update/${props.selected}`
            : "/kota/store",
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
    <VForm class="form card mb-10" @submit="submit" :validation-schema="formSchema" id="form-user" ref="formRef">
        <div class="card-header align-items-center">
            <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Kota </h2>
            <button type="button" class="btn btn-sm btn-light-danger ms-auto" @click="emit('close')">
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
                            Nama
                        </label>
                        <Field class="form-control form-control-lg form-control-solid" type="text" name="nama"
                            autocomplete="off" v-model="user.nama" placeholder="Masukkan Nama Kota" />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="nama" />
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                </div>
                <div class="col-md-6">
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Alamat
                        </label>
                        <Field class="form-control form-control-lg form-control-solid" type="text" name="alamat"
                            autocomplete="off" v-model="user.alamat" placeholder="Masukkan Alamat" />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="alamat" />
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                </div>
                <div class="col-md-15">
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6">
                            Deskripsi
                        </label>
                        <Field as="textarea" class="form-control form-control-lg form-control-solid" name="deskripsi"
                            autocomplete="off" v-model="user.deskripsi" placeholder="Masukkan Deskripsi" rows="4" />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="deskripsi" />
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                </div>

                <div class="col-md-12">
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required"> Foto </label>
                        <!--begin::Input-->
                        <file-upload :files="foto" :accepted-file-types="fileTypes" required
                            v-on:updatefiles="(file) => (foto = file)"></file-upload>
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
