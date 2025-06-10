<?php
namespace models;

use core\Model;
use core\Core;

    /**
    * @property int $id ID скла
    * @property string $name Назва скла
    * @property int $glass_type ID типу скла
    * @property float $length_cm довжина скла
    * @property float $width_cm ширина скла
    * @property int $thickness_mm товщина скла
    * @property float $cost вартість скла
    */
class Glass extends Model
{
    public static $tableName = 'glass';

    public static function getGlass($id)
    {
        $row = Core::get()->db->select('glass', '*', ['id' => $id]);
        return $row[0];
    }

}