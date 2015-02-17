<?php

class CRM_Tokens_Membership {
  
  protected static $singleton;
  
  protected $mandaat = array();

  protected $sp_mid;

  protected $sprood_mid;

  protected $rood_mid;
  
  protected function __construct() {
    $this->sp_mid = civicrm_api3('MembershipType', 'getvalue', array('return' => 'id', 'name' => 'Lid SP'));
    $this->sprood_mid = civicrm_api3('MembershipType', 'getvalue', array('return' => 'id', 'name' => 'Lid SP en ROOD'));
    $this->rood_mid = civicrm_api3('MembershipType', 'getvalue', array('return' => 'id', 'name' => 'Lid ROOD'));
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
      $tokens['membership']['membership.contribution'] = 'Membership fee';
      if (class_exists('CRM_Sepamandaat_Config_SepaMandaat')) {
        $tokens['membership']['membership.'.$mid.'_mandaat_id'] = 'Mandaat ID ('.$name.')';
        $tokens['membership']['membership.'.$mid.'_mandaat_datum'] = 'Mandaat Datum ('.$name.')';
        $tokens['membership']['membership.'.$mid.'_mandaat_iban'] = 'Mandaat IBAN ('.$name.')';
        $tokens['membership']['membership.'.$mid.'_mandaat_type'] = 'Mandaat Type ('.$name.')';

        $tokens['membership']['membership.mandaat_id'] = 'Mandaat ID';
        $tokens['membership']['membership.mandaat_datum'] = 'Mandaat Datum';
        $tokens['membership']['membership.mandaat_iban'] = 'Mandaat IBAN';
        $tokens['membership']['membership.mandaat_type'] = 'Mandaat Type';
      }
    }
  }
  
  public function tokenValues(&$values, $cids, $job = null, $tokens = array(), $context = null) {
    if (!empty($tokens['membership'])) {
      foreach($tokens['membership'] as $k => $i) {
        $key = $i;
        if (is_int($i)) {
          $key = $k;
        }
        $und_pos = stripos($key, "_");
        $mid = substr($key, 0, $und_pos);
        if (!empty($mid) && is_int($mid)) {
          $token = substr($key, $und_pos + 1);
        } else {
          unset($mid);
          $token = $key;
        }

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
        if ($token == 'mandaat_type') { 
          $this->mandaat_typetokens($mid, $key, $values, $cids, $job, $tokens, $context);
        }
      }
    }
  }

  protected function getMembership($contact_id, $mtype_id) {
    if (empty($mtype_id)) {
      $membership = CRM_Member_BAO_Membership::getContactMembership($contact_id, $this->rood_mid, false);
      if (!empty($membership) && !empty($membership['id'])) {
        return $membership['id'];
      }
      $membership = CRM_Member_BAO_Membership::getContactMembership($contact_id, $this->sprood_mid, false);
      if (!empty($membership) && !empty($membership['id'])) {
        return $membership['id'];
      }
      $membership = CRM_Member_BAO_Membership::getContactMembership($contact_id, $this->sp_mid, false);
      if (!empty($membership) && !empty($membership['id'])) {
        return $membership['id'];
      }
    } else {
      $membership = CRM_Member_BAO_Membership::getContactMembership($contact_id, $mtype_id, false);
      if (!empty($membership) && !empty($membership['id'])) {
        return $membership['id'];
      }
    }
    return false;
  }
  
  public function contribution($mtype_id, $key, &$values, $cids, $job = null, $tokens = array(), $context = null) { 
    $sql = "SELECT MAX(`c`.`receive_date`), `c`.* FROM `civicrm_membership_payment` `m`
          INNER JOIN `civicrm_contribution` `c` ON `m`.`contribution_id` = `c`.`id`
          WHERE `m`.`membership_id` = %1
          LIMIT 1";
    
    foreach($cids as $cid) {
      $values[$cid]['membership.'.$key] = 'Onbekend';
      $mid = $this->getMembership($cid, $mtype_id);
      if (!empty($mid)) {
        $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($mid, 'Integer')));
        if ($dao->fetch()) {
          $values[$cid]['membership.'.$key] = CRM_Utils_Money::format($dao->total_amount, $dao->currency);
        }
      }
    }
  }
  
  public function mandaat_id_tokens($mtype_id, $key, &$values, $cids, $job = null, $tokens = array(), $context = null) { 
    foreach($cids as $cid) {
      $values[$cid]['membership.'.$key] = '';
      $mid = $this->getMembership($cid, $mtype_id);
      $mandaat = false;
      if (!empty($mid)) {
        $mandaat = $this->findMandaat($mid);
      }
      if ($mandaat) {
        $values[$cid]['membership.'.$key] = $mandaat['mandaat_nr'];
      }
    }
  }
  
  public function mandaat_type_tokens($mtype_id, $key, &$values, $cids, $job = null, $tokens = array(), $context = null) { 
        
    foreach($cids as $cid) {
      $values[$cid]['membership.'.$key] = '';
      $mid = $this->getMembership($cid, $mtype_id);
      $mandaat = false;
      if (!empty($mid)) {
        $mandaat = $this->findMandaat($mid);
      }
      if ($mandaat) {
        if ($mandaat['status'] === 'OOFF') {
          $values[$cid]['membership.'.$key] = 'Eenmalig';
        } else {
          $values[$cid]['membership.'.$key] = 'Doorlopend';
        }        
      }
    }
  }
  
  public function mandaat_iban_tokens($mtype_id, $key, &$values, $cids, $job = null, $tokens = array(), $context = null) {
    foreach($cids as $cid) {
      $values[$cid]['membership.'.$key] = '';
      $mid = $this->getMembership($cid, $mtype_id);
      $mandaat = false;
      if (!empty($mid)) {
        $mandaat = $this->findMandaat($mid);
      }
      if ($mandaat) {
        $values[$cid]['membership.'.$key] = $mandaat['IBAN'];
      }
    }
  }
  
  public function mandaat_datum_tokens($mtype_id, $key, &$values, $cids, $job = null, $tokens = array(), $context = null) { 
    $config = CRM_Core_Config::singleton();
    
    foreach($cids as $cid) {
      $values[$cid]['membership.'.$key] = '';
      $mid = $this->getMembership($cid, $mtype_id);
      $mandaat = false;
      if (!empty($mid)) {
        $mandaat = $this->findMandaat($mid);
      }
      if ($mandaat) {
        $values[$cid]['membership.'.$key] = CRM_Utils_Date::customFormat($mandaat['mandaat_datum'], $config->dateformatFull);
      }
    }
  }
  
  protected function findMandaat($membership_id) {
    if (isset($this->mandaat[$membership_id])) {
      return $this->mandaat[$membership_id];
    }
    
    if (empty($membership_id)) {
        return false;
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

