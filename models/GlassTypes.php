<?php
namespace models;

use core\Model;
use core\Core;
use core\helper;

    /**
    * @property int $id ID типу скла
    * @property string $name Назва типу скла
    */
class GlassTypes extends Model
{
    public static $tableName = 'glass_types';

    public static function Add($name)
    {
        return Core::get()->db->insert('glass_types', ['name' => $name]);
    }

    public static function getAll()
    {
        $rows = Core::get()->db->select(self::$tableName, '*', '1 ORDER BY id ASC');
        return $rows;
    }

}