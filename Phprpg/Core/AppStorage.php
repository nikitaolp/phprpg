<?php

namespace Phprpg\Core;

class AppStorage {
    //put your code here
    protected static $storage = [];
    
    public static function set($k,$v){
        static::$storage[$k]=$v;
    }
    
    public static function get($k,$v=null){

        if (array_key_exists($k, static::$storage)){

            if ( $v ){
                if (array_key_exists($v, static::$storage[$k])){
                    return static::$storage[$k][$v];
                } throw new Exception ("No {$k} {$v} in the storage");
                
            } 
            
            return static::$storage[$k];
        } else {
            return self::resolveClass($k);
        }
        
        
    }
    
    
    //this basic DI Container is not used
    private static function resolveClass($k){

        $ref = new \ReflectionClass($k);
        if (!$ref->isInstantiable()){
            throw new Exception("$k is not instantiable");
        }
        
        if (!($constr = $ref->getConstructor()) || !($params = $constr->getParameters())){
            return new $k;
        }
        
        $dependencies = [];
        
        foreach ($params as $param){
            $name = $param->getName();
            $type = $param->getType();
            

            
            if (!$type){
                throw new Exception("Can't instantiate $k because $name is not type hinted");
            }
            if ($type instanceof \ReflectionUnionType){
                throw new Exception("Can't instantiate $k because $type is union type");
            }
            
            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()){
                $dependencies[] = self::get($type->getName());
            } else {
                throw new Exception("Can't instantiate $k because $type or $name or whatever is wrong");
            }
            
            
        }
        
        return $ref->newInstanceArgs($dependencies);
    }
    
}
