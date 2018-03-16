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

namespace pocketmine\entity\hostile;

use pocketmine\entity\behavior\LookAtPlayerBehavior;
use pocketmine\entity\behavior\RandomLookAroundBehavior;
use pocketmine\entity\behavior\WanderBehavior;
use pocketmine\entity\Monster;
use pocketmine\inventory\AltayEntityEquipment;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\Player;

class Skeleton extends Monster{
    public const NETWORK_ID = self::SKELETON;

    public $width = 0.6;
    public $height = 1.95;

    /** @var AltayEntityEquipment */
    protected $equipment;

    // speed 0.25
    protected function initEntity(){
        $this->setMaxHealth(20);
        $this->equipment = new AltayEntityEquipment($this);
        $this->equipment->setMainhandItem(ItemFactory::get(Item::BOW));
        parent::initEntity();
    }

    protected function sendSpawnPacket(Player $player) : void{
        parent::sendSpawnPacket($player);
        $this->equipment->sendContents($player);
    }

    public function getEquipment() : AltayEntityEquipment{
        return $this->equipment;
    }

    protected function getNormalBehaviors() : array{
        return [
            //new MeleeAttackBehavior($this, 1.0, 16),
            new LookAtPlayerBehavior($this, 8),
            new RandomLookAroundBehavior($this),
            new WanderBehavior($this)
        ];
    }

    protected function getTargetBehaviors(): array{
        return [
            // TODO
        ];
    }

    public function getName() : string{
        return "Skeleton";
    }
}