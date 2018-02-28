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

namespace pocketmine\entity\vehicle;

use pocketmine\entity\EntityIds;
use pocketmine\entity\Vehicle;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\SetEntityLinkPacket;
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\Player;

class Boat extends Vehicle{

    public const NETWORK_ID = EntityIds::BOAT;

    public const TAG_VARIANT = "Variant";

    public $height = 0.455;

    protected $gravity = 0;

    protected function initEntity(){
        $this->setHealth(4);
        $this->setGenericFlag(self::DATA_FLAG_STACKABLE);
        $this->setImmobile(false);

        $this->setBoatType($this->namedtag->getInt(self::TAG_VARIANT, 0));

        parent::initEntity();
    }

    public function onInteract(Player $player, Item $item, Vector3 $clickVector, array $actions = []) : bool{
        $player->setGenericFlag(self::DATA_FLAG_RIDING);
        $player->vehicleEid = $this->getId();

        $player->propertyManager->setVector3(self::DATA_RIDER_SEAT_POSITION, new Vector3(0, 1.02001, 0));
        $player->propertyManager->setByte(self::DATA_RIDER_ROTATION_LOCKED, 1);
        $player->propertyManager->setFloat(self::DATA_RIDER_MAX_ROTATION, 90);
        $player->propertyManager->setFloat(self::DATA_RIDER_MIN_ROTATION, -90);

        $changedProperties = $this->propertyManager->getDirty();
        if(!empty($changedProperties)){
            $this->sendData($this->hasSpawned, $changedProperties);
            $this->propertyManager->clearDirtyProperties();
        }

        $this->motionY = 0.1; // HACK for gravity problem

        $pk = new SetEntityLinkPacket();
        $pk->link = new EntityLink($this->getId(), $player->getId(), EntityLink::TYPE_RIDE, false);
        $player->dataPacket($pk);

        return true;
    }

    public function getDrops(): array{
        return [
            ItemFactory::get(Item::BOAT, $this->getBoatType())
        ];
    }

    public function getBoatType() : int{
        return $this->propertyManager->getInt(self::DATA_VARIANT);
    }

    public function setBoatType(int $boatType) : void{
        $this->propertyManager->setInt(self::DATA_VARIANT, $boatType);
    }

    public function saveNBT(){
        parent::saveNBT();

        $this->namedtag->setInt(self::TAG_VARIANT, $this->getBoatType());
    }

    public function onLeave(Player $rider) : void{
        $this->motionY = 0;
        $rider->propertyManager->removeProperty(self::DATA_RIDER_SEAT_POSITION);
        $rider->propertyManager->removeProperty(self::DATA_RIDER_ROTATION_LOCKED);
        $rider->propertyManager->removeProperty(self::DATA_RIDER_MAX_ROTATION);
        $rider->propertyManager->removeProperty(self::DATA_RIDER_MIN_ROTATION);
    }
}