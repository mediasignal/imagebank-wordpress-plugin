<?php

function kuvapankki_media_plugin_url( $path = '' ) 
{
	$url = plugins_url( $path, KUVAPANKKI_MEDIA_PLUGIN );

	if ( is_ssl() && 'http:' == substr( $url, 0, 5 ) ) {
		$url = 'https:' . substr( $url, 5 );
	}

	return $url;
}

function kuvapankki_get_option($key)
{
	return get_option(WP_ExternalMedia_Prefix . $key);
}