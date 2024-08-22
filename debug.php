<?php

use App\IdLink;
use App\Link;

include __DIR__ . '/inc.php';

function ln($value)
{
    $value = match ($value) {
        null => 'null',
        true => 'true',
        false => 'false',
        default => $value
    };

    if (is_object($value)) {
        print_r($value);
    } elseif (is_array($value)) {
        echo join(', ', $value) . PHP_EOL;
    } else {
        echo $value . PHP_EOL;
    }
}

ln(Link::decode("[&BtIWAAA=]"));

$skill = new IdLink;
$skill->type = "skill";
$skill->id = 5842;

ln(Link::encode($skill));
