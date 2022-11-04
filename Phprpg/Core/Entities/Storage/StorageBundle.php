<?php

namespace Phprpg\Core\Entities\Storage;

/**
 * Description of StorageBundle
 * 
 * organize storages into single array to perform collision stuff better
 * 
 * 
 * eh, i feel like this callable crutch i came up with is Worse than the good old class check
 * class check: 'Phprpg\Core\Entities\PushableBlock' != get_class($entity), if yes run moveBlock
 * if callable crutch: each class must have useless callable, 2 more methods, shit looking call user func thing... eeeh
 * better go back to original crutch
 * 
 */
class StorageBundle {
    
    private array $storages;
    
    public function __construct(){
        
    }
    
    public function addStorage(string $storage_type, GameEntityStorage $storage):void{
        $this->storages[$storage_type] = $storage;
    }
    
    public function moveEntity(GameEntity $entity, Coordinates $mobCoords, Coordinates $newCoords){
        
        foreach ($this->storages as $storage_type => $storage){
            if ($storedEntity = $storage->getEntity($newCoords)){
                    
                if ('Phprpg\Core\Entities\PushableBlock' != get_class($storedEntity)){
                    
                    if (!($storedEntity->collisionAction($entity))){
                        return false;
                    }
                    
                } else {
                    if (!($this->movePushableBlock($storedEntity,$mobCoords,$newCoords))){
                        return false;
                    }
                }
                 
            }
        }
        
        
        
        $this->getStorage('Mob')->moveEntity($mob,$mobCoords,$newCoords);
        
        return true;
    }
    
    public function getStorage(string $storage_type):?GameEntityStorage{
        
        if (!empty($this->storages[$storage_type])){
            return $this->storages[$storage_type];
        }
        
        return null;
    }
    
    
    private function movePushableBlock(PushableBlock $block, Coordinates $pushFromCoords, Coordinates $pushToCoords){
        
        $pushDirection = DirectionTools::getDirectionFromAtoB($pushFromCoords,$pushToCoords);
        $newBlockCoordinates = DirectionTools::$pushDirection($pushToCoords);
        
        $this->moveEntity($block,$pushToCoords,$newBlockCoordinates);
        
    }
}
