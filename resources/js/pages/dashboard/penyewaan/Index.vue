<script setup lang="ts">
import { h, ref, watch } from "vue";
import { format } from 'date-fns';
import { onMounted, onUnmounted, computed } from "vue";
import { useDelete } from "@/libs/hooks";
import { useclickSelesai } from "@/libs/hooks";
import { currency } from "@/libs/utils";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { User } from "@/types";
import { useRouter } from "vue-router";
import Swal from 'sweetalert2';
import dayjs from 'dayjs';
import { useDownloadExcel } from "@/libs/hooks";
import moment from 'moment';

const column = createColumnHelper<User>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);
const router = useRouter();
const status = ref('aktif');
const date = ref(`${moment().startOf('month').format('YYYY-MM-DD')} to ${moment().format('YYYY-MM-DD')}`);
const tahun = ref(new Date().getFullYear());
const tahuns = ref([]);

for (let i = tahun.value; i >= 2022; i--) {
    tahuns.value.push({ id: i, text: i });
}

const startDate = computed(() => date.value.split(' to ')[0]);
const endDate = computed(() => date.value.split(' to ')[1]);
const isLoading = ref(false);


const statusOptions = ref<any[]>([
    { id: '-', text: 'Semua' },
    { id: 'aktif', text: 'Aktif' },
    { id: 'pending', text: 'Pending' },
    { id: 'selesai', text: 'Selesai' },
]);

const paginatePayload = computed(() => ({
    status: status.value,
    start_date: startDate.value,
    end_date: endDate.value,
    tahun: '2025',
}));


const { clickSelesai } = useclickSelesai({
    onSuccess: async (result) => {
        if (result?.data?.denda > 0) {
            await Swal.fire({
                title: 'Informasi Denda',
                html: `
                    <div class="text-left">
                        <p>Pengembalian terlambat:</p>
                        <p>Denda: ${currency(result.data.denda)}</p>
                        <p class="text-danger">Silakan lakukan pembayaran denda</p>
                    </div>
                `,
                icon: 'warning',
                confirmButtonText: 'Mengerti'
            });
        }
        paginateRef.value.refetch();
    },
});

const { download: downloadTemplate } = useDownloadExcel({
    onSuccess: () => {
        isLoading.value = false;
    },
    onError: () => {
        isLoading.value = false;
    }
});

const downloadReport = () => {
    isLoading.value = true;
    let url = '/penyewaan/report/excel';
    
    const params = new URLSearchParams();
    if (startDate.value) params.append('start_date', startDate.value);
    if (endDate.value) params.append('end_date', endDate.value);
    if (tahun.value) params.append('tahun', tahun.value.toString());
    
    url += `?${params.toString()}`;
    downloadTemplate(url);
};

const { delete: deleteUser } = useDelete({
    onSuccess: () => paginateRef.value.refetch(),
});

const columns = [
    column.accessor("no", {
        header: "No",
    }),
    column.accessor("kode_penyewaan", {
        header: "Kode",
    }),
    column.accessor("user.email", {
        header: "Customer",
    }),
    column.accessor("mobil.merk", {
        header: "Mobil",
    }),
    column.accessor("tanggal_mulai", {
        header: "Tanggal Mulai",
    }),
    column.accessor("tanggal_selesai", {
        header: "Tanggal Selesai",
    }),
    column.accessor("jam_mulai", {
        header: "Jam Mulai",
    }),
    column.accessor("status", {
        header: "Status",
        cell: cell => {
            const status = cell.getValue();
            let badgeClass = '';

            switch (status) {
                case 'aktif':
                    badgeClass = 'badge-light-primary';
                    break;
                case 'selesai':
                    badgeClass = 'badge-light-success';
                    break;
                case 'pending':
                    badgeClass = 'badge-light-warning';
                    break;
                default:
                    badgeClass = 'badge-light-info';
            }

            return h('div', [
                h('span', { class: `badge ${badgeClass}` }, status)
            ]);
        }
    }),
    column.accessor("denda", {
        header: "Denda",
        cell: cell => {
            const denda = cell.getValue();
            return denda ? currency(denda) : '-';
        }
    }),
    column.accessor("uuid", {
        header: "Aksi",
        cell: (cell) => {
            const row = cell.row.original;

            return h("div", { class: "d-flex gap-2" }, [
                h(
                    "button",
                    {
                        class: "btn btn-sm btn-icon btn-info",
                        onClick: () => router.push(`/dashboard/penyewaan/detail/${cell.getValue()}`),
                    },
                    h("i", { class: "la la-eye fs-2" })
                ),
                row.status === 'aktif' && h(
                    "button",
                    {
                        class: "btn btn-sm btn-success d-flex align-items-center",
                        onClick: () => clickSelesai(
                            `penyewaan/click-selesai/update/${cell.getValue()}`
                        ),
                    },
                    [
                        h("i", { class: "la la-check fs-4 me-1" }),
                        h("span", "Selesai")
                    ]
                ),
            ]);
        },
    }),
];

const refresh = () => paginateRef.value.refetch();
const currentTime = ref(new Date());
const intervalId = ref<number | null>(null);

watch(openForm, (val) => {
    if (!val) {
        selected.value = "";
    }
    window.scrollTo(0, 0);
});

watch([date, tahun, status], () => {
    if (paginateRef.value) {
        paginateRef.value.refetch();
    }
});


onMounted(() => {
    intervalId.value = setInterval(() => {
        currentTime.value = new Date();
        refresh();
    }, 60000);
});

onUnmounted(() => {
    if (intervalId.value) {
        clearInterval(intervalId.value);
    }
});
</script>

<template>
    <Form :selected="selected" @close="openForm = false" v-if="openForm" @refresh="refresh" />

    <div class="card">
        <div class="card-header align-items-center d-flex">
            <h2 class="mb-0">Detail Penyewaan</h2>
            <div class="d-flex gap-3 ms-auto align-items-center">
                <!-- <div>
                    <label class="form-label">Tahun:</label>
                    <select2 v-model="tahun" :options="tahuns" class="form-select-solid mw-100px" />
                </div> -->
                <date-picker v-model="date" :config="{ mode: 'range' }" style="width: 225px"
                    class="form-control"></date-picker>
                <select2 placeholder="Pilih Status" class="form-select-solid mw-200px mw-md-100" name="status"
                    :options="statusOptions" v-model="status" />
                <button type="button" class="btn btn-success" @click="downloadTemplate(`/penyewaan/report/excel?start_date=${startDate}&end_date=${endDate}&tahun=${tahun}&status=${status}`)">
                    <i class="la la-file-excel me-1"></i>
                    Unduh Laporan Excel
                </button>
            </div>
        </div>
        <div class="card-body">
            <paginate ref="paginateRef" id="penyewaan" url="/penyewaan" :columns="columns" :payload="paginatePayload">
            </paginate>
        </div>
    </div>
</template>