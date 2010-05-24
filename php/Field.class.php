<?php

class field{
   public $HtmlID;
   public $MysqlName;
   public $LdapName;
   public $LdapValue = null;
   public $MysqlValue = null;
   public $HtmlClass = null;
   public $HtmlParentID = null;
   public $HtmlName = null;
   public $HtmlTitle = null;
 
   //## Display ######################################################
   public function draw(&$firstItem = false, &$multiItemId = '') {
      $buffer = '';
      $this->ConsistencyCheck();

      if (!$this->IsEmpty())
         $buffer = ($this->MultiValue())?
            $this->drawMultiValue($firstItem, $multiItemId):
            $this->drawSingleValue($firstItem);

      return $buffer;
   }
   private function drawMultiValue(&$firstItem, &$multiItemId) {
      $buffer = '';

      if ($this->HtmlParentID !== $multiItemId) {
         $firstItem = true;
         $multiItemId = $this->HtmlParentID;
      }
      else
         $firstItem = false;

      if ($firstItem) {
         $buffer .= "\t<div class=\"dg\">"
                 . "\t <dt "
                 . $this->ID($this->HtmlParentID)
                 . $this->nodeClass()
                 . $this->Title()
                 .  '>'
                 . $this->HtmlName . ':'
                 . "</dt>\n";
      
         $buffer .= "\t\t\t".'<dd '
              . $this->ID($this->HtmlID)
              . $this->nodeClass('first')
              . '>'
              . $this->Value()
              . "</dd>\n";
      }
      else {
         $buffer .= "\t\t\t".'<dd '
                 . $this->ID($this->HtmlID)
                 . $this->nodeClass()
                 . '>'
                 . $this->Value()
                 . "</dd>\n";
      }
	 if ($this->HtmlID === 'LastName' || $this->HtmlID === 'ResidentialZip' || $this->HtmlID === 'MailingZip')
		 $buffer .= '</div>';


      return $buffer;
   }
   private function drawSingleValue(&$firstItem) {
      $buffer = '';

      if ($firstItem === true)

      $firstItem = false;
      $buffer .= "\t<div class=\"dg ";
      if ($this->hasError())
         $buffer .= ' error" title="'
                 .  $this->HtmlTitle;
      $buffer .= '" ';
      $buffer .= ">\n\t\t<dt "
              . $this->ID($this->HtmlID)
              . $this->nodeClass()
              . '>'
              . $this->HtmlName . ':' ."</dt>\n"
              . "\t\t\t<dd>"
              . $this->Value()
              . "</dd>\n"
              . '</div>';

      return $buffer;
   }

   private function nodeClass($inClass = '') {
      if (sizeof($this->HtmlClass) > 0) {
         return 'class="' . implode(' ', $this->HtmlClass) . ' ' . $inClass . ' " ';
      }
   }
   private function Title() {
      if ($this->HtmlTitle !== null)
         return "title=\"$this->HtmlTitle\" ";
   }
   private function ID($inID) {
      return "id=\"$inID\" ";
   }
   private function Value() {
      return ($this->MysqlValue !== null || $this->MysqlValue !== null)?
         trim($this->MysqlValue):
         trim($this->LdapValue);
   }


   //## Backend Processing ###########################################
   private function ConsistencyCheck(){
      if ($this->MysqlValue === null && $this->LdapValue === null)
         return true;
      if ($this->MysqlValue === null && $this->LdapValue !== null)
         return true;
      if ($this->MysqlValue !== null && $this->LdapValue === null)
         return true;

      if ($this->MysqlValue !== null || $this->LdapValue !== null) {
         if (trim($this->LdapValue) === trim($this->MysqlValue))
            return true;
         else {
            $this->HtmlClass[] = 'error';
            $this->HtmlTitle  = 'Ldap: ' . $this->LdapValue . '   Mysql: ' . $this->MysqlValue;
            return false;
         }
      }
      else return true;
   }
   private function CleanCssClasses() {
      if ($this->HtmlClass === 'NULL')
         $this->HtmlClass = '';
   }
   

   //## Simple binary checks #########################################
   private function MultiValue(){
      foreach ($this->HtmlClass as $value) {
         if ($value === 'multi')
            return true;
      }
      return false;
   }
   public function IsEmpty() {
      if ($this->LdapValue === null && $this->MysqlValue === null)
         return true;
      else
         return false;
   }

   private function hasError() {
      foreach ($this->HtmlClass as $value) {
         if ($value === 'error')
            return true;
      }
      return false;
   }


   //## Constructor ##################################################
   public function field($attribute){
      if (is_array($attribute)) {
      $this->HtmlID = $attribute['HtmlID'];
      $this->MysqlName = $attribute['mysql'];
      $this->LdapName = $attribute['ldap'];
      $this->HtmlParentID = $attribute['HtmlParentID'];
      $this->HtmlName = $attribute['HtmlName'];
      $this->HtmlClass = array();
      if (strtolower($attribute['HtmlClass']) !== 'null')
         $this->HtmlClass = explode(" ",$attribute['HtmlClass']);

      return 1;
      }
   }
}
?>
