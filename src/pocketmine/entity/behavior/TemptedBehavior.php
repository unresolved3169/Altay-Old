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

namespace pocketmine\entity\behavior;

use pocketmine\entity\Mob;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;

class TemptedBehavior extends Behavior{

    /** @var float */
    protected $lookDistance;
    /** @var float */
    protected $speedMultiplier;
    /** @var Item */
    protected $temptingItem;
    /** @var int */
    protected $coolDown;
    /** @var Player */
    protected $temptingPlayer;
    /** @var Vector3 */
    protected $lastPlayerPos;
    /** @var Vector3 */
    protected $originalPos;
    /** @var Path */
    protected $currentPath = null;

    public function __construct(Mob $mob, Item $temptingItem, float $lookDistance, float $speedMultiplier){
        parent::__construct($mob);

        $this->temptingItem = $temptingItem;
        $this->speedMultiplier = $speedMultiplier;
        $this->lookDistance = $lookDistance;
        $this->speedMultiplier = $speedMultiplier;
    }

    public function canStart() : bool{
        if($this->coolDown > 0){
            $this->coolDown--;
            return false;
        }

        /** @var Player|null $player */
        $player = $this->mob->level->getNearestEntity($this->mob, $this->lookDistance, Player::class);
        $player = $player->getInventory()->getItemInHand()->equals($this->temptingItem) ? $player : null;

        if($player === null) return false;

        if($player !== $this->temptingPlayer){
            $this->temptingPlayer = $player;
            $this->lastPlayerPos = $player->asVector3();
            $this->originalPos = $this->mob->asVector3();
        }

        return true;
    }

    public function canContinue() : bool{
        if(abs($this->originalPos->y - $this->mob->y) < 0.5)
            return true;

        return false;
    }

    public function onTick(int $tick): void{
        if($this->temptingPlayer === null) return;
        $distanceToPlayer = $this->mob->distance($this->temptingPlayer);

        if($distanceToPlayer < 1.75){
            $this->mob->resetMotion();
            $this->mob->lookAt($this->temptingPlayer);

            $this->currentPath = null;

            return;
        }

        $haveNoPath = ($this->currentPath == null || !$this->currentPath->havePath());
        $deltaDistance = $this->lastPlayerPos->distance($this->temptingPlayer);
        if($haveNoPath || $deltaDistance > 1){
            $this->currentPath = Path::findPath($this->mob, $this->temptingPlayer, $this->lookDistance);
            $this->lastPlayerPos = $this->temptingPlayer->asVector3();
        }

        if($this->currentPath->havePath()){
            $next = $this->currentPath->getNextTile($this->mob);
            if($next === null){
                $this->currentPath = null;
                return;
            }

            $this->mob->lookAt(new Vector3($next->x + 0.5, $this->mob->y, $next->y + 0.5));

            if($distanceToPlayer < 1.75){
                // if within x m stop following (walking)
                $this->mob->resetMotion();
                $this->currentPath = null;
            }else{
                // else find path to player

                $m = 2 - $distanceToPlayer;
                $m = ($m <= 0) ? 1 : $m / 2.0;

                $this->mob->moveForward($this->speedMultiplier * $m);
            }
        }else{
            $this->mob->resetMotion();
            $this->currentPath = null;
        }

        $this->mob->lookAt($this->temptingPlayer);
    }

    public function onEnd(): void{
        $this->coolDown = 100;
        $this->mob->resetMotion();
        $this->temptingPlayer = null;
        $this->mob->pitch = 0;
        $this->currentPath = null;
    }
}