<?php

/**
 * @package Toupti
 */
class Model
{
    public $req = null;
    public $res = null;
    static $toupti = null;
    public $conn = null;

    public function __construct($req, $res)
    {
        // params and toupti are always here for migration, they must desapear.
        self::$toupti = self::getToupti();
        $this->setRequest($req);
        $this->setResponse($res);
        
        //set db conn here 
        //$this->conn = $this->set_conn($this->req->conf['db']);
    }

    public function setResponse($res)
    {
        $this->res = $res; 
    }

    public function setRequest($req)
    {
        $this->req = $req;
    }

    public static function setToupti($toupti)
    {
        self::$toupti = $toupti;
    }
    
    public static function getToupti()
    {
        return self::$toupti;
    }

	public function set_conn($conf_db) {
		$db = mysql_connect($conf_db['host'].":".$conf_db['port'],$conf_db['stduser'],$conf_db['stdpass']) or mysql_die();
		$db_select = @mysql_select_db ($conf_db['only_db']) or error (mysql_error(),$config, __LINE__, __FILE__, 0, '');
		//print_r($db);exit;
		return $db;
	}
	
	public function single_query($sql) {
		$result = $this->m_query($sql) or mysql_die();
	 	return $this->bdna_fetch_array($result);
	}
	
	public function single_result($sql) {
		$result = $this->m_query($sql) or mysql_die();
	 	$row = $this->bdna_fetch_row($result);
		return $row[0];
	}
	
	// Fonction renvoyant un tableau contenant les colonnes des enregistrements
	public function tab_query($sql) {
		$result = $this->m_query($sql);
		while($row = $this->bdna_fetch_array($result)) $temp[$row[0]] = $row;
		return $temp;
	}
	
	// Fonction renvoyant la meme chose, mais sans les clefs numeriques.
	public function tab_query_assoc($sql) {
		$result = $this->m_query($sql);
		$temp = Array();
		while ($row = $this->bdna_fetch_row($result)) {
			$temp[$row[0]] = $row[1];
		}
		return $temp;
	}
	
	
	private function m_query($sql) {
		$t = mysql_query($sql);
		if ($this->req->conf['app']['debug'] && !$t) {
		    echo "<br />========= REQUEST FAILED =========<br />";
		    echo nl2br($sql);
		    print_r(debug_backtrace());
		}
		return $t;
	}	
	
	private function bdna_fetch_row($result) {
		return @mysql_fetch_row($result);
	}
	
	private function bdna_fetch_assoc($result) {
		return @mysql_fetch_assoc($result);
	}
	
	private function bdna_fetch_array($result) {
		return @mysql_fetch_array($result);
	}
	
	private function bdna_num_rows($result) {
		return @mysql_num_rows($result);
	}
	
	private function bdna_insert_id() {
		return mysql_insert_id();
	}
	
	private function bdna_affected_rows() {
		return mysql_affected_rows();
	}
	
}
