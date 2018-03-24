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

use pocketmine\entity\Ageable;
use pocketmine\entity\Monster;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\entity\behavior\{
    HurtByTargetBehavior, MeleeAttackBehavior, WanderBehavior, RandomLookAroundBehavior, LookAtPlayerBehavior, FindAttackableTargetBehavior, FloatBehavior, FleeSunBehavior, RestrictSunBehavior};

class Zombie extends Monster implements Ageable{
    public const NETWORK_ID = self::ZOMBIE;

    public $width = 0.6;
    public $height = 1.8;

    protected function initEntity(){
        parent::initEntity();
        $this->setMovementSpeed($this->isBaby() ? 0.345 : 0.23);
        if($this->isBaby()){
            $this->height *= 0.5;
            $this->setScale(0.5);
        }
    }

    public function getName(): string{
        return "Zombie";
    }

    public function getDrops(): array{
        $drops = [
            ItemFactory::get(Item::ROTTEN_FLESH, 0, mt_rand(0, 2))
        ];

        if(mt_rand(0, 199) < 5){
            switch(mt_rand(0, 2)){
                case 0:
                    $drops[] = ItemFactory::get(Item::IRON_INGOT, 0, 1);
                    break;
                case 1:
                    $drops[] = ItemFactory::get(Item::CARROT, 0, 1);
                    break;
                case 2:
                    $drops[] = ItemFactory::get(Item::POTATO, 0, 1);
                    break;
            }
        }

        return $drops;
    }

    public function getXpDropAmount(): int{
        //TODO: check for equipment
        return $this->isBaby() ? 12 : 5;
    }

    protected function getNormalBehaviors() : array{
        return [
            new MeleeAttackBehavior($this, 1.0, 35),
            new FleeSunBehavior($this),
            new WanderBehavior($this),
            new LookAtPlayerBehavior($this, 8.0),
            new RandomLookAroundBehavior($this)
        ];
    }

    protected function getTargetBehaviors() : array{
        return [
            new HurtByTargetBehavior($this),
            new FindAttackableTargetBehavior($this, 35)
        ];
    }

    protected function getBehaviorTasks() : array{
        return [
            new FloatBehavior($this),
            new RestrictSunBehavior($this)
        ];
    }

    public function isBaby() : bool{
        return $this->getGenericFlag(self::DATA_FLAG_BABY);
    }
}