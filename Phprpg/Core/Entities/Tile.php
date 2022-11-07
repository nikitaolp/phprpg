<?php
namespace Phprpg\Core\Entities;

class Tile extends GameEntity{
    
    protected array $onTile = [];

    
    public function __construct(
            protected string $name,
            protected bool $walkable,
            protected string $gfx,
            protected int $entity_id, 
            protected string $desc,
            protected int $chance) {
        
    }
    
    public function isWalkable():bool{
        return $this->walkable;
    }
    
    public function collisionAction(GameEntity $entity): ?bool {
        
        if ($this->isWalkable()){
            return true;
        }
        
        return null;
    }
}