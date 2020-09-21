<?php

require_once ('config/Config.php');
require_once ('core/ImportModule.php');

try {
$importModule = new ImportModule(Config::HOST, Config::USER, Config::PASSWORD);

   if ($importModule->createDataBaseFromFile("uploads/db/db.sql")){
       if ($importModule->createTablesFromFiles("uploads/db/tables.sql")){
           if ($importModule->insertDataFromFile("uploads/db/data.sql")){
               echo 'DONE!';
           }else {
               die("ERROR: Rows didnt added.");
           }
       }else {
           die("ERROR: Table didnt create.");
       }
   } else {
       die("ERROR: Database didnt create.");
   }
} catch (Exception $e){
    die("ERROR: " . $e->getMessage() . ' Code: ' . $e->getCode());
}

