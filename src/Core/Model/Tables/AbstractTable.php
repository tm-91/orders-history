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
    const NAME = '';
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

    public function select(array $columns, array $where, $limit = 1){
        if (empty($columns) || empty($where)) {
            throw new \Exception('columns and "where" can not be empty');
        }
        if (static::NAME != ''){
            throw new \Exception('Model table name is not set!');
        }

        $whereString = [];
        foreach ($where as $key => $val) {
            $whereString[] = $this->_getParamsString([$key => $val]);
        }
        $stmt = \DbHandler::getDb()->prepare(
            'SELECT `' . implode('`, `', $columns) . '` FROM `' . static::NAME . '` WHERE ' . implode(' AND ', $whereString) . ' LIMIT :limit;'
        );
        $stmt = $this->_bindValues($stmt, $where);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        if ($stmt->execute()) {
            return $stmt->fetchAll();
        }
        return false;
    }
}