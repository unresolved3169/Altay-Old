<?php

namespace pocketmine\entity\behavior;

use pocketmine\entity\Living;

abstract class Behavior{
	
	protected $mob;
	
	public function getName() : string{
		return (new \ReflectionClass($this))->getShortName();
	}
	
	public function __construct(Living $mob){
		$this->mob = $mob;
	}
	
	public abstract function canStart() : bool;
	
	public abstract function canContinue() : bool;
	
	public abstract function onTick(int $tick) : void;
	
	public abstract function onEnd() : void;
	
}