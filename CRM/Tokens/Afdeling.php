<?php

class CRM_Tokens_Afdeling {
  
  protected static $singelton;
  
  protected $afdeling = false;
  
  protected $address = array();
  
  protected $phone = array();
  
  protected function __construct() {
    
  }
  
  public function afdeling_naam(&$values, $cids, $job = null, $tokens = array(), $context = null) { 
    $this->findAfdelingForContact($cids);
    foreach($cids as $cid) {
      $values[$cid]['sp.afdeling_naam'] = '';
      //find afdeling
      if (isset($this->afdeling[$cid])) {
        $values[$cid]['sp.afdeling_naam'] = CRM_Contact_BAO_Contact::displayName($this->afdeling[$cid]);
      }
    }
  }
  
  public function afdeling_adres(&$values, $cids, $job = null, $tokens = array(), $context = null) { 
    $this->findAfdelingForContact($cids);
    foreach($cids as $cid) {
      $values[$cid]['sp.afdeling_adres'] = '';
      //find afdeling
      if (isset($this->afdeling[$cid])) {
        $address = $this->findAddress($this->afdeling[$cid]);
        if (!empty($address)) {
          $values[$cid]['sp.afdeling_adres'] = nl2br($address);
        }  
      }
    }
  }
  
  public function afdeling_telefoon(&$values, $cids, $job = null, $tokens = array(), $context = null) { 
    $this->findAfdelingForContact($cids);
    foreach($cids as $cid) {
      $values[$cid]['sp.afdeling_telefoon'] = '';
      //find afdeling
      if (isset($this->afdeling[$cid])) {
        $phone = $this->findPhone($this->afdeling[$cid]);
        if (!empty($phone)) {
          $values[$cid]['sp.afdeling_telefoon'] = $phone;
        }  
      }
    }
  }
  
  public function tokenValues(&$values, $cids, $job = null, $tokens = array(), $context = null) {
    if (!empty($tokens['sp'])) {
      if (in_array('afdeling_naam', $tokens['sp'])) {
         $this->afdeling_naam($values, $cids, $job, $tokens, $context);
      }
      if (in_array('afdeling_adres', $tokens['sp'])) {
         $this->afdeling_adres($values, $cids, $job, $tokens, $context);
      }
      if (in_array('afdeling_telefoon', $tokens['sp'])) {
         $this->afdeling_telefoon($values, $cids, $job, $tokens, $context);
      }
    }
  }
  
  /**
   * 
   * @return CRM_Tokens_Afdeling
   */
  public static function singleton() {
    if (!self::$singelton) {
      self::$singelton = new CRM_Tokens_Afdeling();
    }
    return self::$singelton;
  }
  
  protected function findPhone($contact_id) {
    if (isset($this->phone[$contact_id])) {
      return $this->phone[$contact_id];
    }
    
    $this->phone[$contact_id] = false;
    
    $phone = new CRM_Core_BAO_Phone();
    $phone->contact_id = $contact_id;
    $phone->is_primary = 1;
    if ($phone->find(true)) {
      $this->phone[$contact_id] = $phone->phone;
    }
    return $this->phone[$contact_id];
  }
  
  protected function findAddress($contact_id) {
    if (isset($this->address[$contact_id])) {
      return $this->address[$contact_id];
    }
    
    $this->address[$contact_id] = false;
    
    $address = new CRM_Core_BAO_Address();
    $address->contact_id = $contact_id;
    $address->is_primary = 1;
    if ($address->find(true)) {
      $address->addDisplay();
      $this->address[$contact_id] = $address->display_text;
    }
    return $this->address[$contact_id];
  }
  
  protected function findAfdelingForContact($cids) {
    if (is_array($this->afdeling)) {
      return;
    } else {
      $this->afdeling = array();
    }
    
    $contact_ids = implode(",", $cids);    
    $config = CRM_Geostelsel_Config::singleton();
    $table = $config->getGeostelselCustomGroup('table_name');
    $afdeling_field = $config->getAfdelingsField('column_name');
    $sql = "SELECT `".$afdeling_field."` AS `afdeling_id`, `entity_id` FROM `".$table."` WHERE `entity_id` IN (".$contact_ids.")";
    $dao = CRM_Core_DAO::executeQuery($sql);
    while($dao->fetch()) {
      $this->afdeling[$dao->entity_id] = $dao->afdeling_id;
    }
  }
  
}

