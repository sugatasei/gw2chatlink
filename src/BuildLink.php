<?php

namespace ChatLink;

use InvalidArgumentException;

class BuildLink extends AbstractLink
{
    const TYPE = 0x0d;

    const FLAG_RANGER = 0x04;
    const FLAG_REVENANT = 0x09;

    public string $type = "build";

    public int $profession = 0;

    public int $specialization1 = 0;
    public array $traitChoices1 = [];
    public int $specialization2 = 0;
    public array $traitChoices2 = [];
    public int $specialization3 = 0;
    public array $traitChoices3 = [];

    public $terrestrialHealSkill = 0;
    public $aquaticHealSkill = 0;
    public $terrestrialUtilitySkill1 = 0;
    public $aquaticUtilitySkill1 = 0;
    public $terrestrialUtilitySkill2 = 0;
    public $aquaticUtilitySkill2 = 0;
    public $terrestrialUtilitySkill3 = 0;
    public $aquaticUtilitySkill3 = 0;
    public $terrestrialEliteSkill = 0;
    public $aquaticEliteSkill = 0;

    // Ranger
    public ?int $terrestrialPet1 = null;
    public ?int $terrestrialPet2 = null;
    public ?int $aquaticPet1 = null;
    public ?int $aquaticPet2 = null;

    // Revenant
    public ?int $terrestrialLegend1 = null;
    public ?int $terrestrialLegend2 = null;
    public ?int $aquaticLegend1 = null;
    public ?int $aquaticLegend2 = null;

    // Added in SOTO
    public array $selectedWeapons = [];
    public array $selectedSkillVariants = [];

    public function encode(): Struct
    {
        $struct = new Struct;
        $struct->write1Byte(static::TYPE);

        $struct->write1Byte($this->profession);

        $struct->write1Byte($this->specialization1);
        $struct->writeTraitSelection(...$this->traitChoices1);
        $struct->write1Byte($this->specialization2);
        $struct->writeTraitSelection(...$this->traitChoices2);
        $struct->write1Byte($this->specialization3);
        $struct->writeTraitSelection(...$this->traitChoices3);

        $struct->write2Bytes($this->terrestrialHealSkill);
        $struct->write2Bytes($this->aquaticHealSkill);
        $struct->write2Bytes($this->terrestrialUtilitySkill1);
        $struct->write2Bytes($this->aquaticUtilitySkill1);
        $struct->write2Bytes($this->terrestrialUtilitySkill2);
        $struct->write2Bytes($this->aquaticUtilitySkill2);
        $struct->write2Bytes($this->terrestrialUtilitySkill3);
        $struct->write2Bytes($this->aquaticUtilitySkill3);
        $struct->write2Bytes($this->terrestrialEliteSkill);
        $struct->write2Bytes($this->aquaticEliteSkill);

        if ($this->profession === static::FLAG_RANGER) {
            $struct->write1Byte($this->terrestrialPet1 ?? 0);
            $struct->write1Byte($this->terrestrialPet2 ?? 0);
            $struct->write1Byte($this->aquaticPet1 ?? 0);
            $struct->write1Byte($this->aquaticPet2 ?? 0);
        } elseif ($this->profession === static::FLAG_REVENANT) {
            $struct->write1Byte($this->terrestrialLegend1 ?? 0);
            $struct->write1Byte($this->terrestrialLegend2 ?? 0);
            $struct->write1Byte($this->aquaticLegend1 ?? 0);
            $struct->write1Byte($this->aquaticLegend2 ?? 0);
        } else {
            $struct->write2Bytes(0x00);
            $struct->write2Bytes(0x00);
        }

        // Profession specifics :
        // Revenant : inactive legend utility skills
        $struct->write2Bytes(0x00);
        $struct->write2Bytes(0x00);
        $struct->write2Bytes(0x00);
        $struct->write2Bytes(0x00);
        $struct->write2Bytes(0x00);
        $struct->write2Bytes(0x00);

        // SOTO
        $struct->writeDynamicArray($this->selectedWeapons ?? [], 2);
        $struct->writeDynamicArray($this->selectedSkillVariants ?? [], 4);

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
        $link->profession = $struct->read1Byte();

        $link->specialization1 = $struct->read1Byte();
        $link->traitChoices1 = $struct->readTraitSelection();
        $link->specialization2 = $struct->read1Byte();
        $link->traitChoices2 = $struct->readTraitSelection();
        $link->specialization3 = $struct->read1Byte();
        $link->traitChoices3 = $struct->readTraitSelection();

        $link->terrestrialHealSkill = $struct->read2Bytes();
        $link->aquaticHealSkill = $struct->read2Bytes();
        $link->terrestrialUtilitySkill1 = $struct->read2Bytes();
        $link->aquaticUtilitySkill1 = $struct->read2Bytes();
        $link->terrestrialUtilitySkill2 = $struct->read2Bytes();
        $link->aquaticUtilitySkill2 = $struct->read2Bytes();
        $link->terrestrialUtilitySkill3 = $struct->read2Bytes();
        $link->aquaticUtilitySkill3 = $struct->read2Bytes();
        $link->terrestrialEliteSkill = $struct->read2Bytes();
        $link->aquaticEliteSkill = $struct->read2Bytes();

        if ($link->profession === static::FLAG_RANGER) {
            $link->terrestrialPet1 = $struct->read1Byte();
            $link->terrestrialPet2 = $struct->read1Byte();
            $link->aquaticPet1 = $struct->read1Byte();
            $link->aquaticPet2 = $struct->read1Byte();
        } elseif ($link->profession === static::FLAG_RANGER) {
            $link->terrestrialLegend1 = $struct->read1Byte();
            $link->terrestrialLegend2 = $struct->read1Byte();
            $link->aquaticLegend1 = $struct->read1Byte();
            $link->aquaticLegend2 = $struct->read1Byte();
        } else {
            $struct->read4Bytes();
        }

        // Profession specifics :
        // Revenant : inactive legend utility skills
        $struct->read2Bytes();
        $struct->read2Bytes();
        $struct->read2Bytes();
        $struct->read2Bytes();
        $struct->read2Bytes();
        $struct->read2Bytes();

        // Legacy chatcode
        if ($struct->atEnd()) {
            return $link;
        }

        // SOTO
        $link->selectedWeapons = $struct->readDynamicArray(2) ?? [];
        $link->selectedSkillVariants = $struct->readDynamicArray(4) ?? [];

        return $link;
    }
}
