<?php

require_once("helpers/QueryHelper.php");

class ImportModule
{
    private $connection;

    public function __construct($host, $user, $password)
    {
        $this->connection = new PDO("mysql:host=$host", $user, $password);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function createDataBaseFromFile($filePath)
    {
        $queryString = QueryHelper::getQueryStringFromFile($filePath);

        if (!$queryString) {
            throw new Exception('Create Database Function. Query String Incorrect.');
        }

        $query = $this->connection->prepare($queryString);

        return $query->execute();
    }

    public function createTablesFromFiles($filePath)
    {
        $queryString = QueryHelper::getQueryStringFromFile($filePath);

        if (!$queryString) {
            throw new Exception('Create Database Function. Query String Incorrect.');
        }

        $query = $this->connection->prepare($queryString);

        try {
            return $query->execute();
        } catch (Exception $e) {
            if ($e->getCode() === '42S01') {
                return $this->changeTableIfExist($queryString);
            } else {
                throw new Exception($e->getMessage());
            }
        }
    }

    public function insertDataFromFile($filePath)
    {
        $queryString = QueryHelper::getQueryStringFromFile($filePath);

        if (!$queryString) {
            throw new Exception('Create Database Function. Query String Incorrect.');
        }

        $query = $this->addOrUpdateRows($queryString);
        $query = $this->connection->prepare($query);

        return $query->execute();
    }


    private function changeTableIfExist($queryString)
    {

        if (preg_match('/test\s\((.*)PRIMARY/', QueryHelper::trimQueryString($queryString), $matches)) {
            $columns = explode(',', $matches[1]);

            $query = 'ALTER TABLE test.test';
            foreach (array_diff($columns, [' ']) as $column) {
                $query .= ' MODIFY ' . $column . ', ';
            }

            $query = substr_replace($query, "", -2);
            $query = $this->connection->prepare($query);

            return $query->execute();
        } else {
            return false;
        }

    }

    private function addOrUpdateRows($queryString)
    {

        if (preg_match_all('/\(\d+.*?\)/', QueryHelper::trimQueryString($queryString), $matches)) {

            $query = 'INSERT INTO test.test VALUES ';
            foreach ($matches[0] as $key =>$row ){
                $query.= $row . ', ';
            }

            $query = substr_replace($query, "", -2);

            if (preg_match('/test\(.*?\) VALUES/', QueryHelper::trimQueryString($queryString), $columnMatches)) {
                $columns = explode(',' , str_replace(['test(',') VALUES'],'', $columnMatches[0]));
                $query.= ' ON DUPLICATE KEY UPDATE ';
                foreach ($columns as $column) {
                    $query.= trim($column) . ' = VALUES(' .trim($column) . '), ';
                }
                $query = substr_replace($query, "", -2);

            }

            return $query;

        } else {
            return $queryString;
        }

    }
}