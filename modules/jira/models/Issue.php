<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\modules\jira\models;

/**
 * Description of Issue
 *
 * @author laska
 */
class Issue extends \yii\base\Model{
    
    public $key;
    public $summary;
    public $description;




    protected static $pattern_in = "@\w+__(in)$@";
    protected static $pattern_in_base = "@__(in)$@";
    
    public static function getLoadFields(){
        return [
            'created',
            'description',
            'duedate',
            'issuetype',
            'priority',
            'status',
            'updated',
            'summary'
        ];
    }


    /**
     * Параметри, що передаються при запиті, але не належать JQL 
     */
    public static function getSearchParams(){
        return [
            'startAt', 'maxResults'
        ];
    }

    public static function getPostRequest($params){
        $post = ['jql' => self::getJQuery($params)];
        foreach ($params as $key => $value){
            if (in_array($key, self::getSearchParams())){
                $post[$key] = $value;
            }
        }
        return $post;
    }

    public static function getJQuery($params){
        
        $query_p = [];  //масив в якому зберігаються параметри JQL
        foreach ($params as $key => $value){
            
            if (in_array($key, self::getSearchParams())){
                continue;
            }
            
            //sort
            if ($key == 'sort'){
                $query_p[] = ['ORDER BY', '', "\"{$value}\""];
                continue;
            }
            
            //__in
            $if__in = preg_match(self::$pattern_in, $key);
            if ($if__in){
                $base_key = preg_replace(self::$pattern_in_base, '', $key);
                $query_p[] = ['IN', $base_key, self::arrayToStr($value)];
                continue;
            }
            
            //== or IN (if $value is array)
            if (is_array($value)){
                $query_p[] = ['IN', $key, self::arrayToStr($value)];
            } else {
                $query_p[] = ['=', $key, "\"{$value}\""];
            }
        }
        
        $jql = [];
        foreach ($query_p as $one_p){
            $jql[] = "({$one_p[1]} {$one_p[0]} {$one_p[2]})";
        }
        
        return implode(' AND ', $jql);
    }
    
    protected static function arrayToStr($arr){
        $arr1 = array_map(function($one){
                        return "\"{$one}\"";
                    }, $arr);
        $vals = implode(',', $arr1);
        return "({$vals})";
    }
}
