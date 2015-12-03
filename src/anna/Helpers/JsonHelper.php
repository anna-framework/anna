<?php

namespace Anna\Helpers;

class JsonHelper
{
    public static function encode($json, $group = false)
    {
        if ($group) {
            $jsonSafed[$group] = $json;
        } else {
            $jsonSafed = $json;
        }

        return json_encode($jsonSafed, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }
}
