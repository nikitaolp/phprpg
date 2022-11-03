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
        [101,102,101,103,[101,303],[101,303],[101,303],[101,303],[101,303],[101,303],[101,303],101,101,101,101,101,101,101,101,101,101,101],
        [101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,102,101,102,101,102,102,102,101,102,101,101,101,102,101,101,101,102,102,102,101,101],
        [101,102,101,102,101,102,101,101,101,102,101,101,101,102,101,101,101,102,101,102,101,101],
        [101,102,102,102,101,102,102,102,101,102,101,101,101,102,101,101,101,102,101,102,101,101],
        [101,102,101,102,101,102,101,101,101,102,101,101,101,102,101,101,101,102,101,102,101,101],
        [101,102,101,102,101,102,102,102,101,102,102,102,101,102,102,102,101,102,102,102,101,101],
        [101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,101,101,101,101,101,101,101,101,[101,999],[101,999],[101,999],[101,999],[101,999],101,101,101,101,101,101,101,101],
        [101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,101,101,[101,302],101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,101,[101,202],[101,201],[101,201],[101,201],[101,201],[101,201],[101,201],101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
    ],
    'name' => 'Tutorial Level',
    'order'=>1,
    'maxItemCount'=>50,
    'maxMobCount'=>50,
    'victoryDefeatConditions' =>[
        'victory' => [
            'Phprpg\Core\Entities\Player'=>[
                'stats'=>['level'=>2],
                'inventory'=>['gold'=>2],
                ]
            ]
            
        ]
    ],
    [
    'entityIdArray' => [
        [101,102,101,103,[101,201],104,101,105,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,101,101,101,101,101,101,101,101,[101,999],[101,999],[101,999],[101,999],[101,999],101,101,101,101,101,101,101,101],
        [101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101,101],
        [101,116,101,102,101,102,102,102,101,102,101,101,101,102,101,101,101,102,102,102,101,101],
        [101,116,101,102,101,102,101,101,101,102,101,101,101,102,101,101,101,102,101,102,101,101],
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
                'stats'=>['level'=>2]
                ]
            ]
        ]
    ]
];