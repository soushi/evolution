<?php

// MySQL Dump Parser
// SNUFFKIN/ Alex 2004

class SqlParser {
	var $host, $dbname, $prefix, $user, $password, $mysqlErrors;
	var $conn, $installFailed, $sitename, $adminname, $adminemail, $adminpass, $managerlanguage;
	var $mode, $fileManagerPath, $imgPath, $imgUrl;
	var $dbVersion;
    var $connection_charset, $connection_collation, $connection_method;

	function SqlParser($host, $user, $password, $db, $prefix='modx_', $adminname, $adminemail, $adminpass, $connection_charset= 'utf8', $connection_collation='utf8_general_ci', $managerlanguage='english', $connection_method = 'SET CHARACTER SET', $auto_template_logic = 'system') {
		$this->host = $host;
		$this->dbname = $db;
		$this->prefix = $prefix;
		$this->user = $user;
		$this->password = $password;
		$this->adminpass = $adminpass;
		$this->adminname = $adminname;
		$this->adminemail = $adminemail;
		$this->connection_charset = $connection_charset;
		$this->connection_collation = $connection_collation;
		$this->connection_method = $connection_method;
		$this->ignoreDuplicateErrors = false;
		$this->managerlanguage = $managerlanguage;
		$this->autoTemplateLogic = $auto_template_logic;
		if (function_exists('mysql_set_charset'))
		{
			mysql_set_charset($connection_charset);
		}
	}

	function connect() {
		$this->conn = mysql_connect($this->host, $this->user, $this->password);
		mysql_select_db($this->dbname, $this->conn);
		if (function_exists('mysql_set_charset'))
		{
			mysql_set_charset($this->connection_charset);
		}

		$this->dbVersion = 3.23; // assume version 3.23
		if(function_exists("mysql_get_server_info")) {
			$ver = mysql_get_server_info();
			$this->dbMODx 	 = version_compare($ver,"4.0.20");
			$this->dbVersion = (float) $ver; // Typecasting (float) instead of floatval() [PHP < 4.2]
		}

        mysql_query("{$this->connection_method} {$this->connection_charset}");
	}

	function process($filename) {
	    global $modx_version;

		// check to make sure file exists
		if (!file_exists($filename)) {
			$this->mysqlErrors[] = array("error" => "File '$filename' not found");
			$this->installFailed = true ;
			return false;
		}

		$idata = file_get_contents($filename);

		$idata = str_replace("\r", '', $idata);

		// check if in upgrade mode
		if ($this->mode=="upd") {
			// remove non-upgradeable parts
			$s = strpos($idata,"non-upgrade-able[[");
			$e = strpos($idata,"]]non-upgrade-able")+17;
			if($s && $e) $idata = str_replace(substr($idata,$s,$e-$s)," Removed non upgradeable items",$idata);
		}
		
		if(version_compare($this->dbVersion,'4.1.0', '>='))
		{
			$char_collate = "DEFAULT CHARSET={$this->connection_charset} COLLATE {$this->connection_collation}";
			$idata = str_replace('ENGINE=MyISAM', "ENGINE=MyISAM {$char_collate}", $idata);
		}
		
		// replace {} tags
		$ph = array();
		$ph['PREFIX'] = $this->prefix;
		$ph['ADMINNAME'] = $this->adminname;
		$ph['ADMINFULLNAME'] = substr($this->adminemail,0,strpos($this->adminemail,'@'));
		$ph['ADMINEMAIL'] = $this->adminemail;
		$ph['ADMINPASS'] = $this->adminpass;
		$ph['IMAGEPATH'] = $this->imagePath;
		$ph['IMAGEURL'] = $this->imageUrl;
		$ph['FILEMANAGERPATH'] = $this->fileManagerPath;
		$ph['MANAGERLANGUAGE'] = $this->managerlanguage;
		$ph['AUTOTEMPLATELOGIC'] = $this->autoTemplateLogic;
		$ph['DATE_NOW'] = time();
		$idata = parse($idata,$ph,'{','}');
		
		/*$ph['VERSION'] = $modx_version;*/

		$sql_array = preg_split('@;[ \t]*\n@', $idata);

		$num = 0;
		foreach($sql_array as $sql_entry)
		{
			$sql_do = trim($sql_entry, "\r\n; ");

			// strip out comments and \n for mysql 3.x
			if ($this->dbVersion <4.0) {
				$sql_do = preg_replace("~COMMENT.*[^']?'.*[^']?'~","",$sql_do);
				$sql_do = str_replace('\r', "", $sql_do);
				$sql_do = str_replace('\n', "", $sql_do);
			}

			$num++;
			if ($sql_do) mysql_query($sql_do, $this->conn);
			if(mysql_error())
			{
				// Ignore duplicate and drop errors - Raymond
				if ($this->ignoreDuplicateErrors)
				{
					if (mysql_errno() == 1060 || mysql_errno() == 1061 || mysql_errno() == 1091) continue;
				}
				// End Ignore duplicate
				$this->mysqlErrors[] = array("error" => mysql_error(), "sql" => $sql_do);
				$this->installFailed = true;
			}
		}
	}
	
	function close() {
		mysql_close($this->conn);
	}
}
