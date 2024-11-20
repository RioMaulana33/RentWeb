import {
    createRouter,
    createWebHistory,
    type RouteRecordRaw,
} from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useConfigStore } from "@/stores/config";
import NProgress from "nprogress";
import "nprogress/nprogress.css";

declare module "vue-router" {
    interface RouteMeta {
        pageTitle?: string;
        permission?: string;
    }
}

const routes: Array<RouteRecordRaw> = [
    {
        path: "/",
        redirect: "/dashboard",
        component: () => import("@/layouts/default-layout/DefaultLayout.vue"),
        meta: {
            middleware: "auth",
        },
        children: [
            {
                path: "/dashboard",
                name: "dashboard",
                component: () => import("@/pages/dashboard/Index.vue"),
                meta: {
                    pageTitle: "Dashboard",
                    breadcrumbs: ["Dashboard"],
                },
            },

            //ADMIN PAGE

            {
                path: "/dashboard/penyewaan",
                name: "dashboard.penyewaan",
                component: () =>
                    import("@/pages/dashboard/penyewaan/Index.vue"),
                meta: {
                    pageTitle: "Penyewaan",
                    breadcrumbs: ["Penyewaan"],
                },
            },

            {
                path: "/dashboard/penyewaan",
                name: "dashboard.penyewaan",
                component: () =>
                    import("@/pages/dashboard/penyewaan/Index.vue"),
                meta: {
                    pageTitle: "Penyewaan",
                    breadcrumbs: ["Penyewaan"],
                },
            },

            {
                path: "/dashboard/pembayaran",
                name: "dashboard.pembayaran",
                component: () =>
                    import("@/pages/dashboard/pembayaran/Index.vue"),
                meta: {
                    pageTitle: "Pembayaran",
                    breadcrumbs: ["Pembayaran"],
                },
            },

            {
                path: "/dashboard/laporan",
                name: "dashboard.laporan",
                component: () =>
                    import("@/pages/dashboard/laporan/Index.vue"),
                meta: {
                    pageTitle: "Laporan",
                    breadcrumbs: ["Laporan"],
                },
            },
            {
                path: "/dashboard/kelola-mobil/detail",
                name: "kelola-mobil.detail",

                component: () =>
                    import("@/pages/dashboard/kelola-mobil/detail/Index.vue"),
                meta: {
                    pageTitle: "Detail Mobil",
                    breadcrumbs: ["Kelola Mobil", "Detail Mobil"],
                },
            },


            //WEBSITE COMPONENT

            {
                path: "/dashboard/profile",
                name: "dashboard.profile",
                component: () => import("@/pages/dashboard/profile/Index.vue"),
                meta: {
                    pageTitle: "Profile",
                    breadcrumbs: ["Profile"],
                },
            },
            {
                path: "/dashboard/setting",
                name: "dashboard.setting",
                component: () => import("@/pages/dashboard/setting/Index.vue"),
                meta: {
                    pageTitle: "Website Setting",
                    breadcrumbs: ["Website", "Setting"],
                },
            },

            // MASTER
            {
                path: "/dashboard/master/users/roles",
                name: "dashboard.master.users.roles",
                component: () =>
                    import("@/pages/dashboard/master/users/roles/Index.vue"),
                meta: {
                    pageTitle: "User Roles",
                    breadcrumbs: ["Master", "Users", "Roles"],
                },
            },
            {
                path: "/dashboard/master/users",
                name: "dashboard.master.users",
                component: () =>
                    import("@/pages/dashboard/master/users/Index.vue"),
                meta: {
                    pageTitle: "Users",
                    breadcrumbs: ["Master", "Users"],
                },
            },
        ],
    },
    {
        path: "/",
        component: () => import("@/layouts/AuthLayout.vue"),
        children: [
            {
                path: "/sign-in",
                name: "sign-in",
                component: () => import("@/pages/auth/sign-in/Index.vue"),
                meta: {
                    pageTitle: "Sign In",
                    middleware: "guest",
                },
            },
        ],
    },
    {
        path: "/password-reset",
        component: () => import("@/layouts/AuthLayout.vue"),
        children: [
            {
                path: "/password-reset",
                name: "password-reset",
                component: () => import("@/pages/auth/sign-in/tabs/ResetPassword.vue"),
                meta: {
                    pageTitle: "Reset Password",
                    middleware: "guest",
                },
            },
        ],
    },
    // {
    //     path: "/password-reset",
    //     name: "password-reset",
    //     component: () => import("@/pages/auth/sign-in/tabs/ResetPassword.vue"),
    //     meta: {
    //         pageTitle: "Reset Password",
    //         middleware: "guest",
    //     },
    // },
    {
        path: "/",
        component: () => import("@/layouts/SystemLayout.vue"),
        children: [
            {
                // the 404 route, when none of the above matches
                path: "/404",
                name: "404",
                component: () => import("@/pages/errors/Error404.vue"),
                meta: {
                    pageTitle: "Error 404",
                },
            },
            {
                path: "/500",
                name: "500",
                component: () => import("@/pages/errors/Error500.vue"),
                meta: {
                    pageTitle: "Error 500",
                },
            },
        ],
    },
    {
        path: "/:pathMatch(.*)*",
        redirect: "/404",
    },
];

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes,
    scrollBehavior(to) {
        // If the route has a hash, scroll to the section with the specified ID; otherwise, scroll to the top of the page.
        if (to.hash) {
            return {
                el: to.hash,
                top: 80,
                behavior: "smooth",
            };
        } else {
            return {
                top: 0,
                left: 0,
                behavior: "smooth",
            };
        }
    },
});

router.beforeEach(async (to, from, next) => {
    if (to.name) {
        // Start the route progress bar.
        NProgress.start();
    }

    const authStore = useAuthStore();
    const configStore = useConfigStore();

    // current page view title
    if (to.meta.pageTitle) {
        document.title = `${to.meta.pageTitle} - ${import.meta.env.VITE_APP_NAME
            }`;
    } else {
        document.title = import.meta.env.VITE_APP_NAME as string;
    }

    // reset config to initial state
    configStore.resetLayoutConfig();

    // verify auth token before each page change
    if (!authStore.isAuthenticated) await authStore.verifyAuth();

    // before page access check if page requires authentication
    if (to.meta.middleware == "auth") {
        if (authStore.isAuthenticated) {
            if (
                to.meta.permission &&
                !authStore.user.permission.includes(to.meta.permission)
            ) {
                next({ name: "404" });
            } else if (to.meta.checkDetail == false) {
                next();
            }

            next();
        } else {
            next({ name: "sign-in" });
        }
    } else if (to.meta.middleware == "guest" && authStore.isAuthenticated) {
        next({ name: "dashboard" });
    } else {
        next();
    }
});

router.afterEach(() => {
    // Complete the animation of the route progress bar.
    NProgress.done();
});

export default router;
