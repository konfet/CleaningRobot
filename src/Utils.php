<?php

namespace app;
class Utils {
    
//  facing of robot
    const D_NORTH = 'N';
    const D_SOUTH = 'S';
    const D_WEST = 'W';
    const D_EAST = 'E';
         
//  the turn to the left or to the right
    const LEFT = 'L';
    const RIGHT = 'R';
    

//  get new facing after the turn
    
    const D_AFTER_TURN = [
        self::D_EAST => [
            self::LEFT => self::D_NORTH,
            self::RIGHT => self::D_SOUTH,
        ],
        self::D_WEST => [
            self::LEFT => self::D_SOUTH,
            self::RIGHT => self::D_NORTH,
        ],
        self::D_NORTH => [
            self::LEFT => self::D_WEST,
            self::RIGHT => self::D_EAST,
        ],
        self::D_SOUTH => [
            self::LEFT => self::D_EAST,
            self::RIGHT => self::D_WEST,
        ],
    ];
    
    const CELL_TYPES = ['S', 'C', 'null'];
    const ACTION_TYPES = ['TR', 'TL', 'A', 'B', 'C'];    
    const BATTERY_COSTS = ['TR' => 1, 'TL' => 1, 'A' => 2, 'B' => 3, 'C' => 5];
    
    const STUCK_STAGES = [
        1 => ['TR', 'A', 'TL'],  
        2 => ['TR', 'A', 'TR'],
        3 => ['TR', 'A', 'TR'],  
        4 => ['TR', 'B', 'TR','A'],  
        5 => ['TL', 'TL', 'A']
    ];
    
    const MAX_TEST_SIZE = 100;
    //  __DIR__ . '/../Files/result.json'
    
    public static function compareResults($json1, $json2) {
        $content1 = json_decode(file_get_contents($json1), true);
        $content2 = json_decode(file_get_contents($json2), true);
        
        if ($content1['battery'] != $content2['battery'])   
            return 0;
        if ($content1['final']['X'] != $content2['final']['X'] || 
                    $content1['final']['Y'] != $content2['final']['Y'] || 
                    $content1['final']['facing'] != $content2['final']['facing']) 
            return 0;
        
        $arr1 =  array_map(function($val) {return self::MAX_TEST_SIZE*$val['X'] + $val['Y'];}, $content1['visited']);
        $arr2 =  array_map(function($val) {return self::MAX_TEST_SIZE*$val['X'] + $val['Y'];}, $content2['visited']);
        
        if (count(array_diff($arr1, $arr2)) != 0 || count(array_diff($arr2, $arr1)) != 0) return 0;
        
        $arr1 =  array_map(function($val) {return self::MAX_TEST_SIZE*$val['X'] + $val['Y'];}, $content1['cleaned']);
        $arr2 =  array_map(function($val) {return self::MAX_TEST_SIZE*$val['X'] + $val['Y'];}, $content2['cleaned']);
        
        if (count(array_diff($arr1, $arr2)) != 0 || count(array_diff($arr2, $arr1)) != 0) return 0;

        return 1;        
    }
    
       
}