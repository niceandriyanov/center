<?php
/**
 * TLS для запросов к API Точка Банка (CA Минцифры).
 */

if (!defined('ABSPATH')) {
    exit;
}

class TochkaSsl {

    /**
     * Регистрирует подмену CA-бандла для URL Точки.
     *
     * @return void
     */
    public static function init() {
        add_filter('http_request_args', array(__CLASS__, 'filter_http_request_args'), 10, 2);
    }

    /**
     * Для запросов к tochka.com подставляет CA с Russian Trusted CA.
     *
     * @param array  $args Аргументы WP_Http.
     * @param string $url  URL запроса.
     * @return array
     */
    public static function filter_http_request_args($args, $url) {
        if (!is_array($args) || !is_string($url) || false === stripos($url, 'tochka.com')) {
            return $args;
        }

        $ca_bundle = self::ca_bundle_path();
        if ('' === $ca_bundle) {
            return $args;
        }

        $args['sslverify'] = true;
        $args['sslcertificates'] = $ca_bundle;

        return $args;
    }

    /**
     * Путь к CA-бандлу, которому доверяет цепочка Точки.
     *
     * @return string
     */
    public static function ca_bundle_path() {
        static $cached = null;

        if (null !== $cached) {
            return $cached;
        }

        $candidates = array(
            '/etc/pki/tls/certs/ca-bundle.crt',
            '/etc/ssl/certs/ca-certificates.crt',
            TOCHKA_BANK_PLUGIN_PATH . 'certs/russian_trusted_ca_bundle.pem',
        );

        foreach ($candidates as $candidate) {
            if (is_readable($candidate)) {
                $cached = $candidate;
                return $cached;
            }
        }

        $cached = '';
        return $cached;
    }

    /**
     * Настраивает проверку TLS для raw-cURL к API Точки.
     *
     * @param resource|\CurlHandle $ch cURL handle.
     * @return void
     */
    public static function apply_curl($ch) {
        $ca_bundle = self::ca_bundle_path();

        if ('' === $ca_bundle) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            return;
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CAINFO, $ca_bundle);
    }
}
