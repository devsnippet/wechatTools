<?php

namespace Home\Controller;

class TestController extends HomeController {
	function token_replace() {
		$sql = "SELECT TABLE_NAME FROM information_schema.`COLUMNS` WHERE TABLE_SCHEMA='citic' AND COLUMN_NAME='token'";
		$list = M ()->query ( $sql );
		foreach ( $list as $vo ) {
			$table = $vo ['TABLE_NAME'];
			$sql = "UPDATE $table SET token='gh_a9f897b74c99' where token='gh_825cf61f1562'";
			$res = M ()->execute ( $sql );
			dump ( $sql );
			dump ( $res );
		}
	}
	function mylove() {
		// ALTER TABLE `wp_prize` ADD COLUMN `token` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Token' AFTER `img`;
		// ALTER TABLE `wp_sn_code` ADD COLUMN `token` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Token' AFTER `prize_title`;
		$_token = array ();
		$dao = M ( 'prize' );
		$list = $dao->select ();
		foreach ( $list as $v ) {
			$map ['id'] = $v ['id'];
			$key = $v ['addon'] . '_' . $v ['target_id'];
			if (isset ( $_token [$key] )) {
				$save ['token'] = $_token [$key];
			} else {
				$save ['token'] = $_token [$key] = M ( $v ['addon'] )->where ( 'id=' . $v ['target_id'] )->getField ( 'token' );
			}
			$res = $dao->where ( $map )->save ( $save );
			dump ( $res );
			lastsql ();
		}
		
		$dao = M ( 'sn_code' );
		$list = $dao->select ();
		foreach ( $list as $v ) {
			$map ['id'] = $v ['id'];
			$key = $v ['addon'] . '_' . $v ['target_id'];
			if (isset ( $_token [$key] )) {
				$save ['token'] = $_token [$key];
			} else {
				$save ['token'] = $_token [$key] = M ( $v ['addon'] )->where ( 'id=' . $v ['target_id'] )->getField ( 'token' );
			}
			$res = $dao->where ( $map )->save ( $save );
			dump ( $res );
			lastsql ();
		}
	}
	function jiamiFile() {
		// 取当前用户的网站信息
		$map ['uid'] = $this->mid;
		$info = M ( 'web_info' )->where ( $map )->find ();
		
		// 第一步：取文件内容
		$file = SITE_PATH . '/test.php';
		
		$get = trim ( file_get_contents ( $file ) );
		
		if ('<?php' == strtolower ( substr ( $get, 0, 5 ) )) {
			$get = substr ( $get, 5 );
		} else {
			$get = substr ( $get, 2 );
		}
		
		if ('?>' == substr ( $get, - 2 )) {
			$get = substr ( $get, 0, - 2 );
		}
		// 第二步：取加密的KEY
		$key = substr ( $info ['web_key'], 0, 5 );
		$license = substr ( $info ['web_key'], 5 );
		
		// 代码里插入判断授权的代码段
		$pre_code = <<<str
		\$ip = gethostbyname ( SITE_DOMAIN );
		\$fip = strtok ( \$ip, '.' );
		\$is_free = \$fip == '10' || \$fip == '127' || \$fip == '168' || \$fip == '192';
		if (! \$is_free) {
			\$license = C ( 'WEIPHP_STORE_LICENSE' );
			\$key = substr ( \$license, 0, 5 );
			\$license = substr ( \$license, 5 );			
			
			\$domain_str = md5 ( think_encrypt ( SITE_DOMAIN, \$key ) );
			\$ip_str = md5 ( think_encrypt ( \$ip, \$key ) );
			if (\$license != \$domain_str && \$license != \$ip_str) {
				header("Content-Type: text/html;charset=utf-8"); 
				echo '禁止访问未授权的应用';
				exit ();
			}
		}
str;
		$content = strip_whitespace ( $pre_code . $get );
		$content = think_encrypt ( $content, $key );
		$content = "<?php eval(code_decode('$content'));";
		dump ( $content );
		
		file_put_contents ( SITE_PATH . '/test2.php', $content );
	}
	function testFile() {
		require_once SITE_PATH . '/test2.php';
	}
}
