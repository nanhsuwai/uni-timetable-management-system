<?php

return [

    /*
    |--------------------------------------------------------------------------
    | mPDF Core Settings
    |--------------------------------------------------------------------------
    | These settings define your default PDF behavior for mPDF integration.
    | You can override them at runtime by passing custom config arrays.
    */

    'mode'              => 'utf-8',
    'format'            => 'A4',
    'orientation'       => 'P', // Portrait mode
    'display_mode'      => 'fullpage', // Fit full page on open

    /*
    |--------------------------------------------------------------------------
    | Margins (in millimeters)
    |--------------------------------------------------------------------------
    */
    'margin_left'       => 10,
    'margin_right'      => 10,
    'margin_top'        => 10,
    'margin_bottom'     => 10,
    'margin_header'     => 6,
    'margin_footer'     => 6,

    /*
    |--------------------------------------------------------------------------
    | Font Settings
    |--------------------------------------------------------------------------
    | Use Pyidaungsu for Burmese/Myanmar text rendering. Add other fonts below.
    */
    'default_font_size' => 14,
    'default_font'      => 'pyidaungsu',
    'custom_font_dir'   => base_path('public/fonts/'),
    'custom_font_data'  => [
        'pyidaungsu' => [
            'R' => 'Pyidaungsu-2.5.3_Regular.ttf',
            'B' => 'Pyidaungsu-2.5.3_Bold.ttf',
            'useOTL' => 0xFF, // Enables advanced OpenType layout for Myanmar text
        ],
        'estonia' => [
            'R' => 'Estonia-Regular.ttf',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Watermark Settings
    |--------------------------------------------------------------------------
    | You can show text or an image watermark on each page.
    */
    'watermark'                => 'UCSH',
    'show_watermark'           => true,
    'show_watermark_image'     => false,
    'watermark_font'           => 'pyidaungsu',
    'watermark_text_alpha'     => 0.08, // Slightly lighter watermark text
    'watermark_image_path'     => '',
    'watermark_image_alpha'    => 0.15,
    'watermark_image_size'     => 'D', // 'D' for default
    'watermark_image_position' => 'P', // 'P' for position

    /*
    |--------------------------------------------------------------------------
    | Document Metadata
    |--------------------------------------------------------------------------
    */
    'title'     => 'University Timetable Management System',
    'subject'   => 'Class Schedule PDF',
    'author'    => 'Nan Hsu Wai',

    /*
    |--------------------------------------------------------------------------
    | System Directories
    |--------------------------------------------------------------------------
    */
    'temp_dir'  => storage_path('app/temp'),

    /*
    |--------------------------------------------------------------------------
    | Additional Options
    |--------------------------------------------------------------------------
    */
    'auto_language_detection' => true,
    'pdfa'                    => false,
    'pdfaauto'                => false,
    'use_active_forms'        => false,
];
