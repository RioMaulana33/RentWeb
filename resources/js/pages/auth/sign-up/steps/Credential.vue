<template>
    <section class="w-100">
        <!--begin::Input group-->
        <div class="fv-row mb-7">
            <label class="form-label fw-bold text-dark fs-6">Nama</label>
            <Field class="form-control form-control-lg form-control-solid" type="text" name="nama" autocomplete="off"
                v-model="formData.nama" />
            <div class="fv-plugins-message-container">
                <div class="fv-help-block">
                    <ErrorMessage name="nama" />
                </div>
            </div>
        </div>
        <!--end::Input group-->

        <!--begin::Input group-->
        <div class="fv-row mb-7">
            <label class="form-label fw-bold text-dark fs-6">Email</label>
            <Field class="form-control form-control-lg form-control-solid" type="email" name="email" autocomplete="off"
                v-model="formData.email" />
            <div class="fv-plugins-message-container">
                <div class="fv-help-block">
                    <ErrorMessage name="email" />
                </div>
            </div>
        </div>
        <!--end::Input group-->

        <!--begin::Input group-->
        <div class="fv-row mb-7">
            <label class="form-label fw-bold text-dark fs-6">No. Telepon</label>
            <Field class="form-control form-control-lg form-control-solid" type="phone" name="phone" autocomplete="off"
                v-model="formData.phone" />
            <div class="fv-plugins-message-container">
                <div class="fv-help-block">
                    <ErrorMessage name="phone" />
                </div>
            </div>
        </div>
        <!--end::Input group-->
    </section>
</template>

<script lang="ts">
import { defineComponent } from 'vue'

export default defineComponent({
    name: 'Credential',
    props: {
        formData: {
            type: Object,
            required: true,
        },
    },
    setup(props) {
        return {
            formData: props.formData,
        }
    },
})
</script>
