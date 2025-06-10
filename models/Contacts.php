<?php
namespace models;

use core\Core;
use core\Model;

/**
 * @property int $id
 * @property string $title
 * @property string $content
 * @property string $color_bg
 * @property string $color_text
 * @property timestamp $created_at
 */


class Contacts extends Model
{
    public static $tableName = 'contacts';

    public static function getAll()
    {
        return Core::get()->db->select(
                self::$tableName,
                '*',
                [
                    'id >' => '0',
                    'ORDER' => ['created_at' => 'DESC']
                ]);    
    }

}