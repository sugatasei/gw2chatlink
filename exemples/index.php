<?php

use ChatLink\IdLink;
use ChatLink\ItemLink;
use ChatLink\Struct;

include __DIR__ . '/inc.php';

// Encode an skill link
$skill = new IdLink;
$skill->type = IdLink::SKILL;
$skill->id = 5842;
echo $skill->encode() . PHP_EOL;
// -> [&BtIWAAA=]

// Encode an Item link
$item = new ItemLink;
$item->id = 46762;
$item->quantity = 10;
$item->skin = 5807;
$item->upgrade1 = 24554;
$item->upgrade2 = 24615;
echo $item->encode() . PHP_EOL;
// -> [&AgqqtgDgrxYAAOpfAAAnYAAA]

// Decode a chat link without knowing the type
print_r(Struct::decode('[&BtIWAAA=]'));
// -> ChatLink\IdLink Object
// (
//     [type] => skill
//     [id] => 5842
// )

// Decode a chat link knowing the type
print_r(IdLink::decode('[&BtIWAAA=]'));
// -> ChatLink\IdLink Object
// (
//     [type] => skill
//     [id] => 5842
// )

// Error
try {
    IdLink::decode('[&AgqqtgDgrxYAAOpfAAAnYAAA]');
} catch (InvalidArgumentException $ex) {
    echo $ex->getMessage() . PHP_EOL;
}
// -> incorrect_type
