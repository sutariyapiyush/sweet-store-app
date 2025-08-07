<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default QR Code Generator
    |--------------------------------------------------------------------------
    |
    | This option controls the default QR code generator that will be used
    | when generating QR codes. You may set this to any of the generators
    | defined in the "generators" array below.
    |
    */

    'default' => env('QR_CODE_GENERATOR', 'svg'),

    /*
    |--------------------------------------------------------------------------
    | QR Code Generators
    |--------------------------------------------------------------------------
    |
    | Here you may configure the QR code generators for your application.
    | Currently, the package supports "svg", "png", "eps", and "pdf" formats.
    |
    */

    'generators' => [
        'svg' => [
            'writer' => 'svg',
            'writer_options' => [],
        ],

        'png' => [
            'writer' => 'png',
            'writer_options' => [],
        ],

        'eps' => [
            'writer' => 'eps',
            'writer_options' => [],
        ],

        'pdf' => [
            'writer' => 'pdf',
            'writer_options' => [],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Size
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default size of your QR code.
    |
    */

    'size' => 300,

    /*
    |--------------------------------------------------------------------------
    | Margin
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default margin of your QR code.
    |
    */

    'margin' => 0,

    /*
    |--------------------------------------------------------------------------
    | Error Correction Level
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default error correction level of your QR code.
    | Available options are: 'L', 'M', 'Q', 'H'
    |
    */

    'error_correction' => 'M',

    /*
    |--------------------------------------------------------------------------
    | Encoding
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default encoding of your QR code.
    |
    */

    'encoding' => 'UTF-8',
];
