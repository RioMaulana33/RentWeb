<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import { currency } from "@/libs/utils";
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
    column.accessor("merk", {
        header: "Merk",
    }),
    column.accessor("model", {
        header: "Model",
    }),
    column.accessor("type", {
        header: "Type",
    }),
    column.accessor("tahun", {
        header: "Tahun",
    }),
    column.accessor("tarif", {
        header: "Tarif",
        cell: (cell) => currency(cell.getValue(), { 
            style: "currency", 
            currency: "IDR", 
            minimumFractionDigits: 0, 
            maximumFractionDigits: 0 
        })
    }),
    column.accessor("kapasitas", {
        header: "Kapasitas",
    }),
    column.accessor("bahan_bakar", {
        header: "Bahan Bakar",
    }),
    column.accessor("foto", {
    header: "Foto mobil",
    cell: cell => {
        const fotoPath = cell.getValue();

        // Cek jika fotoPath ada, jika tidak, tampilkan placeholder atau penanganan default
        if (!fotoPath) {
            return h('img', { src: `/storage/mobil/${cell.getValue()}`, width: 150 });
        }

        // Jika path dimulai dengan '/media', gunakan langsung
        const imageUrl = fotoPath.startsWith('/media')
            ? fotoPath
            : `/storage/${fotoPath}`;

        return h('img', { src: imageUrl, width: 150 });
    }
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
                            deleteUser(`mobil/mobil/destroy/${cell.getValue()}`),
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
            <h2 class="mb-0">Detail Mobil</h2>
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
                id="mobil"
                url="/mobil"
                :columns="columns"
            ></paginate>
        </div>
    </div>
</template>
