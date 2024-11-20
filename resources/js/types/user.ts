export interface User {
    id: BigInteger;
    uuid: string;
    name: string;
    email: string;
    password?: string;
    phone?: BigInteger;
    role_id: BigInteger;
}
