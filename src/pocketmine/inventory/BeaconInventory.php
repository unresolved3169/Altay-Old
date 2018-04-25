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

namespace pocketmine\inventory;

use pocketmine\Player;
use pocketmine\tile\Beacon;
use pocketmine\network\mcpe\protocol\types\WindowTypes;

class BeaconInventory extends ContainerInventory{

    public function __construct(Beacon $tile){
        parent::__construct($tile);
    }

    public function getName() : string{
        return "Beacon";
    }

    public function getDefaultSize() : int{
        return 1;
    }

    /**
     * @return Beacon
     */
    public function getHolder(){
        return $this->holder;
    }

    public function getNetworkType() : int{
        return WindowTypes::BEACON;
    }

    public function onClose(Player $who) : void{
        parent::onClose($who);

        $inv = $who->getInventory();
        for($i = 0, $size = $this->getSize(); $i < $size; ++$i){
            $item = $this->getItem($i);
            if(!$item->isNull()){
                if($inv->canAddItem($item)){
                    $inv->addItem($item);
                }else{
                    $who->dropItem($item);
                }
            }
        }
    }
}