<?php
/**
 * entityIdArray format:
 * 
 * each array element represents either a tile id (starts with 1), or array of [tile id, entity id]
 * 
 * entities starting with 2 are mobs, with 3 are items, with 4 are players, but i don't think i will use 4
 * 
 * entities starting with 9 will be "special", so far i plan to use 999 as a player spawn point
 * 
 * 
 */


return [
    [
    'entityIdArray' => [
        [123,123,123,123,123,123,123,123,123],
        [123,123,123,124,124,124,123,123,123],
        [123,123,123,124,125,124,123,123,123],
        [123,124,124,124,[123,501],124,124,124,123],
        [123,124,125,[123,501],[123,999],[123,501],125,124,123],
        [123,124,124,124,[123,501],124,124,124,123],
        [123,123,123,124,125,124,123,123,123],
        [123,123,123,124,124,124,123,123,123],
        [123,123,123,123,123,123,123,123,123],
    ],
    'name' => 'Tutorial Level',
    'order'=>1,
    'maxItemCount'=>0,
    'maxMobCount'=>0,
    'victoryDefeatConditions' =>[
        'victory' => [
            'Phprpg\Core\Entities\PushableBlock'=>[
                'coordinatesMultipleByEntityId'=>['501'=>[[1,1]]]
            ]
            ]
            
        ]
    ],
    [
    'entityIdArray' => [
        [101,102,101,103,[101,201],104,101,105,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,101,101,101,101,101,101,101,101,[101,304],[101,304],[101,304],[101,304],[101,304],[101,304],101,101,101,101,101,101,101],
        [101,101,101,101,101,101,101,101,101,[101,999],[101,999],[101,999],[101,999],[101,999],101,101,101,101,101,101,101,101],
        [101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,116,101,102,101,102,102,102,101,102,101,101,101,102,101,101,101,102,102,102,101,101],
        [101,116,101,102,101,[101,501],101,101,101,102,101,101,101,102,101,101,101,102,101,102,101,101],
        [101,116,102,102,101,102,102,102,101,102,101,101,101,102,101,101,101,102,101,102,101,101],
        [101,116,101,102,101,102,101,101,101,102,101,101,101,102,101,101,101,102,101,102,101,101],
        [101,116,101,102,101,102,102,102,101,102,102,102,101,102,102,102,101,102,102,102,101,101],
        [101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,109,109,109,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,101,101,109,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,101,109,109,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,101,101,109,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,109,109,109,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,101,[101,202],[101,201],[101,201],[101,201],[101,201],[101,201],[101,201],101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
    ],
    'name' => 'Level 1',
    'order'=>2,
    'maxItemCount'=>20,
    'maxMobCount'=>20,
    'victoryDefeatConditions' =>[
        'victory' => [
            'Phprpg\Core\Entities\Player'=>[
                'stats'=>['level'=>3]
                ]
            ]
        ]
    ]
];