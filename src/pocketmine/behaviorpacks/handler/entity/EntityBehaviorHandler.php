<?php

namespace pocketmine\behaviorpacks\handler\entity;

use pocketmine\entity\Entity;

interface EntityBehaviorHandler{
 
 public function applyData(Entity $entity, $data) : void;
}
?>
