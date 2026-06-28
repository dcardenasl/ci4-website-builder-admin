<?php

declare(strict_types=1);

return [
    'sidebar_label'  => 'Identidad del sitio',
    'page_title'     => 'Identidad del sitio',
    'section_intro'  => 'Configura el nombre, logo, tagline y favicon del sitio.',
    'core_section'   => 'Identidad principal',
    'base_section_intro' => 'Este valor es la fuente canónica que se reutiliza en todo el sistema.',
    'base_badge'     => 'Base',
    'assets_section' => 'Activos de marca',
    'field_site_name'    => 'Nombre del sitio',
    'field_site_tagline' => 'Tagline',
    'field_site_name_translations' => 'Traducciones del nombre del sitio',
    'field_site_tagline_translations' => 'Traducciones del tagline',
    'field_site_logo'    => 'Logo del sitio',
    'field_favicon'      => 'Favicon',
    'placeholder_site_name'    => 'Mi Sitio Web',
    'placeholder_site_tagline' => 'Tu lema o descripción breve',
    'update_success' => 'Identidad del sitio actualizada correctamente.',
    'update_failed'  => 'No se pudo actualizar la identidad del sitio.',
    'cache_note'     => 'Los cambios se reflejan en el sitio público después de ~1 hora (caché). Para ver los cambios de inmediato, ejecuta <code>php spark cache:clear</code> en ci4-website-builder-web.',
    'select_logo'    => 'Seleccionar logo',
    'change_logo'    => 'Cambiar logo',
    'select_favicon' => 'Seleccionar favicon',
    'change_favicon' => 'Cambiar favicon',
    'remove_logo'    => 'Quitar logo',
    'remove_favicon' => 'Quitar favicon',

    // Etiquetas genéricas para campos de archivo metadata-driven
    'select_file'  => 'Seleccionar archivo',
    'change_file'  => 'Cambiar archivo',
    'remove_file'  => 'Quitar',

    // Estado vacío cuando no hay settings de identidad
    'no_settings'  => 'No se han configurado settings de identidad. Crea settings con el grupo "identity" para poblar esta página.',
];
