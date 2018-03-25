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

namespace pocketmine\inventory\transaction\action;

use pocketmine\inventory\transaction\TradingTransaction;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\Player;

/**
 * Action used to take the primary result item during crafting.
 */
class TradingTakeResultAction extends InventoryAction{

	public function onAddToTransaction(InventoryTransaction $transaction) : void{
		if($transaction instanceof TradingTransaction){
			$transaction->setOutput($this->getSourceItem());
		}else{
			throw new \InvalidStateException(get_class($this) . " can only be added to TradingTransactions");
		}
	}

	public function isValid(Player $source) : bool{
		return true;
	}

	public function execute(Player $source) : bool{
		return true;
	}

	public function onExecuteSuccess(Player $source) : void{
	}

	public function onExecuteFail(Player $source) : void{
	}

}
