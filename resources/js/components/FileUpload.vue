<template>
    <file-pond v-bind="$props"></file-pond>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import vueFilePond, { type VueFilePondComponent } from 'vue-filepond';
import "filepond/dist/filepond.min.css";

import FilePondPluginImageExifOrientation from "filepond-plugin-image-exif-orientation";
import FilePondPluginImagePreview from "filepond-plugin-image-preview";
import FilePondPluginFilePoster from "filepond-plugin-file-poster";
import FilePondPluginFileValidateType from "filepond-plugin-file-validate-type";
import FilePondPluginImageResize from "filepond-plugin-image-resize";
import FilePondPluginImageTransform from "filepond-plugin-image-transform";
import FilePondPluginImageCrop from "filepond-plugin-image-crop";
import FilePondPluginPdfPreview from "filepond-plugin-pdf-preview";

import "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css";
import "filepond-plugin-file-poster/dist/filepond-plugin-file-poster.css";
import "filepond-plugin-pdf-preview/dist/filepond-plugin-pdf-preview.min.css";

const FilePond = vueFilePond(FilePondPluginFileValidateType, FilePondPluginImagePreview, FilePondPluginImageExifOrientation, FilePondPluginFilePoster, FilePondPluginImageResize, FilePondPluginImageCrop, FilePondPluginImageTransform, FilePondPluginPdfPreview) as VueFilePondComponent;

export default defineComponent({
    name: 'file-upload',
    components: {
        FilePond
    },
});
</script>
