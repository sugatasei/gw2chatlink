<?php

namespace ChatLink;

use InvalidArgumentException;

class Struct
{
    private array $bytes = [];
    private int $offset  = 0;

    public function __construct(?string $code = null)
    {
        if ($code) {
            $this->parseCode($code);
        }
    }

    private function parseCode(string $code): void
    {
        $this->reset();

        if (preg_match('/\[&([a-z\d+\/]+=*)\]/i', $code) !== 1) {
            throw new InvalidArgumentException("format");
        }

        $base64String = substr($code, 2, -1);

        foreach (str_split(base64_decode($base64String)) as $char) {
            $this->bytes[] = ord($char);
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public static function encode(AbstractLink $obj): string
    {
        if ($obj instanceof BuildLink) {
            return $obj->encode()->__toString();
        }

        if ($obj instanceof ItemLink) {
            return $obj->encode()->__toString();
        }

        if ($obj instanceof ObjectiveLink) {
            return $obj->encode()->__toString();
        }

        if ($obj instanceof IdLink) {
            return $obj->encode()->__toString();
        }

        throw new InvalidArgumentException("incorrect_link");
    }

    public static function decode(string $code): AbstractLink
    {
        $struct = new static($code);
        $typeId = $struct->read1Byte();

        if ($typeId === BuildLink::TYPE) {
            return BuildLink::decode($struct);
        }

        if ($typeId === ItemLink::TYPE) {
            return ItemLink::decode($struct);
        }

        if ($typeId === ObjectiveLink::TYPE) {
            return ObjectiveLink::decode($struct);
        }

        if (in_array($typeId, IdLink::TYPES)) {
            return IdLink::decode($struct);
        }

        throw new InvalidArgumentException("incorrect_type");
    }

    // -------------------------------------------------------------------------
    // Iterator
    // -------------------------------------------------------------------------

    public function getBytes(): array
    {
        return $this->bytes;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function atEnd(): bool
    {
        return $this->offset >= count($this->bytes);
    }

    public function rewind(): static
    {
        $this->offset = 0;

        return $this;
    }

    public function reset(): static
    {
        $this->bytes = [];
        $this->offset = 0;

        return $this;
    }

    public function __toString(): string
    {
        $chars = "";
        foreach ($this->bytes as $byte) {
            $chars .= chr($byte);
        }

        return '[&' . base64_encode($chars) . ']';
    }

    // -------------------------------------------------------------------------
    // Write
    // -------------------------------------------------------------------------

    public function write1Byte($value): static
    {
        $this->bytes[] = $value;

        return $this;
    }

    public function write2Bytes($value): static
    {
        $this->bytes[] = ($value >> 0x00) & 0xff;
        $this->bytes[] = ($value >> 0x08) & 0xff;

        return $this;
    }

    public function write3Bytes($value): static
    {
        $this->bytes[] = ($value >> 0x00) & 0xff;
        $this->bytes[] = ($value >> 0x08) & 0xff;
        $this->bytes[] = ($value >> 0x10) & 0xff;

        return $this;
    }

    public function write4Bytes($value): static
    {
        $this->bytes[] = ($value >> 0x00) & 0xff;
        $this->bytes[] = ($value >> 0x08) & 0xff;
        $this->bytes[] = ($value >> 0x10) & 0xff;
        $this->bytes[] = ($value >> 0x18) & 0xff;

        return $this;
    }

    public function writeTraitSelection(int $trait1 = 0, int $trait2 = 0, int $trait3 = 0): static
    {
        $value = (($trait3 & 3) << 4) | (($trait2 & 3) << 2) | (($trait1 & 3) << 0);
        $this->write1Byte($value);

        return $this;
    }

    public function writeDynamicArray($values, int $bytesPerValue): static
    {
        $this->write1Byte(count($values));

        if ($bytesPerValue === 2) {
            foreach ($values as $value) {
                $this->write2Bytes($value);
            }
        } else {
            foreach ($values as $value) {
                $this->write4Bytes($value);
            }
        }

        return $this;
    }

    // -------------------------------------------------------------------------
    // Read
    // -------------------------------------------------------------------------

    public function read1Byte()
    {
        return $this->bytes[$this->offset++];
    }

    public function read2Bytes()
    {
        return $this->bytes[$this->offset++] | ($this->bytes[$this->offset++] << 8);
    }

    public function read3Bytes()
    {
        return (
            $this->bytes[$this->offset++] |
            ($this->bytes[$this->offset++] << 8) |
            ($this->bytes[$this->offset++] << 16)
        );
    }

    public function read4Bytes()
    {
        return (
            $this->bytes[$this->offset++] |
            ($this->bytes[$this->offset++] << 8) |
            ($this->bytes[$this->offset++] << 16) |
            ($this->bytes[$this->offset++] << 24)
        );
    }

    /**
     * @return int[]
     */
    public function readTraitSelection(): array
    {
        return [
            $this->bytes[$this->offset] & 3,
            ($this->bytes[$this->offset] >> 2) & 3,
            ($this->bytes[$this->offset++] >> 4) & 3,
        ];
    }

    /**
     * @param int $bytesPerValue (2 | 4)
     */
    public function readDynamicArray($bytesPerValue)
    {
        $length = $this->read1Byte();

        if ($length === 0) {
            return null;
        }

        $values = [];

        for ($i = 0; $i < $length; $i++) {
            $values[] = $bytesPerValue === 2 ? $this->read2Bytes() : $this->read4Bytes();
        }

        return $values;
    }

    // -------------------------------------------------------------------------
}
