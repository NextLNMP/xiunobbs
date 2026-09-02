<?php

// Xiuno BBS 5.0: 本类由 mysql_* 扩展(PHP 7.0 移除)整体移植到 mysqli，接口与行为保持不变。
class db_mysql {
	
	public $conf = array(); // 配置，可以支持主从
	public $rconf = array(); // 配置，可以支持主从
	public $wlink = NULL;  // 写连接
	public $rlink = NULL;  // 读连接
	public $link = NULL;   // 最后一次使用的连接
	public $errno = 0;
	public $errstr = '';
	public $sqls = array();
	public $tablepre = '';
	public $innodb_first = TRUE;// 优先 InnoDB
	
	public function __construct($conf) {
		// PHP 8.1 起 mysqli 默认抛异常，恢复 4.x 的静默返回 FALSE 语义 (Xiuno BBS 5.0)
		function_exists('mysqli_report') AND mysqli_report(MYSQLI_REPORT_OFF);
		$this->conf = $conf;
		$this->tablepre = $conf['master']['tablepre'];
	}
	
	// 根据配置文件连接
	public function connect() {
		$this->wlink = $this->connect_master();
		$this->rlink = $this->connect_slave();
		return $this->wlink && $this->rlink;
	}
	
	// 连接写服务器
	public function connect_master() {
		if($this->wlink) return $this->wlink;
		$conf = $this->conf['master'];
		if(!$this->wlink) $this->wlink = $this->real_connect($conf['host'], $conf['user'], $conf['password'], $conf['name'], $conf['charset'], $conf['engine']);
		return $this->wlink;
	}
	
	// 连接从服务器，如果有多台，则随机挑选一台，如果为空，则与主服务器一致。
	public function connect_slave() {
		if($this->rlink) return $this->rlink;
		if(empty($this->conf['slaves'])) {
			if($this->wlink === NULL) $this->wlink = $this->connect_master();
			$this->rlink = $this->wlink;
			$this->rconf = $this->conf['master'];
		} else {
			//$n = array_rand($this->conf['slaves']);
			$arr = array_rand($this->conf['slaves'], 1);
			$conf = $this->conf['slaves'][$arr[0]];
			$this->rconf = $conf;
			$this->rlink = $this->real_connect($conf['host'], $conf['user'], $conf['password'], $conf['name'], $conf['charset'], $conf['engine']);
		}
		return $this->rlink;
	}
	
	public function real_connect($host, $user, $password, $name, $charset = '', $engine = '') {
		// mysql_connect 原生支持 host:port 与 host:/path/to.sock 写法，mysqli 需要拆开 (Xiuno BBS 5.0)
		$socket = NULL;
		if(strpos($host, ':') !== FALSE) {
			list($host, $port) = explode(':', $host, 2);
			if(substr($port, 0, 1) == '/') {
				$socket = $port;
				$port = NULL;
				$host === '' AND $host = 'localhost';
			} else {
				$port = (int)$port;
			}
		} else {
			$port = 3306;
		}
		$link = @mysqli_connect($host, $user, $password, '', $port, $socket);
		if(!$link) { $this->error(mysqli_connect_errno(), '连接数据库服务器失败:'.mysqli_connect_error()); return FALSE; }
		if(!@mysqli_select_db($link, $name)) { $this->error(mysqli_errno($link), '选择数据库失败:'.mysqli_error($link)); return FALSE; }
		//strtolower($engine) == 'innodb' AND $this->query("SET innodb_flush_log_at_trx_commit=no", $link);
		if($charset) {
			$r = $this->query("SET names $charset, sql_mode=''", $link); // sql_mode='' 为历史兼容保留，老库结构依赖非严格模式
			$r === FALSE AND $this->error(mysqli_errno($link), 'SET names 失败:'.mysqli_error($link));
		}
		return $link;
	}
	public function sql_find_one($sql) {
		$query = $this->query($sql);
		if(!$query) return $query;
		// 如果结果为空，返回 FALSE
		$r = mysqli_fetch_assoc($query);
		if(empty($r)) {
			// $this->error();
			return NULL;
		}
		return $r;
	}
	
	
	public function sql_find($sql, $key = NULL) {
		$query = $this->query($sql);
		if(!$query) return $query;
		$arrlist = array();
		while($arr = mysqli_fetch_assoc($query)) {
			$key ? $arrlist[$arr[$key]] = $arr : $arrlist[] = $arr; // 顺序没有问题，尽管是数字，仍然是有序的，看来内部实现是链表，与 js 数组不同。
		}
		return $arrlist;
	}
	
	public function find($table, $cond = array(), $orderby = array(), $page = 1, $pagesize = 10, $key = '', $col = array()) {
		$page = max(1, $page);
		$cond = db_cond_to_sqladd($cond);
		$orderby = db_orderby_to_sqladd($orderby);
		$offset = ($page - 1) * $pagesize;
		$cols = $col ? implode(',', $col) : '*';
		return $this->sql_find("SELECT $cols FROM {$this->tablepre}$table $cond$orderby LIMIT $offset,$pagesize", $key);
		
	}
		
	public function find_one($table, $cond = array(), $orderby = array(), $col = array()) {
		$cond = db_cond_to_sqladd($cond);
		$orderby = db_orderby_to_sqladd($orderby);
		$cols = $col ? implode(',', $col) : '*';
		return $this->sql_find_one("SELECT $cols FROM {$this->tablepre}$table $cond$orderby LIMIT 1");
	}
	
	public function query($sql, $link = NULL) {
		if(!$link) {
			if(!$this->rlink && !$this->connect_slave()) return FALSE;;
			$link = $this->link = $this->rlink;
		}
		$t1 = microtime(1);
		$query = mysqli_query($link, $sql);
		$t2 = microtime(1);
		if($query === FALSE) $this->error();
		
		$t3 = substr($t2 - $t1, 0, 6);
		DEBUG AND xn_log("[$t3]".$sql, 'db_sql');
		if(count($this->sqls) < 1000) $this->sqls[] = "[$t3]".$sql;
		
		return $query;
	}
	
	public function exec($sql, $link = NULL) {
		if(!$link) {
			if(!$this->wlink && !$this->connect_master()) return FALSE;
			$link = $this->link = $this->wlink;
		}
		if(strtoupper(substr($sql, 0, 12) == 'CREATE TABLE')) {
			$fulltext = strpos($sql, 'FULLTEXT(') !== FALSE;
			$highversion = version_compare($this->version(), '5.6') >= 0;
			if(!$fulltext || ($fulltext && $highversion)) {
				$conf = $this->conf['master'];
				if(strtolower($conf['engine']) != 'myisam') {
					$this->innodb_first AND $this->is_support_innodb() AND $sql = str_ireplace('MyISAM', 'InnoDB', $sql);
				}
			}
		}
		$t1 = microtime(1);
		$query = mysqli_query($this->wlink, $sql);
		$t2 = microtime(1);
		$t3 = substr($t2 - $t1, 0, 6);
		
		DEBUG AND xn_log("[$t3]".$sql, 'db_sql');
		if(count($this->sqls) < 1000) $this->sqls[] = "[$t3]".$sql;
		
		if($query !== FALSE) {
			$pre = strtoupper(substr(trim($sql), 0, 7));
			if($pre == 'INSERT ' || $pre == 'REPLACE') {
				return mysqli_insert_id($this->wlink);
			} elseif($pre == 'UPDATE ' || $pre == 'DELETE ') {
				return mysqli_affected_rows($this->wlink);
			}
		} else {
			$this->error();
		}
		
		return $query;
	}
	
	// 如果为 innodb，条件为空，并且有权限读取 information_schema
	public function count($table, $cond = array()) {
		$this->connect_slave();
		if(empty($cond) && $this->rconf['engine'] == 'innodb') {
			$dbname = $this->rconf['name'];
			$sql = "SELECT TABLE_ROWS as num FROM information_schema.tables WHERE TABLE_SCHEMA='$dbname' AND TABLE_NAME='$table'";
		} else {
			$cond = db_cond_to_sqladd($cond);
			$sql = "SELECT COUNT(*) AS num FROM `$table` $cond";
		}
		$arr = $this->sql_find_one($sql);
		return !empty($arr) ? intval($arr['num']) : $arr;
	}
	
	public function maxid($table, $field, $cond = array()) {
		$sqladd = db_cond_to_sqladd($cond);
		$sql = "SELECT MAX($field) AS maxid FROM `$table` $sqladd";
		$arr = $this->sql_find_one($sql);
		return !empty($arr) ? intval($arr['maxid']) : $arr;
	}
	
	public function truncate($table) {
		return $this->exec("TRUNCATE $table");
	}
	
	public function close() {
		$r = FALSE;
		$this->wlink instanceof mysqli AND $r = mysqli_close($this->wlink);
		if($this->wlink !== $this->rlink) {
			$this->rlink instanceof mysqli AND $r = mysqli_close($this->rlink);
		}
		return $r;
	}
	
	public function version() {
		$r = $this->sql_find_one("SELECT VERSION() AS v");
		return $r['v'];
	}
	
	public function error($errno = 0, $errstr = '') {
		$islink = $this->link instanceof mysqli;
		$this->errno = $errno ? $errno : ($islink ? mysqli_errno($this->link) : mysqli_connect_errno());
		$this->errstr = $errstr ? $errstr : ($islink ? mysqli_error($this->link) : mysqli_connect_error());
		DEBUG AND trigger_error('Database Error:'.$this->errstr);
	}
	
	public function is_support_innodb() {
		$arrlist = $this->sql_find('SHOW ENGINES');
		$arrlist2 = arrlist_key_values($arrlist, 'Engine', 'Support');
		return isset($arrlist2['InnoDB']) AND $arrlist2['InnoDB'] == 'YES';
	}

	// pconnect 不释放连接
	public function __destruct() {
		if($this->wlink) $this->wlink = NULL;
		if($this->rlink) $this->rlink = NULL;
	}
}

?>
