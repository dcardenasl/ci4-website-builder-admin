import { isObject } from './url.js';

/** @param {object|null} file @returns {string} */
export const bestFilePreviewUrl = (file) => {
    const variants = isObject(file?.variants) ? file.variants : {};
    return String(
        variants.md?.url || variants.sm?.url || variants.thumb?.url || file?.url || ''
    );
};

/**
 * @param {string} fileId
 * @param {string} fileUrl
 * @returns {string}
 */
export const resolveTranslatableFilePreviewUrl = (fileId, fileUrl = '') => {
    const normalizedUrl = String(fileUrl || '');
    if (normalizedUrl !== '') return normalizedUrl;
    const normalizedId = String(fileId || '');
    return normalizedId !== ''
        ? `${window.location.origin}/files/${encodeURIComponent(normalizedId)}/view`
        : '';
};
