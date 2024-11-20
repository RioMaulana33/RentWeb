export interface Role {
    id: BigInteger;
    name: string;
    full_name: string;
    guard_name: string;
    permissions?: Array<string>;
}
