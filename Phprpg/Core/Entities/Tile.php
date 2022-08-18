<?php
namespace Phprpg\Core\Entities;

class Tile extends GameEntity{
    
    protected array $onTile = [];

    
    public function __construct(
            protected string $name,
            protected bool $walkable,
            protected string $gfx,
            protected string $char, 
            protected string $desc,
            protected int $chance) {
        
    }
    
    public function isWalkable():bool{
        return $this->walkable;
    }
}