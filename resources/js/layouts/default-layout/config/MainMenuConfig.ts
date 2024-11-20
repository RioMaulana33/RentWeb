import type { MenuItem } from "@/layouts/default-layout/config/types";

const MainMenuConfig: Array<MenuItem> = [
    {
        pages: [
            {
                heading: "Dashboard",
                name: "dashboard",
                route: "/dashboard",
                keenthemesIcon: "element-11",
            },
        ],
    },

    // WEBSITE
    {
        heading: "Website",
        route: "/dashboard/website",
        name: "website",
        pages: [

            {
                heading: "Penyewaan",
                route: "/dashboard/penyewaan",
                name: "dashboard-penyewaan",
                keenthemesIcon: "abstract-26",
            },
            {
                heading: "Pembayaran",
                route: "/dashboard/pembayaran",
                name: "dashboard-pembayaran",
                keenthemesIcon: "two-credit-cart",
            },
            {
                heading: "Laporan",
                route: "/dashboard/laporan",
                name: "dashboard-laporan",
                keenthemesIcon: "document",
            },
            {
                sectionTitle: "Kelola Mobil",
                route: "/dashboard/kelola-mobil",
                keenthemesIcon: "car-3",
                name: "kelola-mobil",
                sub: [
                    // {
                    //     heading: "Status Mobil",
                    //     name: "pembayaran-daftartagihanpasien",
                    //     route: "/dashboard/pembayaran/daftartagihanpasien",
                    // },
                    {
                        heading: "Detail Mobil",
                        name: "kelola-mobil-detail",
                        route: "/dashboard/kelola-mobil/detail",
                    },
                ],
            },


            // MASTER
            {
                sectionTitle: "Master",
                route: "/master",
                keenthemesIcon: "cube-3",
                name: "master",
                sub: [
                    {
                        sectionTitle: "User",
                        route: "/users",
                        name: "master-user",
                        sub: [
                            {
                                heading: "Role",
                                name: "master-role",
                                route: "/dashboard/master/users/roles",
                            },
                            {
                                heading: "User",
                                name: "master-user",
                                route: "/dashboard/master/users",
                            },
                        ],
                    },
                ],
            },
            {
                heading: "Setting",
                route: "/dashboard/setting",
                name: "setting",
                keenthemesIcon: "setting-2",
            },
        ],
    },
];

export default MainMenuConfig;
