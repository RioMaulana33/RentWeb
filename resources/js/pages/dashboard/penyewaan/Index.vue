<script setup lang="ts">
import { h, ref, watch } from "vue";
import { format } from 'date-fns';
import { onMounted, onUnmounted } from "vue";
import { useDelete } from "@/libs/hooks";
import { useclickAktif } from "@/libs/hooks";
import { currency } from "@/libs/utils";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { User } from "@/types";
import { useRouter } from "vue-router";

const column = createColumnHelper<User>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);
const router = useRouter();

const { delete: deleteUser } = useDelete({
    onSuccess: () => paginateRef.value.refetch(),
});
const { clickAktif: clickAktif } = useclickAktif({
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

    column.accessor("uuid", {
        header: "Aksi",
        cell: (cell) => {
            const row = cell.row.original;
            const currentTime = new Date();
            const jamMulai = new Date();

            const [hours, minutes] = row.jam_mulai.split(':');
            jamMulai.setHours(parseInt(hours), parseInt(minutes), 0);

            // Check if current time matches rental start time (within a small window)
            const isTimeMatch = Math.abs(currentTime.getTime() - jamMulai.getTime()) <= 5 * 60 * 1000; // 5 minutes window

            // Check if status is pending (only allow activation for pending rentals)
            const isPending = row.status === 'pending';

            return h("div", { class: "d-flex gap-2" }, [
            h(
                    "button",
                    {
                        class: "btn btn-sm btn-icon btn-info",
                        onClick: () => router.push(`/dashboard/penyewaan/detail/${cell.getValue()}`),
                    },
                    h("i", { class: "la la-eye fs-2" })
                ),
                // h(
                //     "button",
                //     {
                //         class: `btn btn-sm btn-icon ${isTimeMatch && isPending ? 'btn-primary' : 'btn-primary disabled opacity-50 '}`,
                //         onClick: () => {
                //             if (isTimeMatch && isPending) {
                //                 clickAktif(`penyewaan/click-aktif/update/${cell.getValue()}`)
                //             }
                //         },
                //         disabled: !isTimeMatch || !isPending,
                //         title: !isTimeMatch ? 'Tombol akan aktif saat waktu sesuai dengan jam mulai rental' :
                //             !isPending ? 'Hanya rental dengan status pending yang dapat diaktifkan' : ''
                //     },
                //     h("i", { class: "la la-check fs-2" })
                // ),
                h(
                    "button",
                    {
                        class: "btn btn-sm btn-icon btn-danger",

                        onClick: () =>
                            deleteUser(`penyewaan/penyewaan/destroy/${cell.getValue()}`),
                    },
                    h("i", { class: "la la-trash fs-2" })
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
    // Update current time every second
    intervalId.value = setInterval(() => {
        currentTime.value = new Date();
        refresh();
    }, 60000); // Refresh every minute
});

onMounted(() => {
    setInterval(() => {
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
        <div class="card-header align-items-center">
            <h2 class="mb-0">Detail Penyewaan</h2>
            <button type="button" class="btn btn-sm btn-primary ms-auto" v-if="!openForm" @click="openForm = true">
                Tambah
                <i class="la la-plus"></i>
            </button>
        </div>
        <div class="card-body">
            <paginate ref="paginateRef" id="penyewaan" url="/penyewaan" :columns="columns"></paginate>
        </div>
    </div>
</template>
