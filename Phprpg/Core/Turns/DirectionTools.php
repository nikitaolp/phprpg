<?php

/**
 * 
 * This class should be made static and be called like DirectionTools or something
 * it doesn't have a state, only methods related to coordinates and directions 
 *
 * @author Nikita
 */

namespace Phprpg\Core\Turns;

use Phprpg\Core\Turns\{Coordinates};

class DirectionTools {
    
    protected static $directions = ['north'=>'north','south'=>'south','east'=>'east','west'=>'west'];
    
    public static function getRandomDirectionsArray(array $exclude = []){
        $shuffled = self::$directions; 
        uksort($shuffled, function() { return rand() <=> rand(); });
        
        foreach ($exclude as $baddir){
            unset($shuffled[$baddir]);
        }
        
        return $shuffled;
    }
    
    public static function getRandom(){
        $dir = self::$directions; 
        
        return $dir[array_rand($dir)];
    }
    
    
    public static function north(Coordinates $coord):Coordinates{
        $coo = new Coordinates($coord->getX(),($coord->getY()-1));
        return $coo;
    }
    
    public static function south(Coordinates $coord):Coordinates{
        $coo = new Coordinates($coord->getX(),($coord->getY()+1));
        return $coo;
    }
    
    public static function east(Coordinates $coord):Coordinates{
        $coo = new Coordinates(($coord->getX()+1),($coord->getY()));
        return $coo;
    }
    
    public static function west(Coordinates $coord):Coordinates{
        $coo = new Coordinates(($coord->getX()-1),($coord->getY()));
        return $coo;
    }
    
    public static function surroundingTiles(Coordinates $coord,int $worldWidth, int $worldHeight){
        $surroundingTilesCoordArray = [];

        for ($y = $coord->getY()-5; $y<=$coord->getY()+5; $y++){
            if ($y<0 || $y>=$worldHeight){
                continue;
            }
            for ($x = $coord->getX()-5; $x<=$coord->getX()+5; $x++){
                if (($x<0 || $x>=$worldWidth) || ($coord->getX() == $x && $coord->getY() == $y)){
                    continue;
                }
                $surroundingTilesCoordArray[] = new Coordinates($x,$y);
            }
            
        }
        return $surroundingTilesCoordArray;
    }
    
    
    public static function checkDirection(Coordinates $mobCoord, Coordinates $mobToCheck,int $sight):array{
        
        $direction = [];
        
        if (($mobCoord!=$mobToCheck)){
            
            if ($mobCoord->getX() == $mobToCheck->getX()){
                //same X axis, check north or south
                
                if ($mobToCheck->getY() <= $mobCoord->getY()){
                    // north
                    if (self::checkNorthMobInSight($mobCoord, $mobToCheck, $sight)){
                        $direction['north'] = 'north';
                    }
                    
                } else {
                    // south
                    if (self::checkSouthMobInSight($mobCoord, $mobToCheck, $sight)){
                        $direction['south'] = 'south';
                    }
                    
                }
                
                
            } else if ($mobCoord->getY() == $mobToCheck->getY()){
                //same Y axis, check east or west
                
                if ($mobToCheck->getX() >= $mobCoord->getX()){
                    // east
                    if (self::checkEastMobInSight($mobCoord, $mobToCheck, $sight)){
                        $direction['east'] = 'east';
                    }
                } else {
                    // west
                    if (self::checkWestMobInSight($mobCoord, $mobToCheck, $sight)){
                        $direction['west'] = 'west';
                    }
                }
                
            } else if (($mobToCheck->getY() >= $mobCoord->getY())){
                //south
                if ($mobToCheck->getX() <= $mobCoord->getX()){
                    //south west
                    if(self::checkSouthWestMobInSight($mobCoord, $mobToCheck, $sight)){
                        $direction['south'] = 'south';
                        $direction['west'] = 'west';
                    }
                    
                } else {
                    //south east
                    if(self::checkSouthEastMobInSight($mobCoord, $mobToCheck, $sight)){
                        $direction['south'] = 'south';
                        $direction['east'] = 'east';
                    }
                }
            } else if (($mobToCheck->getY() <= $mobCoord->getY())){
                //north
                if (($mobToCheck->getX() <= $mobCoord->getX())){
                    //north west
                    if(self::checkNorthWestMobInSight($mobCoord, $mobToCheck, $sight)){
                        $direction['north'] = 'north';
                        $direction['west'] = 'west';
                    }
                } else {
                    //north east
                    if (self::checkNorthEastMobInSight($mobCoord, $mobToCheck, $sight)){
                        $direction['north'] = 'north';
                        $direction['east'] = 'east';
                    }
                }
                
            }
            
        } else {
            Lo::g("Trying to get direction towards same tile ".$mobCoord.' '.$mobToCheck);
        }
        shuffle($direction);
        return $direction;
        
    }
    
    
    public static function getOpposite(array $dirs):array{
        $ret = [];
        foreach ($dirs as $v){
            $ret[] = match ($v) {
                'north' => 'south',
                'south' => 'north',
                'east' => 'west',
                'west'=>'east'
            };
        }
        
        return $ret;
    }
    
    private static function checkNorthMobInSight(Coordinates $mobCoord, Coordinates $mobToCheck,int $sight,$debug = false){
        if ($mobToCheck->getY()+$sight >= $mobCoord->getY()){
            $dbg = '';
            if ($debug) $dbg = 'color:red;';
            //echo "<p style='$dbg'>this mob ({$mobCoord->getX()} {$mobCoord->getY()}) sees another one in North ({$mobToCheck->getX()} {$mobToCheck->getY()})</p>";
            return true;
        } 
    }
    
    private static function checkSouthMobInSight(Coordinates $mobCoord, Coordinates $mobToCheck,int $sight,$debug = false){
        if ($mobToCheck->getY()-$sight <= $mobCoord->getY()){
            $dbg = '';
            if ($debug) $dbg = 'color:red;';
             //echo "<p style='$dbg'>this mob ({$mobCoord->getX()} {$mobCoord->getY()}) sees another one in South ({$mobToCheck->getX()} {$mobToCheck->getY()})</p>";
             return true;
        } 

    }
    
    private static function checkWestMobInSight(Coordinates $mobCoord, Coordinates $mobToCheck,int $sight,$debug = false){
        if ($mobToCheck->getX()+$sight >= $mobCoord->getX()){
            $dbg = '';
            if ($debug) $dbg = 'color:red;';
             //echo "<p style='$dbg'>this mob ({$mobCoord->getX()} {$mobCoord->getY()}) sees another one in West ({$mobToCheck->getX()} {$mobToCheck->getY()})</p>";
             return true;
        } 
    }
    
    private static function checkEastMobInSight(Coordinates $mobCoord, Coordinates $mobToCheck,int $sight,$debug = false){
        if ($mobToCheck->getX()-$sight <= $mobCoord->getX()){
            $dbg = '';
            if ($debug) $dbg = 'color:red;';
             //echo "<p style='$dbg'>this mob ({$mobCoord->getX()} {$mobCoord->getY()}) sees another one in East ({$mobToCheck->getX()} {$mobToCheck->getY()})</p>";
             return true;
        } 
    }
    
    private static function checkSouthWestMobInSight(Coordinates $mobCoord, Coordinates $mobToCheck,int $sight){
        if (self::checkSouthMobInSight($mobCoord, $mobToCheck, $sight,true) && self::checkWestMobInSight($mobCoord, $mobToCheck, $sight,true)){
            //echo "<p>this mob ({$mobCoord->getX()} {$mobCoord->getY()}) sees another one in South West ({$mobToCheck->getX()} {$mobToCheck->getY()})</p>";
            return true;
        } 
    }
    
    private static function checkSouthEastMobInSight(Coordinates $mobCoord, Coordinates $mobToCheck,int $sight){
        if (self::checkSouthMobInSight($mobCoord, $mobToCheck, $sight,true) && self::checkEastMobInSight($mobCoord, $mobToCheck, $sight,true)){
            //echo "<p>this mob ({$mobCoord->getX()} {$mobCoord->getY()}) sees another one in South East ({$mobToCheck->getX()} {$mobToCheck->getY()})</p>";
            return true;
        } 
    }
    
    
    private static function checkNorthWestMobInSight(Coordinates $mobCoord, Coordinates $mobToCheck,int $sight){
        if (self::checkNorthMobInSight($mobCoord, $mobToCheck, $sight,true) && self::checkWestMobInSight($mobCoord, $mobToCheck, $sight,true)){
            //echo "<p>this mob ({$mobCoord->getX()} {$mobCoord->getY()}) sees another one in North West ({$mobToCheck->getX()} {$mobToCheck->getY()})</p>";
            return true;
        } 
    }
    
    private static function checkNorthEastMobInSight(Coordinates $mobCoord, Coordinates $mobToCheck,int $sight){
        if (self::checkNorthMobInSight($mobCoord, $mobToCheck, $sight,true) && self::checkEastMobInSight($mobCoord, $mobToCheck, $sight,true)){
            //echo "<p>this mob ({$mobCoord->getX()} {$mobCoord->getY()}) sees another one in North East ({$mobToCheck->getX()} {$mobToCheck->getY()})</p>";
            return true;
        } 
    }

    
    /*
    public function checkNorthEast(Coordinates $mobCoord, Coordinates $mobToCheck,int $sight){
        
        if (($mobCoord!=$mobToCheck) && ($mobCoord->getX()+$sight >= $mobToCheck->getX()) && ($mobCoord->getY()-$sight >= $mobToCheck->getY())){
            echo "<p>this mob ({$mobCoord->getX()} {$mobCoord->getY()}) sees another one at North East ({$mobToCheck->getX()} {$mobToCheck->getY()})</p>";
        }
    }
    
    public function checkNorthWest(Coordinates $mobCoord, Coordinates $mobToCheck,int $sight){
        
        if (($mobCoord!=$mobToCheck) && ($mobCoord->getX()-$sight >= $mobToCheck->getX()) && ($mobCoord->getY()-$sight > $mobToCheck->getY())){
            echo "<p>this mob ({$mobCoord->getX()} {$mobCoord->getY()}) sees another one at North West ({$mobToCheck->getX()} {$mobToCheck->getY()})</p>";
        }
    }
    
    public function checkSouthEast(Coordinates $mobCoord, Coordinates $mobToCheck,int $sight){
        
        if (($mobCoord!=$mobToCheck) && ($mobCoord->getX()+$sight > $mobToCheck->getX()) && ($mobCoord->getY()+$sight >= $mobToCheck->getY())){
            echo "<p>this mob ({$mobCoord->getX()} {$mobCoord->getY()}) sees another one at South East ({$mobToCheck->getX()} {$mobToCheck->getY()})</p>";
        }
    }
    
    public function checkSouthWest(Coordinates $mobCoord, Coordinates $mobToCheck,int $sight){
        
        if (($mobCoord!=$mobToCheck) && ($mobCoord->getX()+$sight > $mobToCheck->getX()) && ($mobCoord->getY()+$sight > $mobToCheck->getY())){
            echo "<p>this mob ({$mobCoord->getX()} {$mobCoord->getY()}) sees another one at South West ({$mobToCheck->getX()} {$mobToCheck->getY()})</p>";
        }
    }
     * *
     */
}
