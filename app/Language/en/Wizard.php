<?php

declare(strict_types=1);

return [
    // General
    'title'                  => 'Content assistant',
    'structure_title'        => 'Structure assistant',
    'sidebar_label'          => 'Content assistant',
    'structure_sidebar_label'=> 'Structure assistant',
    'loading'                => 'Loading...',
    'error_load'             => 'Could not load the assistant. Please try again.',
    'status'                 => 'Status',
    'btn_back'               => '← Back',
    'btn_retry'              => 'Retry',

    // Home
    'home_heading'           => 'What do you want to do today?',
    'add_content'            => 'Add content',
    'add_content_desc'       => 'Choose the type of content you want to add',
    'add_content_desc_empty' => 'No collections configured yet',
    'edit_page'              => 'Edit a page',
    'edit_page_desc'         => 'Modify the content of site pages',
    'edit_menu'              => 'Change the menu',
    'edit_menu_desc'         => 'Add, remove or reorder items',
    'structure_heading'      => 'What do you want to build today?',
    'structure_intro'        => 'Create the site foundation with guided flows and without touching the expert CRUDs.',
    'create_page'            => 'Create page',
    'create_page_desc'       => 'Home, About, Contact and nested pages',
    'create_collection'      => 'Create collection',
    'create_collection_desc' => 'Blog, portfolio, news, services and more',
    'create_menu'            => 'Create menu',
    'create_menu_desc'       => 'Main nav, footer or secondary menus',
    'create_redirect'        => 'Create redirect',
    'create_redirect_desc'   => '301/302 rules and URL changes',
    'go_structure_panel'     => 'Open structure module',
    'wizard_structure_hint'  => 'Use the assistant to create a minimum viable foundation, then continue in the full CRUD if you need advanced settings.',

    // Draft banner
    'draft_banner_title'     => '📝 You have a saved draft',
    'draft_continue'         => 'Continue',
    'draft_discard'          => 'Discard',

    // Add content — collection select
    'select_collection'      => 'What do you want to add?',
    'no_collections'         => 'No collections available.',

    // Add content — stepper
    'step_of'                => 'Step %s of %s',
    'btn_next'               => 'Next →',
    'btn_review'             => 'Review and publish',
    'upload_image'           => 'Click to upload an image',
    'upload_click_hint'      => 'or drag here',
    'upload_uploading'       => 'Uploading...',
    'required_suffix'        => ' *',

    // Confirm & publish
    'confirm_title'          => 'Review before publishing',
    'confirm_status_published' => 'Published',
    'confirm_status_draft'   => 'Draft',
    'confirm_no_value'       => '(no value)',
    'btn_publish'            => 'Publish now',
    'btn_draft'              => 'Save as draft',
    'btn_publishing'         => 'Publishing...',

    // Success
    'success_title'          => 'Published!',
    'success_subtitle'       => 'is now live on the site.',
    'btn_view_site'          => 'View on site',
    'btn_edit_entry'         => 'Edit in panel',
    'btn_add_more'           => 'Add more content',
    'btn_back_panel'         => 'Back to panel',

    // Edit page — page select
    'page_select_heading'    => 'Which page do you want to edit?',
    'no_pages'               => 'No pages available.',
    'page_fallback'          => 'Page',

    // Edit page — block list (tree view)
    'blocks_loading'         => 'Loading blocks...',
    'blocks_description_page' => 'Manage and order the blocks for this page.',
    'blocks_description_entry' => 'Manage and order the blocks for this entry.',
    'no_blocks_page'         => 'This page has no content blocks yet.',
    'no_blocks_entry'        => 'This entry has no content blocks yet.',
    'no_blocks'              => 'This content item has no editable blocks yet.',
    'btn_edit_block'         => 'Edit',
    'block_fallback'         => 'Block',
    'add_block'              => 'Add block',
    'add_child'              => 'Add element',

    // Edit page — block delete
    'delete_block_title'     => 'Delete block?',
    'delete_block_confirm'   => 'This action cannot be undone.',

    // Edit page — block catalog
    'catalog_heading'        => 'Choose block type',
    'catalog_adding_child_to' => 'Adding inside:',
    'catalog_no_types'       => 'No block types available for this context.',
    'catalog_container_badge' => 'Container',

    // Edit page — block edit
    'no_block_fields'        => 'This block has no editable fields.',
    'btn_save_block'         => 'Save changes',
    'btn_create_block'       => 'Create block',
    'btn_saving'             => 'Saving...',
    'block_edit_child_of'    => 'Inside:',
    'block_edit_new_badge'   => 'New',
    'bool_yes'               => 'Yes',
    'bool_no'                => 'No',

    // Edit page — block saved
    'block_saved_title'      => 'Saved!',
    'block_saved_subtitle'   => 'The block was updated successfully.',
    'btn_view_blocks'        => 'View other blocks',

    // Edit menu — menu select
    'menu_select_heading'    => 'Which menu do you want to edit?',
    'no_menus'               => 'No menus available.',
    'menu_fallback'          => 'Menu',

    // Edit menu — item list
    'items_loading'          => 'Loading items...',
    'menu_item_label_placeholder' => 'Label',
    'menu_item_url_placeholder'   => 'URL (e.g. /contact)',
    'add_item_heading'       => 'Add new item',
    'btn_add_item'           => '+ Add',
    'btn_save_order'         => 'Save order',

    // Edit menu — delete modal
    'delete_item_title'      => 'Delete item?',
    'delete_item_confirm'    => 'The item "%s" will be deleted.',
    'btn_cancel'             => 'Cancel',
    'btn_delete'             => 'Delete',

    // Default wizard steps (used when a collection has no wizard_config)
    'default_step1_title'    => 'What is it called?',
    'default_step1_hint'     => 'The name that will appear on the site.',
    'default_field_title'    => 'Title',
    'default_step2_title'    => 'Image',
    'default_step2_hint'     => 'An image to accompany the content.',
    'default_field_image'    => 'Main image',
    'default_step3_title'    => 'Description',
    'default_step3_hint'     => 'A brief summary.',
    'default_field_excerpt'  => 'Description',
    'collection_wizard_intro' => 'Create a minimal collection ready to be used in the content wizard.',
    'collection_wizard_minimum' => 'This flow saves a valid collection with base translations, URL prefix and coherent flags.',

    // Errors (JS-side — must be injected from PHP)
    'error_no_pages'         => 'No pages available.',
    'error_no_menus'         => 'No menus available.',
    'error_blocks_load'      => 'Could not load the blocks.',
    'error_items_load'       => 'Could not load the menu items.',
    'error_block_save'       => 'Could not save. Please try again.',
    'error_item_save'        => 'Could not save the item.',
    'error_item_delete'      => 'Could not delete the item.',
    'error_upload'           => 'Could not upload the image. Please try again.',
    'error_publish'          => 'An error occurred while publishing. Please try again.',
    'error_generic'          => 'Something went wrong. Please try again.',
    'content_fallback'       => 'Content item',
];
