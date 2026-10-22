export interface DataTableHeader {
    text: string;
    value: string;
    width?: number | string;
    sortable?: boolean;
}

export type DataTableItem = object;

export interface DataTableServerOptions {
    page: number;
    rowsPerPage: number;
}
