<?php
class PostgresDBRecord
{
    const CONN_SETTINGS = array(
        'HOST' => "localhost",
        'PORT' => 5437,
        'DB'   => 'postgres',
        'USER' => 'postgres',
        'PASS' => '',
    );
    protected static $init = null;
    protected $pstDB;

    function __construct()
    {
        $conStr = " host=".self::CONN_SETTINGS['HOST'].
                  " port=".self::CONN_SETTINGS['PORT'].
                  " dbname=".self::CONN_SETTINGS['DB'].
                  " user=".self::CONN_SETTINGS['USER'].
                  " password=".self::CONN_SETTINGS['PASS']."";
        try {
            $this->pstDB = @pg_connect($conStr);
            if(!$this->pstDB)
            {
                throw new ErrorException("Ошибка во время соединения с PostgreSQL");
            }
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    protected function doQuery($tableName, $arSelectOptions = false, $arInsertOptions = false, $arUpdateCounter = false)
    {
        try {
            if(is_array($arSelectOptions))
            {
                $sqlQuery = "SELECT ".implode(', ', $arSelectOptions['SELECT_VALUE'])." FROM ".$tableName;
                if(!empty($arSelectOptions['WHERE_VALUE']))
                {
                    $sqlQuery .= " WHERE";
                    foreach($arSelectOptions['WHERE_VALUE'] as $var => $value)
                    {
                        $sqlQuery.= " ".$var." = ".$value;
                    }
                }
                if(!empty($arSelectOptions['LIMIT_VALUE']))
                {
                    $sqlQuery .=  " LIMIT ".$arSelectOptions['LIMIT_VALUE'];
                }
            }
            elseif(is_array($arInsertOptions))
            {
                $sqlQuery = "INSERT INTO ".$tableName." (".implode(", ", array_keys($arInsertOptions)).")
                                    VALUES (".implode(", ", array_keys($arInsertOptions)).")";

                print_r($sqlQuery);
                die();
            }
            elseif(is_array($arUpdateCounter))
            {

            }

            $result = @pg_query($this->pstDB, $sqlQuery);
            if(!$result)
            {
                throw new ErrorException("Ошибка во время выполнения запроса к PostgreSQL: ".$sqlQuery);
            }
        } catch (\Exception $e) {
            die($e->getMessage());
        }
        if($arSelectOptions['RETURN_OPTIONS'] != false)
        {
            $result = $this->getQueryResult($result, $arSelectOptions['RETURN_OPTIONS']);
        }
        return $result;
    }

    protected function getQueryResult($objResult, $options)
    {
        switch ($options['TYPE']) {
            case 'ASSOC':
                while ($row = pg_fetch_array($objResult, NULL, PGSQL_ASSOC)){
                    $result[] = $row;
                }
            break;
        }
        return $result;
    }
}
