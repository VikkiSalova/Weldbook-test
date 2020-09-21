<?php

class Api
{
    private $connection;
    public $requestParams = [];

    const GET_TABLE_URL = 'get_table_data';
    const SEARCH_URL = 'search';

    public function __construct($host, $user, $password)
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");
        $this->connection = new PDO("mysql:host=$host", $user, $password);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->requestParams = $_REQUEST;

    }

    public function run()
    {
        $uriArray = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

        if (!isset($uriArray[0])) {
            throw new RuntimeException('Action Not Found', 404);
        }
        $action = preg_replace('/\?.*/', '', $uriArray[0]);

        if ($action !== self::GET_TABLE_URL && $action !== self::SEARCH_URL) {
            throw new RuntimeException('Action Not Found', 404);
        }

        if ($action == self::GET_TABLE_URL) {
            return $this->getDataAction();
        }
        if ($action == self::SEARCH_URL) {
            return $this->searchAction();
        }


    }

    protected function response($data)
    {
        return json_encode($data);
    }


    public function getDataAction()
    {
        $error = '';
        $status = 0;
        $data = [];

        if (!isset($this->requestParams['limit'])) {
            $error = 'Limit not found';
            return $this->response(compact(['status', 'error', 'data']));
        }

        if (!isset($this->requestParams['page'])) {
            $error = 'Page not found';
            return $this->response(compact(['status', 'error', 'data']));
        }

        $limit = html_entity_decode(strip_tags($this->requestParams['limit']));
        $page = html_entity_decode(strip_tags($this->requestParams['page']));

        if (!is_numeric($limit) || !is_numeric($page)) {
            $error = 'Wrong parameters';
            return $this->response(compact(['status', 'error', 'data']));
        }

        $page = ($page - 1) * $limit;

        $query = "SELECT * FROM test.test LIMIT {$limit} OFFSET {$page}";


        try {
            $query = $this->connection->prepare($query);
            $query->execute();
        } catch (PDOException $e) {
            $error = $e->getMessage();
            return $this->response(compact(['status', 'error', 'data']));
        }

        $status = 1;
        $results = $query->fetchAll(\PDO::FETCH_ASSOC);

        $data['header'] = array_keys($results[0]);
        foreach ($results as $result) {
            $data['body'][] = array_values($result);
        }

        return $this->response(compact(['status', 'error', 'data']));
    }

    public function searchAction()
    {
        $error = '';
        $status = 0;
        $data = [];
        $query = 'SELECT * FROM test.test limit 10';

        if (isset($this->requestParams['category'])) {
            $category = html_entity_decode(strip_tags($this->requestParams['category']));
            $query = "SELECT * FROM test.test where `pipeline-PB_PIPELINE_CATEGORY` = '{$category}' limit 10";
        }

        if (isset($this->requestParams['order'])) {
            $order = html_entity_decode(strip_tags($this->requestParams['order']));
            if ($order !== 'asc' && $order !== 'desc') {
                $error = 'Wrong parameters';
                return $this->response(compact(['status', 'error', 'data']));
            }
            $query = "SELECT * FROM test.test ORDER BY id {$order} limit 10";
        }

        if (isset($this->requestParams['diameter'])) {
            $diameter = html_entity_decode(strip_tags($this->requestParams['diameter']));
            if (!is_numeric($diameter)) {
                $error = 'Wrong parameters';
                return $this->response(compact(['status', 'error', 'data']));
            }
            $query = "SELECT * FROM test.test where `characteristics-MEMBER_2_DIAMETER_MM` between 0 and {$diameter} limit 10";
        }

        if (isset($this->requestParams['number'])) {
            $number = html_entity_decode(strip_tags($this->requestParams['number']));
            $query = "SELECT * FROM test.test where `pipeline-LINE_NUMBER` LIKE '%{$number}%' limit 10";
        }


        try {
            $query = $this->connection->prepare($query);
            $query->execute();
        } catch (PDOException $e) {
            $error = $e->getMessage();
            return $this->response(compact(['status', 'error', 'data']));
        }

        $status = 1;
        $results = $query->fetchAll(\PDO::FETCH_ASSOC);

        $data['header'] = array_keys($results[0]);
        foreach ($results as $result) {
            $data['body'][] = array_values($result);
        }

        return $this->response(compact(['status', 'error', 'data']));
    }

}