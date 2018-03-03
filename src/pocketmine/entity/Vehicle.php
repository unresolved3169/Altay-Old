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

namespace pocketmine\entity;

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;

abstract class Vehicle extends Entity implements Rideable{

    abstract public function onLeave(Player $rider) : void;

    abstract public function onBoard(Player $rider) : bool;

    public function onInteract(Player $player, Item $item, Vector3 $clickVector, array $actions = []){
        if($this->onBoard($player))
            $player->linkToVehicle($this);
    }

}
