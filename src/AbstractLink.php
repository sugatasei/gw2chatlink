<?php

namespace ChatLink;

abstract class AbstractLink
{
    public string $type = "unknown";

    abstract public function encode(): Struct;
    abstract public static function decode(string|Struct $struct): static;
}
