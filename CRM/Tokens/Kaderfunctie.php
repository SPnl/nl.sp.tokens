<?php

class CRM_Tokens_Kaderfunctie {

    protected static $singelton;

    protected $afdeling = false;

    protected $address = array();

    protected $phone = array();

    protected $website = array();

    protected $email = array();

    protected $functies = array();

    public function __construct() {
    }

    public static function tokens(&$tokens) {
        $tokens['kaderfunctie']['kaderfunctie.afdeling_naam'] = 'Kaderfunctie afdeling';
        $tokens['kaderfunctie']['kaderfunctie.afdeling_adres'] = 'Kaderfunctie afdelingsadres';
        $tokens['kaderfunctie']['kaderfunctie.afdeling_email'] = 'Kaderfunctie afdelingse-mailadres';
        $tokens['kaderfunctie']['kaderfunctie.afdeling_telefoon'] = 'Kaderfunctie afdelingstelefoon';
        $tokens['kaderfunctie']['kaderfunctie.afdeling_website'] = 'Kaderfunctie afdelingswebsite';
        $tokens['kaderfunctie']['kaderfunctie.afdeling_functies'] = 'Kaderfunctie afdelsingsfuncties';
    }

    public function afdeling_functies(&$values, $cids, $job = null, $tokens = array(), $context = null) {
        $this->findAfdelingForContact($cids);
        foreach($cids as $cid) {
            $values[$cid]['kaderfunctie.afdeling_functies'] = '';
            //find afdeling
            if (isset($this->afdeling[$cid])) {
                $functies = $this->findFuncties($this->afdeling[$cid]);
                if (!empty($functies)) {
                    $values[$cid]['kaderfunctie.afdeling_functies'] = $functies;
                }
            }
        }
    }

    public function afdeling_naam(&$values, $cids, $job = null, $tokens = array(), $context = null) {
        $this->findAfdelingForContact($cids);
        foreach($cids as $cid) {
            $values[$cid]['kaderfunctie.afdeling_naam'] = '';
            //find afdeling
            if (isset($this->afdeling[$cid])) {
                $values[$cid]['kaderfunctie.afdeling_naam'] = CRM_Contact_BAO_Contact::displayName($this->afdeling[$cid]);
            }
        }
    }

    public function afdeling_adres(&$values, $cids, $job = null, $tokens = array(), $context = null) {
        $this->findAfdelingForContact($cids);
        foreach($cids as $cid) {
            $values[$cid]['kaderfunctie.afdeling_adres'] = '';
            //find afdeling
            if (isset($this->afdeling[$cid])) {
                $address = $this->findAddress($this->afdeling[$cid]);
                if (!empty($address)) {
                    $values[$cid]['kaderfunctie.afdeling_adres'] = nl2br($address);
                }
            }
        }
    }

    public function afdeling_telefoon(&$values, $cids, $job = null, $tokens = array(), $context = null) {
        $this->findAfdelingForContact($cids);
        foreach($cids as $cid) {
            $values[$cid]['kaderfunctie.afdeling_telefoon'] = '';
            //find afdeling
            if (isset($this->afdeling[$cid])) {
                $phone = $this->findPhone($this->afdeling[$cid]);
                if (!empty($phone)) {
                    $values[$cid]['kaderfunctie.afdeling_telefoon'] = $phone;
                }
            }
        }
    }

    public function afdeling_email(&$values, $cids, $job = null, $tokens = array(), $context = null) {
        $this->findAfdelingForContact($cids);
        foreach($cids as $cid) {
            $values[$cid]['kaderfunctie.afdeling_email'] = '';
            //find afdeling
            if (isset($this->afdeling[$cid])) {
                $email = $this->findEmail($this->afdeling[$cid]);
                if (!empty($email)) {
                    $values[$cid]['kaderfunctie.afdeling_email'] = $email;
                }
            }
        }
    }

    public function afdeling_website(&$values, $cids, $job = null, $tokens = array(), $context = null) {
        $this->findAfdelingForContact($cids);
        foreach($cids as $cid) {
            $values[$cid]['kaderfunctie.afdeling_website'] = '';
            //find afdeling
            if (isset($this->afdeling[$cid])) {
                $website = $this->findWebsite($this->afdeling[$cid]);
                if (!empty($website)) {
                    $values[$cid]['kaderfunctie.afdeling_website'] = $website;
                }
            }
        }
    }

    public function tokenValues(&$values, $cids, $job = null, $tokens = array(), $context = null) {
        if (!empty($tokens['kaderfunctie'])) {
            if (in_array('afdeling_naam', $tokens['kaderfunctie']) || array_key_exists('afdeling_naam', $tokens['kaderfunctie'])) {
                $this->afdeling_naam($values, $cids, $job, $tokens, $context);
            }
            if (in_array('afdeling_adres', $tokens['kaderfunctie']) || array_key_exists('afdeling_adres', $tokens['kaderfunctie'])) {
                $this->afdeling_adres($values, $cids, $job, $tokens, $context);
            }
            if (in_array('afdeling_telefoon', $tokens['kaderfunctie']) || array_key_exists('afdeling_telefoon', $tokens['kaderfunctie'])) {
                $this->afdeling_telefoon($values, $cids, $job, $tokens, $context);
            }
            if (in_array('afdeling_email', $tokens['kaderfunctie']) || array_key_exists('afdeling_email', $tokens['kaderfunctie'])) {
                $this->afdeling_email($values, $cids, $job, $tokens, $context);
            }
            if (in_array('afdeling_website', $tokens['kaderfunctie']) || array_key_exists('afdeling_website', $tokens['kaderfunctie'])) {
                $this->afdeling_website($values, $cids, $job, $tokens, $context);
            }
            if (in_array('afdeling_functies', $tokens['kaderfunctie']) || array_key_exists('afdeling_functies', $tokens['kaderfunctie'])) {
                $this->afdeling_functies($values, $cids, $job, $tokens, $context);
            }
        }
    }

    /**
     *
     * @return CRM_Tokens_Kaderfunctie()
     */
    public static function singleton() {
        if (!self::$singelton) {
            self::$singelton = new CRM_Tokens_Kaderfunctie();
        }
        return self::$singelton;
    }

    protected function findFuncties($contact_id) {
        if (isset($this->functies[$contact_id])) {
            return $this->functies[$contact_id];
        }

        $this->functies[$contact_id] = '';
        $sql = "select r.id, rt.label_a_b, r.contact_id_a, r.contact_id_b, c.display_name, r.start_date, r.end_date from civicrm_relationship r
                inner join civicrm_relationship_type rt on r.relationship_type_id = rt.id
                inner join civicrm_contact c on r.contact_id_a = c.id
                where r.contact_id_b = %1
                and rt.contact_type_a = 'Individual'
                and r.is_active = '1'
                and (r.start_date is null OR r.start_date <= NOW())
                and (r.end_date is null or r.end_date >= NOW())";
        $dao = CRM_Core_DAO::executeQuery($sql, array( 1 => array($contact_id, 'Integer')));
        while ($dao->fetch()) {
            if (strlen($this->functies[$contact_id])) {
                $this->functies[$contact_id] .= "<br />\n";
            }
            $this->functies[$contact_id] .= $dao->label_a_b;
            $vantot = '';
            if ($dao->start_date) {
                $startDate = new DateTime($dao->start_date);
                $vantot .= 'van '.$startDate->format(('d-m-Y')).' ';
            }
            if ($dao->end_date) {
                $endDate = new DateTime($dao->end_date);
                $vantot .= 'tot '.$endDate->format(('d-m-Y'));
            }
            if (strlen($vantot)) {
                $this->functies[$contact_id] .= '('.trim($vantot).')';
            }
            $this->functies[$contact_id] .= ': '.$dao->display_name;
            $telefoon = $this->findPhone($dao->contact_id_a);
            if ($telefoon) {
                $this->functies[$contact_id].=', '.$telefoon;
            }
            $email = $this->findEmail($dao->contact_id_a);
            if ($email) {
                $this->functies[$contact_id].=', '.$email;
            }
        }

        return $this->functies[$contact_id];
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

    protected function findEmail($contact_id) {
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
            $this->website[$contact_id] = $website->email;
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
        if (is_array($this->afdeling)) {
            return;
        } else {
            $this->afdeling = array();
        }

        $contact_ids = implode(",", $cids);
        $sql = "select r.id, rt.id, r.contact_id_a, r.contact_id_b from civicrm_relationship r
                inner join civicrm_relationship_type rt on r.relationship_type_id = rt.id
                where r.contact_id_a in (".$contact_ids.")
                group by r.contact_id_a;";

        $dao = CRM_Core_DAO::executeQuery($sql);
        while($dao->fetch()) {
            $this->afdeling[$dao->contact_id_a] = $dao->contact_id_b;
        }
    }



}