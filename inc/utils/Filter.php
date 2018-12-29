<?php

/**
 * @version      2.0 2009-06-16 23:50:00 $
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
 * @date   December 12, 2008
 */

/**
 * The Filter class uses the InputFilter class by Daniel Morris, per the terms
 * of the GNU/GPL under which it is licensed. See includes/InputFilter.php for
 * copyright and additional details.
 */

namespace AtomicLotus\utils;

use \AtomicLotus\Utils\Utils;


class Filter {

    /**
     * Get a value from an object or array
     * @param mixed    The object or array from which to get the value
     * @param string   The key or property for which to get the value
     * @param mixed    The default value to return if the key/property is not found
     * @param boolean  Whether or not to scrub the value
     * @return mixed   The value of the key/property
     */
    public static function get($subject, $key, $default=null, $scrub=false) {
        if (is_array($subject) && isset($subject[$key])) {
            return $scrub ? Filter::scrub($subject[$key]) : $subject[$key];
        }
        else if (is_object($subject) && isset($subject->$key)) {
            return $subject->$key;
        }
        return $default;
    }

    /**
     * Gets a raw (un-sanitized) value from an object or array
     * @param mixed    The object or array from which to get the value
     * @param string   The key or property for which to get the value
     * @param mixed    The default value to return if the key/property is not found
     * @param boolean  Whether or not to scrub the value
     * @return mixed   The value of the key/property
     */
    public static function getRaw($subject, $key, $default=null) {
        if (is_array($subject) && isset($subject[$key])) {
            return $subject[$key];
        }
        else if (is_object($subject) && isset($subject->$key)) {
            return $subject->$key;
        }
        return $default;
    }

    /**
     * Returns the value only if it is alpha-numeric. Otherwise returns default.
     * found or if it is empty.
     * @param mixed    The object or array from which to get the value
     * @param string   The key or property for which to get the value
     * @param mixed    The default value to return if the key/property is not found
     * @param boolean  Whether or not to scrub the value
     * @return mixed   The value of the key/property
     */
    public static function getAlphaNumeric($subject, $key, $default=null, $scrub=true) {
        $value = Filter::get($subject, $key, $default, $scrub);
        return ctype_alnum($value) ? $value : $default ;
    }

    /**
     * Returns the value only if it is numeric. Otherwise returns default.
     * found or if it is empty.
     * @param mixed    The object or array from which to get the value
     * @param string   The key or property for which to get the value
     * @param mixed    The default value to return if the key/property is not found
     * @param boolean  Whether or not to scrub the value
     * @return mixed   The value of the key/property
     */
    public static function getNumeric($subject, $key, $default=null, $scrub=true) {
        $value = Filter::get($subject, $key, $default, $scrub);
        return is_numeric($value) ? $value : $default ;
    }

    /**
     * Returns the value as a boolean only. See Utils::toBoolean for conversion rules.
     * found or if it is empty.
     * @param mixed    The object or array from which to get the value
     * @param string   The key or property for which to get the value
     * @param mixed    The default value to return if the key/property is not found
     * @param boolean  Whether or not to scrub the value
     * @return mixed   The value of the key/property
     */
    public static function getBoolean($subject, $key, $default=false) {
        return Utils::toBoolean(Filter::get($subject, $key, $default, true));
    }

    /**
     * Alias for Utils::toBoolean()
     * @param $value
     * @param $default
     *
     * @return bool
     */
    public static function toBoolean($value, $default=false) {
        if (empty($value)) return $default;
        return Utils::toBoolean($value);
    }

    /**
     * Works the same ast Filter::get except that $default is returned if the key is not
     * found or if it is empty.
     * @param mixed    The object or array from which to get the value
     * @param string   The key or property for which to get the value
     * @param mixed    The default value to return if the key/property is not found
     * @param boolean  Whether or not to scrub the value
     * @return mixed   The value of the key/property
     */
    public static function getNonEmpty($subject, $key, $default=null, $scrub=true) {
        $result = $default;
        $value = Filter::get($subject, $key, $default, $scrub);
        if (is_array($value) && ! empty($value)) {
            $result = $value;
        }
        else if (is_numeric($value)) {
            if ($value !== 0) {
                $result = $value;
            }
        }
        else if (is_string($value) && trim($value) != "") {
            $result = $value;
        }
        else if (is_object($value)) {
            $result = $value;
        }
        return $result;
    }

    /**
     * Returns the first non-empty value from a set of items.
     * @param array   $items    An array of values to search.
     * @param null    $default  The default value to return if no result is found
     * @return mixed
     */
    public static function getFirstNonEmpty($items, $default=null) {
        if (! is_array($items)) return $default;
        $value = $default;
        foreach ($items as $item) {
            if (! is_array($item) && ! is_string($item)) continue;
            if (is_array($item) && empty($item)) continue;
            if (is_string($item) && trim($item) == "") continue;
            $value = $item; break;
        }
        return $value;
    }

    /**
     *  Choose the non-zero value from two numbers.
     * @param $subject
     * @param int $default
     * @param bool $allowNegative
     *
     * @return int|string
     */
    public static function getNonZero( $subject, $default=1, $allowNegative=false ) {
        $value = $default;
        if (is_numeric($subject)) {
            $subject = intval($subject);
            if ($allowNegative && $subject !== 0) {
                $value = $subject;
            }
            else if (! $allowNegative && $subject > 0) {
                $value = $subject;
            }
        }
        return $value;
    }

    public static function getOneColumn( $collection, $column ) {
        return array_map(
            // create_function('$o', 'return $o->' . $column ),
            function($o) use ($column) { return $o->$column ; },
            $collection
        );
    }

    /**
     * Get a value from an object or array - look for injection
     * @param mixed    The object or array from which to get the value
     * @param string   The key or property for which to get the value
     * @param mixed    The default value to return if the key/property is not found
     * @param boolean  Whether or not to scrub the value
     * @return mixed   The value of the key/property
     */
    public static function noInjection($subject, $key, $default=null, $scrub=true) {
        if (is_array($subject)) {
            $value = Filter::get($subject, $key, $default, $scrub);
        }
        else {
            $value = $subject;
        }
        if (! is_string($value)) return $value;
        if (strpos($value, '<') !== false || strpos($value, '>') !== false) {
            return $default;
        }
        return $value;
    }

    /**
     * Scrub a string for malicious or harmful code.
     * @param string  $value            The string to sanitize.
     * @param bool    $keep_newlines    Whether or not to allow newline chars.
     * @param array   $filters          The PHP Filters to apply.
     *
     * @return bool|mixed|null|string|string[]
     */
    public static function scrub( $value, $keep_newlines = false, $filters=[] ) {

        $filtered = self::wp_check_invalid_utf8( $value );

        if ( strpos($filtered, '<') !== false ) {
            $filtered = self::wp_pre_kses_less_than( $filtered );
            // This will strip extra whitespace for us.
            $filtered = self::wp_strip_all_tags( $filtered, false, $filters );

            // Use html entities in a special case to make sure no later
            // newline stripping stage could lead to a functional tag
            $filtered = str_replace("<\n", "&lt;\n", $filtered);
        }

        if ( ! $keep_newlines ) {
            $filtered = preg_replace( '/[\r\n\t ]+/', ' ', $filtered );
        }
        $filtered = trim( $filtered );

        $found = false;
        while ( preg_match('/%[a-f0-9]{2}/i', $filtered, $match) ) {
            $filtered = str_replace($match[0], '', $filtered);
            $found = true;
        }

        if ( $found ) {
            // Strip out the whitespace that may now exist after removing the octets.
            $filtered = trim( preg_replace('/ +/', ' ', $filtered) );
        }

        return $filtered;
    }

    public static function wp_strip_all_tags($string, $remove_breaks = false) {
        $string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );

        $string = strip_tags($string);

        if ( $remove_breaks )
            $string = preg_replace('/[\r\n\t ]+/', ' ', $string);

        return trim( $string );
    }

    /**
     * @param $text
     *
     * @return null|string|string[]
     */
    public static function wp_pre_kses_less_than( $text ) {
        return preg_replace_callback('%<[^>]*?((?=<)|>|$)%', array('\Drupal\aero_site_search\Filter', 'wp_pre_kses_less_than_callback'), $text);
    }

    /**
     * @param $matches
     *
     * @return mixed
     */
    public static function wp_pre_kses_less_than_callback( $matches ) {
        if ( false === strpos($matches[0], '>') )
            return htmlspecialchars($matches[0]);
        return $matches[0];
    }

    /**
     * @param $string
     * @param bool $strip
     *
     * @return bool|string
     */
    public static function wp_check_invalid_utf8( $string, $strip = false ) {

        if (! is_string($string)) return '';

        $string = (string) $string;

        if ( 0 === strlen( $string ) ) {
            return '';
        }

        // Check for support for utf8 in the installed PCRE library once and store the result in a static
        static $utf8_pcre = null;
        if ( ! isset( $utf8_pcre ) ) {
            $utf8_pcre = @preg_match( '/^./u', 'a' );
        }
        // We can't demand utf8 in the PCRE installation, so just return the string in those cases
        if ( !$utf8_pcre ) {
            return $string;
        }

        // preg_match fails when it encounters invalid UTF8 in $string
        if ( 1 === @preg_match( '/^./us', $string ) ) {
            return $string;
        }

        // Attempt to strip the bad chars if requested (not recommended)
        if ( $strip && function_exists( 'iconv' ) ) {
            return iconv( 'utf-8', 'utf-8', $string );
        }

        return '';
    }
}