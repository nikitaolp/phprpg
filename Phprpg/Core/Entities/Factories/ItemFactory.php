<?php
namespace Phprpg\Core\Entities\Factories;
use Phprpg\Core\Entities\Item;

class ItemFactory extends GameEntityFactory {
    //put your code here
    
    public function fromArray(array $array):void {
        foreach ($array as $name=>$itemArray){
            $this->blueprints[$name] = new Item(
                    $name,
                    $itemArray['gfx'],
                    $itemArray['entity_id'],
                    $itemArray['desc'],
                    $itemArray['chance'],
                    $itemArray['desireable'],
                    $itemArray['action']);
        } 
        
    }
}