<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Tokens_Contact {

    protected static $singleton;

    protected $landline_type;

    protected $mobile_type;

    protected $home_location_type;

    public function __construct()
    {
        $this->home_location_type = civicrm_api3('LocationType', 'getvalue', array('return' => 'id', 'name' => 'Thuis'));
        $phone_type = civicrm_api3('OptionGroup', 'getvalue', array('return' => 'id', 'name' => 'phone_type'));
        $this->landline_type = civicrm_api3('OptionValue', 'getvalue', array('return' => 'value', 'name' => 'Phone', 'option_group_id' => $phone_type));
        $this->mobile_type = civicrm_api3('OptionValue', 'getvalue', array('return' => 'value', 'name' => 'Mobile', 'option_group_id' => $phone_type));
    }

    /**
     *
     * @return CRM_Tokens_Contact
     */
    public static function singleton() {
        if (!self::$singleton) {
            self::$singleton = new CRM_Tokens_Contact();
        }
        return self::$singleton;
    }

    public static function tokens(&$tokens) {
        $tokens['contact']['contact.landline'] = ts('Landline phone');
        $tokens['contact']['contact.mobile'] = ts('Mobile phone');
    }

    public function tokenValues(&$values, $cids, $job = null, $tokens = array(), $context = null) {
        if (!empty($tokens['contact'])) {
            if (in_array('landline', $tokens['contact']) || array_key_exists('landline', $tokens['contact'])) {
                $this->landline($values, $cids, $job, $tokens, $context);
            }
            if (in_array('mobile', $tokens['contact']) || array_key_exists('mobile', $tokens['contact'])) {
                $this->mobile($values, $cids, $job, $tokens, $context);
            }
        }
    }

    public function mobile(&$values, $cids, $job = null, $tokens = array(), $context = null) {
        foreach($cids as $cid) {
            $values[$cid]['contact.mobile'] = '';
            try {
                $params = array();
                $params['contact_id'] = $cid;
                $params['phone_type_id'] = $this->mobile_type;
                $params['location_type_id'] = $this->home_location_type;
                $params['return'] = 'phone';
                $values[$cid]['contact.mobile'] = civicrm_api3('Phone', 'getvalue', $params);
            } catch (Exception $e) {
                //do nothing
            }
        }
    }

    public function landline(&$values, $cids, $job = null, $tokens = array(), $context = null) {
        foreach($cids as $cid) {
            $values[$cid]['contact.landline'] = '';
            try {
                $params = array();
                $params['contact_id'] = $cid;
                $params['phone_type_id'] = $this->landline_type;
                $params['location_type_id'] = $this->home_location_type;
                $params['return'] = 'phone';
                $values[$cid]['contact.landline'] = civicrm_api3('Phone', 'getvalue', $params);
            } catch (Exception $e) {
                //do nothing
            }
        }
    }
}