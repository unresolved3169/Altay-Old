<?php

namespace pocketmine\behaviorpacks\handler\entity;

use pocketmine\entity\Entity;

interface EntityBehaviorHandler{
 
 public function applyToEntity(Entity $entity, $data) : void;
}
?>
