<?php

namespace ChatLink;

use InvalidArgumentException;

class ObjectiveLink extends AbstractLink
{
    const TYPE = 0x0c;

    public string $type = "objective";

    public int $id = 0;
    public int $map = 0;

    public function encode(): Struct
    {
        if (! $this->id || !$this->map) {
            throw new InvalidArgumentException("incorrect_id");
        }

        $struct = new Struct;
        $struct->write1Byte(static::TYPE);

        $struct->write3Bytes($this->id);
        $struct->write1Byte(0x00);

        $struct->write3Bytes($this->map);
        $struct->write1Byte(0x00);

        return $struct;
    }

    public static function decode(string|Struct $struct): static
    {
        $struct = is_string($struct) ? new Struct($struct) : $struct->rewind();
        $typeId = $struct->read1Byte();

        if ($typeId !== static::TYPE) {
            throw new InvalidArgumentException("incorrect_type");
        }

        $link = new static;
        $link->id = $struct->read3Bytes();
        $struct->read1Byte();
        $link->map = $struct->read3Bytes();

        return $link;
    }
}
