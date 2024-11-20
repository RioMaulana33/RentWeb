import axios from "./axios";
import Swal from "sweetalert2";

interface ICallback {
    onSuccess?: Function;
    onError?: Function;
    onSettled?: Function;
}

export const useDelete = (callback?: ICallback, swalMixin?: any) => {
    const mySwal = Swal.mixin(
        swalMixin || {
            customClass: {
                confirmButton: "btn btn-danger btn-sm",
                cancelButton: "btn btn-secondary btn-sm",
            },
            buttonsStyling: false,
        }
    );
    const { onSuccess, onError, onSettled } = callback || {};

    return {
        delete: (url: string) =>
            mySwal
                .fire({
                    title: "Apakah anda yakin?",
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Ya, hapus",
                    cancelButtonText: "Batalkan",
                    reverseButtons: true,
                    preConfirm: () => {
                        return axios.delete(url).catch((error) => {
                            Swal.showValidationMessage(
                                error.response.data.message
                            );
                        });
                    },
                })
                .then((result) => {
                    if (result.isConfirmed) {
                        mySwal.fire(
                            "Berhasil!",
                            result.value.data.message,
                            "success"
                        );
                        onSuccess && onSuccess();
                    }
                }),
    };
};

export const useDownloadWord = (callback?: ICallback, swalMixin?: any) => {
    const mySwal = Swal.mixin(
        swalMixin || {
            customClass: {
                confirmButton: "btn btn-danger btn-sm",
                cancelButton: "btn btn-secondary btn-sm",
            },
            buttonsStyling: false,
        }
    );
    const { onSuccess, onError, onSettled } = callback || {};

    return {
        download: (
            url: string | Array<string>,
            method: string = "GET",
            body: any = {}
        ) =>
            mySwal
                .fire({
                    title: "Apakah Anda Yakin?",
                    text: "Anda Akan Mendownlaod Report Berformat Word, Mungkin Membutuhkan Waktu Beberapa Detik!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Download Sekarang!",
                    showLoaderOnConfirm: true,
                    preConfirm: function (login) {
                        if (typeof url === "string") {
                            return axios(url, {
                                method: method,
                                responseType: "arraybuffer",
                                data: body,
                            })
                                .then((res) => {
                                    var headers = res.headers;
                                    var blob = new Blob([res.data], {
                                        type: "application/msword",
                                    });

                                    var link = document.createElement("a");
                                    link.href =
                                        window.URL.createObjectURL(blob);
                                    link.download = headers[
                                        "content-disposition"
                                    ]
                                        .split("filename=")[1]
                                        .split('"')[0];
                                    link.click();

                                    window.respon = {
                                        status: true,
                                        message: "Berhasil Download!",
                                    };
                                })
                                .catch((error) => {
                                    window.respon = JSON.parse(
                                        String.fromCharCode.apply(
                                            null,
                                            new Uint8Array(error.response.data)
                                        )
                                    );
                                });
                        } else {
                            let promises: Array<any> = [];
                            for (let i = 0; i < url.length; i++) {
                                promises.push(
                                    axios(url[i], {
                                        method: method,
                                        responseType: "arraybuffer",
                                        data: body,
                                    })
                                        .then((res) => {
                                            var headers = res.headers;
                                            var blob = new Blob([res.data], {
                                                type: "application/msword",
                                            });

                                            var link =
                                                document.createElement("a");
                                            link.href =
                                                window.URL.createObjectURL(
                                                    blob
                                                );
                                            link.download = headers[
                                                "content-disposition"
                                            ]
                                                .split("filename=")[1]
                                                .split('"')[0];
                                            link.click();

                                            window.respon = {
                                                status: true,
                                                message: "Berhasil Download!",
                                            };
                                        })
                                        .catch((error) => {
                                            window.respon = JSON.parse(
                                                String.fromCharCode.apply(
                                                    null,
                                                    new Uint8Array(
                                                        error.response.data
                                                    )
                                                )
                                            );
                                        })
                                );
                            }
                            return Promise.all(promises);
                        }
                    },
                })
                .then(function (result) {
                    if (result.isConfirmed) {
                        if (window.respon.status) {
                            mySwal.fire(
                                "Berhasil!",
                                "File Berhasil Di Download.",
                                "success"
                            );
                        } else {
                            mySwal.fire(
                                "Error!",
                                window.respon.message,
                                "error"
                            );
                        }
                    }
                }),
    };
};

export const useDownloadPdf = (callback?: ICallback, swalMixin?: any) => {
    const mySwal = Swal.mixin(
        swalMixin || {
            customClass: {
                confirmButton: "btn btn-danger btn-sm",
                cancelButton: "btn btn-secondary btn-sm",
            },
            buttonsStyling: false,
        }
    );
    const { onSuccess, onError, onSettled } = callback || {};

    return {
        download: (
            url: string | Array<string>,
            method: string = "GET",
            body: any = {}
        ) =>
            mySwal
                .fire({
                    title: "Apakah Anda Yakin?",
                    text: "Anda Akan Mendownlaod Report Berformat PDF, Mungkin Membutuhkan Waktu Beberapa Detik!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Download Sekarang!",
                    showLoaderOnConfirm: true,
                    preConfirm: function (login) {
                        if (typeof url === "string") {
                            return axios(url, {
                                method: method,
                                responseType: "arraybuffer",
                                data: body,
                            })
                                .then((res) => {
                                    var headers = res.headers;
                                    var blob = new Blob([res.data], {
                                        type: "application/pdf",
                                    });

                                    var link = document.createElement("a");
                                    link.href =
                                        window.URL.createObjectURL(blob);
                                    link.download = headers[
                                        "content-disposition"
                                    ]
                                        .split('filename="')[1]
                                        .split('"')[0];
                                    link.click();

                                    window.respon = {
                                        status: true,
                                        message: "Berhasil Download!",
                                    };
                                })
                                .catch((error) => {
                                    window.respon = JSON.parse(
                                        String.fromCharCode.apply(
                                            null,
                                            new Uint8Array(error.response.data)
                                        )
                                    );
                                });
                        } else {
                            let promises: Array<any> = [];
                            for (let i = 0; i < url.length; i++) {
                                promises.push(
                                    axios(url[i], {
                                        method: method,
                                        responseType: "arraybuffer",
                                        data: body,
                                    })
                                        .then((res) => {
                                            var headers = res.headers;
                                            var blob = new Blob([res.data], {
                                                type: "application/pdf",
                                            });

                                            var link =
                                                document.createElement("a");
                                            link.href =
                                                window.URL.createObjectURL(
                                                    blob
                                                );
                                            link.download = headers[
                                                "content-disposition"
                                            ]
                                                .split('filename="')[1]
                                                .split('"')[0];
                                            link.click();

                                            window.respon = {
                                                status: true,
                                                message: "Berhasil Download!",
                                            };
                                        })
                                        .catch((error) => {
                                            window.respon = JSON.parse(
                                                String.fromCharCode.apply(
                                                    null,
                                                    new Uint8Array(
                                                        error.response.data
                                                    )
                                                )
                                            );
                                        })
                                );
                            }
                            return Promise.all(promises);
                        }
                    },
                })
                .then(function (result) {
                    if (result.isConfirmed) {
                        if (window.respon.status) {
                            mySwal.fire(
                                "Berhasil!",
                                "File Berhasil Di Download.",
                                "success"
                            );
                        } else {
                            mySwal.fire(
                                "Error!",
                                window.respon.message,
                                "error"
                            );
                        }
                    }
                }),
    };
};

export const useDownloadExcel = (callback?: ICallback, swalMixin?: any) => {
    const mySwal = Swal.mixin(
        swalMixin || {
            customClass: {
                confirmButton: "btn btn-danger btn-sm",
                cancelButton: "btn btn-secondary btn-sm",
            },
            buttonsStyling: false,
        }
    );
    const { onSuccess, onError, onSettled } = callback || {};

    return {
        download: (
            url: string | Array<string>,
            method: string = "GET",
            body: any = {}
        ) =>
            mySwal
                .fire({
                    title: "Apakah Anda Yakin?",
                    text: "Anda Akan Mendownlaod Report Berformat Excel, Mungkin Membutuhkan Waktu Beberapa Detik!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Download Sekarang!",
                    showLoaderOnConfirm: true,
                    preConfirm: function (login) {
                        if (typeof url === "string") {
                            return axios(url, {
                                method: method,
                                responseType: "arraybuffer",
                                data: body,
                            })
                                .then((res) => {
                                    var headers = res.headers;
                                    var blob = new Blob([res.data], {
                                        type: "application/vnd.ms-excel",
                                    });

                                    var link = document.createElement("a");
                                    link.href =
                                        window.URL.createObjectURL(blob);
                                    link.download = headers[
                                        "content-disposition"
                                    ]
                                        .split('filename="')[1]
                                        .split('"')[0];
                                    link.click();

                                    window.respon = {
                                        status: true,
                                        message: "Berhasil Download!",
                                    };
                                })
                                .catch((error) => {
                                    window.respon = JSON.parse(
                                        String.fromCharCode.apply(
                                            null,
                                            new Uint8Array(error.response.data)
                                        )
                                    );
                                });
                        } else {
                            let promises: Array<any> = [];
                            for (let i = 0; i < url.length; i++) {
                                promises.push(
                                    axios(url[i], {
                                        method: method,
                                        responseType: "arraybuffer",
                                        data: body,
                                    })
                                        .then((res) => {
                                            var headers = res.headers;
                                            var blob = new Blob([res.data], {
                                                type: "application/pdf",
                                            });

                                            var link =
                                                document.createElement("a");
                                            link.href =
                                                window.URL.createObjectURL(
                                                    blob
                                                );
                                            link.download = headers[
                                                "content-disposition"
                                            ]
                                                .split('filename="')[1]
                                                .split('"')[0];
                                            link.click();

                                            window.respon = {
                                                status: true,
                                                message: "Berhasil Download!",
                                            };
                                        })
                                        .catch((error) => {
                                            window.respon = JSON.parse(
                                                String.fromCharCode.apply(
                                                    null,
                                                    new Uint8Array(
                                                        error.response.data
                                                    )
                                                )
                                            );
                                        })
                                );
                            }
                            return Promise.all(promises);
                        }
                    },
                })
                .then(function (result) {
                    if (result.isConfirmed) {
                        if (window.respon.status) {
                            mySwal.fire(
                                "Berhasil!",
                                "File Berhasil Di Download.",
                                "success"
                            );
                        } else {
                            mySwal.fire(
                                "Error!",
                                window.respon.message,
                                "error"
                            );
                        }
                    }
                }),
    };
};

export const useSwalConfirm = (
    options?: any,
    callback?: ICallback,
    swalMixin?: any
) => {
    const mySwal = Swal.mixin(
        swalMixin || {
            customClass: {
                confirmButton: "btn btn-primary btn-sm",
                cancelButton: "btn btn-secondary btn-sm",
            },
            buttonsStyling: false,
        }
    );
    const { onSuccess, onError, onSettled } = callback || {};

    return {
        confirm: (url: string, method: string = "GET", body: any = {}) =>
            mySwal
                .fire({
                    title: "Apakah Anda Yakin?",
                    icon: "warning",
                    showCancelButton: true,
                    showLoaderOnConfirm: true,
                    ...options,
                    preConfirm: function (login) {
                        return axios(url, {
                            method: method,
                            data: body,
                        })
                            .then((res) => {
                                onSuccess && onSuccess();

                                window.respon = {
                                    status: true,
                                    message: res.data,
                                };
                            })
                            .catch((error) => {
                                onError && onError();

                                window.respon = error.response?.data;
                            });
                    },
                })
                .then(function (result) {
                    if (result.isConfirmed) {
                        if (window.respon?.status) {
                            mySwal.fire(
                                "Berhasil!",
                                window.respon?.message,
                                "success"
                            );
                        } else {
                            mySwal.fire(
                                "Error!",
                                window.respon?.message,
                                "error"
                            );
                        }
                    }
                }),
    };
};
