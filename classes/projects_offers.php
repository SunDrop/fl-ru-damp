<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_dialogue.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");

/**
 * ����� ��� ������ � ������������� �� ��������
 *
 */
class projects_offers
{
    
    /**
     * ���� ��� ����� ����������
     */
    const SALT = 'hfhdjs';


    /**
     * ���������� ����������� � �������
     *
     * @param integer $user_id             id ����������
     * @param integer $prj_id              id �������
     * @param integer $cost_from           ���� ��
     * @param integer $cost_to             ���� ��
     * @param integer $cost_type           ������ ����
     * @param integer $time_from           ����� ��
     * @param integer $time_to             ����� ��
     * @param integer $time_type           ��� ������� (0 - ����, 1 - ���. 2 - ������)
     * @param string $text                 ����� �����������
     * @param string $work1_id             id 1 ������������� ������
     * @param string $work2_id             id 2 ������������� ������
     * @param string $work3_id             id 3 ������������� ������
     * @param string $work1_link           ������ �� 1 ������������� ������
     * @param string $work2_link           ������ �� 2 ������������� ������
     * @param string $work3_link           ������ �� 3 ������������� ������
     * @param string $work1_name           �������� 1 ������������� ������
     * @param string $work2_name           �������� 2 ������������� ������
     * @param string $work3_name           �������� 3 ������������� ������
     * @param array $work1_pict            ���� 1 ������
     * @param array $work2_pict            ���� 2 ������
     * @param array $work3_pict            ���� 3 ������
     * @param array $work1_prev_pict       ���� ������ 1 ������
     * @param array $work2_prev_pict       ���� ������ 2 ������
     * @param array $work3_prev_pict       ���� ������ 3 ������
     * @param boolean $for_customer_only   ���������� ����������� ������ ������������
     * @param integer $dialogueId          id ����� �������
     * @param integer $emp_read            ��������� ������������� ��� ���
     * @param boolean $prefer_sbr          ����������� �������� � ���
     * @param boolean $auto                0 ��� ID ���������� (�� �����������)
     * @param integer $moduser_id          UID ������������ (������), ����������� �����������. ���� null - �� $user_id id ����������
     * @param string modified_reason       ������� ��������������
     * @return string                      ����� ������ � ������ ��������
     */

    function AddOffer($user_id, $prj_id, $cost_from, $cost_to, $cost_type, $time_from, $time_to, $time_type, $text,
    $work1_id, $work2_id, $work3_id, $work1_link, $work2_link, $work3_link,
    $work1_name, $work2_name, $work3_name, $work1_pict, $work2_pict, $work3_pict,
    $work1_prev_pict, $work2_prev_pict, $work3_prev_pict, $for_customer_only = false, $dialogueId = 0, $emp_read = 0, $prefer_sbr = false, $is_color = false , $contacts = null, $payed_items = '0', $auto = 0, $moduser_id = null, $modified_reason = '')
    {
        global $DB; 
        $obj_portfolio = new portfolio();
        $obj_dialogue = new projects_offers_dialogue();
        
        if( (int) $user_id <=0 || (int) $prj_id <= 0 ) return false;
        $is_color = $is_color?'t':'f';
    
        if (($cost_from < 0) && ($cost_from !== ''))
        {
          $cost_from = 0;
          //$error = '��������� ��������� ������ ���� ������������� ������.';
        }
        if (($cost_to < 0) && ($cost_to !== ''))
        {
          $cost_to = 0;
          //$error = '�������� ��������� ������ ���� ������������� ������.';
        }
        
        if (($cost_to !== '') && ($cost_from !== '') && ($cost_to < $cost_from && $cost_to > 0))
        {
          $wrk = $cost_to;
          $cost_to = $cost_from;
          $cost_from = $wrk;
        }
    
        if (($time_from < 0) && ($time_from !== ''))
        {
          $time_from = 0;
        }
        if (($time_to < 0) && ($time_to !== ''))
        {
          $time_to = 0;
        }
        if (($time_to !== '') && ($time_from !== '') && ($time_to < $time_from) && intval($time_to) > 0)
        {
          $wrk = $time_to;
          $time_to = $time_from;
          $time_from = $wrk;
        }
    
        $prj_id = intval($prj_id);
        $user_id = intval($user_id);
        $cost_from = floatval(str_replace(",", ".", (str_replace(' ', '', $cost_from))));
        $cost_to = floatval(str_replace(",", ".", (str_replace(' ', '', $cost_to))));
        $cost_type = intval($cost_type);
        $time_from = intval($time_from);
        $time_to = intval($time_to);
        $time_type = intval($time_type);
        $text = __paramValue('string', $text);
        $text = str_replace("\r\n", "\n", $text); // C�������� ��� ��� ������� -- ������� ���� �������� �� ����, ����� ��� �������� �������� ��������� ������� �� ����������
        //$text = preg_replace("/(\r\n|\r|\n){3,100}/i", "\r\n\r\n", $text);//trim(substr(change_q(trim($text), true, 90), 0, 3000));
        $for_customer_only = ($for_customer_only) ? 't' : 'f';
        $prefer_sbr = ($prefer_sbr) ? 't' : 'f';
    
        $work1_id = intval($work1_id);
        $work2_id = intval($work2_id);
        $work3_id = intval($work3_id);
        
        $work1_pict = substr(change_q(trim($work1_pict), false, 25), 0, 24);
        $work2_pict = substr(change_q(trim($work2_pict), false, 25), 0, 24);
        $work3_pict = substr(change_q(trim($work3_pict), false, 25), 0, 24);
        
        $work1_prev_pict = substr(change_q(trim($work1_prev_pict), false, 30), 0, 29);
        $work2_prev_pict = substr(change_q(trim($work2_prev_pict), false, 30), 0, 29);
        $work3_prev_pict = substr(change_q(trim($work3_prev_pict), false, 30), 0, 29);

        $work1_pict = $work1_pict == '' && $work1_prev_pict!='' ? $work1_prev_pict : $work1_pict;
        $work2_pict = $work2_pict == '' && $work2_prev_pict!='' ? $work2_prev_pict : $work2_pict;
        $work3_pict = $work3_pict == '' && $work3_prev_pict!='' ? $work3_prev_pict : $work3_pict;
        
        $moduser_id = $moduser_id ? $moduser_id : $user_id;
    
        $payed_items = ($payed_items == '1')?$payed_items:'0';
        
        $sql = "SELECT po.*, pb.id IS NOT NULL AS is_blocked 
          FROM projects_offers AS po
          LEFT JOIN projects_offers_blocked pb ON  pb.src_id = po.id
          WHERE po.project_id = '$prj_id' AND po.user_id = " . $user_id;
    
        $po = $DB->row($sql, $prj_id, $user_id);
        if($DB->error) {
            return $DB->error;
        }
        
        $sql = 'SELECT e.is_pro FROM projects p 
            INNER JOIN employer e ON e.uid = p.user_id WHERE p.id = ?i';
        
        $emp_is_pro = $DB->val( $sql, $prj_id );
        
        if ( $po['is_blocked'] == 't' && $moduser_id == $user_id ) {
            return "OfferIsBlocked";
        }
        
        $slashedText = addslashes($text);
        
        if ($po['id']) {
            if ( $po['refused'] == 't' && $moduser_id == $user_id ) {
                return 403;
            }
            
            $sql = '';
            $sModer = '';
            
            if ( $emp_is_pro != 't' && $moduser_id == $user_id && !hasPermissions('projects') && !is_pro() && 
                ( $po['descr'] != $slashedText 
                || ($po['pict1'] != $work1_pict && !empty($work1_pict)) 
                || ($po['pict2'] != $work2_pict && !empty($work2_pict)) 
                || ($po['pict3'] != $work3_pict && !empty($work3_pict)) 
                || ($po['prev_pict1'] != $work1_prev_pict && !empty($work1_prev_pict)) 
                || ($po['prev_pict2'] != $work2_prev_pict && !empty($work2_prev_pict)) 
                || ($po['prev_pict3'] != $work3_prev_pict && !empty($work3_prev_pict)) )
            ) {
                // �����, �� �����, �� ��� ������ ��������� ���� ����� - ��������� �� �������������
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
                
                $stop_words    = new stop_words();
                $nStopWordsCnt = $stop_words->calculate( $slashedText );
                $sModer = ' , moderator_status =' . ( $nStopWordsCnt ? ' 0 ' : ' NULL ' );
                
                if ( $nStopWordsCnt ) { // ���� ���� ��� �� �������������� - �� �������������
                    $DB->insert( 'moderation', array('rec_id' => $po['id'], 'rec_type' => user_content::MODER_PRJ_OFFERS, 'stop_words_cnt' => $nStopWordsCnt) );
                }
                else { // ����� �� ������������� �� ����������
                    $DB->query( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i;', $po['id'], user_content::MODER_PRJ_OFFERS );
                }
            }
            
            $sql .= "UPDATE projects_offers SET
            descr = '$slashedText',
            cost_from = $cost_from,
            cost_to = $cost_to,
            cost_type = $cost_type,
            time_from = $time_from,
            time_to = $time_to,
            time_type = $time_type,
            portf_id1 = '$work1_id',
            portf_id2 = '$work2_id',
            portf_id3 = '$work3_id',
            pict1 = '$work1_pict',
            pict2 = '$work2_pict',
            pict3 = '$work3_pict',
            prev_pict1 = '$work1_prev_pict',
            prev_pict2 = '$work2_prev_pict',
            prev_pict3 = '$work3_prev_pict',
            only_4_cust = '$for_customer_only',
            prefer_sbr = '$prefer_sbr',
            po_emp_read = '$emp_read',
            is_color = '{$is_color}',
            payed_items = B'{$payed_items}',
            moduser_id = $moduser_id, 
            modified_reason = '$modified_reason',
            " . ($contacts !== null ? "offer_contacts = '{$contacts}'," : "") . "
            modified = now() 
            $sModer 
            WHERE id = {$po['id']} AND refused = false";
            $this->offer_id = $po['id'];
            $DB->squery($sql);
            $error = $DB->error;
            if($dialogueId) {
                $error .= $obj_dialogue->SaveDialogueMessage($user_id, $text, $dialogueId, 0, true, $moduser_id);
                $authorId = $DB->val("SELECT user_id FROM projects WHERE id = ?", $prj_id);
                $memBuff = new memBuff();
                $memBuff->delete("prjEventsCnt{$authorId}"); 
            }
            return $error;
        }
        else {            
            $nStopWordsCnt = 0;
            
            if ( $emp_is_pro != 't' && !is_pro() ) { // ���� ����� ������� ����-�����
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );

                $stop_words    = new stop_words();
                $nStopWordsCnt = $stop_words->calculate( $slashedText );
            }
            
          include_once $_SERVER['DOCUMENT_ROOT'] . '/classes/projects_offers_answers.php';
          $sModVal = !is_pro() && $emp_is_pro != 't' && $nStopWordsCnt ? '0' : 'NULL';
          $sql = "INSERT INTO projects_offers (project_id, user_id, cost_from, cost_to, cost_type, time_from, time_to, time_type, portf_id1, portf_id2, portf_id3, pict1, pict2, pict3, prev_pict1, prev_pict2, prev_pict3, only_4_cust, prefer_sbr, po_emp_read, descr, is_color, payed_items, offer_contacts, po_frl_read, moderator_status, auto)
          ( SELECT $prj_id, $user_id, $cost_from, $cost_to, $cost_type, $time_from, $time_to, $time_type, '$work1_id', '$work2_id', '$work3_id', '$work1_pict', '$work2_pict', '$work3_pict', '$work1_prev_pict', '$work2_prev_pict', '$work3_prev_pict', '$for_customer_only', '$prefer_sbr', '$emp_read', '$slashedText', '{$is_color}', B'{$payed_items}', '{$contacts}', TRUE, $sModVal, {$auto} 
            WHERE NOT EXISTS(SELECT 1 FROM projects_blocked WHERE project_id = $prj_id) ); 
          SELECT currval('projects_offers_id_seq');";
          $po_id = $DB->val($sql);
          $error = $DB->error;
          $this->offer_id = $po_id;
          if ($po_id)
          {
              if ( $emp_is_pro != 't' && !is_pro() && $nStopWordsCnt ) {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
                $DB->insert( 'moderation', array('rec_id' => $po_id, 'rec_type' => user_content::MODER_PRJ_OFFERS, 'stop_words_cnt' => $nStopWordsCnt) );
              }
            $error .= $obj_dialogue->AddDialogueMessage($po_id, $user_id, $text, true, false, true);
          }
    
          if(!$error)
            $_SESSION['po_count'] = (int)$_SESSION['po_count'] + 1;
    
          return $error;
        }
    }

    /**
     * ��������� ������������ ���� � ������� ������� �� ������
     *
     * @param integer $id_offer    �� ������
     * @param boolean $fields     ���� ��� ����������, @example ���������� ��� ���� �������� array("is_color" => "'t'", "payed_items" => "B'1'");
     * @return $error ������;
     */
    function setFieldsOffers($id_offer, $fields = array()) {
        global $DB;
        
        foreach($fields as $field=>$value) {
            $fields_sql[] = "{$field} = {$value}";
        }
        if(!$fields_sql) return false;
        
        $set_fields = implode(", ", $fields_sql);        
        $sql = "UPDATE projects_offers SET {$set_fields} WHERE id = {$id_offer} AND refused = false";
        $DB->squery($sql);
        $error = $DB->error;
        
        return $error;
    }

    /**
     * ���������� ����������� � ��������
     *
     * @param integer $user_id             id ����������
     * @param integer $prj_id              id ��������
     * @param string $text                 ����� �����������
     * @param array $work_pict             ������
     * @param array $work_prev_pict        ������ ������
     * @param boolean $for_customer_only   ���������� ������ ������������
     *
     * @return string                      ����� ������ � ������ ��������
     */
    function AddOfferKon($user_id, $prj_id, $text, $work_pict, $work_prev_pict, $for_customer_only = false )
    {
        global $DB; 
        $obj_portfolio = new portfolio();
        $obj_dialogue = new projects_offers_dialogue();
    
        $prj_id = intval($prj_id);
        $user_id = intval($user_id);
        $text = substr(change_q(trim($text), true, 90), 0, 3000);
        $for_customer_only = ($for_customer_only) ? 't' : 'f';
    
        $attach = array();
        foreach ($work_pict as $key => $value) {
          if ($value != '') {
            $attach[$key]['pict'] = substr(change_q(trim($value), false, 25), 0, 24);
            $attach[$key]['prev'] = substr(change_q(trim($work_prev_pict[$key]), false, 30), 0, 29);
          }
        }
    
        $sql = "SELECT COUNT(*) as num
          FROM projects_offers AS po
          WHERE (po.project_id = '$prj_id') AND (user_id = " . $user_id . ")";
        $count = $DB->val($sql, $prj_id, $user_id);
        if ($count) {
            return "����������� ��� ����������";
        } else {
         $sql = "INSERT INTO projects_offers (project_id, user_id, cost_from, cost_to, cost_type, time_from, time_to, time_type, pict1, pict2, pict3, prev_pict1, prev_pict2, prev_pict3, only_4_cust)
          VALUES (?i, ?i, 0, 0, 0, 0, 0, 0, '', '', '', '', '', '', ? );SELECT currval('projects_offers_id_seq');";
          $po_id = $DB->val($sql, $prj_id, $user_id, $for_customer_only);
          $error = $DB->error;
    
          if ($po_id)
          {
            $error .= $obj_dialogue->AddDialogueMessage($po_id, $user_id, $text, true, false);
            $sql = "";
            foreach ($attach as $value)
            {
              $sql .= "INSERT INTO projects_offers_attach (offer_id, prev_pict, pict) "
                 .  "VALUES (?i, ?, ?);";
            }
            if ($sql != '')
            {
              $DB->query($sql, $po_id, $value['prev'], $value['pict']);
              $error .= $DB->error;
            }
          }
    
          if(!$error)
            $_SESSION['po_count'] = (int)$_SESSION['po_count'] + 1;
    
          return $error;
        }
    }



    /**
     * ������������ ������ � ��������
     *
     * @param integer $user_id             id ����������
     * @param integer $prj_id              id ��������
     * @param array $work_pict             ������
     * @param array $work_prev_pict        ������ ������
     *
     * @return string                      ����� ������ � ������ ��������
     */
    function ChangeOfferKon($user_id, $prj_id, $work_pict, $work_prev_pict)
    {
        global $DB; 
        $prj_id = intval($prj_id);
        $user_id = intval($user_id);
        $attach = array();
        foreach ($work_pict as $key => $value) {
          if ($value != '') {
            $attach[$key]['pict'] = substr(change_q(trim($value), false, 25), 0, 24);
            $attach[$key]['prev'] = substr(change_q(trim($work_prev_pict[$key]), false, 30), 0, 29);
          }
        }
        
        $sql = "SELECT id FROM projects_offers WHERE project_id=?i AND user_id=?i";
          $po_id = $DB->val($sql, $prj_id, $user_id);
          $error = $DB->error;
    
          if ($po_id && !$error) {
            $sql = $error = "";
            foreach ($attach as $value)
            {
            $sql .= "INSERT INTO projects_offers_attach (offer_id, prev_pict, pict) "
                 .  "VALUES (?i, ?, ? );";
            }
            if ($sql != '')
            {
              $DB->query($sql, $po_id, $value['prev'], $value['pict']);
              $error .= $DB->error;
            }
          }
        return $error;
    }



    /**
     * �������� �����������
     *
     * @param integer $offer_id        id �����������
     * @param integer $prj_id          id �������
     * @param integer $user_id         id ������������
     *
     * @param boolean $force           ������� �������� ��������� ��� �������� id ������������
     */
    function DelOffer($offer_id, $prj_id, $user_id, $force = false)
    {
        global $DB; 
        $offer_id = intval($offer_id);
        $prj_id   = intval($prj_id);
        $user_id  = intval($user_id);
        $force    = ($force == true);
        
        $sql = "DELETE FROM projects_offers WHERE id=?i AND project_id= ?i " . ((!$force) ? " AND user_id=" . $user_id : "") . ";";
        $DB->query($sql, $offer_id, $prj_id);
        $error = $DB->error;
        
        if ( !$error ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
            $sql .= $DB->parse( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i;', $prj_id, user_content::MODER_PRJ_OFFERS );
        }
        
        return $error;
    }
    
    /**
     * �������������. ����� ��������. �������� �������: �������� ����������� � �������
     *
     * @param int $offer_id id �����������
     * @param int $prj_id id �������
     * @param string $prj_name �������� �������
     * @param int $prj_user_id UID ������������, ���������� ������
     */
    function DelOfferLog( $offer_id = 0, $prj_id = 0, $prj_name = '', $prj_user_id ) {
        $aUser = $GLOBALS['DB']->row( 'SELECT f.uid, f.login, f.uname, f.usurname 
            FROM projects_offers po 
            INNER JOIN freelancer f ON f.uid = po.user_id 
            WHERE po.id = ?i', $offer_id 
        );
        
        if ( $aUser['uid'] != $_SESSION['uid'] && hasPermissions('projects')) { 
	        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );
	        
	        $sPrjLink = getFriendlyURL( 'project', $prj_id );
	        $sReason  = '����������� �� <a href="' . $GLOBALS['host'] . '/users/' . $aUser['login'] . '" target="_blank">' 
	           . $aUser['uname'] . ' ' . $aUser['usurname'] . ' [' . $aUser['login'] . ']</a>';
	        
	        admin_log::addLog( admin_log::OBJ_CODE_PROJ, admin_log::ACT_ID_PRJ_DEL_OFFER, $prj_user_id, $prj_id, $prj_name, $sPrjLink, 0, '', 0, $sReason );
	    }
    }
    
    /**
     * ���������� ����������� �� �������� ����������� � �������
     * 
     * @param int $offer_id ID �����������
     * @param int $deluser_id UID ����������
     */
    function DelOfferNotification( $offer_id = 0, $deluser_id = 0 ) {
        $aOffer = $GLOBALS['DB']->row( 'SELECT po.project_id, f.uid, f.login, f.uname, f.usurname, p.name 
            FROM projects_offers po 
            INNER JOIN projects p ON p.id = po.project_id 
            INNER JOIN freelancer f ON f.uid = po.user_id 
            WHERE po.id = ?i', $offer_id 
        );
        
        if ( $aOffer['uid'] != $deluser_id ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
            messages::offerDeletedNotification( $aOffer );
        }
    }

    /**
     * ��������� ����������� ����������� ������������ �� ����������� �������
     *
     * @param integer $prj_id          id �������
     * @param integer $user_id         id ������������
     *
     * @return array                   ������ �����������
     */
    function GetPrjOffer($prj_id, $user_id)
    {
        global $DB;
        $ret = false;
        
        //@todo: ������������� ������� completed_cnt � users_counters ������� � � ��� �����������
        //��� �����, ��� � ��� ��� ���� ���-�� �� ����� �� reserves_completed_cnt
        //����� �������� ���� �� ������ �� � ����������� ����
        
        if ($user_id > 0) {
          $sel_blocked  = ', pb.reason as blocked_reason, pb.blocked_time, COALESCE(pb.src_id::boolean, false) as is_blocked, 
              admins.login as admin_login, admins.uname as admin_uname, admins.usurname as admin_usurname ';
          $join_blocked = 'LEFT JOIN projects_offers_blocked pb ON po.id = pb.src_id 
              LEFT JOIN users as admins ON pb.admin = admins.uid ';
            
          $sql = "SELECT po.*,
          fl.uid, fl.login, fl.uname, fl.usurname, fl.email, fl.photo, fl.photosm, fl.spec, fl.is_pro, fl.is_team, fl.is_pro_test, fl.is_profi, uc.ops_frl_plus as ops_plus, uc.ops_frl_null as ops_null, uc.ops_frl_minus as ops_minus, fl.role, fl.warn, fl.is_banned, fl.ban_where, rating_get(fl.rating, fl.is_pro, fl.is_verify, fl.is_profi) as rating, fl.is_verify, fl.photo_modified_time, fl.reg_date, fl.modified_time,
          p.name AS spec_name, 
          COALESCE(sbr_meta.completed_cnt, 0) + COALESCE(uc.reserves_completed_cnt, 0) AS completed_cnt, -- ������ �� + ����� ��
          cr.country_name,
          ct.city_name,
          uc.ops_emp_plus + uc.ops_frl_plus as ops_all_plus, uc.ops_emp_null + uc.ops_frl_null as ops_all_null, uc.ops_emp_minus + uc.ops_frl_minus as ops_all_minus,
          uc.ops_emp_plus, uc.ops_emp_null, uc.ops_emp_minus,
          uc.sbr_opi_plus, uc.sbr_opi_null, uc.sbr_opi_minus,
          uc.paid_advices_cnt + uc.sbr_opi_plus + uc.ops_emp_plus + uc.tu_orders_plus + uc.projects_fb_plus as opinions_plus,
          uc.sbr_opi_minus + uc.ops_emp_minus + uc.tu_orders_minus + uc.projects_fb_minus as opinions_minus
          $sel_blocked
          FROM projects_offers AS po
          INNER JOIN freelancer as fl ON po.user_id=fl.uid
          $join_blocked
          LEFT JOIN users_counters uc ON uc.user_id = fl.uid
          LEFT JOIN sbr_meta ON sbr_meta.user_id = fl.uid -- ������ ��
          LEFT JOIN professions p ON p.id=fl.spec
          LEFT JOIN country cr ON cr.id=fl.country
          LEFT JOIN city ct ON ct.id=fl.city
          WHERE (po.project_id = ?i ) AND po.user_id=?i AND (fl.is_banned='0')";
          $ret = $DB->row($sql, $prj_id, $user_id);
      
          // �������� ��������.
          if ($ret['id']) {
            $sql = "SELECT a.id, a.prev_pict as prev, a.pict "
                 . "FROM projects_offers_attach AS a "
                 . "WHERE a.offer_id= ?i ";
            $ret['attach'] = $DB->rows($sql, $ret['id']);
          }
        }
        return $ret;
    }
    
    /**
     * ���������� ������ �� ���
     * � ����� �������� �����������
     * � ������ ������������
     */
    function getSbrExecData ($sbrID) {
        global $DB;
        if (!$sbrID) {
            return false;
        }
        $sql = "
            SELECT s.status, f.uname, f.usurname, f.login, rating_get(f.rating, f.is_pro, f.is_verify, f.is_profi) as rating,
            uc.ops_emp_plus, uc.ops_emp_null, uc.ops_emp_minus, uc.sbr_opi_plus, uc.sbr_opi_null, uc.sbr_opi_minus
            FROM sbr s
            INNER JOIN freelancer f ON f.uid = s.frl_id
            INNER JOIN users_counters uc ON s.frl_id = uc.user_id
            WHERE s.id = ?i";
        return $DB->row($sql, $sbrID);
    }
    
    /**
     * ��������� ����������� �� ��� ID
     *
     * @param  integer $offer_id id �������
     * @return array ������ �����������
     */
    function GetPrjOfferById( $offer_id = 0 ) {
        $ret = false;
        $sql = 'SELECT po.*,
            fl.uid, fl.login, fl.uname, fl.usurname, fl.photo, fl.photosm, fl.spec, fl.is_pro, fl.is_team, fl.is_pro_test, uc.ops_frl_plus as ops_plus, uc.ops_frl_null as ops_null, uc.ops_frl_minus as ops_minus, fl.role, fl.warn, fl.is_banned, fl.ban_where, rating_get(fl.rating, fl.is_pro, fl.is_verify) as rating, fl.self_deleted, fl.is_verify, fl.photo_modified_time, fl.reg_date, fl.modified_time,
            p.name AS spec_name,
            cr.country_name,
            ct.city_name,
            uc.ops_emp_plus + uc.ops_frl_plus as ops_all_plus, uc.ops_emp_null + uc.ops_frl_null as ops_all_null, uc.ops_emp_minus + uc.ops_frl_minus as ops_all_minus,
            uc.sbr_opi_plus, uc.sbr_opi_null, uc.sbr_opi_minus, pb.reason as blocked_reason, pb.blocked_time, COALESCE(pb.src_id::boolean, false) as is_blocked, 
            admins.login as admin_login, admins.uname as admin_uname, admins.usurname as admin_usurname 
            FROM projects_offers AS po
            INNER JOIN freelancer as fl ON po.user_id=fl.uid
            LEFT JOIN projects_offers_blocked pb ON po.id = pb.src_id 
            LEFT JOIN users as admins ON pb.admin = admins.uid 
            LEFT JOIN users_counters uc ON uc.user_id=fl.uid
            LEFT JOIN professions p ON p.id=fl.spec
            LEFT JOIN country cr ON cr.id=fl.country
            LEFT JOIN city ct ON ct.id=fl.city
            WHERE po.id = ?i';
        
        $ret = $GLOBALS['DB']->row( $sql, $offer_id );
        
        // �������� ��������.
        if ( $ret['id'] ) {
            $sql = 'SELECT a.id, a.prev_pict as prev, a.pict 
                FROM projects_offers_attach AS a 
                WHERE a.offer_id= ?i';
            
            $ret['attach'] = $GLOBALS['DB']->rows( $sql, $ret['id'] );
        }
        
        return $ret;
    }
    
    /**
     * ��������� �����������
     *
     * @param  integer $offer_id id �����������
     * @param  integer $user_id UID ������������
     * @param  integer $project_id id �������
     * @param  string $reason �������
     * @param  string $reason_id id �������, ���� ��� ������� �� ������
     * @param  integer $uid uid �������������� (���� 0, ������������ $_SESSION['uid'])
     * @param  boolean $from_stream true - ���������� �� ������, false - �� �����
     * @return integer ID ����������
     */
    function Blocked( $offer_id = 0, $user_id = 0, $project_id = 0, $reason, $reason_id = null, $uid = 0, $from_stream = false ) {      
        global $DB;
        if (!$uid && !($uid = $_SESSION['uid'])) return '������������ ����';
        $sql = "INSERT INTO projects_offers_blocked (src_id, \"admin\", reason, reason_id, blocked_time) VALUES(?i, ?i, ?, ?, NOW()) RETURNING id";
        $sId = $DB->val( $sql, $offer_id, $uid, $reason, $reason_id );

        $authorId = $DB->val("SELECT user_id FROM projects WHERE id = ?", $project_id);
        $mem = new memBuff();
        $mem->delete('prjEventsCnt' . $authorId);
        $mem->delete('prjMsgsCnt' . $authorId);
        $mem->delete('prjEventsCntWst' . $authorId);
        $mem->delete('prjMsgsCntWst' . $authorId);
        $mem->delete('prjEventsCnt' . $user_id);
        $mem->delete('prjMsgsCnt' . $user_id);
        $mem->delete('prjEventsCntWst' . $user_id);
        $mem->delete('prjMsgsCntWst' . $user_id);

        if(!$from_stream) {
            require_once $_SERVER['DOCUMENT_ROOT'].'/classes/messages.php';
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
            
            messages::SendBlockedProjectOffer( $offer_id, $user_id, $project_id, $reason );
            
            $DB->query( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i;', $offer_id, user_content::MODER_PRJ_OFFERS );
            $DB->val( 'UPDATE projects_offers SET moderator_status = ?i WHERE id = ?i', $uid, $offer_id );
        }
        
        return $sId;
    }
        
    /**
     * ������������ �����������
     *
     * @param integer $project_id  id �����������
     * @return string ��������� �� ������
     */
    function UnBlocked( $offer_id ) {
        global $DB;
        $DB->query( 'DELETE FROM projects_offers_blocked WHERE src_id = ?i', $offer_id );
        $row = $DB->row("SELECT p.user_id as euid, o.user_id as fuid FROM projects_offers o INNER JOIN projects p ON o.project_id = p.id WHERE p.id = ?", $offer_id);
        $mem = new memBuff();
        $mem->delete('prjEventsCnt' . $row['euid']);
        $mem->delete('prjMsgsCnt' . $row['euid']);
        $mem->delete('prjEventsCntWst' . $row['euid']);
        $mem->delete('prjMsgsCntWst' . $row['euid']);
        $mem->delete('prjEventsCnt' . $row['auid']);
        $mem->delete('prjMsgsCnt' . $row['auid']);
        $mem->delete('prjEventsCntWst' . $row['auid']);
        $mem->delete('prjMsgsCntWst' . $row['auid']);
        return $GLOBALS['DB']->error;
    }
    
    /**
     * ��������� ������� ������������� ����������� ����������� ������������ �� ����������� ������� (����/���)
     *
     * @param integer $prj_id          id �������
     * @param integer $user_id         id ������������
     *
     * @return boolean                 ��/���
     */
    function IsPrjOfferExists($prj_id, $user_id)
    {
        global $DB;
        $ret = false;
        if ($user_id > 0) {
          $sql = "SELECT po.id
          FROM projects_offers AS po
          INNER JOIN freelancer as fl ON po.user_id=fl.uid
          WHERE (po.project_id = ?i ) AND po.user_id=?i AND (fl.is_banned='0')";
          $ret = $DB->row($sql, $prj_id, $user_id);
      
        }
  
      if ($ret['id']) return true;
      else            return false;
    }



    /**
     * ��������� ������ ����������� �� ����������� �������
     *
     * @param integer $count           ���������� ���������� �����������
     * @param integer $prj_id          id �������
     * @param string $show_all         ������� ����������� ���� (true) ��� ������ �������� (false) ����������� �������
     * @param string $sort             ���������� ������ �����������
     * @param string $type             ����� ����������� ������ ���� ('o' - ���, 'c' - ��������� � ���������, 'r' - ����������, 'nor' - ��� ����� ������������, 'i' - �����������)
     *
     * @return array                   ������ �����������
     */
    function GetPrjOffers(&$count, $prj_id, $limit, $offset = 0, $user_id = 0, $show_all = false, $sort = 'date', $type = 'a')
    {
      global $DB;
      require_once $_SERVER['DOCUMENT_ROOT'].'/classes/teams.php';
	  require_once $_SERVER['DOCUMENT_ROOT'].'/classes/notes.php';

	  $user_id = intval($user_id);
      $limit = $limit == 'ALL' ? $limit : intval($limit);
      $offset = intval($offset);
      $limit_str = " LIMIT $limit OFFSET $offset";
      
      $bPermissions = hasPermissions( 'projects' );
      
      // ��������� ��������������� �����������
      $sel_blocked  = ", pb.reason as blocked_reason, pb.blocked_time, COALESCE(pb.src_id::boolean, false) as is_blocked";
      $join_blocked = "LEFT JOIN projects_offers_blocked pb ON po.id = pb.src_id ";
      
      if ( $bPermissions ) {
          $sel_blocked  .= ", admins.login as admin_login, admins.uname as admin_uname, admins.usurname as admin_usurname";
          $join_blocked .= "LEFT JOIN users as admins ON pb.admin = admins.uid ";
          $where_blocked = '';
          $and_blocked   = '';
      }
      else {
          $where_blocked = " (po.user_id = {$user_id} OR pb.src_id IS NULL) ";
          $and_blocked   = ' AND ' . $where_blocked;
      }
  
    //@todo: ������������� ������� completed_cnt � users_counters ������� � � ��� �����������
    //��� �����, ��� � ��� ��� ���� ���-�� �� ����� �� reserves_completed_cnt
    //����� �������� ���� �� ������ �� � ����������� ����
      
	  if ($type == 'i') {
		  $sql = "SELECT
          po.*,
          fl.uid, fl.login, fl.uname, fl.usurname, fl.photo, fl.photosm, fl.spec, fl.is_pro, fl.is_team, fl.is_pro_test, fl.is_profi, uc.ops_frl_plus as ops_plus, uc.ops_frl_null as ops_null, uc.ops_frl_minus as ops_minus, fl.role, fl.warn, fl.is_banned, fl.ban_where, rating_get(fl.rating, fl.is_pro, fl.is_verify, fl.is_profi) as rating, fl.is_verify, fl.reg_date, fl.modified_time, fl.photo_modified_time,
          p.name AS spec_name,
          cr.country_name,
          ct.city_name, 
          COALESCE(sbr_meta.completed_cnt, 0) + COALESCE(uc.reserves_completed_cnt, 0) AS completed_cnt, -- ������ �� + ����� ��
          uc.ops_emp_plus + uc.ops_frl_plus as ops_all_plus, uc.ops_emp_null + uc.ops_frl_null as ops_all_null, uc.ops_emp_minus + uc.ops_frl_minus as ops_all_minus,
          uc.ops_emp_plus, uc.ops_emp_null, uc.ops_emp_minus,
          uc.sbr_opi_plus, uc.sbr_opi_null, uc.sbr_opi_minus,
          uc.paid_advices_cnt + uc.sbr_opi_plus + uc.ops_emp_plus + uc.tu_orders_plus + uc.projects_fb_plus as opinions_plus,
          uc.sbr_opi_minus + uc.ops_emp_minus + uc.tu_orders_minus + uc.projects_fb_minus as opinions_minus
          $sel_blocked 
          FROM (SELECT por.*, exec_id, pr.user_id as p_user_id FROM projects AS pr
          LEFT JOIN projects_offers as por ON por.project_id=pr.id AND (pr.exec_id =por.user_id)
          WHERE  pr.id = '$prj_id' AND (por.user_id > 0)"
          . (($show_all)?'':" AND (por.only_4_cust='f')"). ") AS po
          INNER JOIN freelancer as fl ON (po.exec_id=fl.uid" . ( $bPermissions ? '' : ' AND fl.is_banned::integer = 0' ) . ")
          $join_blocked 
          LEFT JOIN professions p ON p.id=fl.spec
          LEFT JOIN users_counters uc ON uc.user_id = fl.uid
          LEFT JOIN sbr_meta ON sbr_meta.user_id = fl.uid -- ������ ��
          LEFT JOIN country cr ON cr.id=fl.country
          LEFT JOIN city ct ON ct.id=fl.city
          " . (($user_id == 0) ? "WHERE $where_blocked" : "WHERE (fl.uid<>" . $user_id . ") $and_blocked");

          $ret = $DB->rows($sql);
          $error = $DB->error;
          if ($error) $error = parse_db_error($error);
          else {
              if ($ret) {
                foreach ($ret as &$value) {
                  // �������� ��������.
                  if ($value['id']) {
                    $sql = "SELECT a.id, a.prev_pict as prev, a.pict "
                         . "FROM projects_offers_attach AS a "
                         . "WHERE a.offer_id= ?i ";
                    $value['attach'] = $DB->rows($sql, $value['id']);
                  }
                }
              }
          }
        }
        else {
          switch ($sort)
          {
            default:
            case 'date':
              $order = ' ORDER BY (fl.is_verify AND fl.is_pro) DESC, fl.is_pro DESC, fl.is_verify DESC, post_date DESC';
              break;
            case 'rating':
              $order = ' ORDER BY rating DESC, fl.is_pro DESC, post_date DESC';
              break;
            case 'opinions':
              $order = ' ORDER BY ssum DESC, fl.is_pro DESC, post_date DESC';
              break;
            case 'time':
              $order = ' ORDER BY (((time_from = 0) OR (time_from IS NULL)) AND ((time_to = 0) OR (time_to IS NULL))) ASC, time_from_days ASC, time_to_days ASC, is_pro DESC, post_date DESC';
              break;
            case 'cost':
              $order = ' ORDER BY (((cost_from = 0) OR (cost_from IS NULL)) AND ((cost_to = 0) OR (cost_to IS NULL))) ASC, usd_cost_from DESC, usd_cost_to DESC, is_pro DESC, post_date DESC';
              break;
          }
          
          switch ($type)
          {
            default:
            case 'a':
              $filter = '';
              break;
            case 'o':
              $filter = ' AND NOT po.selected AND NOT po.refused AND NOT po.frl_refused AND COALESCE(pr.exec_id,0)<>fl.uid';
              break;
            case 'c':
              $filter = ' AND po.selected';
              break;
            case 'r':
              $filter = ' AND po.refused';
              break;
            case 'nor':
              $filter = ' AND NOT po.frl_refused';
              break;
            case 'fr':
              $filter = ' AND po.frl_refused';
              break;
          }

          require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");
          $project_exRates = project_exrates::GetAll();

          $sql = "SELECT
          CASE po.cost_type
            WHEN 0 THEN po.cost_from
            WHEN 1 THEN po.cost_from*{$project_exRates[32]}
            WHEN 2 THEN po.cost_from*{$project_exRates[42]}
            WHEN 3 THEN po.cost_from*{$project_exRates[12]}
          END as usd_cost_from,
          CASE po.cost_type
            WHEN 0 THEN po.cost_to
            WHEN 1 THEN po.cost_to*{$project_exRates[32]}
            WHEN 2 THEN po.cost_to*{$project_exRates[42]}
            WHEN 3 THEN po.cost_to*{$project_exRates[12]}
          END as usd_cost_to,

          CASE po.time_type
            WHEN 0 THEN po.time_from
            WHEN 1 THEN po.time_from * 30
            WHEN 2 THEN po.time_from * 356
            ELSE po.time_from
          END as time_from_days,
          CASE po.time_type
            WHEN 0 THEN po.time_to
            WHEN 1 THEN po.time_to * 30
            WHEN 2 THEN po.time_to * 356
            ELSE po.time_to
          END as time_to_days,
          po.*, pr.user_id AS p_user_id,
          fl.uid, fl.login, fl.uname, fl.usurname, fl.photo, fl.photosm, fl.spec, fl.is_profi, fl.is_pro, fl.is_team, fl.is_pro_test, 
          uc.ops_frl_plus as ops_plus, 
          uc.ops_frl_null as ops_null, 
          uc.ops_frl_minus as ops_minus, 
          fl.role, fl.warn, fl.is_banned, fl.ban_where, 
          rating_get(fl.rating, fl.is_pro, fl.is_verify, fl.is_profi) as rating, 
          zin(uc.ops_emp_plus) + zin(uc.sbr_opi_plus) - zin(uc.ops_emp_minus) - zin(uc.sbr_opi_minus) as ssum, 
          fl.is_verify, fl.reg_date, fl.modified_time, fl.photo_modified_time,
          p.name AS spec_name,
          cr.country_name,
          COALESCE(sbr_meta.completed_cnt, 0) + COALESCE(uc.reserves_completed_cnt, 0) AS completed_cnt, -- ������ �� + ����� ��
          ct.city_name,
          uc.ops_emp_plus + uc.ops_frl_plus as ops_all_plus, uc.ops_emp_null + uc.ops_frl_null as ops_all_null, uc.ops_emp_minus + uc.ops_frl_minus as ops_all_minus,
          uc.ops_emp_plus, uc.ops_emp_null, uc.ops_emp_minus,
          uc.sbr_opi_plus, uc.sbr_opi_null, uc.sbr_opi_minus,
          uc.paid_advices_cnt + uc.sbr_opi_plus + uc.ops_emp_plus + uc.tu_orders_plus + uc.projects_fb_plus as opinions_plus,
          uc.sbr_opi_minus + uc.ops_emp_minus + uc.tu_orders_minus + uc.projects_fb_minus as opinions_minus
          $sel_blocked 
          FROM projects_offers AS po
          LEFT JOIN projects pr ON pr.id=po.project_id
          INNER JOIN freelancer as fl ON po.user_id=fl.uid
          $join_blocked 
          LEFT JOIN users_counters uc ON uc.user_id = fl.uid
          LEFT JOIN professions p ON p.id=fl.spec
          LEFT JOIN country cr ON cr.id=fl.country
          LEFT JOIN city ct ON ct.id=fl.city
          LEFT JOIN sbr_meta ON sbr_meta.user_id = fl.uid -- ������ ��
          WHERE (po.project_id = ?i ) AND (po.user_id > 0) $and_blocked" . ( $bPermissions ? '' : ' AND fl.is_banned::integer = 0' ) . (($show_all)?'':" AND (po.only_4_cust='f')") . (($user_id == 0)?'':" AND (fl.uid<>" . $user_id . ")") . $filter . $order . $limit_str;
          
          $ret = $DB->rows($sql, $prj_id);
          $error = $DB->error;
          if ($error) $error = parse_db_error($error);
          else {
          //$ret = pg_fetch_all($res);
          $sql = "SELECT COUNT(*) as num
            FROM projects_offers AS po
            LEFT JOIN projects as pr ON po.project_id=pr.id
            INNER JOIN freelancer as fl ON po.user_id=fl.uid
            $join_blocked 
            LEFT JOIN professions p ON p.id=fl.spec
            LEFT JOIN country cr ON cr.id=fl.country
            LEFT JOIN city ct ON ct.id=fl.city
            WHERE (po.project_id = ?i ) AND (po.user_id > 0) $and_blocked" . ( $bPermissions ? '' : ' AND fl.is_banned::integer = 0' ) . (($show_all)?'':" AND (po.only_4_cust='f')") . (($user_id == 0)?'':" AND (fl.uid<>" . $user_id . ")") . $filter;

            $count = $DB->val($sql, $prj_id);
            if ($count && $ret) {
                foreach ($ret as &$value) {
                  // �������� ��������.
                  if ($value['id']) {
                    $sql = "SELECT a.id, a.prev_pict as prev, a.pict "
                         . "FROM projects_offers_attach AS a "
                         . "WHERE a.offer_id= ?i";
                    $value['attach'] = $DB->rows($sql, $value['id']);
                  }
                }
            }
          }

        }

         // ��������� ������� ��� plproxy. ����� �� �������, �� ���� �� ����������� ������� ����� ������,
		 // �������� ������ �������� ���
		 if (!empty($ret)) {
			$teams = new teams;
			$notes = new notes;
			$t = $teams->teamsFavorites($ret[0]['p_user_id'], $error);
			$n = $notes->GetNotes($ret[0]['p_user_id'], 0, $error);
			for ($i=0; $i<count($ret); $i++) {
				// ���������
				$ret[$i]['in_team'] = 0;
				for ($j=0; $j<count($t); $j++) {
					if ($t[$j]['uid'] == $ret[$i]['uid']) {
						$ret[$i]['in_team'] = $ret[$i]['uid'];
						break;
					}
				}
				// �������
				$ret[$i]['n_text'] = '';
				for ($j=0; $j<count($n); $j++) {
					if ($n[$j]['to_id'] == $ret[$i]['uid']) {
						$ret[$i]['n_text'] = $n[$j]['n_text'];
						break;
					}
				}
			}
		}

		 return $ret;
  
    }
    
    /**
     * ��������� ������ ����������� �� ����������� ������� (����������� ������)
     *
     * @param integer $prj_id          id �������
     * @return array                   ������ �����������
     */
    public function getPrjOffersLite($prj_id)
    {
        global $DB;
        $sql = "SELECT * FROM projects_offers WHERE project_id = ?i";
        return $DB->rows($sql, (int)$prj_id);
        
    }



    /**
     * ������� ���������� �����������
     *
     * @param integer $pid             id �������
     * @param string $type             ��� �������������� �����������
     *
     * @return array                   ���������� �����������, ���������� ����� ���������
     */
    function CountPrjOffers($pid, $type = 'offers')
    {
        global $DB;
        
        $bPermissions = hasPermissions( 'projects' );
        $join_blocked = " LEFT JOIN projects_offers_blocked pb ON po.id = pb.src_id ";
        $and_blocked  = !$bPermissions ? ' AND pb.src_id IS NULL ' : '';
        
        if ($type == 'executor') {
          $sql = "SELECT COUNT(*) AS po_count, SUM(emp_new_msg_count) AS po_new_msgs_count
          FROM projects AS p
          LEFT JOIN projects_offers AS po ON po.project_id = p.id AND po.user_id = p.exec_id
          INNER JOIN freelancer AS fr ON(po.user_id = fr.uid" . ( $bPermissions ? '' : ' AND fr.is_banned::integer = 0' ) . ")
          $join_blocked
          WHERE p.id=?i AND p.exec_id>0 AND po.user_id > 0 $and_blocked";

          $ret = $DB->row($sql, intval($pid));
          return array($ret['po_count'], $ret['po_new_msgs_count']);
        }
        else {
          $where = "WHERE project_id=" . intval($pid) . ' AND po.user_id > 0 ' .$and_blocked;
          switch ($type)
          {
            default:
            case 'all':
              break;
            case 'offers':
              $where .= " AND po.selected IS NOT TRUE AND po.refused IS NOT TRUE AND po.frl_refused IS NOT TRUE AND COALESCE(p.exec_id,0)<>u.uid";
              break;
            case 'candidate':
              $where .= " AND po.selected";
              break;
            case 'refuse':
              $where .= " AND po.refused";
              break;
          case 'frl_refuse':
              $where .= " AND po.frl_refused";
              break;
          case 'frl_not_refuse': // ��� ������ �� ������ ����� ������������
              if (get_uid(0)) {
                $where .= " AND (NOT po.frl_refused OR po.user_id = " . get_uid(0) . ")";
              } else {
                $where .= " AND NOT po.frl_refused";
              }
              break;
              
          }
          $sql = "SELECT COUNT(*) AS po_count, SUM(emp_new_msg_count) AS po_new_msgs_count 
            FROM projects_offers AS po 
            INNER JOIN projects AS p ON po.project_id=p.id 
            INNER JOIN freelancer AS u ON (po.user_id=u.uid" . ( $bPermissions ? '' : ' AND u.is_banned::integer = 0' ) . ") 
            $join_blocked " . $where;
          $ret = $DB->row($sql);
          return array($ret['po_count'], $ret['po_new_msgs_count']);
        }
    }



    /**
     * ���������� �������� ������� ���������� �� ������� (��. ������� projects_offers_summary)
     *
     * @param integer $fid             id ����������
     *
     * @return mixed                   �������� ������� ��� NULL � ������ ������
     */
    function GetFrlOffersSummary($fid)
    {
      global $DB;
      $sql = "SELECT * FROM projects_offers_summary WHERE user_id = ?i";
  
      return $DB->row($sql, $fid);
    }



    /**
     * ��� ���� "�������". ���������� ������������ ����������� � ������� � �������.
     *
     * @param integer $offer_id        id �����������
     *
     * @return integer                 -1 - � �������, 1 - �� � �������, 0 - ������
     */
    function WasteProj($offer_id, $user_id)
    {
      global $DB;
      $sql = "UPDATE projects_offers SET is_waste = NOT(is_waste) WHERE id = ?i AND user_id = ?i RETURNING is_waste";

      $ret = $DB->val($sql, $offer_id, $user_id);
      if(!$ret) return 0;
      
        $mem = new memBuff();
        $uid = get_uid(0);
        $mem->delete('prjEventsCnt' . $uid);
        $mem->delete('prjEventsCntWst' . $uid);
  
      return ( ($ret=='t') ? -1 : 1 );
    }


    
    /**
     * ������� ���������� �������� � �������, �� ������� ������� ��������� � ����������
     * �� �������� (��������, �����������, �����, � ��� �����),
     *
     * @param integer $fid             id ����������
     *
     * @return mixed                   ������ ������ ��� NULL � ������ ������
     */
    function GetFrlOffersWaste($fid)
    {
      global $DB;
      $sql = 
      "SELECT 
              COUNT(1) as total,
              SUM((COALESCE(p.exec_id,0)=po.user_id OR COALESCE(pco.position,0) > 0)::int) as executor,
              SUM(po.selected::int) as selected,
              SUM(po.refused::int) as refused,
              SUM(po.frl_refused::int) as frl_refused
         FROM projects_offers po
       INNER JOIN
         projects p
           ON p.id = po.project_id
       INNER JOIN
         employer e
           ON e.uid = p.user_id
          AND e.is_banned = '0'
       LEFT JOIN
         projects_contest_offers AS pco ON po.id = pco.id
       LEFT JOIN
         projects_blocked pb
           ON pb.project_id = p.id
       LEFT JOIN
         projects_offers_blocked pob 
           ON pob.src_id = po.id
        WHERE po.is_waste = true
          AND po.user_id = ?i
          AND pb.project_id IS NULL
          AND pob.src_id IS NULL";
  
    return $DB->row($sql, $fid);
    }



    /**
     * ���������� ������������ ����� ��������� ��������, �� ������� ������� ���������
     *
     * @param integer $fid             id ����������
     * @param string $mode             ���������� ����������
     * @param string $from_date        ������� � ����
     * @param string $limit            ����� ���������
     *
     * @return mixed                   ������ ������ ��� NULL � ������ ������
     */
    function GetFrlOffers($fid, $mode = 'marked', $from_date = NULL, $limit = 'ALL')
    {
      global $DB;
      $where = $DB->parse("WHERE po.user_id = ?i AND po.refused <> 't'", $fid);
      
      if($mode=='marked')
        $where .= ' AND (po.selected OR (po.refused AND po.refuse_reason=0) OR p.exec_id=po.user_id OR pco.position >0)';
      
      if($from_date!==NULL)
        $where .= $DB->parse(" AND p.post_date > ?", $from_date);
  
      $sql =
      "SELECT p.name as project_name, po.project_id, (p.exec_id=po.user_id) as is_executor, po.selected, po.refused, pco.position,
        CASE WHEN pco.position > 0 AND p.win_date >= '2011-12-14' THEN rating_const('o_contest_'||pco.position) 
           WHEN p.exec_id=po.user_id THEN rating_const('a_kis_exr_bns') 
           WHEN po.selected = 't' THEN rating_const('a_kis_sel_bns')
           WHEN po.refused = 't' THEN '0'
           ELSE '1'
        END as rating,
        p.kind,
        p.exec_id,
        p.user_id AS project_user_id,
        po.user_id AS offer_user_id
         FROM projects_offers po
       INNER JOIN
         projects p
           ON p.id = po.project_id
       INNER JOIN
         employer e
           ON e.uid = p.user_id
          AND e.is_banned = '0'
      LEFT JOIN projects_blocked pb ON pb.project_id = p.id
      -- LEFT join projects_contest_blocked  pcb ON pcb.project_id = p.id AND pcb.user_id = po.user_id 
      LEFT JOIN projects_contest_offers pco ON pco. project_id = p.id AND pco.user_id = po.user_id
      LEFT JOIN projects_offers_blocked pob ON pob.src_id = po.id 
        {$where} 
        AND pb.project_id IS NULL AND pob.src_id IS NULL 
        ORDER BY p.post_date DESC
        ".($limit=='ALL'?'':'LIMIT ?i');
      return $DB->rows($sql, $limit);
    }



    /**
     * �������� ������� ����������� ����������� ������������ �� ����������� �������
     *
     * @param integer $prj_id          id �������
     * @param integer $user_id         id ������������
     *
     * @return boolean                 true, ���� ����������� ���������� � false, ���� ���
     */
    function OfferExist($prj_id, $user_id)
    {
      global $DB;
      if (($prj_id > 0) && ($user_id > 0)) {
          $sql = "SELECT 1 FROM projects_offers WHERE project_id = ?i AND user_id= ?i LIMIT 1";
          $ret = $DB->val($sql, $prj_id, $user_id);
          return (bool)$ret;
      } else {
          return false;
      }
    }



    /**
     * ��������� ������� "��������" ��� ������ ������� �����������.
     * 
     *
     * @param integer $po_id           id �����������
     * @param integer $prj_id          id �������
     * @param integer $user_id         id ������������
     * @param boolean $selected        ������� (true) / �� ������� (false)
     *
     * @return string                  ��������� �� ������
     */
    function SetSelected($po_id, $prj_id, $user_id, $selected = true)
    {
      global $DB;
      $po_id = intval($po_id);
      $prj_id = intval($prj_id);
      $user_id = intval($user_id);
      $sql = "UPDATE projects_offers SET selected='" . ($selected ? 't' : 'f') . "' WHERE id=?i AND project_id=?i AND user_id=?i RETURNING user_id";
      $frl_id = $DB->val($sql, $po_id, $prj_id, $user_id);
      $error = parse_db_error($DB->error);
      
        $mem = new memBuff();
        $mem->delete('prjEventsCnt' . $frl_id);
        $mem->delete('prjEventsCntWst' . $frl_id);
        
      return $error;
    }



    /**
     * ��������� ������� "��������" ��� ������ ������� �����������.
     *
     * @param integer $po_id           id �����������
     * @param integer $prj_id          id �������
     * @param integer $user_id         id ������������
     * @param integer $po_reason       ������� ������ (0 - �����������, 1 - �� �������� ������, 2 - �� �������� ����, 3 - ������ �������)
     * @param boolean $selected        �������� (true) / �� �������� (false)
     *
     * @return string                  ��������� �� ������
     */
    function SetRefused($po_id, $prj_id, $user_id, $po_reason = 0, $refused = true)
    {
      global $DB;
      $po_id = intval($po_id);
      $prj_id = intval($prj_id);
      $user_id = intval($user_id);
      $po_reason = intval($po_reason);
      $sql = "UPDATE projects_offers SET refused='" . ($refused ? 't' : 'f') . "', refuse_reason=?i WHERE id=?i AND project_id=?i AND user_id=?i RETURNING user_id";
      $frl_id = $DB->val($sql, $po_reason, $po_id, $prj_id, $user_id);
      $error = parse_db_error($DB->error);
      
        $mem = new memBuff();
        $mem->delete('prjEventsCnt' . $frl_id);
        $mem->delete('prjEventsCntWst' . $frl_id);
        
      return $error;
    }



    /**
     * ������� ��������� ����������� ������� ������������.
     *
     * @param integer $frl_id          id ����������
     *
     * @return array                   ���������� �����������
     */
    function CheckOffers($frl_id)
    {
        global $DB;
        $frl_id = (int) $frl_id;
        $sql = "SELECT COUNT(*) as cnt FROM projects_offers WHERE user_id=?i";
        $out = $DB->row($sql, $frl_id);
        return $out;
    }

    
    /**
     * ���� �� ������ ���������� �� ������ ������
     * 
     * @global type $DB
     * @param type $frl_id          id ����������
     * @param type $project_id      id �������
     * @return bool
     */
    function IsExistOffer($frl_id, $project_id)
    {   
        global $DB;
        return (bool)$DB->val('
            SELECT id 
            FROM projects_offers 
            WHERE 
                user_id = ?i AND 
                project_id = ?i 
            LIMIT 1', 
            $frl_id, $project_id);
    }


    
    
    /**
     * ������ ID �������� �� ������� ������� ������ ���������
     * 
     * @global type $DB
     * @param type $frl_id
     * @return array()
     */
    function AllFrlOfferProjectIDs($frl_id)
    {
        global $DB;
        
        return $DB->col('
            SELECT project_id 
            FROM projects_offers 
            WHERE 
                user_id = ?i',
        $frl_id);
    }


    
    /**
     * ID ����������� ���������� �� �����-���� �� ��������
     * 
     * @global type $DB
     * @param type $project_ids
     * @return type
     */
    function AllFrlOffersByProjectIDs($project_ids = array())
    {
        global $DB;
        
        return $DB->rows('
            SELECT user_id, project_id 
            FROM projects_offers 
            WHERE project_id IN(?l)
        ',  $project_ids);
    }





    /**
     * ������������ ���������� ����������� ��� ������������ �� ���� ��� ��������.
     *
     * @param integer $user_id         id ������������
     * @param boolean $new             ������� �������� ����� (true) ��� ���� (false) �����������
     *
     * @return integer                 ���������� �����������
     */
    function CountOffersForEmp($user_id, $new = false)
    {
        global $DB;
        $user_id = intval($user_id);
        $new_where = ($new) ? " AND po.po_emp_read = 'f'" : '';

        $sql = "SELECT "
             . "COUNT(*) AS cnt "
             . "FROM projects_offers AS po "
             . "INNER JOIN projects AS p ON p.id = po.project_id "
             . "LEFT JOIN projects_blocked pb ON pb.project_id = p.id "
             . "INNER JOIN users AS u ON po.user_id = u.uid "
             . "WHERE p.user_id = ?i AND u.is_banned=B'0' AND pb.project_id IS NULL " 
             . $new_where;
        return $DB->val($sql, $user_id);
    }
    
    /**
     * ���������� ���������� ����� ������� � �������� � ���������
     * @global type $DB
     * @param type $user_id id ������������
     * @return type 
     */
    function CountNewPrjEventsForEmp($user_id)
    {
        $mem = new memBuff();
        $count = $mem->get('prjEventsCnt' . $user_id);
        if ($count !== false && !is_array($count)) {
            return $count;
        }
        global $DB;
        $user_id = intval($user_id);

        $sql = "SELECT 
                COUNT(*) AS cnt 
                FROM projects_offers AS po 
                INNER JOIN projects AS p ON p.id = po.project_id 
                LEFT JOIN projects_blocked pb ON pb.project_id = p.id 
                INNER JOIN users AS u ON po.user_id = u.uid 
                WHERE p.user_id = ?i AND u.is_banned=B'0' AND pb.project_id IS NULL 
                AND po.po_emp_read = 'f'  AND po.is_deleted = 'f'";
        $count = $DB->val($sql, $user_id);

        // ���� ������������ ����� �� ����� ��������, �� ��������� ��������� � ������
        if (!$DB->error) {
            $mem->set('prjEventsCnt' . $user_id, $count, 1800);
        }        
        return $count;
    }    
    
    /**
     * ������������ ���-�� �������� � ������� ��������� �����-������ ������� ��� ������������
     *
     * @param integer $user_id           id ������������
     *
     * @return integer                 1 - ������� ���������, 0 - ������� �� ���������
     */  
    function CheckNewEmpEvents($user_id) {
        global $DB;
         
        $sql = "SELECT EXISTS (
                        SELECT po.id 
                        FROM projects_offers as po
	                    INNER JOIN projects AS p ON p.id = po.project_id  
	                    LEFT JOIN projects_blocked pb ON pb.project_id = p.id 
	                    INNER JOIN users AS u ON po.user_id = u.uid 
	                    WHERE po.po_emp_read = 'f' AND p.user_id = ?i AND u.is_banned=B'0' AND pb.project_id IS NULL)::integer
                FROM projects_offers LIMIT 1;"; 
        return $DB->val($sql, $user_id); 
    }
    

    /**
     * ���������, ���� �� ���� � ����� ������� ���������� ��������������� ������� (�����/���������� � ���������/�����������)
     *
     * @param integer $user_id           id ����������
     * @param boolean $waster            ��������� ������� � �������?
     *
     * @return integer                 1:��, 0:���
     */    
    function CheckNewFrlEvents($user_id, $waste = true) {
        global $DB;
        $ex_where = $waste ? '' : ' AND is_waste = false ';
        $sql = "SELECT 1 FROM projects_offers WHERE po_frl_read = false {$ex_where} AND user_id = ?i LIMIT 1";
        return (int)$DB->val($sql, $user_id);
    }
    
    /**
     * ���������� ���������� ����� ������� � �������� ����������
     *
     * @param integer $user_id           id ����������
     * @param boolean $waster            ��������� ������� � �������?
     *
     * @return integer                 
     */    
    function GetNewFrlEventsCount($user_id, $waste = true) {
        global $DB;
        $mem = new memBuff();
        $key = 'prjEventsCnt' . ($waste ? 'Wst' : '') . $user_id;
        $count = $mem->get($key);
        if ($count === FALSE || is_array($count)) {
            $ex_where = $waste ? '' : ' AND po.is_waste = false ';
            $sql = "
                SELECT count(po.*) 
                FROM projects_offers AS po
                LEFT JOIN projects p 
                    ON p.id = po.project_id
                WHERE 
                    p.state = 0
                    AND po.po_frl_read = false 
                    {$ex_where}
                    AND po.user_id = ?i 
               LIMIT 1";
                
            $count = (int)$DB->val($sql, $user_id);
            $mem->set($key, $count, 1800);
	}
        return $count;
    }

    

    /**
     * ����� ����� ������� � ���� �������� ����������� ����������
     *
     * @param integer $user_id         id ����������
     *
     * @return string                  ��������� �� ������, � ������ ��������
     */    
    function ResetAllEvents($frl_id)
    {
        global $DB;
        if (!is_array($prj_id)) $prj_id = array($prj_id);
        $sql = 'UPDATE projects_offers SET po_frl_read = true WHERE user_id = ?i AND po_frl_read = false';
        $DB->query($sql, $frl_id);
        
        $mem = new memBuff();
        $mem->delete('prjEventsCnt' . $frl_id);
        $mem->delete('prjEventsCntWst' . $frl_id);
        $mem->delete('prjMsgsCnt' . $frl_id);
        $mem->delete('prjMsgsCntWst' . $frl_id);
        
        return $DB->error;
    }

    /**
     * ������� ���������� ������������� ������ ������� ��� ����������� �������
     * @see public function getPages()
     *
     * @param integer $iCurrent        ����� ������� ��������
     * @param integer $iStart          ����� ��������, � ������� �������� ���������� ���������
     * @param integer $iAll            ���������� �������
     * @param string $sHref            ������
     *
     * @return string                  HTML-���
     */    
    private function _buildNavigation($iCurrent, $iStart, $iAll, $sHref) {
        $sNavigation = '';
        for ($i=$iStart; $i<=$iAll; $i++) {
            if ($i != $iCurrent) {
                $sNavigation .= "<a href=\"".$sHref.$i."\" >".$i."</a>";
            }else {
                $sNavigation .= '<b style="margin-right: 5px">'.$i.'</b>';
            }
        }
        return $sNavigation;
    }



    /**
     * ��������� ������������� ������ ������� ��� ����������� �������
     *
     * @param integer $prj_id          id �������
     * @param integer $page            ����� ������� ��������
     * @param integer $pages           ���������� �������
     * @param string $po_sort          ���������� ������ ������� (��� ��������� ������)
     * @param integer $po_type         ��� ��������� ������ ������� (��� ��������� ������)
     *
     * @return string                  HTML-���
     */    
    public function getPages($prj_id, $page, $pages, $po_sort = null, $po_type = null){
        $sBox = "<div id=\"fl2_paginator\">";

        // ��������
        if ($pages > 1){

            $sBox .= '<table width="100%"><tr>';
            if ($page == 1){
                $sBox .= "<td>&nbsp;</td>";//<div id=\"nav_pre_not_active\"><span>����������</span></div></td>";
            }else {
                $sBox .= "<input type=\"hidden\" id=\"pre_navigation_link\" value=\"/projects/index.php?pid=" . $prj_id . ((isset($po_sort)) ? "&amp;sort=" . $po_sort : "") . ((isset($po_type)) ? "&amp;type=" . $po_type : "") . "&amp;page=" . ($page-1)."\">";
                $sBox .= "<td><div id=\"nav_pre_not_active\"><a href=\"/projects/index.php?pid=" . $prj_id . ((isset($po_sort)) ? "&amp;sort=" . $po_sort : "") . ((isset($po_type)) ? "&amp;type=" . $po_type : "") . "&amp;page=" . ($page-1) . "\" style=\"color: #717171; \">����������</a></div></td>";
            }
            $sBox .= '<td width="94%" align="center">';
        
            //� ������
            if ($page <= 10) {
                $sBox .= $this->_buildNavigation($page, 1, ($pages>10)?($page+4):$pages, "/projects/index.php?pid=" . $prj_id . ((isset($po_sort)) ? "&amp;sort=" . $po_sort : "") . ((isset($po_type)) ? "&amp;type=" . $po_type : "") . "&amp;page=");
                if ($pages > 10) {
                    $sBox .= '<span style="padding-right: 5px">...</span>';
                }
            }
            //� �����
            elseif ($page >= $pages-10) {
                $sBox .= '<span style="padding-right: 5px">...</span>';
                $sBox .= $this->_buildNavigation($page, $page-5, $pages, "/projects/index.php?pid=" . $prj_id . ((isset($po_sort)) ? "&amp;sort=" . $po_sort : "") . ((isset($po_type)) ? "&amp;type=" . $po_type : "") . "&amp;page=");
            } else {
                $sBox .= '<span style="padding-right: 5px">...</span>';
                $sBox .= $this->_buildNavigation($page, $page-4, $page+4, "/projects/index.php?pid=" . $prj_id . ((isset($po_sort)) ? "&amp;sort=" . $po_sort : "") . ((isset($po_type)) ? "&amp;type=" . $po_type : "") . "&amp;page=");
                $sBox .= '<span style="padding-right: 5px">...</span>';
            }
            

            $sBox .= '</td>';
            if ($page == $pages){
                $sBox .= "<td>&nbsp;</td>";//<div id=\"nav_next_not_active\"><span>���������</span></div></td>";
            }else {
                $sBox .= "<input type=\"hidden\" id=\"next_navigation_link\" value=\"/projects/index.php?pid=" . $prj_id . ((isset($po_sort)) ? "&amp;sort=" . $po_sort : "") . ((isset($po_type)) ? "&amp;type=" . $po_type : "") . "&amp;page=" . ($page+1)."\">";
                $sBox .= "<td><div id=\"nav_next_not_active\"><a href=\"/projects/index.php?pid=" . $prj_id . ((isset($po_sort)) ? "&amp;sort=" . $po_sort : "") . ((isset($po_type)) ? "&amp;type=" . $po_type : "") . "&amp;page=" . ($page+1)."\" style=\"color: #717171\">���������</a></div></td>";
            }
            $sBox .= '</tr>';
            $sBox .= '</table>';

        } // �������� �����������
        $sBox .= "</div>";
        return $sBox;
    }
    
    /**
     * ������� ���������� �� ����� ������� � �������� ��� �������� �����������.
     * 
     * ����� ��������� ���� �������, ���������� ������������� ��������� /classes/pgq/mail_cons.php �� �������.
     * ���� ��� �����������, �� �������� ������.
     * @see pmail::NewPrjOffer()
     * @see PGQMailSimpleConsumer::finish_batch()
     *
     * @param string|array  $offer_ids      �������������� �������
     * @param resource      $connect        ���������� � �� (���������� � PgQ) ��� NULL -- ������� �����.
     * @return array|mixed                  ���� ���� ������ � �������� �� ���������� ������ ��, ���� ��� �� NULL
     */
    function getNewProjectOffers($offer_ids, $connect=NULL) {
        global $DB;
        if(!$offer_ids) return NULL;
        if(is_array($offer_ids))
          $offer_ids = implode(',', array_unique($offer_ids));
          
        $sql = "SELECT 
                    f.usurname as from_usurname, f.uname as from_uname, f.login as from_login, f.uid AS user_id,
                    e.is_pro as emp_is_pro, e.usurname as to_usurname, e.uname as to_uname, e.login as to_login, e.email as to_email, e.subscr as to_subscr,
                    po.id, po.project_id, p.name as project_name, p.kind, po.descr as description, p.state, p.payed
                FROM 
                    projects_offers po 
                INNER JOIN 
                    projects p ON (p.id = po.project_id)
                INNER JOIN 
                    freelancer f ON (f.uid = po.user_id) 
                INNER JOIN 
                    employer e ON (e.uid = p.user_id)      
                WHERE 
                    po.id IN({$offer_ids})"; 
        
        return $DB->rows($sql);
    }
    
    /**
     * ������� ���������� �� ����� ������� � �������� ��� �������� �����������.
     * 
     * ����� ��������� ���� �������, ���������� ������������� ��������� /classes/pgq/mail_cons.php �� �������.
     * ���� ��� �����������, �� �������� ������.
     * @see pmail::NewPrjMessageOnOffer()
     * @see PGQMailSimpleConsumer::finish_batch()
     *
     * @param string|array  $dialog_ids     �������������� �������
     * @param resource      $connect        ���������� � �� (���������� � PgQ) ��� NULL -- ������� �����.
     * @return array|mixed                  ���� ���� ������ � �������� �� ���������� ������ ��, ���� ��� �� NULL
     */
    function getNewPrjMessageOnOffer($dialog_ids, $connect=NULL) {
        global $DB;
        if(!$dialog_ids) return NULL;
        if(is_array($dialog_ids))
        $dialog_ids = implode(',', array_unique($dialog_ids));
          
        $sql = "SELECT 
                    p.name as project_name, p.id as project_id, po.id AS spoiler_id,
                    e.uid as emp_uid, e.uname as emp_name, e.usurname as emp_uname, e.login as emp_login, e.email as emp_email, e.subscr as emp_subscr, 
                    f.uid as frl_uid, f.uname as frl_name, f.usurname as frl_uname, f.login as frl_login, f.email as frl_email, f.subscr as frl_subscr,
                    d.user_id as usr_dialog, d.post_text as msg,
                    (p.exec_id = f.uid OR f.is_pro) AS is_view_contacts
                FROM 
                    projects_offers_dialogue d 
                INNER JOIN 
                    projects_offers po ON po.id = d.po_id
                INNER JOIN 
                    projects p ON p.id = po.project_id
                INNER JOIN 
                    freelancer f ON f.uid = po.user_id 
                INNER JOIN 
                    employer e ON e.uid = p.user_id        
                WHERE d.id IN({$dialog_ids})"; 
        
        return $DB->rows($sql);
    }



    /**
     * ������� ���������� �� ������� � �������� ��� �������� �����������.
     *
     * ����� ��������� ���� �������, ���������� ������������� ��������� /classes/pgq/mail_cons.php �� �������.
     * ���� ��� �����������, �� �������� ������.
     * @see pmail::ProjectsOfferRefused()
     * @see PGQMailSimpleConsumer::finish_batch()
     *
     * @param string|array  $offer_ids      �������������� �������
     * @param resource      $connect        ���������� � �� (���������� � PgQ) ��� NULL -- ������� �����.
     * @return array|mixed                  ���� ���� ������ � �������� �� ���������� ������ ��, ���� ��� �� NULL
     */
    function getRefusedProjectOffers($offer_ids, $connect=NULL) {
        global $DB;
        if(!$offer_ids) return NULL;
        if(is_array($offer_ids))
          $offer_ids = implode(',', array_unique($offer_ids));

        $sql = "SELECT
                    f.usurname, f.uname, f.login, f.email, f.subscr, f.is_banned,
                    po.id, po.project_id, p.name as project_name, p.kind
                FROM
                    projects_offers po
                INNER JOIN
                    projects p ON (p.id = po.project_id)
                INNER JOIN
                    freelancer f ON (f.uid = po.user_id)
                WHERE
                    po.id IN({$offer_ids})
                    AND po.refused = TRUE";

        return $DB->rows($sql);
    }



    /**
     * ������� ���������� �� ���������� � �������� ��� �������� �����������.
     *
     * ����� ��������� ���� �������, ���������� ������������� ��������� /classes/pgq/mail_cons.php �� �������.
     * ���� ��� �����������, �� �������� ������.
     * @see pmail::ProjectsOfferSelected()
     * @see PGQMailSimpleConsumer::finish_batch()
     *
     * @param string|array  $offer_ids      �������������� �������
     * @param resource      $connect        ���������� � �� (���������� � PgQ) ��� NULL -- ������� �����.
     * @return array|mixed                  ���� ���� ������ � �������� �� ���������� ������ ��, ���� ��� �� NULL
     */
    function getSelectedProjectOffers($offer_ids, $connect=NULL) {
        global $DB;
        if(!$offer_ids) return NULL;
        if(is_array($offer_ids))
          $offer_ids = implode(',', array_unique($offer_ids));

        $sql = "SELECT
                    f.usurname, f.uname, f.login, f.email, f.subscr, f.is_banned,
                    po.id, po.project_id, p.name as project_name, p.kind
                FROM
                    projects_offers po
                INNER JOIN
                    projects p ON (p.id = po.project_id)
                INNER JOIN
                    freelancer f ON (f.uid = po.user_id)
                WHERE
                    po.id IN({$offer_ids})
                    AND po.selected = TRUE";

        return $DB->rows($sql);
    }
	
    
	/**
     * ���������� ������ ������������ �������
     *
     * @param  integer     $prj_id      ������������� �������
     * @return array|mixed              ������ �����������
     */
	function OffersEmpNewMessages($prj_id) {
        global $DB;
        $sql = "SELECT * FROM projects_offers WHERE project_id = ?i AND emp_new_msg_count > 0";
        return $DB->rows($sql, intval($prj_id));
	}
	
    /**
     * ���������� �� ������� ����� �� ����������� �� �������
     * 
     * @global object $DB          ����������� � ��
     * 
     * @param integer $offer_id    �� ����������� ����������
     * @return integer     
     */
    function getProjectIDByOfferID($offer_id) {
        global $DB;
        $sql = "SELECT project_id FROM projects_offers WHERE id = ?i";
        return $DB->val($sql, $offer_id);
    }
    
    
    /**
     * �������� ID ����������� �� ID ������� � ����� ��������� �����������
     * 
     * @global type $DB
     * @param type $project_id
     * @param type $user_id
     * @return type
     */
    function getOfferIDByProjectID($project_id, $user_id)
    {
        global $DB;
        
        return $DB->val("
            SELECT po.id
            FROM projects_offers AS po
            INNER JOIN projects AS p ON p.id = po.project_id
            WHERE 
                po.project_id = ?i AND 
                p.exec_id = ?i
        ", $project_id, $user_id);
    }








    /**
     * ���������� ������ ������� (���������� ��� ���)
     * ���������� true false ��� 0 ���� ����������� �� ���������� 
     * @param  integer $offer_id    �� ����������� (���� false �� ������ ���������� �� ���� ������ ����������)
     * @param  integer $frl_id      �� ����������
     * @param  integer $prj_id      �� ����������� ����������
     * @return mixed bool|integer   true | false | 0     
     */
    function isOfferBlocked($offer_id, $frl_id = null, $prj_id = null) {
    	$prj_id = (int)$prj_id;
    	$frl_id = (int)$frl_id;
    	$offer_id = (int)$offer_id;
        global $DB;
        if (!$offer_id && $frl_id && $prj_id) {
            $cmd = "SELECT pb.id IS NOT NULL AS is_blocked, po.id
                FROM projects_offers AS po
                LEFT JOIN projects_offers_blocked pb ON  pb.src_id = po.id
                WHERE (po.project_id = '$prj_id') AND (user_id = " . $frl_id . ")";
            $row = $DB->row($cmd);
            if ($row['id'] && $row['is_blocked'] == 't') {
                return true;
            } elseif ($row['id'] && $row['is_blocked'] == 'f') {
                return false;
            } else {
                return 0;
            }
        }elseif ($offer_id) {
            $cmd = "SELECT pb.id IS NOT NULL AS is_blocked, po.id
                FROM projects_offers AS po
                LEFT JOIN projects_offers_blocked pb ON  pb.src_id = po.id
                WHERE (po.id = '$offer_id')";
            $row = $DB->row($cmd);
            if ($row['id'] && $row['is_blocked'] == 't') {
                return true;
            } elseif ($row['id'] && $row['is_blocked'] == 'f') {
                return false;
            } else {
                return 0;
            }
        }
       return 0;
    }
        
    /**
     * ��������� �� ��������������, ����� �� ������� ������������ �������� �� ������
     * 
     * @param int $projec_id    �� �������
     * @return boolean      
     */
    public static function offerSpecIsAllowed($projec_id) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
        // ��� �������� ��� �����������
        if (is_pro()) {
            return true;
        }

        if (!get_uid(false)) {
            return false;
        }

        $is_send_offers = false;
        $spec_project = new_projects::getSpecs($projec_id);
        $user_spec = professions::GetProfsAddSpec($_SESSION['uid']);

        if (is_array($user_spec)) {
            $user_spec = array_merge($user_spec, $_SESSION['specs']);
        } else {
            $user_spec = $_SESSION['specs'];
        }

        if ($user_spec) {
            $user_spec = array_merge($user_spec, professions::GetMirroredProfs(professions::GetProfessionOrigin(implode(',', $user_spec))));
            $user_spec = array_unique($user_spec);
            //@todo �������� � ���� ������
            foreach ($user_spec as $spec) {
                $prof_group[$spec] = professions::GetProfField($spec, 'prof_group');
            }
        }

        foreach ($spec_project as $specs) {
                if (is_array($prof_group) && in_array($specs['category_id'], $prof_group)) {
                    $is_send_offers = true; // ��������� ��������� �����
                    break;
                }
        }

        return $is_send_offers;
    }
    
    public function sendStatistic($project_kind_ident, $offers_count, $is_pro, $offer_id)
    {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
        global $DB;
        
        $params = array(
            'cid' => users::getCid(),
            'project_kind_ident' => $project_kind_ident, 
            'offer_count' => $offers_count, 
            'is_pro' => $is_pro,
            'offer_id' => $offer_id
        );
        
        $query = http_build_query($params);
        
        $DB->query(
            "SELECT pgq.insert_event('statistic', 'project_answer', ?)", 
            $query
        );
    }
    
}
