<?php
namespace Phprpg\Core\Entities\Factories;

use Phprpg\Core\Entities\Tile;

class TileFactory extends GameEntityFactory {
    //put your code here
    
    public function fromArray(array $array):void {
        foreach ($array as $name=>$tileArray){
            $this->blueprints[$tileArray['entity_id']] = new Tile(
                    $name,
                    $tileArray['walkable'],
                    $tileArray['gfx'],
                    $tileArray['entity_id'],
                    $tileArray['desc'],
                    $tileArray['chance']);
        } 
        
    }
}
