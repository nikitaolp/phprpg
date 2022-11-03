<?php

namespace Phprpg\Core\Entities\Storage;

/**
 * Description of StorageStorage
 * 
 * Storage that stores storages... Maybe it should be StorageWarehouse? Lol. But actually... StorageStorage is not a child of GameEntityStorage, so it is kind of not storage
 * Ah, maybe, StorageBundle? But Bundle is something from Symfony or whatever
 *
 * it's supposed to accept a bunch of GameEntityStorage objects and be responsible for their intersection
 * 
 * 
 * ok what was i about to do...
 * i think... 
 * uhhh...
 * ummm...
 * 
 * so entities should have a method like 
 * beInteractedWith or beAffectedBy, getSteppedOn, getPushedBy... maybe receiveActionFrom
 * getTriggeredBy - huh! 
 * 
 * that would affect entity and the incoming mob
 * 
 * soo...
 * 
 * like Tile - checks if walkable
 * Mob - getAttacked, something like that
 * item -> action , we have it like this i guess
 * 
 * but... what should it accept? what should it return?
 * 
 * and how do i process interaction that concerns multiple entities?
 * 
 * llike, checked if the tile is walkable, then checked if there is a mob ther to attack
 * uuuh i haven't touched this code for a bit too long
 */
class StorageBundle {
    
    private array $storages;
    
    public function __construct(){
        
    }
    
    public function addStorage(string $storage_type, GameEntityStorage $storage){
        $this->storages[$storage_type] = $storage;
    }
    
    public function moveMob(Mob $mob, Coordinates $mobCoords, Coordinates $newCoords){
        
        foreach ($this->storages as $storage_type => $storage){
            if ($storedEntity = $storage->getEntity($newCoords)){
                 if (!$storedEntity->receiveAction($mob)){
                     return false;
                 }
                 
            }
        }
        
        $this->getStorage('Mob')->moveEntity($mob,$mobCoords,$newCoords);
        
    }
}
