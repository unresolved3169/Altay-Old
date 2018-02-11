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

namespace pocketmine\entity\object;

use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\Player;
class ArmorStand extends Entity{

    public const TAG_ARMOR = "Armor";
    public const TAG_MAINHAND = "Mainhand";
    public const TAG_OFFHAND = "Offhand";

    /** @var Item */
    protected $mainhand;
    /** @var Item */
    protected $offhand; // TODO : Handle when shield is added
    /** @var Item[] */
    protected $armors;

    protected $gravity = 0.04;

    public const NETWORK_ID = EntityIds::ARMOR_STAND;

    protected function initEntity(){
        $this->setMaxHealth(6);

        parent::initEntity();

        $airNBT = ItemFactory::get(Item::AIR)->nbtSerialize();
        if(!$this->namedtag->hasTag(self::TAG_ARMOR, ListTag::class)){
            $this->namedtag->setTag(new ListTag(self::TAG_ARMOR, [
                $airNBT,
                $airNBT,
                $airNBT,
                $airNBT
            ], NBT::TAG_Compound));
        }

        if(!$this->namedtag->hasTag(self::TAG_MAINHAND, ListTag::class)){
            $this->namedtag->setTag(new ListTag(self::TAG_MAINHAND, [
                $airNBT
            ], NBT::TAG_Compound));
        }

        if(!$this->namedtag->hasTag(self::TAG_OFFHAND, ListTag::class)){
            $this->namedtag->setTag(new ListTag(self::TAG_OFFHAND, [
                $airNBT
            ], NBT::TAG_Compound));
        }

        // TODO : Add POSE

        /** @var ListTag $armor */
        $armor = $this->namedtag->getTag(self::TAG_ARMOR);
        /** @var ListTag $mainhand */
        $mainhand = $this->namedtag->getTag(self::TAG_MAINHAND);
        /** @var ListTag $offhand */
        $offhand = $this->namedtag->getTag(self::TAG_OFFHAND);

        $this->armors = array_map(function(CompoundTag $tag) : Item{ return Item::nbtDeserialize($tag); }, $armor->getAllValues());
        $this->mainhand = Item::nbtDeserialize($mainhand[0]);
        $this->offhand = Item::nbtDeserialize($offhand[0]);

        $this->sendAll();
    }

    public function onInteract(Player $player, Item $item, Vector3 $clickVector) : bool{
        if($item->isNull()){
            var_dump($clickVector->__toString());
            $player->getInventory()->sendContents($player);
            // TODO : Remove items from armorstand
        }else{
            $newItem = $item->pop();
            if($newItem instanceof Armor){
                $this->armors[$newItem->getArmorSlot()] = $newItem;
            }else{
                switch($newItem->getId()){
                    case Item::PUMPKIN:
                    case Item::SKULL:
                    case Item::SKULL_BLOCK:
                        $this->armors[Armor::SKULL] = $newItem;
                        break;
                    default:
                        $this->mainhand = $newItem;
                        break;
                }
            }
            $this->sendAll();
            $player->getInventory()->setItemInHand($item);
        }
        return true;
    }

    public function onUpdate(int $currentTick): bool{
        if(($hasUpdated = parent::onUpdate($currentTick))){
            if($this->getGenericFlag(Entity::DATA_FLAG_AFFECTED_BY_GRAVITY)){
                if($this->level->getBlock($this->getSide(Vector3::SIDE_DOWN)) === Item::AIR){
                    $this->applyGravity();
                }
            }
            return true;
        }

        return $hasUpdated;
    }

    public function saveNBT(){
        parent::saveNBT();

        $armorNBT = array_map(function(Item $item) : CompoundTag{ return $item->nbtSerialize(); }, $this->armors);
        $this->namedtag->setTag(new ListTag(self::TAG_ARMOR, $armorNBT, NBT::TAG_Compound));
        $this->namedtag->setTag(new ListTag(self::TAG_MAINHAND, [$this->mainhand->nbtSerialize()], NBT::TAG_Compound));
        $this->namedtag->setTag(new ListTag(self::TAG_OFFHAND, [$this->offhand->nbtSerialize()], NBT::TAG_Compound));
    }

    public function kill(){
        $dropVector = $this->add(0.5, 0.5, 0.5);
        $items = array_merge($this->armors, [$this->mainhand], [$this->offhand], [ItemFactory::get(Item::ARMOR_STAND)]);
        $this->level->dropItems($dropVector, $items);

        return parent::kill();
    }

    public function attack(EntityDamageEvent $source){
        if($source->getCause() != EntityDamageEvent::CAUSE_CONTACT){ // cactus
            parent::attack($source);
        }
    }

    /**
     * @param Player[]|Player $target
     */
    protected function sendArmorSlots($target){
        if($target instanceof Player){
            $target = [$target];
        }

        $pk = new MobArmorEquipmentPacket();
        $pk->entityRuntimeId = $this->getId();
        $pk->slots = $this->armors;
        $pk->encode();

        foreach($target as $t){
            $t->dataPacket($pk);
        }
    }

    /**
     * @param Player[]|Player $target
     */
    protected function sendMainhandItem($target){
        if($target instanceof Player){
            $target = [$target];
        }

        $pk = new MobEquipmentPacket();
        $pk->entityRuntimeId = $this->getId();
        $pk->item = $this->mainhand;
        $pk->hotbarSlot = $pk->inventorySlot = 0;

        foreach ($target as $t){
            $t->dataPacket($pk);
        }
    }

    public function sendAll(){
        $this->sendMainhandItem($this->getViewers());
        $this->sendArmorSlots($this->getViewers());
    }

    public function spawnTo(Player $player){
        parent::spawnTo($player);
        $this->sendArmorSlots($player);
        $this->sendMainhandItem($player);
    }

    public function getName(): string{
        return "Armor Stand";
    }
}