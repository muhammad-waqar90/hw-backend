<?php

namespace App\Traits;

trait UrlTrait
{
    // PHP_URL_SCHEME, PHP_URL_HOST, PHP_URL_PORT, PHP_URL_USER, PHP_URL_PASS, PHP_URL_PATH, PHP_URL_QUERY or PHP_URL_FRAGMENT
    public function getHost($url)
    {
        return parse_url($url, PHP_URL_HOST);
    }

    /**
     * domain suffix extraction - handling signle level subdomain only
     *
     * @return host-suffix
     */
    public function getHostWithOutSubDomain($url)
    {
        $host = $this->getHost($url);
        $hostArray = explode('.', str_replace('www.', '', $host));

        $count = count($hostArray);
        if ($count >= 3) {
            return implode('.', [$hostArray[$count - 2], $hostArray[$count - 1]]);
        }

        return $host;
    }

    public function getPath($url)
    {
        return parse_url($url, PHP_URL_PATH);
    }

    public function parseUrl($url)
    {
        return parse_url($url);
    }

    public function parseUrlPath($path)
    {
        return pathinfo($path);
    }

    public function getPathDirname($path)
    {
        $parsedPath = $this->parseUrlPath($path);

        return isset($parsedPath['dirname']) ? $parsedPath['dirname'] : null;
    }

    public function parseUrlFull($url)
    {
        // TODO: full url parse along with path and query parsed as well
    }

    public function getUrlRequiredSigned($key, $file = '/*')
    {
        $dirname = $this->getPathDirname($key);

        return config('aws.cloudfront.cname').$dirname.$file;
    }

    /**
     * @return PATHINFO_EXTENSION
     */
    public function getUrlExtension(string $url)
    {
        $path = $this->getPath($url);

        return pathinfo($path, PATHINFO_EXTENSION);
    }
}
