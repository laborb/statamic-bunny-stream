let markRawImplementation = (value) => value;

export function configureMarkRaw(implementation) {
    markRawImplementation = implementation;
}

export function markRaw(value) {
    return markRawImplementation(value);
}
