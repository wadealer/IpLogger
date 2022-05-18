<?php

class IpLoggerPlugin extends MantisPlugin {

	const log_table = 'ip_logs';

	/**
	 * A method that populates the plugin information and minimum requirements.
	 * @return void
	 */
	function register() {
		$this->name = plugin_lang_get('title');
		$this->description = plugin_lang_get('description');
		$this->page = "config_page";

		$this->version = '0.0.1';
		$this->requires = array(
		    'MantisCore' => '2.0.0',
		);

		$this->author = 'Evgeny Khryukin';
		$this->contact = 'wadealer@gmail.com';
		$this->url = 'https://github.com/wadealer/IpLoggerPlugin';
	}

	/**
	 * Default plugin configuration.
	 * @return array
	 */
	public function config() {
		return array(
		    "logging_user_threshold" => MANAGER,
		    "proxy_ips" => "",

		);
	}

	/**
	 * Plugin hooks
	 * @return array
	 */
	function hooks() {
		$t_hooks = array(
			'EVENT_CORE_READY' => 'log_ip',
			'EVENT_MANAGE_USER_PAGE' => 'show_ip',
		);
		return $t_hooks;
	}

	function schema() {
		$t_table_options = array(
					  'mysql' => 'DEFAULT CHARSET=utf8'
		);

		return array(
			array('CreateTableSQL',
				array(plugin_table(self::log_table), "
					mantis_user_id    I   NOTNULL  PRIMARY,
					ip_address        C(40)   ",
					$t_table_options
				)
			),
		);
	}

	/**
	 * Plugin Installation
	 * @return boolean
	 */
	function install() {
		return true;
	}

	function log_ip() {
		try {
			if(!auth_is_user_authenticated()) {
				return;
			}

			$ip = $this->ip_address();
			$id = auth_get_current_user_id();
			$table = plugin_table(self::log_table);
			if(!db_table_exists($table)) {
				return;
			}

			$query = "INSERT INTO $table(mantis_user_id, ip_address) VALUES(" . db_param() . ',' . db_param() . ')';
			if(db_is_mysql()) {
				$query .= 'ON DUPLICATE KEY UPDATE ip_address = VALUES(ip_address)';
			}
			else if(db_is_pgsql()) {
				$query .= 'ON CONFLICT(mantis_user_id) DO UPDATE  SET ip_address = EXCLUDED.ip_address';
			}

			db_query($query, array($id, $ip));
		}
		catch(\Throwable $t) {
			log_event(LOG_PLUGIN, $t->getMessage());
		}
	}

	function ip_address() {
		$proxy_ips = plugin_config_get('proxy_ips');
		if (!empty($proxy_ips) && !is_array($proxy_ips)) {
			$proxy_ips = explode(',', str_replace(' ', '', $proxy_ips));
		}

		$ip_address = $_SERVER['REMOTE_ADDR'];

		if (!empty($proxy_ips) && count($proxy_ips)) {
			foreach (array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP') as $header) {
				if (in_array($header, $_SERVER) && ($spoof = $_SERVER[$header]) !== NULL) {
					sscanf($spoof, '%[^,]', $spoof);

					if (!$this->valid_ip($spoof)){
						$spoof = NULL;
					}
					else {
						break;
					}
				}
			}

			if (isset($spoof)) {
				for ($i = 0, $c = count($proxy_ips); $i < $c; $i++) {
					if ($proxy_ips[$i] === $ip_address) {
						$ip_address = $spoof;
						break;
					}
				}
			}
		}

		return $ip_address;
	}

	function valid_ip($ip) {
		return (bool) filter_var($ip, FILTER_VALIDATE_IP);
	}

	function show_ip($event, $user_id) {
		$table = plugin_table(self::log_table);
		if(!db_table_exists($table)) {
			return;
		}

		$query = "SELECT ip_address from $table where mantis_user_id=" . db_param();
		$res = db_query($query, array($user_id));
		$row = db_fetch_array($res);
		if($row) {
			$ip = $row['ip_address'];
			echo '<table class="table-bordered table-condensed table-striped"><tr><td class="category">' . plugin_lang_get('ip') . "</td><td>$ip</td></tr></table>";
		}
	}
}
