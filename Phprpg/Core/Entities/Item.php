<?php
namespace Phprpg\Core\Entities;

class Item extends GameEntity{
    //put your code here
    
    protected int $amount = 0;
    
    
    public function __construct(
            protected string $name,
            protected string $gfx,
            protected int $entity_id, 
            protected string $desc,
            protected int $chance,
            private ?bool $desireable,
            protected array $action) {
        if (!empty($action['inventory'])){
            $this->amount = $action['inventory'];
        }
    }
    
    public function getAction():array{
        return $this->action;
    }
    
    public function getAmount():int{
        return $this->amount;
    }
    
    public function add(int $addAmount):void{
        $this->amount += $addAmount;
    }
    
    public function isDesireable():?bool{
        return $this->desireable;
    }
    
    public function __toString():string {
        return $this->name." (".$this->desc."): ".$this->amount;
    }
}
