<?php

namespace pocketmine\entity\behavior;

use pocketmine\entity\Living;

class RandomStrollBehavior extends Behavior{
	
	protected $speedMultiplier = 1.0;
	protected $chance = 120;
	/** @var Path */
	protected $currentPath;
	
	public function __construct(Living $mob, float $speedMultiplier = 1.0, int $chance = 120){
		parent::__construct($mob);
		
		$this->speedMultiplier = $speedMultiplier;
		$this->chance = $chance;
	}
	
	public function canStart() : bool{
		if(rand(0,$this->chance) === 0){
			$this->currentPath = Path::findPath($this->mob->level->getBlock($this->mob));
			
			return $this->currentPath->havePath();
		}
		return false;
	}
	
	public function canContinue() : bool{
		return $this->currentPath->havePath();
	}
	
	public function onTick(int $tick) : void{
		if($this->currentPath->havePath()){
			if(($vec = $this->currentPath->getNextVector()) !== null){
				$this->mob->lookAt($vec->add(0.5,0.5,0.5));
				$this->mob->moveFormard($this->speedMultiplier);
			}
		}
	}
	
	public function onEnd() : void{
		$this->mob->motionX = $this->mob->motionZ = 0;
		$this->currentPath = null;
	}
}