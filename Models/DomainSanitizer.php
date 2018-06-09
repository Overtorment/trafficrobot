<?php

namespace Models;

class DomainSanitizer
{

    public static function sanitize($domain)
    {
        $domain = str_ireplace('http://', '', $domain);
        $domain = str_ireplace('https://', '', $domain);
        $domain = str_ireplace('www.', '', $domain);
        $domain = str_ireplace('www1.', '', $domain);
        $domain = explode(':', $domain);
        $domain = $domain[0];
        $domain = explode('/', $domain);
        if ($domain[0]) {
            $domain = $domain[0];
        } else {
            $domain = $domain[1];
        }

        return $domain;
    }
}
