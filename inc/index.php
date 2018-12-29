<?php

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

$HERE = trailingslashit( dirname( __FILE__ ) ) ;

require_once $HERE . '/utils/Filter.php';
require_once $HERE . '/utils/Utils.php';
require_once $HERE . '/my-gallery/MyGallery.php';