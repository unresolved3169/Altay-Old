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
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\inventory\AltayEntityEquipment;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\inventory\utils\EquipmentSlot;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;

class ArmorStand extends Entity{

    public const TAG_ARMOR = "Armor";
    public const TAG_MAINHAND = "Mainhand";
    public const TAG_OFFHAND = "Offhand";
    public const TAG_POSE = "Pose";
    public const TAG_LAST_SIGNAL = "LastSignal";
    public const TAG_POSE_INDEX = "PoseIndex";

    /** @var AltayEntityEquipment */
    protected $equipment;
    /** @var int */
    protected $pose = 0; // have 13

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

        if(!$this->namedtag->hasTag(self::TAG_POSE, CompoundTag::class)){
            $this->namedtag->setTag(new CompoundTag(self::TAG_POSE, [
                new IntTag(self::TAG_LAST_SIGNAL, 0),
                new IntTag(self::TAG_POSE_INDEX, 0)
            ]));
        }

        /** @var ListTag $armor */
        $armor = $this->namedtag->getTag(self::TAG_ARMOR);
        /** @var ListTag $mainhand */
        $mainhand = $this->namedtag->getTag(self::TAG_MAINHAND);
        /** @var ListTag $offhand */
        $offhand = $this->namedtag->getTag(self::TAG_OFFHAND);

        $contents = array_merge(array_map(function(CompoundTag $tag) : Item{ return Item::nbtDeserialize($tag); }, $armor->getAllValues()), [Item::nbtDeserialize($offhand[0])], [Item::nbtDeserialize($mainhand[0])]);
        $this->equipment = new AltayEntityEquipment($this);
        $this->equipment->setContents($contents);

        /** @var CompoundTag $poseTag */
        $poseTag = $this->namedtag->getTag(self::TAG_POSE);
        $this->pose = $poseTag->getInt(self::TAG_POSE_INDEX, 0);
    }

    public function onInteract(Player $player, Item $item, Vector3 $clickVector, array $actions = []) : bool{
        if($player->isSneaking()){
            // I couldn't find a way to set a pose, but MCPE is doing it himself here.
            $this->pose++;
            if($this->pose >= 13)
                $this->pose = 0;

            return true;
        }
        foreach($actions as $action){
            if($action instanceof SlotChangeAction){
                if($action->execute($player)){
                    $action->onExecuteSuccess($player);
                }else{
                    $action->onExecuteFail($player);
                }

                if($action->getSourceItem()->getCount() < $action->getTargetItem()->getCount()){
                    $first = $this->equipment->first($action->getTargetItem());
                    $this->equipment->clear($first, false);
                }else{
                    $item = $action->getSourceItem();
                    $newItem = $item->pop();
                    $slot = $this->getEquipmentSlot($item);
                    $this->equipment->setItem($slot, $newItem, false);
                }

                return true;
            }
        }

        if(!$item->isNull()){
            $slot = $this->getEquipmentSlot($item);
            $newItem = $item->pop();
            $this->equipment->setItem($slot, $newItem);
            $player->getInventory()->setItemInHand($item);
        }else{
            $this->equipment->sendContents($player);
        }

        return true;
    }

    public function onUpdate(int $currentTick): bool{
        if(($hasUpdated = parent::onUpdate($currentTick))){
            if($this->isAffectedByGravity()){
                if($this->level->getBlock($this->getSide(Vector3::SIDE_DOWN)) === Item::AIR){
                    $this->applyGravity();
                    $this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_ARMOR_STAND_FALL);
                }
            }
            return true;
        }

        return $hasUpdated;
    }

    public function saveNBT(){
        parent::saveNBT();

        $this->namedtag->setTag(new ListTag(self::TAG_MAINHAND, [$this->equipment->getMainhandItem()->nbtSerialize()], NBT::TAG_Compound));
        $this->namedtag->setTag(new ListTag(self::TAG_OFFHAND, [$this->equipment->getOffhandItem()->nbtSerialize()], NBT::TAG_Compound));

        $armorNBT = array_map(function(Item $item) : CompoundTag{ return $item->nbtSerialize(); }, $this->equipment->getArmorContents());
        $this->namedtag->setTag(new ListTag(self::TAG_ARMOR, $armorNBT, NBT::TAG_Compound));

        /** @var CompoundTag $poseTag */
        $poseTag = $this->namedtag->getTag(self::TAG_POSE);
        $this->namedtag->setTag(new CompoundTag(self::TAG_POSE, [
            $poseTag->getTag(self::TAG_LAST_SIGNAL),
            new IntTag(self::TAG_POSE_INDEX, $this->pose)
        ]));
    }

    public function kill(){
        $dropVector = $this->add(0.5, 0.5, 0.5);
        $items = array_merge($this->equipment->getContents(false), [ItemFactory::get(Item::ARMOR_STAND)]);
        $this->level->dropItems($dropVector, $items);

        return parent::kill();
    }

    public function attack(EntityDamageEvent $source){
        if($source instanceof EntityDamageByEntityEvent){
            $damager = $source->getDamager();
            if($damager instanceof Player){
                if($damager->isCreative()){
                    $this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_ARMOR_STAND_BREAK);
                    $this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_PARTICLE_DESTROY, 5);
                    $this->flagForDespawn();
                }else{
                    $this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_ARMOR_STAND_HIT);
                }
            }
        }
        if($source->getCause() != EntityDamageEvent::CAUSE_CONTACT){ // cactus
            parent::attack($source);
        }
    }

    public function spawnTo(Player $player){
        parent::spawnTo($player);
        $this->equipment->sendContents($player);
    }

    public function getName(): string{
        return "Armor Stand";
    }

    public function getEquipmentSlot(Item $item){
        if($item instanceof Armor){
            return $item->getArmorSlot() + 2; // HACK :D
        }else{
            switch($item->getId()){
                case Item::SKULL:
                case Item::SKULL_BLOCK:
                case Item::PUMPKIN:
                    return EquipmentSlot::HACK_HEAD;
            }
            return EquipmentSlot::MAINHAND;
        }
    }
}