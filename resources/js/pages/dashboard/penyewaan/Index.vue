<script setup lang="ts">
import { h, ref, watch } from "vue";
import { format } from 'date-fns';
import { onMounted, onUnmounted } from "vue";
import { useDelete } from "@/libs/hooks";
import { useclickSelesai } from "@/libs/hooks";
import { currency } from "@/libs/utils";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { User } from "@/types";
import { useRouter } from "vue-router";
import Swal from 'sweetalert2';

const column = createColumnHelper<User>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);
const router = useRouter();
const status = ref('aktif');

const statusOptions = ref<any[]>([
      { id: '-', text: 'Semua' },
      { id: 'aktif', text: 'Aktif' },
      { id: 'pending', text: 'Pending' },
      { id: 'selesai', text: 'Selesai' },
  ]);

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
                        class: "btn btn-sm btn-success",
                        onClick: () => clickSelesai(
                            `penyewaan/click-selesai/update/${cell.getValue()}`
                        ),
                    },
                    [
                        h("i", { class: "la la-check fs-4" }),
                        h("span", { class: "ms-1" }, "Selesai")
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
                <select2
                    placeholder="Pilih Status"
                    class="form-select-solid mw-200px mw-md-100"
                    name="status"
                    :options="statusOptions"
                    v-model="status"
                />
                <button type="button" class="btn btn-sm btn-primary" v-if="!openForm" @click="openForm = true">
                    Tambah
                    <i class="la la-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <paginate ref="paginateRef" id="penyewaan" url="/penyewaan" :columns="columns" :payload="{ status: status }"></paginate>
        </div>
    </div>
</template>