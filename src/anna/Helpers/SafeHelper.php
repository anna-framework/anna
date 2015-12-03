<?php

namespace Anna\Helpers;

/**
 * -------------------------------------------------------------
 * SafeHelper
 * -------------------------------------------------------------.
 *
 * Helper para autilizar nas questões de segurança
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 11, Novembro 2015
 */
class SafeHelper
{
    public static $salt = 'tfju=fiM148We4oYuyojjzmA6b9UKGhQ';

    public static function encrypt($text)
    {
        return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, self::$salt, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
    }

    public static function decrypt($text)
    {
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, self::$salt, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
    }

    public static function hashEncode($value)
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    public static function secure(&$array)
    {
        if (isset($array)) {
            foreach ($array as $key => $value) {
                if (is_array($array[$key])) {
                    self::secure($array[$key]);
                } else {
                    $array[$key] = trim($array[$key]);
                    $array[$key] = htmlspecialchars($array[$key], ENT_QUOTES, 'UTF-8');
                    $array[$key] = strip_tags($array[$key]);
                    $array[$key] = addslashes($array[$key]);
                    $array[$key] = filter_var($array[$key], FILTER_SANITIZE_STRING);
                    $array[$key] = html_entity_decode($array[$key], ENT_COMPAT, 'UTF-8');
                            //$array[$key] = mysql_escape_string ($array[$key]);
                            //$array[$key] = utf8_encode ($array[$key]);
                }
            }
        }
    }
}
