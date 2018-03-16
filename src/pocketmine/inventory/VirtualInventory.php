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

use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\Player;
use pocketmine\tile\VirtualHolder;

class VirtualInventory extends CustomInventory{

    /** @var VirtualHolder */
    protected $holder;

    public function __construct(VirtualHolder $holder){
        parent::__construct($holder);
    }

    public function getName() : string{
        return $this->holder->getName();
    }

    public function close(Player $who) : void{
        $this->holder->translateBlocks($who);
        parent::close($who);
        $this->holder->close();
    }

    public function getDefaultSize() : int{
        return $this->holder->getSize();
    }

    public function getNetworkType() : int{
        return $this->holder->getNetworkType();
    }
}