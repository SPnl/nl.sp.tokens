<?php

class CRM_Tokens_Membership {
  
  protected static $singleton;
  
  protected $mandaat = array();
  
  protected function __construct() {
    
  }
  
  /**
   * 
   * @return CRM_Tokens_Membership
   */
  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new CRM_Tokens_Membership();
    }
    return self::$singleton;
  }
  
  public static function tokens(&$tokens) {
    $membership_types = self::membershipTypes();
    foreach($membership_types as $mid => $name) {    
      $tokens['membership']['membership.'.$mid.'_contribution'] = 'Membership fee ('.$name.')';
      if (class_exists('CRM_Sepamandaat_Config_SepaMandaat')) {
        $tokens['membership']['membership.'.$mid.'_mandaat_id'] = 'Mandaat ID ('.$name.')';
        $tokens['membership']['membership.'.$mid.'_mandaat_datum'] = 'Mandaat Datum ('.$name.')';
        $tokens['membership']['membership.'.$mid.'_mandaat_iban'] = 'Mandaat IBAN ('.$name.')';
      }
    }
  }
  
  public function tokenValues(&$values, $cids, $job = null, $tokens = array(), $context = null) {
    if (!empty($tokens['membership'])) {
      foreach($tokens['membership'] as $key) {
        $und_pos = stripos($key, "_");
        $mid = substr($key, 0, $und_pos);
        $token = substr($key, $und_pos+1);
        if ($token == 'contribution') {
          $this->contribution($mid, $key, $values, $cids, $job, $tokens, $context);
        }
        if ($token == 'mandaat_id') { 
          $this->mandaat_id_tokens($mid, $key, $values, $cids, $job, $tokens, $context);
        }
        if ($token == 'mandaat_datum') { 
          $this->mandaat_datum_tokens($mid, $key, $values, $cids, $job, $tokens, $context);
        }
        if ($token == 'mandaat_iban') { 
          $this->mandaat_iban_tokens($mid, $key, $values, $cids, $job, $tokens, $context);
        }
      }
    }
  }
  
  public function contribution($mtype_id, $key, &$values, $cids, $job = null, $tokens = array(), $context = null) { 
    foreach($cids as $cid) {
      $values[$cid]['membership.'.$key] = 'Onbekend';
      $membership = CRM_Member_BAO_Membership::getContactMembership($cid, $mtype_id, false);
      $sql = "SELECT MAX(`c`.`receive_date`), `c`.* FROM `civicrm_membership_payment` `m`
          INNER JOIN `civicrm_contribution` `c` ON `m`.`contribution_id` = `c`.`id`
          WHERE `m`.`membership_id` = %1
          LIMIT 1";
      $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($membership['id'], 'Integer')));
      if ($dao->fetch()) {
        $values[$cid]['membership.'.$key] = CRM_Utils_Money::format($dao->total_amount, $dao->currency);
      }
    }
  }
  
  public function mandaat_id_tokens($mtype_id, $key, &$values, $cids, $job = null, $tokens = array(), $context = null) { 
        
    foreach($cids as $cid) {
      $values[$cid]['membership.'.$key] = '';
      $membership = CRM_Member_BAO_Membership::getContactMembership($cid, $mtype_id, false);
      $mandaat = $this->findMandaat($membership['id']);
      if ($mandaat) {
        $values[$cid]['membership.'.$key] = $mandaat['mandaat_nr'];
      }
    }
  }
  
  public function mandaat_iban_tokens($mtype_id, $key, &$values, $cids, $job = null, $tokens = array(), $context = null) { 
        
    foreach($cids as $cid) {
      $values[$cid]['membership.'.$key] = '';
      $membership = CRM_Member_BAO_Membership::getContactMembership($cid, $mtype_id, false);
      $mandaat = $this->findMandaat($membership['id']);
      if ($mandaat) {
        $values[$cid]['membership.'.$key] = $mandaat['IBAN'];
      }
    }
  }
  
  public function mandaat_datum_tokens($mtype_id, $key, &$values, $cids, $job = null, $tokens = array(), $context = null) { 
    $config = CRM_Core_Config::singleton();
    
    foreach($cids as $cid) {
      $values[$cid]['membership.'.$key] = '';
      $membership = CRM_Member_BAO_Membership::getContactMembership($cid, $mtype_id, false);
      $mandaat = $this->findMandaat($membership['id']);
      if ($mandaat) {
        $values[$cid]['membership.'.$key] = CRM_Utils_Date::customFormat($mandaat['mandaat_datum'], $config->dateformatFull);
      }
    }
  }
  
  protected function findMandaat($membership_id) {
    if (isset($this->mandaat[$membership_id])) {
      return $this->mandaat[$membership_id];
    }
    
    $sepa_config = CRM_Sepamandaat_Config_SepaMandaat::singleton();
    $membership_config = CRM_Sepamandaat_Config_MembershipSepaMandaat::singleton();
    $table = $sepa_config->getCustomGroupInfo('table_name');
    $mtable = $membership_config->getCustomGroupInfo('table_name');
    $mandaat_id_field = $sepa_config->getCustomField('mandaat_nr', 'column_name');
    $mmandaat_id_field = $membership_config->getCustomField('mandaat_id', 'column_name');
    
    $sql = "SELECT `m`.* FROM `".$mtable."` `member`
            INNER JOIN `".$table."` `m` ON `member`.`".$mmandaat_id_field."` = `m`.`".$mandaat_id_field."`
            WHERE `member`.`entity_id` = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($membership_id, 'Integer')));
    $this->mandaat[$membership_id] = false;
    if ($dao->fetch()) {
      $values = array();
      $fields = $sepa_config->getAllCustomFields();
      foreach($fields as $field) {
        $fname = $field['name'];
        $cname = $field['column_name'];
        $values[$fname] = $dao->$cname;
      }
      $this->mandaat[$membership_id] = $values;
    }
    return $this->mandaat[$membership_id];
  }
  
  public static function membershipTypes() {
    return CRM_Member_BAO_MembershipType::getMembershipTypes(FALSE);
  }
  
}

