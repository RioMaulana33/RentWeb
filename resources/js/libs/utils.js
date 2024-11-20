import { toast } from "vue3-toastify";

function checkElem(elem) {
    if (typeof elem === "string") {
        return document.querySelector(elem);
    } else {
        return elem;
    }
}

export function blockBtn(elem) {
    const btn = checkElem(elem);
    btn.disabled = true;
    btn.setAttribute("data-kt-indicator", "on");
}

export function unblockBtn(elem) {
    const btn = checkElem(elem);
    btn.disabled = false;
    btn.removeAttribute("data-kt-indicator");
}

let elBlockPosition = null;
export function block(target, options) {
    var el = $(target);
    elBlockPosition = el.css('position'); // save default position attribute

    options = $.extend(true, {
        opacity: 0.05,
        overlayColor: '#000000',
        type: '',
        size: '',
        state: 'primary',
        centerX: true,
        centerY: true,
        message: '',
        shadow: true,
        width: 'auto'
    }, options);

    var version = options.type ? 'spinner-' + options.type : '';
    var state = options.state ? 'spinner-' + options.state : '';
    var size = options.size ? 'spinner-' + options.size : '';
    var spinner = '<span class="spinner ' + version + ' ' + state + ' ' + size + '"></span';

    var el = $(target);
    el.css('position', 'relative');
    el.append(`
    <div class="blockUI blockOverlay" style="z-index: 10; border: none; margin: 0px; padding: 0px; width: 100%; height: 100%; top: 0px; left: 0px; background-color: rgb(90, 90, 90); opacity: 0.05; cursor: wait; position: absolute;"></div>
    `)
    el.append(`
    <div class="blockUI blockMsg blockElement" style="z-index: 1011; position: absolute; padding: 0px; margin: 0px; width: auto; top: 50%; left: 50%; transform: translateX(-50%); text-align: center; color: rgb(90, 90, 90); border: 0px; cursor: wait;">${spinner}</div>
    `);
}

export function unblock(target) {
    var el = $(target);
    el.find('.blockUI').remove();
    el.css('position', elBlockPosition); // restore default position attribute
}

export function currency(
    value,
    options = { style: "currency", currency: "IDR" }
) {
    return Intl.NumberFormat("id-ID", options).format(value);
}

export function mapStatusPengujian(status) {
    const statusPengujian = {
        "-1": "Revisi",
        0: "Mengajukan Permohonan",
        1: "Menyerahkan Sampel",
        2: "Menyerahkan Surat Perintah Pengujian",
        3: "Menyerahkan sampel untuk Proses Pengujian",
        4: "Menyerahkan RDPS",
        5: "Menyerahkan RDPS untuk Pengetikan LHU",
        6: "Menyerahkan LHU untuk Diverifikasi",
        7: "Mengesahkan LHU",
        8: "Pembayaran",
        9: "Penyerahan LHU",
        10: "Penyerahan LHU Amandemen (Jika ada)",
        11: "Selesai"
    };

    return statusPengujian[status] || "Sedang Diproses";
}

export function mapStatusPembayaran(status) {
    const statusPembayaran = {
        0: "Belum Dibayar",
        1: "Berhasil",
        2: "Gagal",
    };

    return statusPembayaran[status] || "Belum Dibayar";
}

export function convertToDMS(coord) {
    const absoluteCoord = Math.abs(coord);
    const degrees = Math.floor(absoluteCoord);
    const minutes = Math.floor((absoluteCoord - degrees) * 60);
    const seconds = ((absoluteCoord - degrees - minutes / 60) * 3600).toFixed(1);

    return `${degrees}°${minutes}'${seconds}"${coord >= 0 ? 'N' : 'S'}`;
}

export function copyString(str) {
    const el = document.createElement('textarea');
    el.value = str;
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);

    toast.success('Berhasil disalin');
}

export function ucfirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

export function formDataToObject(formData) {
    const formDataObject = {};

    formData.forEach((value, key) => {
        // Check if the key already exists in the object
        if (formDataObject.hasOwnProperty(key)) {
            // If it does, and it's not an array, convert it to an array
            if (!Array.isArray(formDataObject[key])) {
                formDataObject[key] = [formDataObject[key]];
            }
            // Push the new value to the array
            formDataObject[key].push(value);
        } else {
            // If the key doesn't exist, simply assign the value
            formDataObject[key] = value;
        }
    });

    return formDataObject;
}