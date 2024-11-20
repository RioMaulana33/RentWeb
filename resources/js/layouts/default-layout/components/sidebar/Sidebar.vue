<script setup lang="ts">
import { defineComponent, ref } from "vue";
import { displaySidebar } from "@/layouts/default-layout/config/helper";
import KTSidebarLogo from "@/layouts/default-layout/components/sidebar/SidebarLogo.vue";
import KTSidebarMenu from "@/layouts/default-layout/components/sidebar/SidebarMenu.vue";

const sidebarRef = ref<HTMLFormElement | null>(null);
</script>

<template>
    <!--begin::sidebar-->
    <div
        v-if="displaySidebar"
        ref="sidebarRef"
        id="kt_app_sidebar"
        class="app-sidebar flex-column"
        data-kt-drawer="true"
        data-kt-drawer-name="app-sidebar"
        data-kt-drawer-activate="{default: true, lg: false}"
        data-kt-drawer-overlay="true"
        data-kt-drawer-width="225px"
        data-kt-drawer-direction="start"
        data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle"
    >
        <KTSidebarLogo :sidebar-ref="sidebarRef"></KTSidebarLogo>
        <KTSidebarMenu></KTSidebarMenu>
    </div>
    <!--end::sidebar-->
</template>
