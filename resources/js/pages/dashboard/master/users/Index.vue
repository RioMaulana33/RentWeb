<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import PasswordForm from "./PasswordForm.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { User } from "@/types";

const column = createColumnHelper<User>();
const paginateAdmin = ref<any>(null);
const paginateUser = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);
const openPasswordForm = ref<boolean>(false);

const { delete: deleteUser } = useDelete({
    onSuccess: () => {
        paginateAdmin.value?.refetch();
        paginateUser.value?.refetch();
    },
});

const createActionButtons = (cell: any, isAdmin: boolean) => {
    const buttons = [];

    if (isAdmin) {
        // Edit button for admin only
        buttons.push(
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
            )
        );

        // Password change button for admin only
        buttons.push(
            h(
                "button",
                {
                    class: "btn btn-sm btn-icon btn-warning",
                    onClick: () => {
                        selected.value = cell.getValue();
                        openPasswordForm.value = true;
                    },
                },
                h("i", { class: "la la-key fs-2" })
            )
        );
    }

    // Delete button for both admin and customer
    buttons.push(
        h(
            "button",
            {
                class: "btn btn-sm btn-icon btn-danger",
                onClick: () => deleteUser(`/master/users/${cell.getValue()}`),
            },
            h("i", { class: "la la-trash fs-2" })
        )
    );

    return h("div", { class: "d-flex gap-2" }, buttons);
};

const adminColumns = [
    column.accessor("no", {
        header: "#",
    }),
    column.accessor("name", {
        header: "Nama",
    }),
    column.accessor("email", {
        header: "Email",
    }),
    column.accessor("phone", {
        header: "No. Telp",
    }),
    column.accessor("uuid", {
        header: "Aksi",
        cell: (cell) => createActionButtons(cell, true),
    }),
];

const userColumns = [
    column.accessor("no", {
        header: "#",
    }),
    column.accessor("name", {
        header: "Nama",
    }),
    column.accessor("email", {
        header: "Email",
    }),
    column.accessor("phone", {
        header: "No. Telp",
    }),
    column.accessor("verify_ktp", {
        header: "KTP",
        cell: cell => cell.getValue() || '-'
    }),
    column.accessor("uuid", {
        header: "Aksi",
        cell: (cell) => createActionButtons(cell, false),
    }),
];

const refresh = () => {
    paginateAdmin.value?.refetch();
    paginateUser.value?.refetch();
};

watch(openForm, (val) => {
    if (!val) {
        selected.value = "";
    }
    window.scrollTo(0, 0);
});

watch(openPasswordForm, (val) => {
    if (!val) {
        selected.value = "";
    }
});
</script>

<template>
    <Form
        :selected="selected"
        @close="openForm = false"
        v-if="openForm"
        @refresh="refresh"
    />

    <!-- You'll need to create this component -->
    <PasswordForm
        :selected="selected"
        @close="openPasswordForm = false"
        v-if="openPasswordForm"
        @refresh="refresh"
    />

    <div class="card">
        <div class="card-header align-items-center">
            <h2 class="mb-0">List Users</h2>
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
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
                <li class="nav-item">
                    <a
                        class="nav-link btn btn-active-light rounded-bottom-0 active"
                        data-bs-toggle="tab"
                        href="#tab-admin"
                    >
                        Admin
                    </a>
                </li>
                <li class="nav-item">
                    <a
                        class="nav-link btn btn-active-light rounded-bottom-0"
                        data-bs-toggle="tab"
                        href="#tab-user"
                    >
                        Customer
                    </a>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div
                    class="tab-pane fade show active"
                    id="tab-admin"
                    role="tabpanel"
                >
                    <paginate
                        ref="paginateAdmin"
                        id="table-users-admin"
                        url="/master/users"
                        :columns="adminColumns"
                        queryKey="table-users-admin"
                        :payload="{ role_filter: 'admin' }"
                    ></paginate>
                </div>
                <div class="tab-pane fade" id="tab-user" role="tabpanel">
                    <paginate
                        ref="paginateUser"
                        id="table-users-user"
                        url="/master/users"
                        :columns="userColumns"
                        queryKey="table-users-user"
                        :payload="{ role_filter: 'user' }"
                    ></paginate>
                </div>
            </div>
        </div>
    </div>
</template>