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
    
    public function moveEntityV3(GameEntity $entity, Coordinates $mobCoords, Coordinates $newCoords):?bool{
        
        if ($this->checkTileWalkability($newCoords)){
            
            $action_performed = [];
            $movement_possible = [];
            
            
            foreach ($this->storages as $storage_type => $storage){
                
                if ($storedEntity = $storage->getEntity($newCoords)){
                
                    if ('PushableBlock' == $storage_type){
                        $collision = $this->movePushableBlock($storedEntity,$mobCoords,$newCoords);
                    } else {
                        $collision = $storedEntity->collisionAction($entity);
                    }
                    
                    $action_performed[$storage_type] = $collision;

                    if ($collision){
                        $movement_possible[$storage_type] = true;
                        $action_performed[$storage_type] = true;
                    } else if ($collision == false){
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
    
    /**
     * this code is MUCH worse than what you had before StorageBundle thing. not even funny. although it didn't have recursive block pushing back then
     * @param GameEntity $entity
     * @param Coordinates $mobCoords
     * @param Coordinates $newCoords
     * @return boolean if true - entity moved or collision actions performed
     */
    public function moveEntityV2(GameEntity $entity, Coordinates $mobCoords, Coordinates $newCoords){
        
        if (empty($this->getStorage('Tile')->getEntity($newCoords))){
            return false;
        }
        
        $action_performed = [];
        $movement_possible = [];
        
        $tile = $this->getStorage('Tile')->getEntity($newCoords);
        
        if ($tile && $tile->collisionAction($entity)){
            $movement_possible['tile'] = true;
        } else {
            return false;
        }
        
        
        $item = $this->getStorage('Item')->getEntity($newCoords);
        
        if ($item){
            if ($item->collisionAction($entity)){
                $action_performed['item'] = true;
                $movement_possible['item'] = true;
            } else {
                $movement_possible['item'] = false;
            }
            
        }
        
        $pushable = $this->getStorage('PushableBlock')->getEntity($newCoords);
        
        if ($pushable){
            
            $pushable_collision = $this->movePushableBlock($pushable,$mobCoords,$newCoords);
            $action_performed['pushable'] = $pushable_collision;
            if ($pushable_collision) {
                $movement_possible['pushable'] = true;
            } else {
                $movement_possible['pushable'] = false;
            }
            
            
            
            
        }
        
        $mob = $this->getStorage('Mob')->getEntity($newCoords);
        
        if ($mob){
            $mob_collision = $mob->collisionAction($entity);
            $action_performed['mob'] = $mob_collision;
            
            if ($mob_collision && $mob->isExpired()){
                $this->getStorage('Mob')->unsetEntity($newCoords);
                $movement_possible['mob'] = true;
            } else {
                $movement_possible['mob'] = false;
            }
            
        }
        $entity_class_name = substr(strrchr(get_class($entity), '\\'), 1);
        Lo::gG($entity_class_name);
        Lo::gG($movement_possible);
        Lo::gG($action_performed);
        if (!in_array(false,$movement_possible)){
            
            $action_performed['tile'] = true;
            
            if ($entity_class_name != 'PushableBlock'){
                $this->getStorage('Mob')->moveEntity($entity,$mobCoords,$newCoords);
            } else {
                
                $this->getStorage($entity_class_name)->moveEntity($entity,$mobCoords,$newCoords);
            }
            
        }
        
       if (in_array(true,$action_performed)){
           return true;
       } 
       return false;
    }
    
    public function moveEntityOld(GameEntity $entity, Coordinates $mobCoords, Coordinates $newCoords){
        
        if (empty($this->getStorage('Tile')->getEntity($newCoords))){
            return false;
        }
        
        foreach ($this->storages as $storage_type => $storage){
            if ($storedEntity = $storage->getEntity($newCoords)){
                    
                if ('Phprpg\Core\Entities\PushableBlock' != get_class($storedEntity)){
                    
                    $movementPossible = $storedEntity->collisionAction($entity);
                    
                    if ($storedEntity->isExpired()){
                        $storage->unsetEntity($newCoords);
                    }
                    
                    
                    if (!$movementPossible){
                        return false;
                    }
                    
                } else {
                    if (!($this->movePushableBlock($storedEntity,$mobCoords,$newCoords))){
                        return false;
                    }
                }
                 
            }
        }
        
        $entity_class_name = substr(strrchr(get_class($entity), '\\'), 1);
        

        if ($entity_class_name != 'PushableBlock'){
            
            //ok this is bad. so, the problem: collisionAction should ideally return 3 states:
            // "action completed, movement allowed", "action completed, movement forbidden" (successful attack but no kill)", "action failed"
            // however, i only have true/false . so for now i just treat it as completed/failed, and check for "no kill" here. Bad bad.
            //this doesn't 100% solve the fucking issue
            
            if (!($this->getStorage('Mob')->getEntity($newCoords)) || $this->getStorage('Mob')->getEntity($newCoords)->isExpired()){
                $this->getStorage('Mob')->moveEntity($entity,$mobCoords,$newCoords);
                return true;
            }
            
        } else {
            
            
            $this->getStorage($entity_class_name)->moveEntity($entity,$mobCoords,$newCoords);
            return true;
            
            
        }
        
        
        
        return false;
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
        
        return $this->moveEntityV3($block,$pushToCoords,$newBlockCoordinates);
        
    }
}
