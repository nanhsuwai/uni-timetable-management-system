<?php

return [
    'mode'                     => 'utf-8',
    'format'                   => 'A4',
    'default_font_size'        => '14',
    'default_font'             => 'pyidaungsu',
    'margin_left'              => 8,
    'margin_right'             => 8,
    'margin_top'               => 8,
    'margin_bottom'            => 8,
    'margin_header'            => 8,
    'margin_footer'            => 8,
    'orientation'              => 'P',
    'title'                    => 'Laravel mPDF',
    'subject'                  => '',
    'author'                   => '',
    'watermark'                => '',
    'show_watermark'           => false,
    'show_watermark_image'     => false,
    'watermark_font'           => 'pyidaungsu',
    'display_mode'             => 'fullpage',
    'watermark_text_alpha'     => 0.1,
    'watermark_image_path'     => '',
    'watermark_image_alpha'    => 0.2,
    'watermark_image_size'     => 'D',
    'watermark_image_position' => 'P',
    'custom_font_dir'          => base_path('public/fonts/'),
    'custom_font_data'         => [
        'pyidaungsu' => [
            'R'  => 'Pyidaungsu-2.5-Regular.ttf',
        ]
    ],
    'auto_language_detection'  => true,
    'temp_dir'                 => storage_path('app'),
    'pdfa'                     => false,
    'pdfaauto'                 => false,
    'use_active_forms'         => false,
];
