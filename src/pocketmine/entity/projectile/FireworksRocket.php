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

namespace pocketmine\entity\projectile;

use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\item\FireworkRocket;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\utils\Random;

class FireworksRocket extends Projectile{

    public const NETWORK_ID = EntityIds::FIREWORKS_ROCKET;

    public const DATA_FIREWORK_ITEM = 16;

    public $width = 0.25;
    public $height = 0.25;

    protected $gravity = 0.0;
    protected $drag = 0.1;

    /** @var FireworkRocket */
    public $fireworksItem;
    /** @var int */
    public $lifeTime;

    public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null, FireworkRocket $fireworks, Random $random = null){
        $this->fireworksItem = $fireworks;
        $random = $random ?? new Random();

        $flyTime = 1;
        $lifeTime = null;

        try{
            if($nbt->hasTag("Fireworks", CompoundTag::class))
                $fireworkCompound = $nbt->getCompoundTag("Fireworks");
                $flyTime = $fireworkCompound->getByte("Flight", 1);
                $lifeTime = $fireworkCompound->getInt("LifeTime", 20 * $flyTime + $random->nextBoundedInt(5) + $random->nextBoundedInt(7));
        }catch(\Exception $exception){
            $this->server->getLogger()->debug($exception);
        }

        $this->lifeTime = $lifeTime ?? 20 * $flyTime + $random->nextBoundedInt(5) + $random->nextBoundedInt(7);

        $nbt->setInt("Life", $this->lifeTime);
        $nbt->setInt("LifeTime", $this->lifeTime);

        parent::__construct($level, $nbt, $shootingEntity);
    }

    protected function initEntity(){
        $this->setGenericFlag(self::DATA_FLAG_AFFECTED_BY_GRAVITY, true);
        $this->setGenericFlag(self::DATA_FLAG_HAS_COLLISION, true);
        $this->propertyManager->setItem(self::DATA_FIREWORK_ITEM, $this->fireworksItem);

        parent::initEntity();
    }

    public function spawnTo(Player $player){
        $this->setMotion($this->getDirectionVector());
        $this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_LAUNCH);
        parent::spawnTo($player);
    }

    public function despawnFromAll(){
        $this->broadcastEntityEvent(EntityEventPacket::FIREWORK_PARTICLES, 0);
        parent::despawnFromAll();
        $this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_BLAST);
    }

    public function entityBaseTick(int $tickDiff = 1) : bool{
        if($this->lifeTime-- <= 0)
            $this->flagForDespawn();
        else
            return parent::entityBaseTick($tickDiff);

        return true;
    }
}