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
use pocketmine\entity\Villager;
use pocketmine\network\mcpe\protocol\UpdateTradePacket;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\Player;

class TradingInventory extends BaseInventory{

	/** @var Villager */
	protected $villager;

	public function __construct(Villager $villager){
		$this->villager = $villager;
		parent::__construct();
	}

	public function getNetworkType() : int{
		return WindowTypes::TRADING;
	}

	public function getName() : string{
		return "Trading";
	}

	public function getDefaultSize() : int{
		return 3; //1 buyA, 1 buyB, 1 sell
	}

	public function onOpen(Player $who) : void{
		$tag = $this->villager->getRecipes($who)->writeToTags();
		if($tag !== null){
			parent::onOpen($who);

			$pk = new UpdateTradePacket();
			$pk->windowId = $who->getWindowId($this);
			$pk->windowType = $this->getNetworkType();
			$pk->varint1 = 0;
			$pk->varint2 = 0;
			$pk->isWilling = false;
			$pk->traderEid = $this->villager->getId();
			$pk->playerEid = $who->getId();
			$pk->displayName = $this->villager->getDisplayName();
			try{
				$nbtWriter = new NetworkLittleEndianNBTStream();
				$pk->offers = $nbtWriter->write($tag);
			}catch(IOException $exception){
			}
			$who->dataPacket($pk);
		}else{
			parent::onClose($who);
		}
	}

	/*public function onClose(Player $who) : void{
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
	}*/
}
