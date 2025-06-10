<?php
namespace models;

use core\Model;
use core\Core;
use core\helper;

    /**
    * @property int $id ID тегу
    * @property string $name Назва тегу
    */
class Tags extends Model
{
    public static $tableName = 'tags';

     public static function getAll()
    {
        return Core::get()->db->select(self::$tableName, '*', '1 ORDER BY id ASC');
    }

    public static function Add($name)
    {
        return Core::get()->db->insert('tags', ['name' => $name]);
    }

    public static function findByName($tagName)
    {
        $rows = self::findByCondition(['name' => $tagName]);
        if(!empty($rows))
            return $rows[0];
        else
            return null;
    }

}