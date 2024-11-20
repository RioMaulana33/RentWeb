<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { User } from "@/types";

const column = createColumnHelper<User>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);

const { delete: deleteUser } = useDelete({
    onSuccess: () => paginateRef.value.refetch(),
});

const columns = [
    column.accessor("no", {
        header: "No",
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
    column.accessor("rental_option", {
        header: "Rental Option",
    }),
    column.accessor("status", {
        header: "Status",
        cell: cell => {
            const status = cell.getValue();
            let badgeClass = '';
            
            switch(status) {
                case 'Aktif':
                    badgeClass = 'badge-light-primary';
                    break;
                case 'Selesai':
                    badgeClass = 'badge-light-success';
                    break;
                case 'Delay':
                    badgeClass = 'badge-light-warning';
                    break;
                default:
                    badgeClass = 'badge-light-primary';
            }
            
            return h('div', [
                h('span', { class: `badge ${badgeClass}` }, status)
            ]);
        }
    }),
    column.accessor("total_biaya", {
        header: "Total Biaya",
    }),
    column.accessor("alamat_pengantaran", {
        header: "Alamat Pengantaran",
    }),

    column.accessor("uuid", {
        header: "Aksi",
        cell: (cell) =>
            h("div", { class: "d-flex gap-2" }, [
                h(
                    "button",
                    {
                        class: "btn btn-sm btn-icon btn-info",
                        onClick: () => {
                            selected.value = cell.getValue();
                            openForm.value = true;
                        },
                    },
                    h("i", { class: "la la-pencil fs-2" })
                ),
                h(
                    "button",
                    {
                        class: "btn btn-sm btn-icon btn-danger",
                        onClick: () =>
                            deleteUser(`penyewaan/penyewaan/destroy/${cell.getValue()}`),
                    },
                    h("i", { class: "la la-trash fs-2" })
                ),
            ]),
    }),
];

const refresh = () => paginateRef.value.refetch();

watch(openForm, (val) => {
    if (!val) {
        selected.value = "";
    }
    window.scrollTo(0, 0);
});
</script>

<template>
    <Form
        :selected="selected"
        @close="openForm = false"
        v-if="openForm"
        @refresh="refresh"
    />

    <div class="card">
        <div class="card-header align-items-center">
            <h2 class="mb-0">Detail Penyewaan</h2>
            <button
                type="button"
                class="btn btn-sm btn-primary ms-auto"
                v-if="!openForm"
                @click="openForm = true"
            >
                Tambah
                <i class="la la-plus"></i>
            </button>
        </div>
        <div class="card-body">
            <paginate
                ref="paginateRef"
                id="penyewaan"
                url="/penyewaan"
                :columns="columns"
            ></paginate>
        </div>
    </div>
</template>
