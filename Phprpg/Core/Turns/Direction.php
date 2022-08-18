<?php

/**
 * having direction as a string is an annoying thing... but so is making an object for that? idk idk 
 * i guess i'll postpone implementing this, as it is a PITA
 *
 *
 * @author Nikita
 */

namespace Phprpg\Core\Turns;

class Direction {
    //put your code here
    
    private ?string $dirX;
    private ?string $dirY;
    
    public function __construct(string $dir1, ?string $dir2){
        
        $dir1axis = $this->checkAxis($dir1);
        $this->$dir1axis = $dir1;
        
        if (!empty($dir2)){
            $dir2axis = $this->checkAxis($dir2);
            if ($dir2axis != $dir1axis){
                $this->$dir2axis = $dir2;
            } else {
                throw new Exception("both direction strings are in the same axis, {$dir1} {$dir2}");
            }
            
        }
        
    }
    
    private function checkAxis($dir){
        $dir_arr = ['north'=>'dirY','south'=>'dirY','east'=>'dirX','west'=>'dirX'];
        
        if (!empty($dir_arr[$dir])){
            return $dir_arr[$dir];
        } else {
            throw new Exception("supplied direction string {$dir} is unorthodox");
        }
    }
    
    public function getSingleDirection(){
        
    }
    
}
