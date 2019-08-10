<?php
/**
 * Created by huangyihao.
 * User: Administrator
 * Date: 2019/8/7 0007
 * Time: 9:14
 */

defined('ADMIN_SYSTEM') or define('ADMIN_SYSTEM',   1); //平台账户
defined('ADMIN_FACTORY')or define('ADMIN_FACTORY',  2); //商户账户
defined('ADMIN_CHANNEL')or define('ADMIN_CHANNEL',  3); //渠道商账户
defined('ADMIN_DEALER') or define('ADMIN_DEALER',   4); //经销商/零售商账户
defined('ADMIN_SERVICE')or define('ADMIN_SERVICE',  5); //服务商账户
defined('ADMIN_SERVICE_NEW')or define('ADMIN_SERVICE_NEW',  6); //新服务商账户=渠道+服务商

defined('STORE_FACTORY')or define('STORE_FACTORY',  1); //厂商商户
defined('STORE_CHANNEL')or define('STORE_CHANNEL',  2); //渠道商商户
defined('STORE_DEALER') or define('STORE_DEALER',   3); //经销商/零售商商户
defined('STORE_SERVICE')or define('STORE_SERVICE',  4); //服务商商户
defined('STORE_ECHODATA')or define('STORE_ECHODATA',5); //平台应用商户
defined('STORE_SERVICE_NEW')or define('STORE_SERVICE_NEW',6); //新服务商账户=渠道+服务商

defined('GROUP_FACTORY')or define('GROUP_FACTORY',  1); //商户角色
defined('GROUP_CHANNEL')or define('GROUP_CHANNEL',  2); //渠道商角色
defined('GROUP_DEALER') or define('GROUP_DEALER',   3); //经销商/零售商角色
defined('GROUP_SERVICE')or define('GROUP_SERVICE',  4); //服务商角色
defined('GROUP_SERVICE_NEW')or define('GROUP_SERVICE_NEW',  15); //新服务商角色(服务商+渠道商)
//@TODO 临时写死角色ID，如线上ID不一致则需手动修改
defined('GROUP_E_COMMERCE_KEFU') or define('GROUP_E_COMMERCE_KEFU', 16); //电商客服角色
defined('GROUP_E_CHANGSHANG_KEFU') or define('GROUP_E_CHANGSHANG_KEFU', 9); //厂商客服角色