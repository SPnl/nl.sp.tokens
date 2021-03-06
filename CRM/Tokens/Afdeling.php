<?php

class CRM_Tokens_Afdeling {
  
  protected static $singelton;
  
  protected $afdeling = false;
  
  protected $address = array();
  
  protected $phone = array();
  
  protected $website = array();
  
  protected $email = array();

  protected $voorzitter_rel_type_id;

  protected $voorzitter = array();
  
  protected function __construct() {
    $this->voorzitter_rel_type_id = civicrm_api3('RelationshipType', 'getvalue', array('name_a_b' => 'sprel_voorzitter_afdeling', 'return' => 'id'));
  }
  
  public static function tokens(&$tokens) { 
    if (class_exists('CRM_Geostelsel_Config')) {
      $tokens['sp']['sp.afdeling_naam'] = 'Naam van afdeling';
      $tokens['sp']['sp.afdeling_adres'] = 'Adres van afdeling';
      $tokens['sp']['sp.afdeling_email'] = 'E-mailadres van afdeling';
      $tokens['sp']['sp.afdeling_telefoon'] = 'Telfoon van afdeling';
      $tokens['sp']['sp.afdeling_website'] = 'Website van afdeling';
    }
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
  
  public function afdeling_email(&$values, $cids, $job = null, $tokens = array(), $context = null) { 
    $this->findAfdelingForContact($cids);

    foreach($cids as $cid) {
      $values[$cid]['sp.afdeling_email'] = '';
      //find afdeling
      if (isset($this->afdeling[$cid])) {
        $email = $this->findEmail($this->afdeling[$cid]);
        if (!empty($email)) {
          $values[$cid]['sp.afdeling_email'] = $email;
        }  
      }
    }
  }
  
  public function afdeling_website(&$values, $cids, $job = null, $tokens = array(), $context = null) { 
    $this->findAfdelingForContact($cids);
    foreach($cids as $cid) {
      $values[$cid]['sp.afdeling_website'] = '';
      //find afdeling
      if (isset($this->afdeling[$cid])) {
        $website = $this->findWebsite($this->afdeling[$cid]);
        if (!empty($website)) {
          $values[$cid]['sp.afdeling_website'] = $website;
        }  
      }
    }
  }
  
  public function tokenValues(&$values, $cids, $job = null, $tokens = array(), $context = null) {
    if (!empty($tokens['sp'])) {
      if (in_array('afdeling_naam', $tokens['sp']) || array_key_exists('afdeling_naam', $tokens['sp'])) {
         $this->afdeling_naam($values, $cids, $job, $tokens, $context);
      }
      if (in_array('afdeling_adres', $tokens['sp']) || array_key_exists('afdeling_adres', $tokens['sp'])) {
         $this->afdeling_adres($values, $cids, $job, $tokens, $context);
      }
      if (in_array('afdeling_telefoon', $tokens['sp']) || array_key_exists('afdeling_telefoon', $tokens['sp'])) {
         $this->afdeling_telefoon($values, $cids, $job, $tokens, $context);
      }
      if (in_array('afdeling_email', $tokens['sp']) || array_key_exists('afdeling_email', $tokens['sp'])) {
         $this->afdeling_email($values, $cids, $job, $tokens, $context);
      }
      if (in_array('afdeling_website', $tokens['sp']) || array_key_exists('afdeling_website', $tokens['sp'])) {
         $this->afdeling_website($values, $cids, $job, $tokens, $context);
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

  protected function findVoorzitter($contact_id) {
    if (isset($this->voorzitter[$contact_id])) {
      return $this->voorzitter[$contact_id];
    }
    $this->voorzitter[$contact_id] = false;

    $params['relationship_type_id'] = $this->voorzitter_rel_type_id;
    $params['contact_id'] = $contact_id;
    $params['status_id'] = 4; //current active relationships
    $params['return'] = 'contact_id_a';
    try {
      $this->voorzitter[$contact_id] = civicrm_api3('Relationship', 'getvalue', $params);
    } catch (Exception $e) {
      //do nothing
    }

    return $this->voorzitter[$contact_id];
  }
  
  protected function findPhone($contact_id, $doNotRecurse=false) {
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

    if (empty($this->phone[$contact_id])) {
      $voorzitter = $this->findVoorzitter($contact_id);
      if ($voorzitter && !$doNotRecurse) {
        $this->phone[$contact_id] = $this->findPhone($voorzitter, true);
      }
    }

    return $this->phone[$contact_id];
  }
  
  protected function findEmail($contact_id, $doNotRecurse=false) {
    if (isset($this->email[$contact_id])) {
      return $this->email[$contact_id];
    }
    
    $this->email[$contact_id] = false;
    
    $email = new CRM_Core_BAO_Email();
    $email->contact_id = $contact_id;
    $email->is_primary = 1;
    if ($email->find(true)) {
      $this->email[$contact_id] = $email->email;
    }

    if (empty($this->email[$contact_id])) {
      $voorzitter = $this->findVoorzitter($contact_id);
      if ($voorzitter && !$doNotRecurse) {
        $this->email[$contact_id] = $this->findEmail($voorzitter, true);
      }
    }

    return $this->email[$contact_id];
  }
  
  protected function findWebsite($contact_id) {
    if (isset($this->website[$contact_id])) {
      return $this->website[$contact_id];
    }
    
    $this->website[$contact_id] = false;
    
    $website = new CRM_Core_BAO_Website();
    $website->contact_id = $contact_id;
    if ($website->find(true)) {
      $this->website[$contact_id] = $website->url;
    }

    return $this->website[$contact_id];
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
      $this->address[$contact_id] = $this->displayAddress($address);
    }
    return $this->address[$contact_id];
  }

  protected function displayAddress(CRM_Core_BAO_Address $address) {
    if (!$address->master_id) {
      $address->addDisplay();
      return $address->display_text;
    } else {
      $a = new CRM_Core_BAO_Address();
      $a->id = $address->master_id;
      if ($a->find(true)) {
        return $this->displayAddress($a);
      }
    }
    return '';
  }
  
  protected function findAfdelingForContact($cids) {
    $diff = array_values($cids);
    if (is_array($this->afdeling)) {
      $diff = array_diff(array_values($cids), array_keys($this->afdeling));
    }
    if (is_array($this->afdeling) && count($diff) == 0) {
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

