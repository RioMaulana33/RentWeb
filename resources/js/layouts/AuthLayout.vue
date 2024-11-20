<template>
	<!--begin::Authentication Layout -->
	<div class="d-flex flex-column flex-column-fluid flex-lg-row justify-content-center"
	  :style="`background-image: url('${setting?.bg_auth}'); background-size: cover`">
	  <!-- begin::Body -->
	  <div class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12 p-lg-20">
		<!--begin::Card-->
		<div class="bg-body d-flex flex-row align-items-stretch flex-center rounded-4 w-xxl-1400px p-md-10 w-100">
		  <!--begin::Content-->
		  <div :class="[
			'd-flex flex-center flex-column px-15 py-15',
			{ 'w-100': isPasswordResetPage, 'w-50': !isPasswordResetPage }
		  ]">
			<router-view></router-view>
		  </div>
		  <!--end::Content-->
  
		  <!--begin::Illustration-->
		  <div v-if="!isPasswordResetPage" class="d-flex flex-center flex-column px-15 py-15 w-50 bg-light">
			<div class="text-center mb-10" style="max-width: 90%; width: 500px;">
			  <img src="/media/misc/vector_auth.png" alt="Illustration" class="img-fluid w-100">
			</div>
			<h3 class="text-center mb-4"> Kemudahan dalam Genggaman!</h3>
			<p class="text-center fs-5">Sewa Mobil Mudah, Perjalanan Lebih Seru.</p>
		  </div>
		  <!--end::Illustration-->
		</div>
		<!--end::Card-->
	  </div>
	  <!--end::Body-->
	</div>
	<!--end::Authentication Layout -->
  </template>
  
  <script lang="ts">
  import { defineComponent, onMounted, computed } from "vue";
  import { useRoute } from "vue-router";
  import LayoutService from "@/core/services/LayoutService";
  import { useBodyStore } from "@/stores/body";
  import { useSetting } from "@/services";
  
  export default defineComponent({
	name: "auth-layout",
	setup() {
	  const store = useBodyStore();
	  const { data: setting = {} } = useSetting();
	  const route = useRoute();
  
	  const isPasswordResetPage = computed(() => route.name === 'password-reset');
  
	  onMounted(() => {
		LayoutService.emptyElementClassesAndAttributes(document.body);
		store.addBodyClassname("app-blank");
		store.addBodyClassname("bg-body");
	  });
  
	  return {
		setting,
		isPasswordResetPage,
	  };
	},
  });
  </script>	