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

namespace pocketmine\entity;

use pocketmine\entity\behavior\Behavior;

class EntityBehaviorManager{
	
	protected $behaviors = [];
	protected $behaviorsEnabled = false;
	protected $currentBehavior;
	
    public function getReadyBehavior() : ?Behavior{
        foreach($this->behaviors as $index => $behavior){
            if($behavior == $this->currentBehavior){
                if($behavior->canContinue()){
                    return $behavior;
                }
                $behavior->onEnd();
                $this->currentBehavior = null;
            }
            if($behavior->canStart()){
                if($this->currentBehavior == null or (array_search($this->currentBehavior, $this->behaviors)) > $index){
                    if($this->currentBehavior != null){
                        $this->currentBehavior->onEnd();
                    }
                    return $behavior;
                }
            }
        }
        return null;
    }
	
    public function getCurrentBehavior(){
        return $this->currentBehavior;
    }
	
	public function setCurrentBehavior(Behavior $behavior = null){
        $this->currentBehavior = $behavior;
    }
	
    public function addBehavior(Behavior $behavior){
        $this->behaviors[] = $behavior;
    }
    
    public function setBehavior(int $index, Behavior $b){
    	$this->behaviors[$index] = $b;
    }
    public function removeBehavior(int $key){
        unset($this->behaviors[$key]);
    }
    
    public function isBehaviorsEnabled() : bool{
    	return $this->behaviorsEnabled;
    }
    
    public function setBehaviorsEnabled(bool $value = true){
    	$this->behaviorsEnabled = $value;
    }
	
	public function getBehaviors() : array{
		return $this->behaviors;
	}
}