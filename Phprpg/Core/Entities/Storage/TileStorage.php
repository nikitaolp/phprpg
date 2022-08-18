<?php
namespace Phprpg\Core\Entities\Storage;

use Phprpg\Core\Turns\Coordinates;

class TileStorage extends GameEntityStorage{
    //put your code here
    
    
        //move to world class... actually, maybe should be in TileStorage , extending GameEntityStorage
    public function checkTileWalkability(Coordinates $coord):bool{
        $walkable = false;
        
        if (!empty($tile = $this->getEntity($coord))){
            $walkable = $tile->isWalkable();
        }
        
        return $walkable;
    }
    
    
    public function getRandomTileCoordinates():?Coordinates{
        $count_y = count($this->storage) - 1;
        $count_x = 0;
        if (!empty($this->storage[0])){
            $count_x = count($this->storage[0]) - 1;
            
            $random_y = rand(0,$count_y);
            $random_x = rand(0,$count_x);
            
            if (!empty($this->storage[$random_y][$random_x])){
                return new Coordinates($random_x,$random_y);
            }
            
        }
        
        return null;
        
    }
}
