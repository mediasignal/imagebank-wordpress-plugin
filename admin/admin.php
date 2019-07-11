<?php

function kuvapankki_media_admin_enqueue_scripts()
{
    wp_enqueue_script(
        'image-picker',
        kuvapankki_media_plugin_url('admin/js/image-picker.min.js')
    );

    wp_enqueue_script(
        'masonry',
        kuvapankki_media_plugin_url('admin/js/masonry.pkgd.min.js')
    );

    wp_enqueue_script(
        'kuvapankki-media-admin',
        kuvapankki_media_plugin_url('admin/js/script.js'),
        array('image-picker'),
        KUVAPANKKI_MEDIA_VERSION,
        true
    );

    wp_enqueue_style(
        'image-picker',
        kuvapankki_media_plugin_url('admin/css/image-picker.css')
    );

    wp_enqueue_style(
        'kuvapankki-style',
        kuvapankki_media_plugin_url('admin/css/style.css')
    );
}

add_action('admin_enqueue_scripts', 'kuvapankki_media_admin_enqueue_scripts');
