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

// TODO : Change to Living
class ArmorStand extends Entity{
    public const NETWORK_ID = EntityIds::ARMOR_STAND;

    public const TAG_ARMOR = "Armor";
    public const TAG_MAINHAND = "Mainhand";
    public const TAG_OFFHAND = "Offhand";
    public const TAG_POSE = "Pose";
    public const TAG_LAST_SIGNAL = "LastSignal";
    public const TAG_POSE_INDEX = "PoseIndex";

    /** @var AltayEntityEquipment */
    protected $equipment;

    public $width = 0.5;
    public $height = 1.975;

    protected $gravity = 0.04;

    protected function initEntity(){
        $this->setMaxHealth(6);

        parent::initEntity();

        $this->equipment = new AltayEntityEquipment($this);

        $items = [];
        if($this->namedtag->hasTag(self::TAG_MAINHAND, ListTag::class)){
            /** @var ListTag $mainhand */
            $mainhand = $this->namedtag->getTag(self::TAG_MAINHAND);
            $items[0] = Item::nbtDeserialize($mainhand->offsetGet(0));
        }

        if($this->namedtag->hasTag(self::TAG_OFFHAND, ListTag::class)){
            /** @var ListTag $offhand */
            $offhand = $this->namedtag->getTag(self::TAG_OFFHAND);
            $items[1] = Item::nbtDeserialize($offhand->offsetGet(0));
        }

        if($this->namedtag->hasTag(self::TAG_ARMOR, ListTag::class)){
            /** @var ListTag $armor */
            $armor = $this->namedtag->getTag(self::TAG_ARMOR);
            $armors = array_map(function(CompoundTag $tag) : Item{ return Item::nbtDeserialize($tag); }, $armor->getAllValues());
            foreach($armors as $index => $item){
                $items[$index + 2] = $item; // HACK
            }
        }

        if($this->namedtag->hasTag(self::TAG_POSE, CompoundTag::class)){
            /** @var CompoundTag $pose */
            $pose = $this->namedtag->getTag(self::TAG_POSE);
            $pose = $pose->getInt(self::TAG_POSE_INDEX, 0);
        }else{
            $pose = 0;
        }

        $this->equipment->setContents($items);

        $this->setPose($pose);
    }

    public function onInteract(Player $player, Item $item, Vector3 $clickVector, array $actions = []){
        if($player->isSneaking()){
            $pose = $this->getPose();
            if(++$pose >= 13){
                $pose = 0;
            }

            $this->setPose($pose);

            return true;
        }
        foreach($actions as $action){
            if($action instanceof SlotChangeAction){
                if($action->execute($player)){ // TODO : Özel action oluştur ve $actions kaldır
                    $action->onExecuteSuccess($player);

                    $targetItem = $action->getTargetItem();
                    if($action->getSourceItem()->getCount() < $targetItem->getCount()){
                        $targetItemPOP = $targetItem->pop();
                        $first = $this->equipment->first($targetItemPOP);
                        if($first !== -1){
                            $this->equipment->clear($first);
                        }else{
                            $slot = $this->getEquipmentSlot($targetItemPOP);
                            $equipmentItem = $this->equipment->getItem($slot);
                            if(!$equipmentItem->isNull()){
                                $this->server->getLogger()->debug($targetItemPOP->__toString()." item was not found in the ArmorStandInventory, but there is a ".$equipmentItem->__toString()." item in slot ".$slot.".");
                                $this->equipment->clear($slot);
                            }
                        }
                    }else{
                        $item = $action->getSourceItem();
                        $newItem = $item->pop();
                        $slot = $this->getEquipmentSlot($item);
                        $this->equipment->setItem($slot, $newItem);
                    }
                }else{
                    $action->onExecuteFail($player);
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

    public function setPose(int $pose) : void{
        $this->propertyManager->setInt(self::DATA_ARMOR_STAND_POSE, $pose);
    }

    public function getPose() : int{
        return $this->propertyManager->getInt(self::DATA_ARMOR_STAND_POSE);
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

        /** @var CompoundTag $lastSignal */
        $lastSignal = $this->namedtag->hasTag(self::TAG_POSE, CompoundTag::class) ? $this->namedtag->getTag(self::TAG_POSE) : null;
        $lastSignal = $lastSignal !== null ? $lastSignal->getInt(self::TAG_LAST_SIGNAL, 0) : 0;
        $this->namedtag->setTag(new CompoundTag(self::TAG_POSE, [
            new IntTag(self::TAG_LAST_SIGNAL, $lastSignal),
            new IntTag(self::TAG_POSE_INDEX, $this->getPose())
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