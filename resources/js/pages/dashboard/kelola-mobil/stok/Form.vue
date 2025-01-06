<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { User, Role, Mobil, Kota } from "@/types";
import ApiService from "@/core/services/ApiService";
import { useMobil } from "@/services/useMobil";
import { useKota } from "@/services/useKota";

const props = defineProps({
    selected: {
        type: String, Date,
        default: null,
    },
});

const emit = defineEmits(["close", "refresh"]);

const user = ref<User>({} as User);
const formRef = ref();

const formSchema = Yup.object().shape({
    mobil_id: Yup.string().required("Mobil harus diisi"),
    kota_id: Yup.string().required("Kota harus diisi"),
    stok: Yup.string().required("Stok harus diisi"),
});

function getEdit() {
    block(document.getElementById("form-user"));
    ApiService.get("stok/stok/edit", props.selected)
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

    formData.append("mobil_id", user.value.mobil_id);
    formData.append("kota_id", user.value.kota_id);
    formData.append("stok", user.value.stok);

    if (props.selected) {
        formData.append("_method", "PUT");
    }

    block(document.getElementById("form-user"));
    axios({
        method: "post",
        url: props.selected
            ? `/stok/stok/update/${props.selected}`
            : "/stok/store",
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
            if (err.response.status === 422) {
                toast.error(err.response.data.message);
            } else {
                formRef.value.setErrors(err.response.data.errors);
                toast.error(err.response.data.message || "Terjadi kesalahan.");
            }
        })
        .finally(() => {
            unblock(document.getElementById("form-user"));
        });
}


const Mobil = useMobil();
const mobil = computed(() =>
    Mobil.data.value?.map((item: Mobil) => ({
        id: item.id,
        text: item.merk + " " + item.model,
    }))
);

const Kota = useKota();
const kota = computed(() =>
    Kota.data.value?.map((item: Kota) => ({
        id: item.id,
        text: item.nama,
    }))
);


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
            <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Stok Mobil </h2>
            <button type="button" class="btn btn-sm btn-light-danger ms-auto" @click="emit('close')">
                Batal
                <i class="la la-times-circle p-0"></i>
            </button>
        </div>
        <div class="card-body">
            <div class="row">

                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Pilih Mobil
                        </label>
                        <Field name="mobil_id" type="hidden" v-model="user.mobil_id">
                            <select2 placeholder="Pilih Mobil" class="form-select-solid" :options="mobil"
                                name="mobil_id" v-model="user.mobil_id">
                            </select2>
                        </Field>
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="mobil_id" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Pilih Kota
                        </label>
                        <Field name="kota_id" type="hidden" v-model="user.kota_id">
                            <select2 placeholder="Pilih Kota" class="form-select-solid" :options="kota" name="kota_id"
                                v-model="user.kota_id">
                            </select2>
                        </Field>
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="kota_id" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Stok
                        </label>
                        <Field name="stok" type="number" v-model="user.stok" class="form-control form-control-solid"
                            placeholder="Masukkan Stok" />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="stok" />
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
