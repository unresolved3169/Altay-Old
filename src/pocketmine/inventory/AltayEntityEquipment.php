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

namespace pocketmine\inventory;

use pocketmine\entity\Entity;
use pocketmine\inventory\utils\EquipmentSlot;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\Player;

class AltayEntityEquipment extends BaseInventory{

    /** @var Entity */
    protected $holder;

    public function __construct(Entity $entity){
        $this->holder = $entity;

        parent::__construct();
    }

    public function getName(): string{
        return "Altay Entity Equipment";
    }

    public function getDefaultSize(): int{
        return 6; // equipment slots (4 armor 2 hand)
    }

    public function getHolder() : Entity{
        return $this->holder;
    }

    public function sendSlot(int $index, $target): void{
        if($target instanceof Player){
            $target = [$target];
        }

        switch($index){
            case EquipmentSlot::MAINHAND:
                $pk = new MobEquipmentPacket();
                $pk->entityRuntimeId = $this->holder->getId();
                $pk->inventorySlot = $pk->hotbarSlot = $index;
                $pk->item = $this->getItem($index);
                break;

            case EquipmentSlot::HACK_OFFHAND:
                return;

            case EquipmentSlot::HACK_HEAD:
            case EquipmentSlot::HACK_CHEST:
            case EquipmentSlot::HACK_LEGS:
            case EquipmentSlot::HACK_FEET:
                $pk = new MobArmorEquipmentPacket();
                $pk->entityRuntimeId = $this->holder->getId();
                $pk->slots = $this->getArmorContents();
                break;
            default:
                throw new \InvalidArgumentException("Unknown equipment slot: $index");
        }

        if($target instanceof Player){
            $target = [$target];
        }

        foreach($target as $player){
            $player->dataPacket($pk);
        }
    }

    public function sendContents($target): void{
        $this->sendSlot(EquipmentSlot::MAINHAND, $target);
        $this->sendSlot(EquipmentSlot::HACK_OFFHAND, $target);
        $this->sendArmorContents($target);
    }

    public function sendArmorContents($target){
        $this->sendSlot(EquipmentSlot::HACK_HEAD, $target); // HACK !
    }

    public function getArmorContents() : array{
        $armors = [];

        for($i=0; $i<4; $i++){
            $armors[] = $this->getItem(2 + $i);
        }

        return $armors;
    }

    public function getMainhandItem() : Item{
        return $this->getItem(EquipmentSlot::MAINHAND);
    }

    public function getOffhandItem() : Item{
        return $this->getItem(EquipmentSlot::HACK_OFFHAND);
    }

    public function setMainhandItem(Item $item, bool $send = true) : bool{
        return $this->setItem(EquipmentSlot::MAINHAND, $item, $send);
    }

    public function setOffhandItem(Item $item, bool $send = true) : bool{ // not work
        return $this->setItem(EquipmentSlot::HACK_OFFHAND, $item, $send);
    }

    public function setHelmet(Item $item, bool $send = true) : bool{
        return $this->setItem(EquipmentSlot::HACK_HEAD, $item, $send);
    }

    public function setChestplate(Item $item, bool $send = true) : bool{
        return $this->setItem(EquipmentSlot::HACK_CHEST, $item, $send);
    }

    public function setLeggings(Item $item, bool $send = true) : bool{
        return $this->setItem(EquipmentSlot::HACK_LEGS, $item, $send);
    }

    public function setBoots(Item $item, bool $send = true) : bool{
        return $this->setItem(EquipmentSlot::HACK_FEET, $item, $send);
    }

    public function getHelmet() : Item{
        return $this->getItem(EquipmentSlot::HACK_HEAD);
    }

    public function getChestplate() : Item{
        return $this->getItem(EquipmentSlot::HACK_CHEST);
    }

    public function getLeggings() : Item{
        return $this->getItem(EquipmentSlot::HACK_LEGS);
    }

    public function getBoots() : Item{
        return $this->getItem(EquipmentSlot::HACK_FEET);
    }

}