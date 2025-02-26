<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { currency } from "@/libs/utils";
import axios from '@/libs/axios';

const route = useRoute();
const rental = ref(null);
const loading = ref(true);

const fetchRental = async () => {
    try {
        const response = await axios.get(`penyewaan/penyewaan/detail/${route.params.uuid}`).then(res => res.data.data);
        rental.value = response;
    } catch (error) {
        console.error('Error fetching rental details:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchRental();
});

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'aktif':
            return 'badge-light-primary';
        case 'selesai':
            return 'badge-light-success';
        case 'pending':
            return 'badge-light-warning';
        default:
            return 'badge-light-info';
    }
};
</script>

<template>
    <div class="container-fluid px-4 py-6">
        <!-- Loading State -->
        <div v-if="loading" class="min-h-[50vh] d-flex justify-content-center align-items-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <!-- Error State -->
        <div v-else-if="!rental" class="alert alert-warning">
            Data penyewaan tidak ditemukan
        </div>

        <!-- Content -->
        <div v-else class="card mb-10">
            <!-- Card Header -->
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <div class="d-flex flex-column">
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge fs-7" :class="getStatusBadge(rental.status)">
                            {{ rental.status.toUpperCase() }}
                        </span>
                        <span class="text-gray-600">Kode: {{ rental.kode_penyewaan }}</span>
                    </div>
                </div>

                <button type="button" class="btn btn-sm btn-light-danger ms-auto" @click="$router.back()">
                    Batal
                    <i class="la la-times-circle p-0"></i>
                </button>
            </div>

            <!-- Card Body -->
            <div class="card-body">
                <div class="row g-5">
                    <!-- Customer Info -->
                    <div class="col-md-6">
                        <div class="card card-bordered h-100">
                            <div class="card-body p-6">
                                <h3 class="card-title  mb-4">
                                    <span class="symbol-label bg-success bg-opacity-10 rounded-1">
                                        <i class="fas fa-user text-success p-3"></i>
                                    </span>
                                    Informasi Customer
                                </h3>
                                <div class="ps-2">
                                    <div class="mb-4">
                                        <label class="text-gray-400 mb-1 d-block">Nama Customer</label>
                                        <span class="">{{ rental.user?.name }}</span>
                                    </div>
                                    <div class="mb-4">
                                        <label class="text-gray-400 mb-1 d-block">Email</label>
                                        <span class="">{{ rental.user?.email }}</span>
                                    </div>
                                    <div>
                                        <label class="text-gray-400 mb-1 d-block">No. Telepon</label>
                                        <span class="">{{ rental.user?.phone }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle Info -->
                    <div class="col-md-6">
                        <div class="card card-bordered h-100">
                            <div class="card-body p-6">
                                <h3 class="card-title  mb-4">
                                    <span class="symbol-label bg-primary bg-opacity-10 rounded-3">
                                        <i class="fas fa-car text-primary p-3"></i>
                                    </span>
                                    Informasi Kendaraan
                                </h3>
                                <div class="ps-2">
                                    <div class="mb-4">
                                        <label class="text-gray-400 mb-1 d-block">Merk</label>
                                        <span class="">{{ rental.mobil?.merk }}</span>
                                    </div>
                                    <div class="mb-4">
                                        <label class="text-gray-400 mb-1 d-block">Model</label>
                                        <span class="">{{ rental.mobil?.model }}</span>
                                    </div>
                                    <div class="mb-4">
                                        <label class="text-gray-400 mb-1 d-block">Kota</label>
                                        <span class="">{{ rental.kota?.nama }}</span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rental Details -->
                    <div class="col-12">
                        <div class="card card-bordered">
                            <div class="card-body p-6">
                                <h3 class="card-title mb-4">
                                    <span class="symbol-label bg-info bg-opacity-10 rounded-3">

                                        <i class="fas fa-calendar text-info p-3"></i>
                                    </span>
                                    Detail Penyewaan
                                </h3>
                                <div class="row g-5 ps-2">
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label class="text-gray-400 mb-1 d-block">Tanggal Mulai</label>
                                            <span class="">{{ rental.tanggal_mulai }}</span>
                                        </div>
                                        <div class="mb-4">
                                            <label class="text-gray-400 mb-1 d-block">Tanggal Selesai</label>
                                            <span class="">{{ rental.tanggal_selesai }}</span>
                                        </div>
                                        <div>
                                            <label class="text-gray-400 mb-1 d-block">Jam Mulai</label>
                                            <span class="">{{ rental.jam_mulai }}</span>
                                        </div>
                                        <div class="mt-4">
                                            <label class="text-gray-400 mb-1 d-block">Alamat Pengantaran</label>
                                            <span class="">{{ rental.alamat_pengantaran || '-' }}</span>
                                        </div>
                                        <div class="mt-4">
                                            <label class="text-gray-400 mb-1 d-block">Deskripsi Alamat Pengantaran</label>
                                            <span class="">{{ rental.deskripsi_alamat || '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label class="text-gray-400 mb-1 d-block">Metode</label>
                                            <span class="">{{ rental.delivery?.nama }}</span>
                                        </div>
                                       
                                        <div class="mb-4">
                                            <label class="text-gray-400 mb-1 d-block">Opsi Rental</label>
                                            <span class="">{{ rental.rentaloption?.nama }}</span>
                                        </div>
                                        <div>
                                            <label class="text-gray-400 mb-1 d-block">Total Biaya</label>
                                            <span class="text-primary">{{ currency(rental.total_biaya, {
                                                style: "currency",
                                                currency: "IDR",
                                                minimumFractionDigits: 0,
                                                maximumFractionDigits: 0
                                            }) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.card-bordered {
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.bg-dark {
    background-color: #1e1e2d !important;
}

.text-gray-400 {
    color: #878c9f !important;
}

.badge {
    font-weight: 500;
    padding: 0.5rem 0.75rem;
}

.card-title {
    font-size: 1.25rem;
    font-weight: 600;
}

a.text-danger {
    text-decoration: none;
}

a.text-danger:hover {
    text-decoration: underline;
}
</style>