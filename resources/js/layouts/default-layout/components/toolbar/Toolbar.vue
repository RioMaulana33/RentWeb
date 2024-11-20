<script setup lang="ts">
import { toolbarWidthFluid } from "@/layouts/default-layout/config/helper";
import KTPageTitle from "@/layouts/default-layout/components/toolbar/PageTitle.vue";
</script>

<template>
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <!--begin::Toolbar container-->
        <div
            id="kt_app_toolbar_container"
            class="app-container d-flex flex-stack"
            :class="{
                'container-fluid': toolbarWidthFluid,
                'container-xxl': !toolbarWidthFluid,
            }"
        >
            <KTPageTitle />
        </div>
        <!--end::Toolbar container-->
    </div>
    <!--end::Toolbar-->
</template>
