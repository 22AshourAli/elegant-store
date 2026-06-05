<?php
/**
 * @see https://github.com/artesaos/seotools
 */

return [
    'inertia' => env('SEO_TOOLS_INERTIA', false),
    'meta' => [
        /*
         * The default configurations to be used by the meta generator.
         */
        'defaults'       => [
            'title'        => 'Elegant Store',
            'titleBefore'  => false,
            'description'  => 'Best online store for men fashion in Egypt.',
            'separator'    => ' - ',
            'keywords'     => [],
            'canonical'    => 'current',
            'robots'       => 'index, follow',
        ],
        'webmaster_tags' => [
            'google'    => null,
            'bing'      => null,
            'alexa'     => null,
            'pinterest' => null,
            'yandex'    => null,
            'norton'    => null,
        ],
        'add_notranslate_class' => false,
    ],
    'opengraph' => [
        'defaults' => [
            'title'       => 'Elegant Store',
            'description' => 'Best online store for men fashion in Egypt.',
            'url'         => null,
            'type'        => 'website',
            'site_name'   => 'Elegant Store',
            'images'      => [],
        ],
    ],
    'twitter' => [
        'defaults' => [
            'card'        => 'summary_large_image',
            'site'        => '@elegantstore',
        ],
    ],
    'json-ld' => [
        'defaults' => [
            'title'       => 'Elegant Store',
            'description' => 'Best online store for men fashion in Egypt.',
            'url'         => null,
            'type'        => 'WebPage',
            'images'      => [],
        ],
    ],
];
