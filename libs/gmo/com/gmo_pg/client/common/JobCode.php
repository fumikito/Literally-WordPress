<?php
/**
 * 処理区分の定数定義
 * 
 * @package com.gmo_pg.client
 * @subpackage common
 * @author GMO PaymentGateway
 * @version 1.0
 * @created 01-01-2008 00:00:00
 */

/**
 * 有効性チェック
 *
 */
define(JOBCODE_CHECK, "CHECK");
	
/**
 * 即時売上
 *
 */
define(JOBCODE_CAPTURE, "CAPTURE");

/**
 * 仮売上
 *
 */
define(JOBCODE_AUTH, "AUTH");

/**
 * 売上計上
 *
 */
define(JOBCODE_SALES, "SALES");

/**
 * 取引キャンセル
 *
 */
define(JOBCODE_CANCEL, "CANCEL");

?>