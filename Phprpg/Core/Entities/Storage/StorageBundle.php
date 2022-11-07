<?php

namespace Phprpg\Core\Entities\Storage;
use Phprpg\Core\Entities\{GameEntity,Tile,Mob,Player,PushableBlock};
use Phprpg\Core\Turns\{DirectionTools,Coordinates};
use Phprpg\Core\{Lo};
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
    
    public function __construct(private TileStorage $tileStorage, array $storages_to_add = []){
        
        if (!empty($storages_to_add)){
            foreach ($storages_to_add as $type => $storage){
                $this->setStorage($type,$storage);
            }
        }
        
    }
    
    public function setStorage(string $storage_type, GameEntityStorage $storage):void{
        
        if ($storage_type == 'Tile'){
            $this->tileStorage = $storage;
            return;
        }
        
        $this->storages[$storage_type] = $storage;
    }
    
    private function checkTileWalkability(Coordinates $coords){
        
        if (empty($this->getStorage('Tile')->getEntity($coords)) || !$this->getStorage('Tile')->getEntity($coords)->isWalkable()){
            return false;
        }
        return true;
    }
    /**
     * 
     * @param GameEntity $entity
     * @param Coordinates $mobCoords
     * @param Coordinates $newCoords
     * @return bool|null returns TRUE if collision action performed and entity moved to new coordinates, FALSE if action performed but no movement, and NULL if no actions performed
     */
    public function collisionActionCheck(GameEntity $entity, Coordinates $mobCoords, Coordinates $newCoords):?bool{
        
        if ($this->checkTileWalkability($newCoords)){
            
            $action_performed = [];
            $movement_possible = [];
            
            
            foreach ($this->storages as $storage_type => $storage){
                
                if ($storedEntity = $storage->getEntity($newCoords)){
                
                    if ('PushableBlock' == $storage_type){
                        $collision = $this->movePushableBlock($storedEntity,$mobCoords,$newCoords);
                        Lo::gG(gettype($collision));
                    } else {
                        $collision = $storedEntity->collisionAction($entity);
                    }
                    
                    //$action_performed[$storage_type] = $collision;

                    if ($collision){
                        $movement_possible[$storage_type] = true;
                        $action_performed[$storage_type] = true;
                    } else if (!is_null($collision)){
                        $action_performed[$storage_type] = true;
                        $movement_possible[$storage_type] = false;
                    } else {
                        $movement_possible[$storage_type] = false;
                    }
                
                }
                
            }
            
            $entity_class_name = substr(strrchr(get_class($entity), '\\'), 1);
            
            
            if (!in_array(false,$movement_possible)){

                if ($entity_class_name != 'PushableBlock'){
                    $this->getStorage('Mob')->moveEntity($entity,$mobCoords,$newCoords);
                } else {

                    $this->getStorage($entity_class_name)->moveEntity($entity,$mobCoords,$newCoords);
                }
                return true;
            }
            
            if (in_array(true,$action_performed)){
                return false;
            } 
            
        }
        
        return null;
    }
    
    
    
    public function getStorage(string $storage_type):?GameEntityStorage{
        
        if ($storage_type == 'Tile'){
            return $this->tileStorage;
        }
        
        if (!empty($this->storages[$storage_type])){
            return $this->storages[$storage_type];
        }
        
        return null;
    }
    
    
    private function movePushableBlock(PushableBlock $block, Coordinates $pushFromCoords, Coordinates $pushToCoords){
        
        $pushDirection = DirectionTools::getDirectionFromAtoB($pushFromCoords,$pushToCoords);
        $newBlockCoordinates = DirectionTools::$pushDirection($pushToCoords);
        
        return $this->collisionActionCheck($block,$pushToCoords,$newBlockCoordinates);
        
    }
}
