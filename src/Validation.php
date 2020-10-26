<?php


namespace Livy\Plumbing\NormalizeLinks;


class Validation
{
    public static function url($string): bool
    {
        if (!is_string($string) || strlen($string) < 1) {
            return false; // Can't possibly be a valid URL
        }

        // This allows for URLs that don't have a protocol or host.
        // It's not meant to be a robust definition of "what is a url"--
        // just a definition that makes sense in the context of this package.
        return strpos($string,'/') === 0
               || filter_var($string, FILTER_VALIDATE_URL);
    }

    public static function title($string): bool
    {
        return is_string($string) && strlen($string) > 0;
    }

    public static function probablyExternal($url, string $homeUrl = null): bool
    {
        // In a WP environment, assume home_url()
        if (null === $homeUrl && function_exists('home_url')) {
            $homeUrl = home_url();
        }

        // If link starts with a slash, it's almost certainly local
        if (strpos($url, '/') === 0) {
            return false;
        }

        return parse_url($url, PHP_URL_HOST) !== parse_url($homeUrl, PHP_URL_HOST);
    }
}
