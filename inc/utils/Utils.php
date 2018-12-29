<?php
/**
 * @version      2.0 2009-04-14 23:50:00 $
 * @package      SkyBlueCanvas
 * @copyright    Copyright (C) 2005 - 2010 Scott Edwin Lewis. All rights reserved.
 * @license      GNU/GPL, see COPYING.txt
 * SkyBlueCanvas is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYING.txt for copyright notices and details.
 */

/**
 * @author Scott Lewis
 * @date   June 22, 2009
 */

namespace AtomicLotus\utils;

use Exception;

class Utils {

    static $console;
    // static $markers;

    public static function is_wp_cli() {
        return defined( 'WP_CLI' );
    }

    public static function get_console() {
        return is_array( self::$console ) ? self::$console : [] ;
    }

    public static function console( $message ) {
        if ( ! is_array( self::$console ) ) {
            self::$console = [];
        }
        self::$console[] = $message;
    }

    /**
     * @param $object
     * @param $classname
     *
     * @return bool
     */
    public static function is_a($object, $classname) {
        return self::class_name($object) === $classname;
    }

    /**
     * Gets the classname for an object.
     * @param $entity
     *
     * @return mixed
     */
    public static function class_name($entity) {
        if (! is_object($entity)) return '';
        $bits = explode('\\', get_class($entity));
        return  array_pop($bits);
    }

    /**
     * Pass the results of a callback through the object cache.
     * @param $callback
     * @param array $args
     * @return mixed|null
     */
    public static function get_thru_cache($callback, $args=[]) {

        static $results;

        if ( ! is_callable( $callback ) ) {
            if ( is_array( $callback ) ) {
                $func = self::class_name($callback[0]) . "::{$callback[1]}( )";
            }
            else {
                $func = $callback;
            }
            trigger_error("Callback `{$func}` is not callable", E_ERROR);
        }

        if (! is_array( $results ) ) $results = [];

        $result = null;

        try {
            if ( is_array( $callback ) )  {
                $cache_key = strtolower(self::class_name( $callback[0] ) . "_" . $callback[1]);
                if ( ! empty( $args ) )  {
                    $cache_key .= '_' . md5( serialize( $args ) ) ;
                }
            }
            else {
                $cache_key = strtolower($callback);
            }

            if (isset( $results[$cache_key] ) ) {
                $result = $results[$cache_key];
            }
            else {
                $result = get_transient( $cache_key );

                if ( empty( $result ) ) {
                    if ( ! empty( $args ) )  {
                        $result = call_user_func_array( $callback, $args );
                    }
                    else {
                        $result = call_user_func( $callback );
                    }
                    set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
                }
            }
        }
        catch( Exception $e ) {
            trigger_error( $e->getMessage(), E_ERROR);
        }

        return $result;
    }

    public static function safe_js_name( $name ) {
        $reserved = [
            'abstract', 'arguments', 'await', 'boolean', 'break', 'byte',
            'case', 'catch', 'char', 'class', 'const', 'continue', 'debugger',
            'default', 'delete', 'do', 'double', 'else', 'enum', 'eval',
            'export', 'extends', 'false', 'final', 'finally', 'float', 'for',
            'function', 'goto', 'if', 'implements', 'import', 'in', 'instanceof',
            'int', 'interface', 'let', 'long', 'native', 'new', 'null', 'package',
            'private', 'protected', 'public', 'return', 'short', 'static', 'super',
            'switch', 'synchronized', 'this', 'throw', 'throws', 'transient',
            'true', 'try', 'typeof', 'var', 'void', 'volatile', 'while', 'with',
            'yield', 'abstract', 'boolean', 'byte', 'char', 'double', 'final',
            'float', 'goto', 'int', 'long', 'native', 'short', 'synchronized',
            'throws', 'transient', 'volatile', 'Array', 'Date', 'eval', 'function',
            'hasOwnProperty', 'Infinity', 'isFinite', 'isNaN', 'isPrototypeOf',
            'length', 'Math', 'NaN', 'name', 'Number', 'Object', 'prototype',
            'String', 'toString', 'undefined', 'valueOf', 'alert', 'all', 'anchor',
            'anchors', 'area', 'assign', 'blur', 'button', 'checkbox', 'clearInterval',
            'clearTimeout', 'clientInformation', 'close', 'closed', 'confirm', 'constructor',
            'crypto', 'decodeURI', 'decodeURIComponent', 'defaultStatus', 'document',
            'element', 'elements', 'embed', 'embeds', 'encodeURI', 'encodeURIComponent',
            'escape', 'event', 'fileUpload', 'focus', 'form', 'forms', 'frame', 'innerHeight',
            'innerWidth', 'layer', 'layers', 'link', 'location', 'mimeTypes', 'navigate',
            'navigator', 'frames', 'frameRate', 'hidden', 'history', 'image', 'images',
            'offscreenBuffering', 'open', 'opener', 'option', 'outerHeight', 'outerWidth',
            'packages', 'pageXOffset', 'pageYOffset', 'parent', 'parseFloat', 'parseInt',
            'password', 'pkcs11', 'plugin', 'prompt', 'propertyIsEnum', 'radio', 'reset',
            'screenX', 'screenY', 'scroll', 'secure', 'select', 'self', 'setInterval',
            'setTimeout', 'status', 'submit', 'taint', 'text', 'textarea', 'top', 'unescape',
            'untaint', 'window', 'onblur', 'onclick', 'onerror', 'onfocus', 'onkeydown',
            'onkeypress', 'onkeyup', 'onmouseover', 'onload', 'onmouseup', 'onmousedown',
            'onsubmit'
        ];
        return (
            is_string($name)
            && ! in_array( $name, $reserved)
            && trim($name) != ''
        );
    }

    /**
     * The script is returned rather than printed so that it can be
     * attached to events in the code if necessary. Otherwise, just call
     * `echo Utils::set_js_cata( $var_name, $data, $key );`
     * @param        $var_name
     * @param        $data
     * @param string $key
     *
     * @return string
     */
    public static function js_data_block( $data_set, $data, $key='' ) {
        if (! self::safe_js_name( $data_set )) {
            trigger_error(
                "You have tried to use a reserved JavaScript 
                keyword - `{$data_set}` - as a variable name. 
            ", E_ERROR);
            die('Keyword error');
        }
        if (! empty($key) && ! Utils::safe_js_name($key)) {
            trigger_error(
                "You have tried to use a reserved JavaScript 
                keyword - `{$key}` - as a variable name. 
            ", E_ERROR);
            die('Keyword error');
        }
        if ( empty( $data ) ) {
            trigger_error("JavaScript data cannot be empty ", E_ERROR);
            die('Empty JS data error');
        }
        $data = empty($data) ? "{}" : $data ;
        $script_tag_old = "
            <script id='{$data_set}'>
                window['{$data_set}'] = window['{$data_set}'] || [];
                window['{$data_set}']['#{$key}'] = {$data};
            </script>
        ";

        $script_tag = sprintf("
            <script id='%s'>
                window['%s'] = window['%s'] || [];
                window['%s']['%s'] = %s;
            </script>
        ",
            $data_set, $data_set, $data_set,
            $data_set, $key, $data
        );
        return $script_tag;
    }

    /**
     * Create a unique cache identifier.
     * @param        $prefix
     * @param        $data
     * @param string $algorithm
     *
     * @return string
     */
    public static function cache_key( $prefix, $data, $algorithm='md5' ) {

        return "{$prefix}_" . Utils::fingerprint( maybe_serialize( $data ), $algorithm );
    }

    /**
     * Only enable logging on dev or QA.
     * @return bool
     */
    public static function logging_enabled() {
        return ! Utils::is_production();
    }

    /**
     * Only enable caching on production.
     * @return bool
     */
    public static function cache_enabled() {
        return !! Utils::is_production();
    }

    /**
     * @param        $cache_key
     * @param        $value
     * @param string $cache_group
     *
     * @return bool
     */
    public static function cache_set( $cache_key, $value, $cache_group='' ) {
        if ( ! self::cache_enabled() ) return false;
        if ( self::is_production() ) {
            return wp_cache_set( $cache_key, $value, $cache_group );
        }
        else {
            return set_transient( $cache_key, $value );
        }
    }

    /**
     * @param        $cache_key
     * @param string $cache_group
     *
     * @return bool|mixed
     */
    public static function cache_get( $cache_key, $cache_group='' ) {
        if ( ! self::cache_enabled() ) {
            return false;
        }
        if ( self::is_production() ) {
            return wp_cache_get( $cache_key, $cache_group );
        }
        else {
            return get_transient( $cache_key );
        }
    }

    /**
     * @return bool
     */
    public static function is_production() {
        return strcasecmp( Filter::get( $_SERVER, 'HTTP_HOST', false ), 'turnoveronline.com' ) === 0;
    }

    /**
     * Redirects the browser to a page specified by the $url argument.
     * @param string  The URL to which to redirect the browser.
     */
    public static function redirect($url) {
        if (headers_sent()) {
            echo "<script type='text/javascript'>document.location.href='{$url}';</script>";
        }
        else {
            header("Location: $url");
        }
        exit(0);
    }

    /**
     * Outputs a JSON Header
     */
    public static function httpHeaderJson() {
        if (! headers_sent()) {
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Content-type: application/json");
        }
    }

    /**
     * Outputs a JavaScript Header
     */
    public static function httpHeaderJavascript() {
        if (! headers_sent()) {
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Content-type: application/javascript");
        }
    }

    /**
     * Outputs an XML Header
     */
    public static function httpHeaderXml() {
        if (! headers_sent()) {
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("content-type: text/xml");
        }
    }

    /**
     * Outputs an XML Header
     */
    public static function httpHeaderCss() {
        if (! headers_sent()) {
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("content-type: text/css");
        }
    }

    /**
     * Adds a trailing slash to a path string when needed.
     * @param string   The path to which to add the trailing slash.
     * @return string  The path with trailing slash added
     */
    public static function addTrailingSlash($path) {
        if (!strlen($path)) return '/';
        return $path . ($path{strlen($path)-1} != '/' ? '/' : '') ;
    }

    /**
     * Alias of addTrailingSlash (or legacy support)
     * @param string   The path to which to add the trailing slash.
     * @return string  The path with trailing slash added
     */
    public static function checkTrailingSlash($path) {
        return self::addTrailingSlash($path);
    }

    /**
     * Checks if a value is empty
     * @param mixed  The value to check
     * @return bool  Whether or not the value is empty
     */
    public static function isEmpty($value) {
        if (is_array($value) && !count($value)) {
            return true;
        }
        return trim($value) == "";
    }


    /**
     * Conditionally prints a value if it is not empty
     * @param string  The value to print
     * @param string  The tag to pre-pend the value
     * @param string  The tag to post-pend the value
     * @return void
     */
    public static function echoValue($value, $before='', $after='') {
        if (self::isEmpty($value)) return;
        echo $before . $value . $after ;
    }

    /**
     * Sorts an array of objects by comparing a member property.
     * @param array   The array of objects to sort
     * @param string  The name of the property to sort on
     * @return void
     */
    public static function sort_on_field(&$objects, $sort_on, $order='ASC') {
        if ('DESC' == strtoupper($order)) {
            $comparator = function($a, $b) use ($sort_on) {
                return -strcasecmp($a->$sort_on, $b->$sort_on);
            };
        }
        else {
            $comparator = function($a, $b) use ($sort_on) {
                return strcasecmp($a->$sort_on, $b->$sort_on);
            };
        }
        usort($objects, $comparator);
    }

    /**
     * Maps stripslashes to every item in an array
     * @return mixed
     */
    public static function stripslashes_deep($value) {
        if (is_array($value)) {
            return array_map('stripslashes_deep', $value);
        }
        return stripslashes($value);
    }

    /**
     * Counts the number of objects in an array that have some property
     * value matching a test string.
     * @param array  The array of objects to test.
     * @param bool   The property to search on.
     * @param int    The value to search for.
     * @return int   The object count
     */
    public static function countMatching($objs, $key, $value) {
        $x=0;
        foreach ($objs as $obj) {
            if (Filter::get($obj, $key) == $value) {
                $x++;
            }
        }
        return $x;
    }

    /**
     * Select an object by the 'id' property from an array of objects.
     * @param array    The array of objects from which to select.
     * @param int      The id of the object to select.
     * @return object  The object matching $id
     */
    public static function selectObject($objs, $id) {
        if (count($objs)) {
            foreach ($objs as $obj) {
                if (Filter::get($obj, 'id') == $id) {
                    return $obj;
                }
            }
        }
        return false;
    }

    /**
     * Select an object by a specified property from an array of objects.
     * @param array    The array of objects from which to select.
     * @param string   The name of the property to search on.
     * @param string   The value to search for.
     * @return object  The object whose $key matches the $match value
     */
    public static function findObjByKey($objects, $key, $match) {
        foreach ($objects as $Object) {
            if (strcasecmp(Filter::get($Object, $key), $match) == 0) {
                return $Object;
            }
        }
        return false;
    }

    /**
     * Selects all objects from an array where a named property
     * matches a specified value.
     * @param array    The array of objects.
     * @param string   The name of the property to search on.
     * @param string   The value to search for.
     * @return array   An array of objects whose $key matches $match
     */
    public static function findAllByKey($objs, $key, $match) {
        $rows = array();
        foreach ($objs as $Object) {
            if (strcasecmp(Filter::get($Object, $key), $match) == 0) {
                array_push($rows, $Object);
            }
        }
        return $rows;
    }

    /**
     * @deprecated  Duplicate of self::findAllByKey()
     */
    public static function selectObjects($objs, $k, $v) {
        return self::findAllByKey($objs, $k, $v);
    }

    /**
     * purpose:   To select an item from within an array of objects or arrays
     *            where the desired object may be stored in a property/key of
     *            some other object/array.
     *
     * Note:      This public static function will work with an array of objects or
     *            An array of associative arrays. Each associative array
     *            is converted to an object on the fly and is converted back
     *            to an array so the return type matches the input type.
     *
     * Warning:   This public static function will NOT work on an array of scalar arrays.
     *
     * example:
     *
     * $item = SelectObjFromTree($myObjs, 'id', 2, 'children');
     *
     * Array
     * (
     *     [0] => stdClass Object
     *         (
     *             [id] => 1
     *             [title] => Parent Item 1
     *             [parent] =>
     *             [children] => Array
     *                 (
     *                     [0] => stdClass Object
     *                         (
     *                             [id] => 2
     *                             [title] => Child Item 1
     *                             [parent] => 1
     *                       )
     *
     *               )
     *
     *       )
     *
     *     [1] => stdClass Object
     *         (
     *             [id] => 3
     *             [title] => Parent Item 2
     *             [parent] =>
     *       )
     *
     *     [2] => stdClass Object
     *         (
     *             [id] => 4
     *             [title] => Parent Item 3
     *             [parent] =>
     *       )
     *
     *)
     *
     * The example above will return $objs[0]->children[0].
     *
     * @param array  - the array of objects.
     * @param string - the object property to search on.
     * @param string - the value for which to search.
     * @param string - the name of the property potentially holding the
     * nested objects.
     * @return object | boolean
     */
    public static function findItemFromTree($objs, $key, $match, $children) {
        $returnType = 'object';
        for ($i=0; $i<count($objs); $i++) {
            $parent = $objs[$i];

            if (is_array($parent)) {
                $returnType = 'array';
                $parent = (object) $parent;
            }
            if ($parent->$key == $match) {
                if ($returnType == 'array') {
                    $parent = (array) $parent;
                }
                return $parent;
            }
            else if (isset($parent->$children)) {
                foreach ($parent->$children as $child) {
                    if (is_array($child)) {
                        $returnType = 'array';
                        $child = (object) $child;
                    }
                    if (isset($child->$key) &&
                        !empty($child->$key) &&
                        $child->$key == $match) {

                        if ($returnType == 'array') {
                            $child = (array) $child;
                        }
                        return $child;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Inserts an object into an array where the object property
     * matches the search property. If no match is found, the object
     * is inserted at the end of the array.
     * @param array    The array of objects to search.
     * @param object   The object to be inserted.
     * @param string   The object property to match on.
     * @return array   The updated array of objects
     */
    public static function insertObject($objs, $obj, $match) {
        $marker = 0;
        for ($i=0; $i<count($objs); $i++) {
            if (Filter::get($objs[$i], $match) == Filter::get($obj, $match)) {
                $objs[$i] = $obj;
                $marker = 1;
            }
        }
        if ($marker === 0) {
            array_push($objs, $obj);
        }
        return $objs;
    }

    /**
     * Inserts an object into an array by matching a named property
     * with a specified value.
     *
     * @param object   The object to insert.
     * @param array    The array of objects.
     * @param string   The name of the property to search on.
     * @param string   The value to search for.
     * @return array   The array of objects
     */
    public static function insertObjByKey($obj, $objs, $key, $match) {
        for ($i=0; $i<count($objs); $i++) {
            if (Filter::get($objs[$i], $key) == $match) {
                $objs[$i] = $obj;
            }
        }
        return $objs;
    }

    /**
     * Deletes an object from an array.
     * @param array    The array of objects.
     * @param int      The id of the object to be deleted.
     * @return array   The updated array of objects
     */
    public static function deleteObject($objs, $id) {
        $newObjs = array();
        for ($i=0; $i<count($objs); $i++) {
            if (Filter::get($objs[$i], 'id') != $id) {
                array_push($newObjs, $objs[$i]);
            }
        }
        return $newObjs;
    }

    /**
     * Creates a unique hash string
     * @return a unique string
     */
    public static function getHash($algo='md5', $str='') {
        $str = empty($str) ? microtime(true) : $str ;
        return hash($algo, $str);
    }

    /**
     * Binds an associative array to an object
     * @param array    The array to bind to the object
     * @param object   The object to be bound to
     * @return object  The new object
     */
    public static function bindTransferObject($source, $object) {
        foreach ($source as $key=>$value) {
            $method = 'set' . ucwords($key);
            $methods = get_class_methods(get_class($object));
            if (in_array($method, $methods)) {
                call_user_func(array($object, $method), $source->$key);
            }
        }
        foreach ($object as $key=>$value) {
            if ($key{0} == '_') {
                unset($object->$key);
            }
        }
        return $object;
    }

    /**
     * Backward compatible object cloning
     * @param object  The object to clone
     * @return object  The cloned object
     */
    public static function cloneObject($object) {
        if (version_compare(PHP_VERSION, '5.0.0', '<')) {
            return $object;
        }
        return clone($object);
    }

    /**
     * Returns a string in a format safe for CSS class and/or ID selectors
     * @param string   The string to re-format
     * @return string  The selector-safe string
     */
    public static function cssSafe($string) {
        if (empty($string)) return "";
        $new = "";
        $safe = "abcdefghijklmnopqrstuvwxyz_-0123456789";
        $string = strtolower($string);
        $firstChar = $string{0};
        if (is_numeric($firstChar)) {
            $new .= "x";
        }
        $length = strlen($string);
        for ($i=0; $i<$length; $i++) {
            if (stripos($safe, $string{$i}) === false) {
                $new .= "-";
            }
            else {
                $new .= $string{$i};
            }
        }
        return $new;
    }

    /**
     * Creates a new object if the class exists
     * @param  string  The name of the class
     * @param  mixed   The constructor arguments
     * @return mixed   The new object or null if the class does not exist
     */
    public static function getObject($Class, $args=null) {
        if (class_exists($Class)) {
            return new $Class($args);
        }
        return null;
    }

    /**
     * Sanitizes a string to strip code tags
     * @param string   The string to sanitize
     * @param string    The character to replace illegal chars with
     * @return string  The sanitized string
     */
    public static function sanitize($string, $replace_with="-") {
        $safe_chars = explode("", "abcdefghijklmnopqrstuvwxyz_-0123456789.");
        $clean = '';
        for ($i=0; $i<strlen($string); $i++) {
            $char = $string{$i};
            if (in_array($char, $safe_chars)) {
                $clean += $char;
            }
            else {
                $clean .= $replace_with;
            }
        }
        return $clean;
    }

    /**
     * Remove all white space and convert string to lowercase for comparisons of strings
     * that may have minor differences but are effectively the same.
     * @param $str
     *
     * @return string
     */
    public static function str_flatten( $str ) {
        return strtolower(
            preg_replace(
                [ '/\n+/', '/\r+/', '/\s+/', '/\t+/' ]
                , "", $str
            )
        );

    }

    /**
     * Fingerprints a string
     * @param string   The string to fingerprint
     * @param string   The hash algorithm to use
     * @param boolean  Whether or not to salt the fingerprint
     * @return string  A fingerprinted string
     */
    public static function fingerprint($str, $algorithm='sha1', $salt=false) {

        if ($salt) $str .= SB_PASS_SALT;

        switch ($algorithm) {
            case 'sha1':
                $fingerprint = sha1($str);
                break;
            case 'crc32':
                $fingerprint = crc32($str);
                break;
            case 'md5':
            default:
                $fingerprint = md5($str);
                break;
        }
        return $fingerprint;
    }

    /**
     * Formats a file size as B, KB, MB, GB or TB
     * @param int $bytes      The file size in bytes
     * @param int $precision  The precision to which to round the size
     * @return string  The formatted file size string
     */
    public static function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Used in pagination routines and other routines that need to capture
     * a subset of a set of data.
     * @param int - the offset within the data set.
     * @param int - number of items in the data set.
     * @param int - the minimum number of items to include in the subset.
     * @return int
     */
    public static function getStartOfRange($offset, $itemsPerPage, $itemCount, $min=0) {
        $start = ($offset * $itemsPerPage) - $itemsPerPage;
        return self::getNumInRange(
            $start,
            $itemCount,
            $min
        );
    }

    /**
     * Verifies that the specified number is within the range of
     * the minimum and maximum range specified.
     * @param int - the number to check.
     * @param int - the maximum number in the set.
     * @param int - the minimum number in the set.
     * @return int
     */
    public static function getNumInRange($num, $max, $min=1) {
        if ($num > $min) {
            return $num < $max ? $num : $max ;
        }
        return $min;
    }
    /**
     * Paginates a set of items.
     * @param array $items         An array of the items to paginate
     * @param int   $itemsPerPage  The number of items per page
     * @param int   $pageNum       The current page number
     * @return array  The paginated (sub-set) items
     */
    public static function paginate($items, $itemsPerPage, $pageNum) {

        $itemCount = count($items);

        /*
         * By default we return the full set of items
         */

        $subset = $items;

        # self::debug( [$itemsPerPage, $pageNum, $itemCount] );

        /*
         * If there are more items in the set than the max number
         * of items per page, we want to get sub-set
         */

        if ($itemCount > $itemsPerPage) {
            $start = self::getStartOfRange($pageNum, $itemsPerPage, $itemCount, 0);
            $subset = array_slice($items, $start, $itemsPerPage);
        }

        return $subset;
    }

    /**
     * Returns 1 or 0 as Yes or No
     * @param int $int
     * @return string Yes or No
     */
    public static function intToYesNo($int) {
        return self::chooseBooleanOption($int, 'Yes', 'No');
    }

    /**
     * Switches between two strings depending on whether or not $bool is true
     * @param boolean  $bool          The boolean/integer value
     * @param string   $trueValue     The string to return if $bool is true
     * @param string   $falseValue    The string to return if $bool is false
     * @return string  The string value
     */
    public static function chooseBooleanOption($bool, $trueValue, $falseValue) {
        if (!is_numeric($bool)) return "";
        return intval($bool) >= 1 ? __($trueValue, $trueValue, 1) : __($falseValue, $falseValue, 1) ;
    }

    /**
     * Coerces a value to a boolean
     *
     *   Coercion rules:
     *       Literal string "true"  = true
     *       Literal string "false" = false
     *       Any other string       = false
     *       1 or greater integer   = true
     *       0 or less integer      = false
     *
     * @param mixed $value  A string or integer value
     * @return boolean  The boolean value of $value
     */
    public static function toBoolean($value) {
        if (strtolower($value) == "true") return true;
        if (strtolower($value) == "false") return false;
        if (strtolower($value) == "yes") return true;
        if (strtolower($value) == "no") return false;
        return intval($value) > 0 ? true : false ;
    }

    /**
     * Parses a URL-style query string into an array
     * @param string $str   The key=value pair query string
     * @return array  The keyed array of the query values
     */
    public static function parseQuery($str) {

        $str = html_entity_decode($str);

        $arr = array();

        $pairs = explode('&', $str);

        foreach ($pairs as $i) {
            list($name,$value) = explode('=', $i, 2);

            if (isset($arr[$name])) {
                if (is_array($arr[$name])) {
                    array_push($arr[$name], $value);
                }
                else {
                    $arr[$name] = array($arr[$name], $value);
                }
            }
            else {
                $arr[$name] = $value;
            }
        }
        return $arr;
    }

    /**
     * Builds a URL-style query string from a keyed array
     * @param array    The keyed array of the query values
     * @return string
     */
    public static function buildQuery($options) {
        if (! is_array($options) || count($options) == 0) return null;
        $tmp = array();
        foreach ($options as $key=>$value) {
            array_push($tmp, "{$key}={$value}");
        }
        return implode("&", $tmp);
    }

    /**
     * Removes whitespace from CSS/JS files.
     * @param String $buffer  The buffered CSS/JS output.
     * @return String         The minified CSS/JS output.
     */
    public static function minify($buffer) {
        $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
        $buffer = str_replace(array("\r\n", "\r", "\n", "\t", ' ', ' ', ' '), '', $buffer);
        return $buffer;
    }

    /**
     * Gets a date in the future
     * @param Array $add  An array of times to add: array(month,day,year).
     * @return String     Date formatted as m/d/Y T
     */
    public static function getFutureDate($add) {
        $m = count($add) >= 1 ? $add[0] : 0 ;
        $d = count($add) >= 2 ? $add[1] : 0 ;
        $y = count($add) >= 3 ? $add[2] : 0 ;
        date_default_timezone_set('Europe/London');
        $futureDate = gmdate("m/d/Y T", mktime(0,0,0,date("m")+$m,date("d")+$d,date("Y")+$y));
        return $futureDate;
    }

    /**
     * Determines is a public static function has been disabled in php.ini
     * @parma String $public static function  The name of the function
     * @return bool  Whether or not $public static function is disabled
     */
    public static function isDisabled($function) {
        $disabled = array_map('trim', explode(',', ini_get('disable_functions')));
        return in_array($function, $disabled);
    }

    /**
     * Detects if the current User Agent is a mobile device
     * @return boolean
     */
    public static function isMobile() {
        $uaRegex   = '/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone)/i';
        $accept    = 'application/vnd.wap.xhtml+xml';
        $userAgent = strtolower(Filter::get($_SERVER, 'HTTP_USER_AGENT'));

        $isMobile = false;

        if (preg_match($uaRegex, strtolower($userAgent))) {
            $isMobile = true;
        }

        if (strpos(strtolower($_SERVER['HTTP_ACCEPT']), $accept) !== false) {
            $isMobile = true;
        }

        if (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
            $isMobile = true;
        }

        $mobileUserAgent = substr($userAgent,0,4);
        $mobileAgents = array(
            'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
            'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
            'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
            'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
            'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
            'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
            'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
            'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
            'wapr','webc','winw','winw','xda','xda-'
        );

        if (in_array($mobileUserAgent, $mobileAgents)) {
            $isMobile = true;
        }

        if (strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false) {
            $isMobile = true;
        }

        if (strpos($userAgent,'windows') !== false) {
            $isMobile = true;
        }

        return $isMobile;
    }

    /**
     * This is a debug function and ideally should be removed from the production code.
     * @param array|object  $what   The object|array to be printed
     * @param bool          $die    Whether or not to die after printing the object
     * @return string
     */
    public static function dump($what, $die=true, $preformat=true) {

        $output = is_string( $what ) ? $what : print_r( $what, true ) ;
        if ( $preformat ) {
            $output = '<pre style="width: 100%; height: 200px;">' . $output . '</pre>';
        }
        if ( $die ) die( $output );
        return $output;
    }

    /**
     * This is an alias for self::dump()
     * @param array|object  $what   The object|array to be printed
     * @param bool          $die    Whether or not to die after printing the object
     * @return string
     */
    public static function debug($what, $die=true) {

        return self::dump( $what, $die );
    }

    /**
     * Display anything inline in a textarea.
     * @param $what
     */
    function inline( $what ) {

        $uuid = "utils-" . self::short_uuid();
        $what = self::dump( $what, false, false );
        echo "<style>#{$uuid} { width: 100%; height: 200px; } </style>\n";
        echo "<textarea id='{$uuid}'>{$what}</textarea>";
    }

    /**
     * Log debug message to log file.
     * @param array|object  $what   The object|array to be logged
     * @param string $log_name
     * @return void
     */
    public static function log( $what, $log_name='utils_log', $reset=true ) {
        static $logger;

        if ( ! logging_enabled() ) {
            echo '<p>Logging is disabled</p>';
            return false;
        }

        if (! class_exists('NF_Logger')) return;

        if (is_null($logger)) {
            $logger = new \NF_Logger();
        }

        $output = $what;
        if ( is_object($what) || is_array($what) ) {
            $output = self::dump( $what, false );
        }
        // if ( $reset ) $logger->clear( $log_name );
        $logger->add( $log_name, $output . "\n" );
    }

    /**
     * This calculation is not meant for public consumption. It simply
     * provides the UUID array for other UUID methods.
     * @return array
     */
    private static function uuid_calc() {
        $uuid = array(
            'time_low'  => 0,
            'time_mid'  => 0,
            'time_hi'  => 0,
            'clock_seq_hi' => 0,
            'clock_seq_low' => 0,
            'node'   => array()
        );

        $uuid['time_low'] = mt_rand(0, 0xffff) + (mt_rand(0, 0xffff) << 16);
        $uuid['time_mid'] = mt_rand(0, 0xffff);
        $uuid['time_hi'] = (4 << 12) | (mt_rand(0, 0x1000));
        $uuid['clock_seq_hi'] = (1 << 7) | (mt_rand(0, 128));
        $uuid['clock_seq_low'] = mt_rand(0, 255);

        for ($i = 0; $i < 6; $i++) {
            $uuid['node'][$i] = mt_rand(0, 255);
        }

        return $uuid;
    }

    /**
     * @return array|string
     */
    public static function uuid() {

        $uuid = self::uuid_calc();

        $uuid = sprintf('%08x-%04x-%04x-%02x%02x-%02x%02x%02x%02x%02x%02x',
            $uuid['time_low'],
            $uuid['time_mid'],
            $uuid['time_hi'],
            $uuid['clock_seq_hi'],
            $uuid['clock_seq_low'],
            $uuid['node'][0],
            $uuid['node'][1],
            $uuid['node'][2],
            $uuid['node'][3],
            $uuid['node'][4],
            $uuid['node'][5]
        );

        return $uuid;
    }

    /**
     * Get a shorter UUID.
     * WARNING! This has a greater chance of collision. This is not for precise
     * identification but just quick "probable" uniqueness.
     * @return mixed
     */
    public static function short_uuid() {

        $uuid = self::uuid_calc();
        return $uuid['time_low'];
    }

    /**
     * @return mixed
     */
    public static function microtime() {
        return microtime(true);
    }

    /**
     * Utility function to test code execution.
     * Example:
     *   self::timer('start');
     *   ... run some code
     *   self::timer('stop');
     *   self::timer('result', 'The script ran in ');
     * @param string $action
     * @param string $message
     */
    public static function timer($action='start', $message='Execution time') {

        static $counter = 0;
        static $start_time;
        static $end_time;
        static $elapsed;

        $counter++;

        if ($action == 'start') {
            $time = microtime();
            $time = explode(' ', $time);
            $time = $time[1] + $time[0];
            $start_time = $time;
            Utils::log( 'Start timer : ' . $start_time );
        }
        else if ($action == 'stop') {
            $time = microtime();
            $time = explode(' ', $time);
            $time = $time[1] + $time[0];
            $end_time = $time;
            $elapsed = round(($end_time - $start_time), 4);
            Utils::log( 'End timer : ' . $end_time );
        }
        else if ($action == 'result') {
            echo "<span class='timer'>[" . $counter/3 . "] {$message} : {$elapsed} seconds</span>";
            Utils::log( "[" . $counter/3 . "] {$message} : {$elapsed} seconds" );
        }
    }

    /**
     * Tests a mixed variable for true-ness.
     * @param int|null|bool|string $value
     * @param null|string|bool|int $default
     * @return bool|null
     */
    public static function is_true($value, $default=null) {
        $result = $default;
        $trues  = array(1, '1', 'true', true, 'yes', 'da', 'si', 'oui', 'absolutment', 'yep', 'yeppers', 'fuckyeah');
        $falses = array(0, '0', 'false', false, 'no', 'non', 'nein', 'nyet', 'nope', 'nowayjose');
        if (in_array(strtolower($value), $trues, true)) {
            $result = true;
        }
        else if (in_array(strtolower($value), $falses, true)) {
            $result = false;
        }
        return $result;
    }

    /**
     * Buffers the output from a file and returns the contents as a string.
     * You can pass named variables to the file using a keyed array.
     * For instance, if the file you are loading accepts a variable named
     * $foo, you can pass it to the file  with the following:
     *
     * @example
     *
     *      do_buffer('path/to/file.php', array('foo' => 'bar'));
     *
     * @param string $path
     * @param array $vars
     * @return string
     */
    public static function buffer( $path, $vars=null ) {
        $output = null;
        if (! empty($vars)) {
            extract($vars);
        }
        if (file_exists( $path )) {
            ob_start();
            include_once( $path );
            $output = ob_get_contents();
            ob_end_clean();
        }
        return $output;
    }

    /**
     * Buffer the output from a function call.
     * @param Function $func
     * @param array    $args
     *
     * @return null|string
     */
    public static function buffer_function( $func, $args=[] ) {
        $output = null;
        if ( function_exists( $func ) ) {
            ob_start();
            call_user_func( $func, $args );
            $output = ob_get_contents();
            ob_end_clean();
        }
        return $output;
    }

    /**
     * Merges two arrays. This differs from PHP's array_merge in that any non-empty values are skipped.
     * So if $key does not exist in $target or is empty, it is updated with the value from $source.
     * @param $target
     * @param $source
     */
    public static function array_merge_empty_values(&$target, &$source) {
        foreach ($source as $key => $value) {
            if (! empty($value)) {
                if (! isset($target[$key]) || empty($target[$key])) {
                    $target[$key] = $value;
                }
            }
        }
    }

    /**
     * De-queue a list of javascript handles in WordPress.
     * @param $the_handles
     */
    public static function omit_js($the_handles) {
        if (! is_array($the_handles)) $the_handles = [$the_handles];
        add_action( 'wp_print_scripts', function() use ($the_handles) {
            foreach ($the_handles as $handle) {
                wp_dequeue_script( $handle );
                echo "<!-- $handle -->\n";
            }
        }, 100, 1 );
    }

    /**
     * De-queue a list of javascript handles in WordPress.
     * @param $the_handles
     */
    public static function omit_css($the_handles) {
        if (! is_array($the_handles)) $the_handles = [$the_handles];
        add_action( 'wp_print_styles', function() use ($the_handles) {
            foreach ($the_handles as $handle) {
                wp_dequeue_style( $handle );
                echo "<!-- $handle -->\n";
            }
        }, 100, 1 );
    }
}