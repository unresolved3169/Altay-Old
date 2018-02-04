<?php

namespace pocketmine\behaviorpacks\handler\trade;

use pocketmine\inventory\transaction\TradeTransaction; // todo

interface TradeBehaviorHandler{

 public function applyData(TradeTransaction $trans, $data) : void;
}
