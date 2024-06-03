<?php

namespace App\Traits\Tests;

trait CertificateTestTrait
{
    public function findItemInArray($response, $item)
    {
        $result = array_filter($response, function ($response) use ($item) {
            return $response['id'] == $item->id;
        });
        return array_reduce($result, 'array_merge', []);
    }
}
