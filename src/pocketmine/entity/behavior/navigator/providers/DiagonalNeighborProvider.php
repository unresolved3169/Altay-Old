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

namespace pocketmine\entiy\behavior\navigator\providers;

use pocketmine\utils\navigator\Tile;

class DiagonalNeighborProvider implements NeighborProvider{

    protected $neighbors = [
        [0, -1],
        [1, 0],
        [0, 1],
        [-1, 0],
        [-1, -1],
        [1, -1],
        [1, 1],
        [-1, 1]
    ];

    public function getNeighbors(Tile $tile) : array{
        $result = [];

        for($i = 0; $i < count($this->neighbors); $i++){
            $xy = $this->neighbors[$i];
            $result[] = new Tile($xy[0], $xy[1]);
        }

        return $result;
    }
}
