<?php
namespace models;

use core\Model;
use core\Core;
use core\helper;

    /**
    * @property int $id ID жанру
    * @property string $name Назва жанру
    */
class Genres extends Model
{
    public static $tableName = 'genres';

    public static function findByName($genreName)
    {
        $rows = self::findByCondition(['name' => $genreName]);
        if(!empty($rows))
            return $rows[0];
        else
            return null;
    }

    public static function getAll()
    {
        return Core::get()->db->select(self::$tableName, '*', '1 ORDER BY id ASC');
    }

    public static function getItemCountByGenre($genreId)
    {
        $res = Core::get()->db->select('items', ['COUNT(*) as count'], ['genre_id' => $genreId]);
        return $res[0]['count'] ?? 0;
    }

    public static function Add($genreName)
    {
        $genre = new Genres();
        $genre->name = $genreName;
        $genre->save();
    }

}