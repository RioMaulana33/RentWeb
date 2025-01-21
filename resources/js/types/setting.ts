export interface Setting {
    id: BigInteger,
    uuid: string,
    app: string;
    telepon: string;
    description: string;
    alamat: string;
    email: string;
    logo: Array<File | string> | string;
    inlog: Array<File | string> | string;
}