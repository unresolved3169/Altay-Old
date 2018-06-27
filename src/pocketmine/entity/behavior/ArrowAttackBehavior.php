<?php

namespace pocketmine\entity\behavior;

use pocketmine\entity\Mob;

class ArrowAttackBehavior extends Behavior{
    
    protected $rangedAttackTime = 0, $targetSeenTicks = 0;
    protected $speedMultiplier;
    protected $maxAttackTime, $minAttackTime;
    protected $maxAttackDistance, $maxAttackDistanceIn;
    
    public function __construct(Mob $mob, float $speedMultiplier, int $minAttackTime, int $maxAttackTime, float $maxAttackDistanceIn){
        parent::__construct($mob);
        
        $this->speedMultiplier = $speedMultiplier;
        $this->minAttackTime = $minAttackTime;
        $this->maxAttackTime = $maxAttackTime;
        $this->maxAttackDistanceIn = $maxAttackDistanceIn;
        $this->maxAttackDistance = $maxAttackDistanceIn ** 2;
        $this->rangedAttackTime = -1;
    }
    
    public function canStart() : bool{
        return $this->mob->getTargetEntityId() !== null;
    }
    
    public function canContinue() : bool{
        return $this->canStart() and $this->mob->getNavigator()->havePath();
    }

    public function onEnd() : void{
        $this->targetSeenTicks = 0;
        $this->rangedAttackTime = -1;
        $this->mob->getNavigator()->clearPath();
    }
    
    public function onTick() : void{
        $dist = $this->mob->distanceSquared($this->mob->getTargetEntity());
        
        if($flag = $this->mob->canSeeEntity($this->mob->getTargetEntity())){
            $this->targetSeenTicks++;
        }else{
            $this->targetSeenTicks = 0;
        }
        
        if($dist <= $this->maxAttackDistance and $thid->targetSeenTicks >= 20){
            $this->mob->getNavigator()->clearPath();
        }else{
            $this->mob->getNavigator()->tryMoveTo($this->mob->getTargetEntity());
        }
        
        $this->mob->setLookPosition($this->mob->getTargetEntity());
        
        if(--$this->rangedAttackTime === 0){
            if($dist > $this->maxAttackDistance or !$flag){
                return;
            }
            
            $f = sqrt($dist) / $this->maxAttackDistanceIn;
            if($f > 1) $f = 1;
            if($f < 0.1) $f = 0.1;
            
            $this->mob->onRangedAttackToTarget($this->mob->getTargetEntity(), $f);
            
            $this->rangedAttackTime = floor($f * ($this->maxAttackDistance - $this->minAttackTime) + $this->minAttackTime);
        }elseif($this->rangedAttackTime < 0){
            $f = sqrt($dist) / $this->maxAttackDistanceIn;       
            $this->rangedAttackTime = floor($f * ($this->maxAttackDistance - $this->minAttackTime) + $this->minAttackTime);
        }
    }
}