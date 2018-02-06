<?php

namespace pocketmine\entity\behavior;

use pocketmine\entity\Living;
use pocketmine\Player;

class LookAtPlayerBehavior extends Behavior{
	
	protected $lookDistance = 6.0;
	protected $player;
	protected $duration = 0;
	
	public function __construct(Living $mob, float $lookDistance = 6.0){
		parent::__construct($mob);
		
		$this->lookDistance = $lookDistance;
	}
	
	public function canStart() : bool{
		if(rand(0,20) === 0){
			$player = $this->mob->level->getNearestEntity($this, $this->lookDistance, Player::class);
			
			if($player instanceof Player){
				$this->player = $player;
				$this->duration = 40 + rand(0,40);
				
				return true;
			}
		}
		return false;
	}
	
	public function canContinue() : bool{
		return $this->duration-- > 0;
	}
	
	public function onTick(int $tick) : void{}
		if($this->player instanceof Player){
			$this->mob->lookAt($this->player);
		}
	}
	
	public function onEnd() : void{
		$this->mob->pitch = 0;
		$this->player = null;
	}
}