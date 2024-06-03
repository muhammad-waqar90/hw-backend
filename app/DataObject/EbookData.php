<?php

namespace App\DataObject;

class EbookData
{
    const PDF = 'pdf';

    const IMAGE = 'image';

    public static function getMeta()
    {
        $pdfMeta = [
            self::PDF => [
                'expiry_time' => 3,
                'expiry_time_unit' => 'S',
            ],
        ];

        $imageMeta = array_fill_keys(['svg', 'png', 'jpeg', 'jpg'], [
            'expiry_time' => 1,
            'expiry_time_unit' => 'H',
        ]);

        return array_merge($pdfMeta, $imageMeta);
    }
}
