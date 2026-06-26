<?php

declare(strict_types=1);

return [
    // General
    'title'                  => 'Asistente de contenido',
    'sidebar_label'          => 'Asistente',
    'loading'                => 'Cargando...',
    'error_load'             => 'No se pudo cargar el asistente. Intenta de nuevo.',
    'status'                 => 'Estado',
    'btn_back'               => '← Atrás',
    'btn_retry'              => 'Reintentar',

    // Home
    'home_heading'           => '¿Qué quieres hacer hoy?',
    'add_content'            => 'Agregar contenido',
    'add_content_desc'       => 'Elige qué tipo de contenido quieres agregar',
    'add_content_desc_empty' => 'No hay colecciones configuradas aún',
    'edit_page'              => 'Editar una página',
    'edit_page_desc'         => 'Modifica el contenido de las páginas del sitio',
    'edit_menu'              => 'Cambiar el menú',
    'edit_menu_desc'         => 'Agregar, quitar o reordenar ítems',

    // Draft banner
    'draft_banner_title'     => '📝 Tienes un borrador guardado',
    'draft_continue'         => 'Continuar',
    'draft_discard'          => 'Descartar',

    // Add content — collection select
    'select_collection'      => '¿Qué quieres agregar?',
    'no_collections'         => 'No hay colecciones disponibles.',

    // Add content — stepper
    'step_of'                => 'Paso %s de %s',
    'btn_next'               => 'Siguiente →',
    'btn_review'             => 'Revisar y publicar',
    'upload_image'           => 'Haz clic para subir una imagen',
    'upload_click_hint'      => 'o arrastra aquí',
    'upload_uploading'       => 'Subiendo...',
    'required_suffix'        => ' *',

    // Confirm & publish
    'confirm_title'          => 'Revisa antes de publicar',
    'confirm_status_published'=> 'Publicado',
    'confirm_status_draft'   => 'Borrador',
    'confirm_no_value'       => '(sin valor)',
    'btn_publish'            => 'Publicar ahora',
    'btn_draft'              => 'Guardar borrador',
    'btn_publishing'         => 'Publicando...',

    // Success
    'success_title'          => '¡Publicado!',
    'success_subtitle'       => 'ya aparece en el sitio.',
    'btn_view_site'          => 'Ver en el sitio',
    'btn_edit_entry'         => 'Editar en el panel',
    'btn_add_more'           => 'Agregar otro contenido',
    'btn_back_panel'         => 'Volver al panel',

    // Edit page — page select
    'page_select_heading'    => '¿Qué página quieres editar?',
    'no_pages'               => 'No hay páginas disponibles.',
    'page_fallback'          => 'Página',

    // Edit page — block list (árbol jerárquico)
    'blocks_loading'         => 'Cargando bloques...',
    'blocks_description_page'=> 'Administra y ordena los bloques de esta página.',
    'blocks_description_entry'=> 'Administra y ordena los bloques de esta entrada.',
    'no_blocks_page'         => 'Esta página aún no tiene bloques de contenido.',
    'no_blocks_entry'        => 'Esta entrada aún no tiene bloques de contenido.',
    'no_blocks'              => 'Este contenido no tiene bloques editables todavía.',
    'btn_edit_block'         => 'Editar',
    'block_fallback'         => 'Bloque',
    'add_block'              => 'Agregar bloque',
    'add_child'              => 'Agregar elemento',

    // Edit page — block delete
    'delete_block_title'     => '¿Eliminar bloque?',
    'delete_block_confirm'   => 'Esta acción no se puede deshacer.',

    // Edit page — catálogo de bloques
    'catalog_heading'        => 'Elige el tipo de bloque',
    'catalog_adding_child_to'=> 'Agregando dentro de:',
    'catalog_no_types'       => 'No hay tipos de bloque disponibles para este contexto.',
    'catalog_container_badge'=> 'Contenedor',

    // Edit page — block edit
    'no_block_fields'        => 'Este bloque no tiene campos editables.',
    'btn_save_block'         => 'Guardar cambios',
    'btn_create_block'       => 'Crear bloque',
    'btn_saving'             => 'Guardando...',
    'block_edit_child_of'    => 'Dentro de:',
    'block_edit_new_badge'   => 'Nuevo',
    'bool_yes'               => 'Sí',
    'bool_no'                => 'No',

    // Edit page — block saved
    'block_saved_title'      => '¡Guardado!',
    'block_saved_subtitle'   => 'El bloque se actualizó correctamente.',
    'btn_view_blocks'        => 'Ver otros bloques',

    // Edit menu — menu select
    'menu_select_heading'    => '¿Qué menú quieres editar?',
    'no_menus'               => 'No hay menús disponibles.',
    'menu_fallback'          => 'Menú',

    // Edit menu — item list
    'items_loading'          => 'Cargando ítems...',
    'menu_item_label_placeholder' => 'Etiqueta',
    'menu_item_url_placeholder'   => 'URL (ej: /contacto)',
    'add_item_heading'       => 'Agregar nuevo ítem',
    'btn_add_item'           => '+ Agregar',
    'btn_save_order'         => 'Guardar orden',

    // Edit menu — delete modal
    'delete_item_title'      => '¿Eliminar ítem?',
    'delete_item_confirm'    => 'Se eliminará el ítem «%s».',
    'btn_cancel'             => 'Cancelar',
    'btn_delete'             => 'Eliminar',

    // Default wizard steps (used when a collection has no wizard_config)
    'default_step1_title'    => '¿Cómo se llama?',
    'default_step1_hint'     => 'El nombre que aparecerá en el sitio.',
    'default_field_title'    => 'Título',
    'default_step2_title'    => 'Imagen',
    'default_step2_hint'     => 'Una imagen para acompañar el contenido.',
    'default_field_image'    => 'Imagen principal',
    'default_step3_title'    => 'Descripción',
    'default_step3_hint'     => 'Un resumen breve.',
    'default_field_excerpt'  => 'Descripción',

    // Errors (JS-side — must be injected from PHP)
    'error_no_pages'         => 'No hay páginas disponibles.',
    'error_no_menus'         => 'No hay menús disponibles.',
    'error_blocks_load'      => 'No se pudieron cargar los bloques.',
    'error_items_load'       => 'No se pudieron cargar los ítems del menú.',
    'error_block_save'       => 'No se pudo guardar. Intenta de nuevo.',
    'error_item_save'        => 'No se pudo guardar el ítem.',
    'error_item_delete'      => 'No se pudo eliminar el ítem.',
    'error_upload'           => 'No se pudo subir la imagen. Intenta de nuevo.',
    'error_publish'          => 'Hubo un error al publicar. Intenta de nuevo.',
    'error_generic'          => 'Algo salió mal. Intenta de nuevo.',
    'content_fallback'       => 'Contenido',
];
