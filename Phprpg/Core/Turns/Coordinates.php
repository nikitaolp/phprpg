<?php
namespace Phprpg\Core\Turns;

class Coordinates {

    
    public function __construct(private int $x,private int $y){
        
    }
    
    public function __toString() {
        return $this->x.' '.$this->y;
    }
    
    public function setX(int $x){
        $this->x = $x;
    }
    
    public function setY(int $y){
        $this->y = $y;
    }
    
    public function getX(){
        return $this->x;
    }
    
    public function getY(){
        return $this->y;
    }
    
    
}
