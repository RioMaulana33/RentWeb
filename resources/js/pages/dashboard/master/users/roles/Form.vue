<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { Role } from "@/types";
import ApiService from "@/core/services/ApiService";
import type { MenuItem } from "@/layouts/default-layout/config/types";
import MainMenuConfig from "@/layouts/default-layout/config/MainMenuConfig";

const props = defineProps({
    selected: {
        type: Number,
        default: null,
    },
});

interface Item {
    text: string;
    value: string;
    children?: Array<Item>;
    parent_idx?: number;
}

const extractNames = (menuConfig: Array<MenuItem>) => {
    const names: Array<Item> = [];
    for (const item of menuConfig) {
        if (item.name) {
            const nameObject: Item = {
                text: item.heading || item.sectionTitle,
                value: item.name,
            };

            // Check if the item has pages or sub
            if (item.pages || item.sub) {
                nameObject.children = [];
                // Recursively extract names for pages or sub
                const childNames = extractNames(item.pages || item.sub);
                // Add the extracted names to the 'children' property
                nameObject.children.push(...childNames);
                // nameObject.children = children;
            }
            names.push(nameObject);
        } else {
            if (item.pages || item.sub) {
                const childNames = extractNames(item.pages || item.sub);
                names.push(...childNames);
            }
        }
    }
    return names;
};

const emit = defineEmits(["close", "refresh"]);

const formData = ref<Role>({} as Role);
const formRef = ref();

const formSchema = Yup.object().shape({
    name: Yup.string().required("Nama harus diisi"),
    full_name: Yup.string().required("Nama Lengkap harus diisi"),
    permissions: Yup.array().of(Yup.string()),
});

const permissions: Array<Item> = extractNames(MainMenuConfig);

const findChildren = (menuConfig: Array<Item>) => {
    const names: Array<string> = [];
    for (const item of menuConfig) {
        if (item.children?.length) {
            const childNames = findChildren(item.children);
            names.push(...childNames);
        }
        names.push(item.value);
    }
    return names;
};

const findAllParents = (
    tree: Array<Item>,
    targetValue: Item,
    parents: Array<string> = []
) => {
    for (const item of tree) {
        if (item.value === targetValue.value) {
            // Jika elemen saat ini sesuai dengan target, tambahkan parent ke daftar
            parents.push(item.value);
            return parents;
        }
        if (item.children && item.children.length > 0) {
            // Jika elemen saat ini memiliki children, rekursif mencari di children
            const newParents = [...parents, item.value];
            const foundParents = findAllParents(
                item.children,
                targetValue,
                newParents
            );
            if (foundParents.length > 0) {
                // Jika parent ditemukan di salah satu children, kembalikan seluruh daftar parent
                return foundParents;
            }
        }
    }
    // Jika tidak ada parent ditemukan, kembalikan daftar parent yang kosong
    return [];
};

function handleCheck(item: Item, event) {
    const addedPermissions: Array<string> = [];
    addedPermissions.push(item.value);

    // check if have parent
    // const haveParent = findAllParents(permissions, item);
    // if (haveParent.length > 1) {
    //     haveParent.pop();
    //     addedPermissions.push(...haveParent);
    // }

    // check if have children
    if (item.children) {
        const a = findChildren(item.children);
        addedPermissions.push(...a);
    }

    // save to formData
    if (event.target.checked) {
        formData.value.permissions.push(...new Set(addedPermissions));
    } else {
        formData.value.permissions = formData.value.permissions.filter(
            (permission: string) => !addedPermissions.includes(permission)
        );
    }

    formData.value.permissions = [...new Set(formData.value.permissions)];
}

function getEdit() {
    block(document.getElementById("form-role"));
    ApiService.get("master/roles", props.selected)
        .then(({ data }) => {
            formData.value = data.role;
        })
        .catch((err: any) => {
            toast.error(err.response.data.message);
        })
        .finally(() => {
            unblock(document.getElementById("form-role"));
        });
}

function submit() {
    block(document.getElementById("form-role"));
    axios({
        method: props.selected ? "put" : "post",
        url: props.selected
            ? `/master/roles/${props.selected}`
            : "/master/roles/store",
        data: formData.value,
    })
        .then(() => {
            window.location.reload();
            toast.success("Data berhasil disimpan");
        })
        .catch((err: any) => {
            formRef.value.setErrors(err.response.data.errors);
            toast.error(err.response.data.message);
        })
        .finally(() => {
            unblock(document.getElementById("form-role"));
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
    <VForm class="form card mb-10" @submit="submit" :validation-schema="formSchema" id="form-role" ref="formRef">
        <div class="card-header align-items-center">
            <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Role</h2>
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
                        <Field class="form-control form-control-lg form-control-solid" type="text" name="name"
                            autocomplete="off" v-model="formData.name" placeholder="Masukkan Nama" />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="name" />
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                </div>
                <div class="col-md-6">
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Nama Lengkap
                        </label>
                        <Field class="form-control form-control-lg form-control-solid" name="full_name" autocomplete="off"
                            v-model="formData.full_name" placeholder="Masukkan Nama Lengkap" />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="full_name" />
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                </div>
                <div class="col-md-12">
                    <template v-for="(permission, p_id) in permissions">
                        <div class="form-check mb-5">
                            <Field class="form-check-input" type="checkbox" name="permissions[]" :value="permission.value"
                                :id="`${permission.value}-${p_id}`" v-model="formData.permissions" @change="(val) => {
                                        handleCheck(permission, val);
                                    }
                                    " />
                            <label class="form-check-label" :for="`${permission.value}-${p_id}`">
                                {{ permission.text }}
                            </label>
                        </div>

                        <template v-if="permission.hasOwnProperty('children')" v-for="(child, c_id) in permission.children">
                            <div class="form-check mb-5" style="margin-left: 3rem">
                                <Field class="form-check-input" type="checkbox" name="permissions[]" :value="child.value"
                                    :id="`${child.value}-${c_id}`" v-model="formData.permissions" @change="(val) => {
                                            handleCheck(child, val);
                                        }
                                        " />
                                <label class="form-check-label" :for="`${child.value}-${c_id}`">
                                    {{ child.text }}
                                </label>
                            </div>

                            <template v-if="child.hasOwnProperty('children')" v-for="(grandChild, gc_id) in child.children">
                                <div class="form-check mb-5" style="margin-left: 6rem">
                                    <Field class="form-check-input" type="checkbox" name="permissions[]"
                                        :value="grandChild.value" :id="`${grandChild.value}-${gc_id}`"
                                        v-model="formData.permissions" @change="(val) => {
                                                handleCheck(grandChild, val);
                                            }
                                            " />
                                    <label class="form-check-label" :for="`${grandChild.value}-${gc_id}`">
                                        {{ grandChild.text }}
                                    </label>
                                </div>

                                <template v-if="grandChild.hasOwnProperty('children')" v-for="(
                                        grandSon, gs_id
                                    ) in grandChild.children">
                                    <div class="form-check mb-5" style="margin-left: 9rem">
                                        <Field class="form-check-input" type="checkbox" name="permissions"
                                            :value="grandSon.value" :id="`${grandSon.value}-${gs_id}`"
                                            v-model="formData.permissions" @change="(val) => {
                                                    handleCheck(grandSon, val);
                                                }
                                                " />
                                        <label class="form-check-label" :for="`${grandSon.value}-${gs_id}`">
                                            {{ grandSon.text }}
                                        </label>
                                    </div>
                                </template>
                            </template>
                        </template>
                    </template>
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
