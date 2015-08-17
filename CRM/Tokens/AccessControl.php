<?php

class CRM_Tokens_AccessControl {

  public static function getExtraPermissions(&$permissions) {
    $permissions['access sepa tokens'] = ts('CiviCRM') . ': ' . ts('Access SEPA mandaat tokens');
  }

  public static function accessSepaTokens() {
    return CRM_Core_Permission::check('access sepa tokens') ? true : false;
  }

}