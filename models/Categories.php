<?php

namespace models;

use core\Model;
use core\Core;

 /**
    * @property int $id ID категорії
    * @property string $name назва категорії
    */
class Categories extends Model
{
    public static $tableName = 'categories';


    public static function Add($categoryName)
    {
        $category = new Categories();
        $category->name = $categoryName;
        $category->save();
    }

    public static function findByName($categoryName)
    {
        $rows = self::findByCondition(['name' => $categoryName]);
        if(!empty($rows))
            return $rows[0];
        else
            return null;
    }

    public static function getCategories()
    {
        $rows = Core::get()->db->select(self::$tableName, '*', '1 ORDER BY id ASC');
        return $rows;
    }

    public static function getItemCountByCategory($categoryId)
    {
        $res = Core::get()->db->select('items', ['COUNT(*) as count'], ['category_id' => $categoryId]);
        return $res[0]['count'] ?? 0;
    }



}