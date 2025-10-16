<?php

namespace App\Traits;

use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

trait MPDFTrait
{
    /**
     * Create and configure a new MPDF instance.
     *
     * @param array $configs  Additional mPDF configuration options.
     * @return \Mpdf\Mpdf
     */
    protected function mpdf(array $configs = []): Mpdf
    {
        // Prevent regex backtracking limit issues (large PDFs with Unicode text)
        ini_set('pcre.backtrack_limit', '5000000');

        // Load default configuration and font settings
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $defaultFontConfig = (new FontVariables())->getDefaults();

        $fontDirs = $defaultConfig['fontDir'];
        $fontData = $defaultFontConfig['fontdata'];

        // Merge custom fonts
        $customFonts = [
            'pyidaungsu' => [
                'R' => 'Pyidaungsu-2.5.3_Regular.ttf',
                'B' => 'Pyidaungsu-2.5.3_Bold.ttf',
                'useOTL' => 0xFF, // Full OpenType layout for Myanmar script
            ],
            'estonia' => [
                'R' => 'Estonia-Regular.ttf',
            ],
        ];

        // Merge user-provided configs safely
        $finalConfig = array_merge([
            'tempDir' => storage_path('app/temp'),
            'fontDir' => array_merge($fontDirs, [public_path('fonts')]),
            'fontdata' => $fontData + $customFonts,
            'default_font' => 'pyidaungsu',  // ensures Myanmar text renders perfectly
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 5,
            'margin_footer' => 5,
        ], $configs);

        // Initialize mPDF
        $mpdf = new Mpdf($finalConfig);

        // Error handling & text rendering options
        $mpdf->showImageErrors = true;
        $mpdf->useSubstitutions = false; // avoids replacing fonts automatically
        $mpdf->autoScriptToLang = true;  // detect script for multilingual support
        $mpdf->autoLangToFont = true;    // select font per language automatically
        $mpdf->SetDisplayMode('fullpage'); // nice view on open

        return $mpdf;
    }
}
