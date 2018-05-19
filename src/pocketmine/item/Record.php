<?php

namespace pocketmine\item;

abstract class Record extends Item{
	
	public function __construct(int $id){
		parent::__construct($id, 0, "Music Disc");
	}
	
	public function getMaxStackSize() : int{
		return 1;
	}
	
	abstract public function getSoundId() : int;
}