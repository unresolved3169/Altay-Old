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

namespace pocketmine\item;

use pocketmine\math\Vector3;
use pocketmine\Player;

class EnderPearl extends ProjectileItem{

    public function __construct(int $meta = 0){
        parent::__construct(self::ENDER_PEARL, $meta, "Ender Pearl");
    }

    public function getProjectileEntityType(): string{
        return "EnderPearl";
    }

    public function getThrowForce(): float{
        return 1.5;
    }

    public function getMaxStackSize(): int{
        return 16;
    }

    public function onClickAir(Player $player, Vector3 $directionVector) : bool{
        if(!$player->canUseEnderPearl()){
            return false;
        }
        $player->onUseEnderPearl();
        return parent::onClickAir($player, $directionVector);
    }
}