<?php
namespace models;

use core\Model;
use core\Core;

class ItemImages extends Model
{
    public static $tableName = 'item_images';

    public static function getMainImage($itemId)
    {
        $res = Core::get()->db->select(self::$tableName, '*', [
            'item_id' => $itemId,
            'is_main' => true
        ]);
        return $res[0] ?? null;
    }

}
