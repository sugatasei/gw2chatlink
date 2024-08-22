<?php

namespace ChatLink;

use InvalidArgumentException;

class ItemLink extends AbstractLink
{
    const TYPE = 0x02;

    const FLAG_SKIN     = 0x80;
    const FLAG_UPGRADE1 = 0x40;
    const FLAG_UPGRADE2 = 0x20;

    public string $type = "item";

    public int $id = 0;
    public int $quantity = 1;
    public ?int $skin = null;
    public ?int $upgrade1 = null;
    public ?int $upgrade2 = null;

    public function encode(): Struct
    {
        $struct = new Struct;
        $struct->write1Byte(static::TYPE);
        $struct->write1Byte($this->quantity);
        $struct->write3Bytes($this->id);
        $struct->write1Byte($this->getFlags());

        // If a skin or upgrade is absent the relevant field
        // is omitted from the code, not zeroed out.
        if ($this->skin) {
            $struct->write3Bytes($this->skin);
            $struct->write1Byte(0x00);
        }

        if ($this->upgrade1) {
            $struct->write3Bytes($this->upgrade1);
            $struct->write1Byte(0x00);
        }

        if ($this->upgrade2) {
            $struct->write3Bytes($this->upgrade2);
            $struct->write1Byte(0x00);
        }

        return $struct;
    }

    private function getFlags(): int
    {
        $flags = 0;

        if ($this->skin) {
            $flags = $flags | static::FLAG_SKIN;
        }

        if ($this->upgrade1) {
            $flags = $flags | static::FLAG_UPGRADE1;
        }

        if ($this->upgrade2) {
            $flags = $flags | static::FLAG_UPGRADE2;
        }

        return $flags;
    }

    public static function decode(string|Struct $struct): static
    {
        $struct = is_string($struct) ? new Struct($struct) : $struct->rewind();
        $typeId = $struct->read1Byte();

        if ($typeId !== static::TYPE) {
            throw new InvalidArgumentException("incorrect_type");
        }

        $link = new static;
        $link->quantity = $struct->read1Byte();
        $link->id = $struct->read3Bytes();

        $flags = $struct->read1Byte();

        // If a skin or upgrade is absent the relevant field
        // is omitted from the code, not zeroed out.
        if (static::hasFlag($flags, static::FLAG_SKIN)) {
            $link->skin = $struct->read3Bytes();
            $struct->read1Byte();
        }

        if (static::hasFlag($flags, static::FLAG_UPGRADE1)) {
            $link->upgrade1 = $struct->read3Bytes();
            $struct->read1Byte();
        }

        if (static::hasFlag($flags, static::FLAG_UPGRADE2)) {
            $link->upgrade2 = $struct->read3Bytes();
            $struct->read1Byte();
        }

        return $link;
    }

    private static function hasFlag(int $flags, int $flag): bool
    {
        return ($flags & $flag) === $flag;
    }
}
