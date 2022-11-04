<?php
namespace Phprpg\Core\Entities;

class PushableBlock extends GameEntity {
    
    public function __construct(
            protected string $name,
            protected string $gfx,
            protected int $entity_id, 
            protected string $desc,
            protected int $chance) {
        
    }
    
    public function collisionAction(GameEntity $entity): bool {
        return false;
    }
    
}
