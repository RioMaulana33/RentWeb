<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { User, Role, Mobil } from "@/types";
import ApiService from "@/core/services/ApiService";
import { useMobil } from "@/services/useMobil";
import dayjs from 'dayjs';

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
    tanggal_mulai: Yup.string().required("Tanggal Mulai harus diisi"),
    tanggal_selesai: Yup.string().required("Tanggal Selesai harus diisi"),
    status: Yup.string().required("Status Mobil harus diisi"),
    rental_option: Yup.string().required("Tarif harus diisi"),
});

function getEdit() {
    block(document.getElementById("form-user"));
    ApiService.get("penyewaan/penyewaan/edit", props.selected)
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
    formData.append("tanggal_mulai", user.value.tanggal_mulai);
    formData.append("tanggal_selesai", user.value.tanggal_selesai);
    formData.append("status", user.value.status);
    formData.append("rental_option", user.value.rental_option);
    formData.append("total_biaya", user.value.total_biaya);
    formData.append("alamat_pengantaran", user.value.alamat_pengantaran);

    if (props.selected) {
        formData.append("_method", "PUT");
    }
    block(document.getElementById("form-user"));
    axios({
        method: "post",
        url: props.selected
            ? `/penyewaan/penyewaan/update/${props.selected}`
            : "/penyewaan/store",
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


const Mobil = useMobil();
const mobil = computed(() =>
    Mobil.data.value?.map((item: Mobil) => ({
        id: item.id,
        text: item.merk + " " + item.model,
    }))
);

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
const totalBiayaFormat = computed({
    get: () => formatRupiah(user.value.total_biaya),
    set: (newValue) => {
        // Remove non-numeric characters
        user.value.total_biaya = newValue.replace(/[^0-9]/g, '');
    }
});

onMounted(async () => {
    if (props.selected) {
        getEdit();
    }

    // Set the initial value of tanggal_mulai to today
    user.value.tanggal_mulai = dayjs().toISOString().slice(0, 10);
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
            <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Penyewaan </h2>
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
                <!-- Row 1: Tanggal Mulai & Tanggal Selesai -->
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Tanggal Mulai Rental
                        </label>
                        <Field name="tanggal_mulai" class="form-control form-control-lg form-control-solid p"
                            v-model="user.tanggal_mulai">
                            <date-picker v-model="user.tanggal_mulai" placeholder="Tanggal Mulai Rental" :config="{
                                enableTime: false,
                                dateFormat: 'Y-m-d',
                                minDate: new Date(),
                            }"></date-picker>
                        </Field>
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="tanggal_mulai" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Tanggal Selesai Rental
                        </label>
                        <Field name="tanggal_selesai" class="form-control form-control-lg form-control-solid p"
                            v-model="user.tanggal_selesai">
                            <date-picker v-model="user.tanggal_selesai" placeholder="Tanggal Selesai Rental" :config="{
                                enableTime: false,
                                dateFormat: 'Y-m-d',
                                minDate: dayjs(user.tanggal_mulai).toDate(),
                            }">
                            </date-picker>
                        </Field>
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="tanggal_selesai" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Rental Option & Status -->
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Rental Option
                        </label>
                        <Field name="rental_option" type="hidden" v-model="user.rental_option">
                            <select2 placeholder="Pilih RentalOption" class="form-select-solid" :options="[
                                { id: 'Dengan Kunci', text: 'Dengan Kunci' },
                                { id: 'Lepas Kunci', text: 'Lepas Kunci' },
                            ]" name="rental_option" v-model="user.rental_option">
                            </select2>
                        </Field>
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="rental_option" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Status
                        </label>
                        <Field name="status" type="hidden" v-model="user.status">
                            <select2 placeholder="Pilih Status" class="form-select-solid" :options="[
                                { id: 'Aktif', text: 'Aktif' },
                                { id: 'Selesai', text: 'Selesai' },
                                { id: 'Delay', text: 'Delay' },
                            ]" name="status" v-model="user.status">
                            </select2>
                        </Field>
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="status" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Total Biaya & Alamat Pengantaran -->
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Total Biaya
                        </label>
                        <Field class="form-control form-control-lg form-control-solid" type="text" name="total_biaya"
                            v-model="totalBiayaFormat" placeholder="Masukkan Total Biaya Mobil" />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="total_biaya" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6">
                            Alamat Pengantaran
                        </label>
                        <Field class="form-control form-control-lg form-control-solid" type="string"
                            name="alamat_pengantaran" autocomplete="off" v-model="user.alamat_pengantaran"
                            placeholder="Masukkan Alamat Pengantaran" />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="alamat_pengantaran" />
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
