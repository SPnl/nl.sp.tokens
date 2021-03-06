<?php

require_once 'tokens.civix.php';

function tokens_civicrm_tokens(&$tokens) {
  $tokens['tokens']['tokens.contact_id'] = 'Contact ID';
  $tokens['tokens']['tokens.today'] = 'Date of Today';
  
  CRM_Tokens_Afdeling::tokens($tokens);  
  CRM_Tokens_Membership::tokens($tokens);
  CRM_Tokens_Kaderfunctie::tokens($tokens);
  CRM_Tokens_Contact::tokens($tokens);
}

function tokens_civicrm_tokenValues(&$values, $cids, $job = null, $tokens = array(), $context = null) {  
  if (!empty($tokens['tokens'])) {
    if (in_array('contact_id', $tokens['tokens']) || array_key_exists('contact_id', $tokens['tokens'])) {
       tokens_contact_id($values, $cids, $job, $tokens, $context);
    }
    if (in_array('today', $tokens['tokens']) || array_key_exists('today', $tokens['tokens'])) {
       tokens_today($values, $cids, $job, $tokens, $context);
    }
  }
  $sp_tokens = CRM_Tokens_Afdeling::singleton();
  $sp_tokens->tokenValues($values, $cids, $job, $tokens, $context);

  $kaderfuncties_tokens = CRM_Tokens_Kaderfunctie::singleton();
  $kaderfuncties_tokens->tokenValues($values, $cids, $job, $tokens, $context);
  
  $membership_tokens = CRM_Tokens_Membership::singleton();
  $membership_tokens->tokenValues($values, $cids, $job, $tokens, $context);

  $contact_tokens = CRM_Tokens_Contact::singleton();
  $contact_tokens->tokenValues($values, $cids, $job, $tokens, $context);
  
}

function tokens_today(&$values, $cids, $job = null, $tokens = array(), $context = null) {
  $months[1] = 'januari';
  $months[2] = 'februari';
  $months[3] = 'maart';
  $months[4] = 'april';
  $months[5] = 'mei';
  $months[6] = 'juni';
  $months[7] = 'juli';
  $months[8] = 'augustus';
  $months[9] = 'september';
  $months[10] = 'oktober';
  $months[11] = 'november';
  $months[12] = 'december';

  $date = new DateTime();
  foreach($cids as $cid) {
    $values[$cid]['tokens.today'] = $date->format('d').' '.$months[$date->format('n')].' '.$date->format('Y');
  }
}

function tokens_contact_id(&$values, $cids, $job = null, $tokens = array(), $context = null) { 
  foreach($cids as $cid) {
    $values[$cid]['tokens.contact_id'] = $cid;
  }
}

/**
 * Implementation of hook_civicrm_permission
 * Voegt extra permissies toe die gebruikt worden door deze extensie.
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_permission
 */
function tokens_civicrm_permission(&$permissions) {
  CRM_Tokens_AccessControl::getExtraPermissions($permissions);
}

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function tokens_civicrm_config(&$config) {
  _tokens_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function tokens_civicrm_xmlMenu(&$files) {
  _tokens_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function tokens_civicrm_install() {
  return _tokens_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function tokens_civicrm_uninstall() {
  return _tokens_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function tokens_civicrm_enable() {
  return _tokens_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function tokens_civicrm_disable() {
  return _tokens_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function tokens_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _tokens_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function tokens_civicrm_managed(&$entities) {
  return _tokens_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function tokens_civicrm_caseTypes(&$caseTypes) {
  _tokens_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function tokens_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _tokens_civix_civicrm_alterSettingsFolders($metaDataFolders);
}
