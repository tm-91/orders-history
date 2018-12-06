<?php
/**
 * Created by PhpStorm.
 * User: AsusPC
 * Date: 2018-12-06
 * Time: 19:25
 */

namespace Core\Model\Tables;


class AbstractTable
{
    const COLUMN_TYPE = [];

    protected function _getParamsString(array $columns) {
        $output = [];
        foreach ($columns as $col => $val) {
            $output[] = '`' . $col . '` = :' . $col;
        }
        return implode(', ', $output);
    }

    protected function _bindValues(\PDOStatement $statement, array $fieldsAndValues) {
        $types = static::COLUMN_TYPE;
        foreach ($fieldsAndValues as $field => $value) {
            $type = isset($types[$field]) ? $types[$field] : \PDO::PARAM_STR;
            $statement->bindValue(':' . $field, $value, $type);
        }
        return $statement;
    }
}