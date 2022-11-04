<?php

namespace Phprpg\Core\Entities\Factories;

use Phprpg\Core\Entities\PushableBlock;

/**
 * Description of PushableBlockFactory
 *
 * @author Nikita
 */
class PushableBlockFactory extends GameEntityFactory {
    
    public function fromArray(array $array):void {
        foreach ($array as $name=>$blockArray){
            $this->blueprints[$blockArray['entity_id']] = new PushableBlock(
                    $name,
                    $blockArray['gfx'],
                    $blockArray['entity_id'],
                    $blockArray['desc'],
                    $blockArray['chance']);
        } 
        
    }
    
}












