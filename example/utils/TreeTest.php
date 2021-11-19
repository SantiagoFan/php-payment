<?php


namespace JoinPhpCommon\example\utils;


use JoinPhpCommon\utils\Tree;

class TreeTest
{
    public function tree(){
        $data = [
            ['name'=>"水果","id"=>"1","parent_id"=>''],
            ['name'=>"蔬菜","id"=>"2","parent_id"=>''],
            ['name'=>"西瓜","id"=>"3","parent_id"=>'1'],
            ['name'=>"橘子","id"=>"4","parent_id"=>'1'],
            ['name'=>"南瓜","id"=>"5","parent_id"=>'2'],
            ['name'=>"土豆","id"=>"6","parent_id"=>'2'],
        ];
        $tree = new Tree();
        vdump($data);
        $tree_data = $tree->makeTree($data);
        vdump(json_encode($tree_data));
    }

}