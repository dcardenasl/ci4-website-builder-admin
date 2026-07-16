import { bootLucideIcons } from './utils/lucide.js';
import { bootSlugFields } from './utils/slug.js';
import { bestFilePreviewUrl, resolveTranslatableFilePreviewUrl } from './utils/fileUrl.js';
import { formValuesToObject } from './utils/formSerialization.js';
import { buildConfirmDeleteMessage } from './utils/labels.js';

import { confirmStore } from './stores/confirm.store.js';
import { toastStore } from './stores/toast.store.js';
import { filePickerStore } from './stores/filePicker.store.js';

import { appShell } from './components/appShell.js';
import { remoteTableFactory } from './components/remoteTable.js';
import { formFieldBuilderFactory } from './components/formFieldBuilder.js';
import { filePickerField } from './components/filePickerField.js';
import { translatableFileField } from './components/translatableFileField.js';
import { mediaReferenceField } from './components/mediaReferenceField.js';
import { blockRepeaterField } from './components/blockRepeaterField.js';
import { adminMetadataField } from './components/adminMetadataField.js';
import { adminMediaGallery } from './components/adminMediaGallery.js';
import { langTabs } from './components/langTabs.js';
import { jsonEditor } from './components/jsonEditor.js';
import { blockPreview } from './components/blockPreview.js';
import { blockTypeDesigner } from './components/blockTypeDesigner.js';
import { blockInstanceConfig } from './components/blockInstanceConfig.js';
import { schemaEditor } from './components/schemaEditor.js';
import { blockSorter } from './components/blockSorter.js';
import { bootSessionExpiryWatcher } from './components/sessionWatcher.js';
import { handleGoogleCredentialResponse } from './components/googleAuth.js';
import { richTextEditor } from './components/richTextEditor.js';
import {
    copyLangTabsFileFieldToAll,
    copyLangTabsFileFieldToTargets,
    copyLangTabsMediaReferenceFieldToAll,
} from './components/langTabs.js';
import { wizard } from './components/wizard/index.js';
import { structureWizard } from './components/wizard/structureIndex.js';

document.addEventListener('alpine:init', () => {
    Alpine.store('confirm', confirmStore());
    Alpine.store('toast', toastStore);
    Alpine.store('filePicker', filePickerStore);

    Alpine.data('appShell', appShell);
    Alpine.data('remoteTable', remoteTableFactory);
    Alpine.data('formFieldBuilder', formFieldBuilderFactory);
    Alpine.data('filePickerField', filePickerField);
    Alpine.data('translatableFileField', translatableFileField);
    Alpine.data('mediaReferenceField', mediaReferenceField);
    Alpine.data('blockRepeaterField', blockRepeaterField);
    Alpine.data('adminMetadataField', adminMetadataField);
    Alpine.data('adminMediaGallery', adminMediaGallery);
    Alpine.data('langTabs', langTabs);
    Alpine.data('jsonEditor', jsonEditor);
    Alpine.data('blockPreview', blockPreview);
    Alpine.data('blockTypeDesigner', blockTypeDesigner);
    Alpine.data('blockInstanceConfig', blockInstanceConfig);
    Alpine.data('schemaEditor', schemaEditor);
    Alpine.data('blockSorter', blockSorter);
    Alpine.data('wizard', wizard);
    Alpine.data('structureWizard', structureWizard);

    // Window globals expected by PHP views and other components
    window.remoteTable = remoteTableFactory;
    window.formFieldBuilder = formFieldBuilderFactory;
    window.confirmDeleteMessage = buildConfirmDeleteMessage;
    window.bestFilePreviewUrl = bestFilePreviewUrl;
    window.resolveTranslatableFilePreviewUrl = resolveTranslatableFilePreviewUrl;
    window.formValuesToObject = formValuesToObject;
    window.copyLangTabsFileFieldToTargets = copyLangTabsFileFieldToTargets;
    window.copyLangTabsFileFieldToAll = copyLangTabsFileFieldToAll;
    window.copyLangTabsMediaReferenceFieldToAll = copyLangTabsMediaReferenceFieldToAll;
    window.blockInstanceConfigFactory = blockInstanceConfig;
});

// Must be on window before the Google GSI script fires
window.handleGoogleCredentialResponse = handleGoogleCredentialResponse;
window.richTextEditor = richTextEditor;

let lucideBootstrapped = false;

document.addEventListener('DOMContentLoaded', () => {
    if (!lucideBootstrapped) { bootLucideIcons(); lucideBootstrapped = true; }
    bootSlugFields();
    const config = window.__componentConfig || {};
    bootSessionExpiryWatcher({ expiringMessage: config.sessionExpiringMessage });
});

window.addEventListener('load', () => {
    if (!lucideBootstrapped) { bootLucideIcons(); lucideBootstrapped = true; }
});
