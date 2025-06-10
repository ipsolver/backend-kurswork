<?php

namespace core;

class DB
{
    public $pdo;

    public function __construct($host, $name, $login, $password)
    {
        $this->pdo = new \PDO("mysql:host={$host};dbname={$name}",
            $login, $password,
        [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
        ]);
    }

    protected function where($where)
    {
        if (is_array($where)) 
        {
            $where_string = "WHERE ";
            $where_parts = [];

            foreach ($where as $field => $value) 
            {
                if (preg_match('/^(.+?)\s*(=|!=|<>|<|>|<=|>=|LIKE)$/i', $field, $matches)) 
                {
                    $column = trim($matches[1]);
                    $operator = strtoupper($matches[2]);
                    $paramName = preg_replace('/[^a-zA-Z0-9_]/', '_', $column); // параметр без символів

                    $where_parts[] = "{$column} {$operator} :{$paramName}";
                } 
                else 
                {
                    // За замовчуванням — '='
                    $paramName = preg_replace('/[^a-zA-Z0-9_]/', '_', $field);
                    $where_parts[] = "{$field} = :{$paramName}";
                }
            }

            $where_string .= implode(' AND ', $where_parts);
        } 
        elseif (is_string($where)) 
        {
            $where_string = "WHERE " . $where;
        } 
        else 
        {
            $where_string = '';
        }

        return $where_string;
    }

    public function select($table, $fields = "*", $where = null)
    {
        if (is_array($fields))
            $fields_string = implode(', ', $fields);
        else
            $fields_string = $fields;

        $order = "";
        $limit = "";

        if (is_array($where)) 
        {
            if (isset($where['ORDER'])) 
            {
                $orderBy = $where['ORDER'];
                if (is_array($orderBy)) 
                {
                    foreach ($orderBy as $col => $dir)
                        $order = " ORDER BY {$col} " . strtoupper($dir);
                } 
                else
                    $order = " ORDER BY {$orderBy}";
                unset($where['ORDER']);
            }

            if (isset($where['LIMIT'])) 
            {
                $limitVal = $where['LIMIT'];
                if (is_string($limitVal) && preg_match('/^\d+\s*,\s*\d+$/', $limitVal)) 
                    $limit = " LIMIT {$limitVal}";
                else
                    $limit = " LIMIT " . intval($limitVal);
                unset($where['LIMIT']);
            }

        }

        $where_string = $this->where($where);
        $sql = "SELECT {$fields_string} FROM {$table} {$where_string}{$order}{$limit}";
        $sth = $this->pdo->prepare($sql);

        if (is_array($where)) 
        {
            foreach ($where as $key => $value) 
            {
                if (preg_match('/^(.+?)\s*(=|!=|<>|<|>|<=|>=|LIKE)$/i', $key, $matches))
                    $paramName = preg_replace('/[^a-zA-Z0-9_]/', '_', trim($matches[1]));
                
                else
                    $paramName = preg_replace('/[^a-zA-Z0-9_]/', '_', $key);

                $sth->bindValue(":{$paramName}", $value);
            }
        }

        $sth->execute();
        return $sth->fetchAll();
    }


    public function insert($table, $row_to_insert)
    {
        $fields_list = implode(", ", array_keys($row_to_insert));
        $params_array = [];

        foreach($row_to_insert as $key => $value)
        {
            $params_array [] = ":{$key}";
        }

        $params_list = implode(", ", $params_array);
        $sql = "INSERT INTO {$table} ({$fields_list}) VALUES ({$params_list})";
        $sth = $this->pdo->prepare($sql);

        foreach($row_to_insert as $key => $value)
            $sth->bindValue(":{$key}", $value);

        $sth->execute();
        // return $sth->rowCount();
        return $this->pdo->lastInsertId();
    }

    // public function delete($table, $where)
    // {
    //     $where_string = $this->where($where);

    //     $sql = "DELETE FROM {$table} {$where_string}";
    //     $sth = $this->pdo->prepare($sql);

    //     foreach($where as $key => $value)
    //         $sth->bindValue(":{$key}", $value);

    //     $sth->execute();
    //     return $sth->rowCount();
    // }

    public function delete($table, $where)
    {
        $where_string = $this->where($where);

        $sql = "DELETE FROM {$table} {$where_string}";
        $sth = $this->pdo->prepare($sql);

        foreach ($where as $key => $value) 
        {
            if (preg_match('/^(.+?)\s*(=|!=|<>|<|>|<=|>=|LIKE)$/i', $key, $matches))
                $param = preg_replace('/[^a-zA-Z0-9_]/', '_', trim($matches[1]));
            else
                $param = preg_replace('/[^a-zA-Z0-9_]/', '_', $key);

            $sth->bindValue(":{$param}", $value);
        }

        $sth->execute();
        return $sth->rowCount();
    }


    public function update($table, $row_to_update, $where)
    {
        $where_string = $this->where($where);
        $set_array = [];

        foreach($row_to_update as $key => $value)
            $set_array [] = "{$key} = :{$key}";

        $set_string = implode(", ", $set_array);
        $sql = "UPDATE {$table} SET {$set_string} {$where_string}";
        $sth = $this->pdo->prepare($sql);

        foreach($where as $key => $value)
            $sth->bindValue(":{$key}", $value);

        foreach($row_to_update as $key => $value)
            $sth->bindValue(":{$key}", $value);
        
        $sth->execute();
        return $sth->rowCount();
    }

}