<?php

namespace App\Core\Model;

class FileDifference
{
    public static function calculate(string $data1, string $data2): string
    {
        $length = min(strlen($data1), strlen($data2));
        $difference = '';

        for ($i = 0; $i < $length; $i++) {
            $difference .= $data1[$i] ^ $data2[$i]; 
        }

        return $difference;
    }
}
