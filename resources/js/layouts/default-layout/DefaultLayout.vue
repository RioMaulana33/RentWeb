<script setup lang="ts">
import { nextTick, onBeforeMount, onMounted, watch } from "vue";
import KTHeader from "@/layouts/default-layout/components/header/Header.vue";
import KTSidebar from "@/layouts/default-layout/components/sidebar/Sidebar.vue";
import KTContent from "@/layouts/default-layout/components/content/Content.vue";
import KTToolbar from "@/layouts/default-layout/components/toolbar/Toolbar.vue";
import KTScrollTop from "@/layouts/default-layout/components/extras/ScrollTop.vue";
import { useRoute } from "vue-router";
import { reinitializeComponents } from "@/core/plugins/keenthemes";
import LayoutService from "@/core/services/LayoutService";

const route = useRoute();

onBeforeMount(() => {
    LayoutService.init();
});

onMounted(() => {
    nextTick(() => {
        reinitializeComponents();
    });
});

watch(
    () => route.path,
    () => {
        nextTick(() => {
            reinitializeComponents();
        });
    }
);
</script>

<template>
    <!--begin::App-->
    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <!--begin::Page-->
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            <KTHeader />
            <!--begin::Wrapper-->
            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                <KTSidebar />
                <!--begin::Main-->
                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <!--begin::Content wrapper-->
                    <div class="d-flex flex-column flex-column-fluid">
                        <KTToolbar />
                        <div id="kt_app_content" class="app-content flex-column-fluid">
                            <KTContent></KTContent>
                        </div>
                    </div>
                    <!--end::Content wrapper-->
                </div>
                <!--end:::Main-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Page-->
    </div>
    <!--end::App-->
    <KTScrollTop />
</template>
