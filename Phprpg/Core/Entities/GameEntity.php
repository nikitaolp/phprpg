<?php
namespace Phprpg\Core\Entities;

abstract class GameEntity {
    
    protected string $gfx;
    protected int $entity_id;
    protected string $desc;
    protected string $name;
    protected int $chance;
    private bool $expired = false;
   
    
    private function __construct(

            ){

    }
    
    public function __toString():string {
        return $this->name.": ".$this->desc;
    }
    
    public function getGfx():string{
        return $this->gfx;
    }
    
    
    public function getEntityId():string{
        return $this->entity_id;
    }
    
     public function getChance():string{
        return $this->chance;
    }
    
    public function getName():string{
        return $this->name;
    }
    
    public function expire():void{
        $this->expired = true;
    }
    
    
    public function isExpired():bool{
        return $this->expired;
    }
    
    abstract function collisionAction(GameEntity $entity):bool;
}
