const glob = require('@actions/glob'); // @TODO move to 'imports from' when moved to TS !

export async function* lookup(pattern, options = undefined) {
    const finalOptions = {
        followSymbolicLinks: options?.followSymbolicLinks ?? true,
        implicitDescendants: options?.implicitDescendants ?? false, // False by default to avoid big results !
        matchDirectories: options?.implicitDescendants ?? false, // False by default to avoid big results !
        omitBrokenSymbolicLinks: options?.omitBrokenSymbolicLinks ?? false, // Avoid error related to broken files
    };

    const globber = await glob.create(pattern, finalOptions);

    yield* globber.globGenerator();
}
