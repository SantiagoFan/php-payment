<?php


namespace JoinPhpCommon\utils;


class Tree
{
    private static $primary = 'id';
    private static $parentId = 'parent_id';
    private static $child = 'children';

    public function __construct($primary ="id",$parentId="parent_id",$child="children")
    {
        self::$parentId=$primary;
        self::$parentId=$parentId;
        self::$child=$child;
    }

    public  function makeTree(&$data, $index = 0)
    {
        $childs = self::findChild($data, $index);
        if (empty($childs)) {
            return $childs;
        }
        foreach ($childs as $k => &$v) {
            if (empty($data)) break;
            $child = self::makeTree($data, $v[self::$primary]);
            if (!empty($child)) {
                $v[self::$child] = $child;
            }
        }
        unset($v);
        return $childs;
    }

    public  function findChild(&$data, $index)
    {
        $childs = [];
        foreach ($data as $k => $v) {
            if ($v[self::$parentId] == $index) {
                $childs[] = $v;
                unset($v);
            }
        }
        return $childs;
    }

    public  function getTreeNoFindChild($data)
    {
        $map = [];
        $tree = [];
        foreach ($data as &$it) {
            $map[$it[self::$primary]] = &$it;
        }
        foreach ($data as $key => &$it) {
            $parent = &$map[$it[self::$parentId]];
            if ($parent) {
                $parent['child'][] = &$it;
            } else {
                $tree[] = &$it;
                //$tree[]['child'] = null;
            }
        }
        return $tree;
    }

    public  function getParents($data, $catId)
    {
        $tree = array();
        foreach ($data as $item) {
            if ($item[self::$primary] == $catId) {
                if ($item[self::$parentId] > 0)
                    $tree = array_merge($tree, self::getParents($data, $item[self::$parentId]));
                $tree[] = $item;
                break;
            }
        }
        return $tree;
    }
}