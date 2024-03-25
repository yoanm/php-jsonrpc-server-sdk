export type Metadata = {
    name: string;
    format: string;
    path: string;
    reports: string[];
    flags: string[];
    artifact?: string;
}
export type MetadataString = {
    name: string;
    format: string;
    path: string;
    reports: string;
    flags: string;
    artifact?: string;
}
export type MetadataJson = {
    name: string[];
    format: string[];
    path: string[];
    reports: string[][];
    flags: string[][];
    artifact?: string[];
}

export type ActionOutputData = {
    count: number;
    paths: string;
    artifacts?: string;
    list: (MetadataString|MetadataJson)[];
}

export type MultiGroupOutput<MType extends MetadataString|MetadataJson = MetadataString|MetadataJson> = {
    count: number;
    paths: string;
    list: MType[];
}

export type MetadataKeyMapper<K extends keyof Metadata> = (list: Metadata[], property: K) => Metadata[K][];


export type IdentityTypeMapper<T = any> = (list: T[]) => T[];

export type MergeField = (keyof Metadata) & ('name' | 'flags' | 'format' | 'artifact');
