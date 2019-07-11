<?php
function kuvapankki_ajax_languages()
{
    $kapi = KuvapankkiAPI::get_instance();
    return kuvapankki_success($kapi->languages());
}
add_action('wp_ajax_kuvapankki_languages', 'kuvapankki_ajax_languages');

function kuvapankki_ajax_categories()
{
    $kapi = KuvapankkiAPI::get_instance();
    return kuvapankki_success($kapi->categories());
}
add_action('wp_ajax_kuvapankki_categories', 'kuvapankki_ajax_categories');

function kuvapankki_ajax_fields()
{
    $kapi = KuvapankkiAPI::get_instance();
    return kuvapankki_success($kapi->fields());
}
add_action('wp_ajax_kuvapankki_fields', 'kuvapankki_ajax_fields');

function kuvapankki_ajax_search()
{
    // Execute search, get results
    $kapi = KuvapankkiAPI::get_instance();
    $params = $_POST['params'];
    $data = $kapi->search($params);

    if (!$data) {
        return kuvapankki_error(__('Haku epäonnistui'));
    }

    // Add thumbnail url to images
    $data['data'] = array_map(function($product) use ($kapi) {
        // Mapception, eww
        $product['files'] = array_map(function($file) use ($kapi) {
            $file['url'] = kuvapankki_image_url($file, false);
            $file['thumbnail_url'] = kuvapankki_image_url($file, true);

            return $file;
        }, $product['files']);

        return $product;
    }, $data['data']);
    
    // Return results
    return kuvapankki_success($data);
}
add_action('wp_ajax_kuvapankki_search', 'kuvapankki_ajax_search');

function kuvapankki_image_url($item, $thumbnail=false)
{
    $proxy_url = kuvapankki_media_plugin_url('includes/imageproxy.php');
    $params = [
        'id' => $item['id'],
        'thumbnail' => $thumbnail
    ];

    return $proxy_url . '?' . http_build_query($params);
}

function kuvapankki_username()
{
    return kuvapankki_get_option('kuvapankki_username');
}

function kuvapankki_password()
{
    return kuvapankki_get_option('kuvapankki_password');
}

function kuvapankki_url()
{
    return kuvapankki_get_option('kuvapankki_url');
}

function kuvapankki_success($message) 
{
    wp_send_json([
        'success'   => true,
        'message'   => $message
    ]);

    exit;
}

function kuvapankki_error($message) 
{
    wp_send_json([
        'success'   => false,
        'message'   => $message
    ]);
    
    exit;
}