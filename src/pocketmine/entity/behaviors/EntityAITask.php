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

namespace pocketmine\entity\behaviors;

class EntityAITask{

	/** @var EntityAITaskEntry[] */
	private $taskEntries = [];
	/** @var EntityAITaskEntry[] */
	private $executingTaskEntries = [];

	/** @var int */
	private $tickCount;
	/** @var int */
	private $tickRate = 3;

	public function addTask(int $priority, Behavior $task) : void{
		$taskEntry = new EntityAITaskEntry($priority, $task);
		$this->taskEntries[spl_object_hash($taskEntry)] = $taskEntry;
	}

	public function removeTask(Behavior $task) : void{
		foreach($this->taskEntries as $index => $taskEntry){
			if($task === $taskEntry){
				if(isset($this->executingTaskEntries[$index])){
					$taskEntry->action->resetTask();
					unset($this->executingTaskEntries[$index]);
				}
			}
		}
	}

	public function onUpdateTasks() : void{
		if($this->tickCount++ % $this->tickRate == 0){
			foreach($this->taskEntries as $index => $taskEntry){
				if(!isset($this->executingTaskEntries[$index])){
					$this->startExecute($index);
					continue;
				}

				$entry = $this->executingTaskEntries[$index];
				if(!$this->canUse($entry) || !$this->canContinue($entry)){
					$entry->action->resetTask();
					unset($this->executingTaskEntries[$index]);

					$this->startExecute($index);
					continue;
				}

				$this->startExecute($index);
			}
		}else{
			foreach($this->executingTaskEntries as $index => $entry){
				if(!$this->canContinue($entry)){
					$entry->action->resetTask();
					unset($this->executingTaskEntries[$index]);
				}
			}
		}

		foreach($this->executingTaskEntries as $index => $entry)
			$entry->action->updateTask();
	}

	private function startExecute(string $index) : void{
		$taskEntry = $this->taskEntries[$index];
		if($this->canUse($taskEntry) && $taskEntry->action->shouldExecute()){
			$taskEntry->action->startExecuting();
			$this->executingTaskEntries[$index] = $taskEntry;
		}
	}

	private function canContinue(EntityAITaskEntry $taskEntry) : bool{
		return $taskEntry->action->continueExecuting();
	}

	private function canUse(?EntityAITaskEntry $taskEntry) : bool{
		if($taskEntry === null) return false;
		foreach($this->taskEntries as $index => $entry){
			if($entry !== $taskEntry){
				if($taskEntry->priority >= $entry->priority){
					if (!$this->areTasksCompatible($taskEntry, $entry) && isset($this->executingTaskEntries[spl_object_hash($entry)])) {
						return false;
					}
				}elseif(!$entry->action->isInterruptible() && isset($this->executingTaskEntries[spl_object_hash($entry)])) {
					return false;
				}
			}
		}

		return true;
	}

	private function areTasksCompatible(EntityAITaskEntry $taskEntry, EntityAITaskEntry $taskEntry2) : bool{
		return ($taskEntry->action->getMutexBits() & $taskEntry2->action->getMutexBits()) == 0;
	}

}