export const localePrefix = () =>
    String(document.documentElement?.lang || 'es').toLowerCase().startsWith('en') ? 'en' : 'es';

export const localeTag = () => (localePrefix() === 'en' ? 'en-US' : 'es-ES');

export const focusableSelector = 'a[href], button:not([disabled]), textarea, input:not([type="hidden"]):not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';

export const uiLabels = {
    es: {
        confirmAction: 'Confirmar acción',
        confirm: 'Confirmar',
        confirmDeleteNamed: '¿Seguro que quieres eliminar "{item}"?',
        confirmDeleteFallback: '¿Seguro que quieres eliminar este elemento?',
        requestFailed: 'La solicitud falló (HTTP {status}).',
        loadRetry: 'No se pudo cargar la información. Intenta nuevamente.',
        refreshing: 'Actualizando resultados...',
        readonlyNotice: 'Esta pantalla es de solo lectura.'
    },
    en: {
        confirmAction: 'Confirm action',
        confirm: 'Confirm',
        confirmDeleteNamed: 'Are you sure you want to delete "{item}"?',
        confirmDeleteFallback: 'Are you sure you want to delete this item?',
        requestFailed: 'Request failed (HTTP {status}).',
        loadRetry: 'Could not load the information. Please try again.',
        refreshing: 'Refreshing results...',
        readonlyNotice: 'This screen is read-only.'
    }
};

export const buildConfirmDeleteMessage = (itemLabel = '', fallback = '') => {
    const labels = uiLabels[localePrefix()] || uiLabels.es;
    const label = String(itemLabel ?? '').trim();
    if (label === '') return String(fallback || labels.confirmDeleteFallback || labels.confirm);
    return String(labels.confirmDeleteNamed || labels.confirmDeleteFallback || labels.confirm)
        .replace('{item}', label);
};

export const paginationLabels = {
    es: { visibleResults: 'Resultados visibles', showing: 'Mostrando', of: 'de' },
    en: { visibleResults: 'Visible results', showing: 'Showing', of: 'of' }
};

const statusLabels = {
    es: { active: 'Activo', pending: 'Pendiente', pending_approval: 'Pendiente de aprobacion', suspended: 'Suspendido', approved: 'Aprobado', rejected: 'Rechazado', processing: 'Procesando', success: 'Exitoso', failed: 'Fallido' },
    en: { active: 'Active', pending: 'Pending', pending_approval: 'Pending approval', suspended: 'Suspended', approved: 'Approved', rejected: 'Rejected', processing: 'Processing', success: 'Success', failed: 'Failed' }
};

const auditActionLabels = {
    es: { create: 'Crear', update: 'Actualizar', delete: 'Eliminar', login: 'Iniciar sesion', login_success: 'Inicio de sesion exitoso', login_failure: 'Inicio de sesion fallido', logout: 'Cerrar sesion', approve: 'Aprobar' },
    en: { create: 'Create', update: 'Update', delete: 'Delete', login: 'Login', login_success: 'Login Success', login_failure: 'Login Failure', logout: 'Logout', approve: 'Approve' }
};

const auditResultLabels = {
    es: { success: 'Exito', failure: 'Fallo', denied: 'Denegado' },
    en: { success: 'Success', failure: 'Failure', denied: 'Denied' }
};

const auditSeverityLabels = {
    es: { info: 'Info', warning: 'Advertencia', critical: 'Critico' },
    en: { info: 'Info', warning: 'Warning', critical: 'Critical' }
};

/** @param {string} status @returns {string} */
export const statusLabel = (status) => {
    const value = String(status || '').trim();
    if (value === '') return '-';
    return statusLabels[localePrefix()]?.[value.toLowerCase()] || value;
};

/** @param {string} action @returns {string} */
export const auditActionLabel = (action) => {
    const value = String(action || '').trim();
    if (value === '') return '-';
    return auditActionLabels[localePrefix()]?.[value.toLowerCase()] || value;
};

/** @param {string} result @returns {string} */
export const auditResultLabel = (result) => {
    const value = String(result || '').trim();
    if (value === '') return '-';
    return auditResultLabels[localePrefix()]?.[value.toLowerCase()] || value;
};

/** @param {string} severity @returns {string} */
export const auditSeverityLabel = (severity) => {
    const value = String(severity || '').trim();
    if (value === '') return '-';
    return auditSeverityLabels[localePrefix()]?.[value.toLowerCase()] || value;
};
