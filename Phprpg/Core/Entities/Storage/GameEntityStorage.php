<?php
/**
 * Have to create some kind of storage to get rid of storing coordinates within Mob objects which caused major performance hit on large mob count
 *
 * @author Nikita
 */
namespace Phprpg\Core\Entities\Storage;

use Phprpg\Core\Lo;
use Phprpg\Core\Entities\GameEntity;
use Phprpg\Core\Turns\Coordinates;

class GameEntityStorage {
    //put your code here
    protected array $storage = [];
    
    
    public function storeAtXY(GameEntity $ent,int $x, int $y):void{
            $this->storage[$y][$x] = $ent;
    }
    
    public function getEntities():array{
        return $this->storage;
    }
    
    public function setEntities(array $entities){
        $this->storage = $entities;
    }
    
    public function getEntity(Coordinates $coord):?GameEntity{
        return $this->getEntityByXY($coord->getX(),$coord->getY());
    }
    
    public function getEntityByXY($x,$y):?GameEntity{
        if(!empty($this->storage[$y][$x])){
            
            
            if (!$this->isEntityActive($x, $y)){
                return null;
            }
            
            return $this->storage[$y][$x];
        }
        return null;
    }
    
    public function getSurroundingEntities(Coordinates $coord,$range){
        return $this->getSurroundingEntitiesByXY($coord->getX(),$coord->getY(), $range);
    }
    
    public function getSurroundingEntitiesByXY(int $x, int $y,int $range):array{
        $surrounding = [];

        for($iy=$y-$range;$iy<=$y+$range;$iy++){
            
            for($ix=$x-$range;$ix<=$x+$range;$ix++){
                if(!empty($this->storage[$iy][$ix])){

                    $surrounding[$iy][$ix] = $this->storage[$iy][$ix];
                }
            }
        }
        
        return $surrounding;
    }
    
    public function getDirectlyAccessibleEntities(Coordinates $coord):array{
        
        $x = $coord->getX();
        $y = $coord->getY();
        
        $surrounding = [];
        
        if(!empty($this->storage[$y][$x+1])){
             $surrounding[$y][$x+1] = $this->storage[$y][$x+1];
        }
        
        if(!empty($this->storage[$y][$x-1])){
             $surrounding[$y][$x-1] = $this->storage[$y][$x-1];
        }
        
        if(!empty($this->storage[$y+1][$x])){
             $surrounding[$y+1][$x] = $this->storage[$y+1][$x];
        }
        
        if(!empty($this->storage[$y-1][$x])){
             $surrounding[$y-1][$x] = $this->storage[$y-1][$x];
        }
        //Lo::g("getDirectlyAccessibleEntities");
        //Lo::g($surrounding);
        return $surrounding;
    }
    
    public function getAllEntityCount():int{
        $count = 0;
            
        foreach ($this->storage as $y=>$yval){
            
            foreach ($yval as $x =>$val){
                
                if ($this->isEntityActive($x, $y)){
                    $count++;
                }
                
                
            } 
            
        }
        
        return $count;
    }
    
    public function moveEntity(GameEntity $entity, Coordinates $oldCoord, Coordinates $newCoord):void{
        
        if ($this->isEntityActive($oldCoord->getX(), $oldCoord->getY())){
            $this->storage[$newCoord->getY()][$newCoord->getX()] = $entity;
        }
        
        unset($this->storage[$oldCoord->getY()][$oldCoord->getX()]);
        
    }
    
    public function unsetEntity(Coordinates $coord){
        
        $x = $coord->getX();
        $y = $coord->getY();
        
        unset($this->storage[$y][$x]);
    }
    
    
    public function clearStorage():void{
        $this->storage = [];
    }
    /**
     * need this wrapper to replace all existing expiration checks. so i could override and do actions before unsetting
     * @param int $x
     * @param int $y
     * @return bool
     */
    private function isEntityActive(int $x,int $y):bool{
        
        if (empty($this->storage[$y][$x])){
            return false;
        }
        
        if ($this->storage[$y][$x]->isExpired()){
            $this->unsetEntity(new Coordinates($x, $y));
            return false;
        }
        
        return true;
    }
    
}
