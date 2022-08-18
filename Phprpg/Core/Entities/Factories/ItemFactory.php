<?php
namespace Phprpg\Core\Entities\Factories;
use Phprpg\Core\Entities\Item;

class ItemFactory extends GameEntityFactory {
    //put your code here
    
    public function fromArray(array $array) {
        foreach ($array as $name=>$itemArray){
            $this->blueprints[$name] = new Item(
                    $name,
                    $itemArray['gfx'],
                    $itemArray['char'],
                    $itemArray['desc'],
                    $itemArray['chance'],
                    $itemArray['desireable'],
                    $itemArray['action']);
        } 
        
    }
}