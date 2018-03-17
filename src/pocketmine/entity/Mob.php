<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\entity;

use pocketmine\entity\behaviors\EntityProperties;
use pocketmine\entity\behaviors\LootGenerator;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

abstract class Mob extends Living{

    /** @var EntityProperties */
    protected $entityProperties;
    /** @var LootGenerator */
    protected $lootGenerator = null;

    /** @var int */
    protected $seatCount;
    /** @var array */
    protected $seats;

    protected function initEntity(){
        parent::initEntity();

        $this->setEntityProperties(new EntityProperties(strtolower($this->getName()), $this));
        if($this->lootGenerator === null){
            $this->setLootGenerator(new LootGenerator());
        }
    }

    public function setWidthandHeight(float $width, float $height){
        $this->eyeHeight = $height / 2 + 0.1;
        $this->width = $width;
        $this->height = $height;
        $this->propertyManager->setFloat(self::DATA_BOUNDING_BOX_WIDTH, $width);
        $this->propertyManager->setFloat(self::DATA_BOUNDING_BOX_HEIGHT, $height);

        $this->recalculateBoundingBox();
    }

    public function saveNBT(){
        parent::saveNBT();

        $activeComponents = new CompoundTag("components");
        foreach($this->getEntityProperties()->getActiveComponentGroups() as $name => $value){
            $activeComponents->setByte($name, 1);
        }
        $this->namedtag->setTag($activeComponents);
    }

    protected function sendSpawnPacket(Player $player) : void{
        parent::sendSpawnPacket($player);

        $activeComponents = $this->namedtag->getCompoundTag("components") ?? [];
        /** @var ByteTag $activeComponent */
        foreach($activeComponents as $activeComponent){
            if($activeComponent->getValue() !== 0)
                $this->getEntityProperties()->addActiveComponentGroup($activeComponent->getName());
        }
    }

    public function setDefaultMovementSpeed(float $speed){
        $this->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->setDefaultValue($speed);
    }

    public function setDefaultAttackDamage(float $attackDamage){
        $this->getAttributeMap()->getAttribute(Attribute::ATTACK_DAMAGE)->setDefaultValue($attackDamage);
    }

    public function getSeatCount() : int{
        return $this->seatCount;
    }

    public function setSeatCount(int $seatCount) : void{
        $this->seatCount = $seatCount;
    }

    public function setSeats(array $seats) : void{
        $this->seats = $seats;
    }

    public function getSeats() : array{
        return $this->seats;
    }

    public function getLootGenerator() : LootGenerator{
        return $this->lootGenerator;
    }

    public function setLootGenerator(LootGenerator $lootGenerator) : void{
        $this->lootGenerator = $lootGenerator;
    }

    public function getEntityProperties() : EntityProperties{
        return $this->entityProperties;
    }

    public function setEntityProperties(EntityProperties $entityProperties) : void{
        $this->entityProperties = $entityProperties;
    }
}