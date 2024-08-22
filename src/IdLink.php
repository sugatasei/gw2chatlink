<?php

namespace ChatLink;

use InvalidArgumentException;

class IdLink extends AbstractLink
{
    const MAP    = 'map';
    const SKILL  = 'skill';
    const TRAIT  = 'trait';
    const RECIPE = 'recipe';
    const SKIN   = 'skin';
    const OUTFIT = 'outfit';

    const TYPES = [
        self::MAP    => 0x04,
        self::SKILL  => 0x06,
        self::TRAIT  => 0x07,
        self::RECIPE => 0x09,
        self::SKIN   => 0x0a,
        self::OUTFIT => 0x0b,
    ];

    public int $id = 0;

    public function encode(): Struct
    {
        if (!isset(static::TYPES[$this->type])) {
            throw new InvalidArgumentException("incorrect_type");
        }

        $struct = new Struct;
        $struct->write1Byte(static::TYPES[$this->type]);
        $struct->write3Bytes($this->id);
        $struct->write1Byte(0x00);

        return $struct;
    }

    public static function decode(string|Struct $struct): static
    {
        $struct = is_string($struct) ? new Struct($struct) : $struct->rewind();
        $type = static::getTypeString($struct->read1Byte());

        if (!isset(static::TYPES[$type])) {
            throw new InvalidArgumentException("incorrect_type");
        }

        $link = new static;
        $link->type = $type;
        $link->id = $struct->read3Bytes();

        return $link;
    }

    private static function getTypeString(int $typeId): ?string
    {
        $types = array_flip(static::TYPES);
        return $types[$typeId] ?? null;
    }
}
