<?php
abstract class XtacData {
   //## Variables ############################
   protected $host;
   protected $database;
   protected $hasConnection;
   protected $connection;


   //## Constructor ###########################################
   public function __construct($inHost, $inDatabase=null) {
      $this->host = $inHost;

      if ($inDatabase !== null)
         $this->database = $inDatabase;

      $hasConnection = false;
   }


   //## Connection Management #################################
   abstract public function connect($inUsername, $inPassword);
   abstract public function disconnect();
   public function hasConnection(){
      return $this->hasConnection;
   }


   //## Fetching Operations ###################################
   abstract public function getUser($inID, $inCol, &$outUser);

   protected function translateValue($inValue) {
      switch ($inValue) {
         case '':
            return null;
            break;
         case 'TRUE':
            return 'Y';
            break;
         case 'FALSE':
            return 'N';
            break;
         case 'OFF':
            return 'N';
            break;
         case 'ON':
            return 'Y';
            break;
         default:
            return $inValue;
      }
   }
   
}
?>
