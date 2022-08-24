<?php
namespace Phprpg\Core\Turns;

class Coordinates {

    
    public function __construct(private int $x,private int $y){
        
    }
    
    public function __toString():string {
        return $this->x.' '.$this->y;
    }
    
    public function setX(int $x):void{
        $this->x = $x;
    }
    
    public function setY(int $y):void{
        $this->y = $y;
    }
    
    public function getX():int{
        return $this->x;
    }
    
    public function getY():int{
        return $this->y;
    }
    
    
}
