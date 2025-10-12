<?php
/**
 * Production環境用の設定
 */

return array(
    'base_url'  => null,  // 自動検出
    'index_file' => '',
    'profiling'  => false,
    'caching'    => true,
    'errors'  => array(
        'continue_on' => array(),
        'throttle'    => 10,
        'notices'     => false,  // 本番環境ではnoticeを非表示
    ),
    'language'           => 'ja',
    'language_fallback'  => 'en',
    'locale'             => 'ja_JP.UTF-8',
    'encoding'  => 'UTF-8',
    'server_gmt_offset'  => 0,
    'default_timezone'   => 'Asia/Tokyo',
    'log_threshold'    => Fuel::L_WARNING,  // 本番環境ではwarning以上のみログ
    'log_path'         => APPPATH.'logs/',
    'log_date_format'  => 'Y-m-d H:i:s',
    'security' => array(
        'csrf_autoload'    => false,
        'csrf_token_key'   => 'fuel_csrf_token',
        'csrf_expiration'  => 0,
        'uri_filter'       => array('htmlentities'),
        'input_filter'  => array(),
        'output_filter'  => array('Security::htmlentities'),
        'htmlentities_flags' => ENT_QUOTES,
        'htmlentities_double_encode' => false,
        'auto_filter_output'  => true,
        'whitelisted_classes' => array(
            'Fuel\\Core\\Presenter',
            'Fuel\\Core\\Response',
            'Fuel\\Core\\View',
            'Fuel\\Core\\ViewModel',
            'Closure',
        ),
    ),
    'cookie' => array(
        'expiration'  => 0,
        'path'        => '/',
        'domain'      => null,
        'secure'      => false,  // HTTPSを使用する場合はtrueに
        'http_only'   => true,
    ),
    'module_paths' => array(
        APPPATH.'modules'.DS,
    ),
    'package_paths' => array(
        PKGPATH,
    ),
    'always_load'  => array(
        'packages'  => array(
            'orm',
        ),
        'config'  => array(),
        'language'  => array(),
    ),
);

