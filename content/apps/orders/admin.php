<?php
//
//    ______         ______           __         __         ______
//   /\  ___\       /\  ___\         /\_\       /\_\       /\  __ \
//   \/\  __\       \/\ \____        \/\_\      \/\_\      \/\ \_\ \
//    \/\_____\      \/\_____\     /\_\/\_\      \/\_\      \/\_\ \_\
//     \/_____/       \/_____/     \/__\/_/       \/_/       \/_/ /_/
//
//   上海商创网络科技有限公司
//
//  ---------------------------------------------------------------------------------
//
//   一、协议的许可和权利
//
//    1. 您可以在完全遵守本协议的基础上，将本软件应用于商业用途；
//    2. 您可以在协议规定的约束和限制范围内修改本产品源代码或界面风格以适应您的要求；
//    3. 您拥有使用本产品中的全部内容资料、商品信息及其他信息的所有权，并独立承担与其内容相关的
//       法律义务；
//    4. 获得商业授权之后，您可以将本软件应用于商业用途，自授权时刻起，在技术支持期限内拥有通过
//       指定的方式获得指定范围内的技术支持服务；
//
//   二、协议的约束和限制
//
//    1. 未获商业授权之前，禁止将本软件用于商业用途（包括但不限于企业法人经营的产品、经营性产品
//       以及以盈利为目的或实现盈利产品）；
//    2. 未获商业授权之前，禁止在本产品的整体或在任何部分基础上发展任何派生版本、修改版本或第三
//       方版本用于重新开发；
//    3. 如果您未能遵守本协议的条款，您的授权将被终止，所被许可的权利将被收回并承担相应法律责任；
//
//   三、有限担保和免责声明
//
//    1. 本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的；
//    2. 用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未获得商业授权之前，我们不承
//       诺提供任何形式的技术支持、使用担保，也不承担任何因使用本软件而产生问题的相关责任；
//    3. 上海商创网络科技有限公司不对使用本产品构建的商城中的内容信息承担责任，但在不侵犯用户隐
//       私信息的前提下，保留以任何方式获取用户信息及商品信息的权利；
//
//   有关本产品最终用户授权协议、商业授权与技术服务的详细内容，均由上海商创网络科技有限公司独家
//   提供。上海商创网络科技有限公司拥有在不事先通知的情况下，修改授权协议的权力，修改后的协议对
//   改变之日起的新授权用户生效。电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和
//   等同的法律效力。您一旦开始修改、安装或使用本产品，即被视为完全理解并接受本协议的各项条款，
//   在享有上述条款授予的权力的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本
//   授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。
//
//  ---------------------------------------------------------------------------------
//
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * ECJIA 订单管理
 */
class admin extends ecjia_admin {
	private $db_order_info;
	private $db_order_view;

	public function __construct() {
		parent::__construct();

		RC_Lang::load('order');
		RC_Loader::load_app_func('admin_order', 'orders');
		RC_Loader::load_app_func('global', 'goods');
		RC_Loader::load_app_func('global', 'orders');
		Ecjia\App\Orders\Helper::assign_adminlog_content();
		
		$this->db_order_info	= RC_Model::model('orders/order_info_model');
		$this->db_order_view	= RC_Model::model('orders/order_order_info_viewmodel');
		
		/* 加载所全局 js/css */
		RC_Script::enqueue_script('jquery-validate');
		RC_Script::enqueue_script('jquery-form');
		RC_Script::enqueue_script('ecjia-region');
		
		/* 列表页 js/css */
		RC_Script::enqueue_script('smoke');
		RC_Script::enqueue_script('jquery-chosen');
		RC_Style::enqueue_style('chosen');
		
		/* 编辑页 js/css */	
		RC_Style::enqueue_style('uniform-aristo');
		RC_Script::enqueue_script('jquery-uniform');
		
		//时间控件
		RC_Script::enqueue_script('bootstrap-datepicker', RC_Uri::admin_url('statics/lib/datepicker/bootstrap-datepicker.min.js'));
		RC_Style::enqueue_style('datepicker', RC_Uri::admin_url('statics/lib/datepicker/datepicker.css'));
		
		RC_Script::enqueue_script('orders', RC_Uri::home_url('content/apps/orders/statics/js/orders.js'));
		RC_Script::enqueue_script('order_delivery', RC_App::apps_url('statics/js/order_delivery.js', __FILE__));
		RC_Script::localize_script('orders', 'js_lang', RC_Lang::get('orders::order.js_lang'));
		RC_Style::enqueue_style('orders', RC_App::apps_url('statics/css/admin_orders.css', __FILE__), array());
		
		RC_Style::enqueue_style('aristo', RC_Uri::admin_url('statics/lib/jquery-ui/css/Aristo/Aristo.css'), array(), false, false);
	}
	
	/**
	 * 订单列表
	 */
	public function init() {
		/* 检查权限 */
		$this->admin_priv('order_view');
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('system::system.02_order_list')));
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> RC_Lang::get('orders::order.overview'),
			'content'	=> '<p>' . RC_Lang::get('orders::order.order_list_help') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . RC_Lang::get('orders::order.more_info') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:订单列表#.E8.AE.A2.E5.8D.95.E5.88.97.E8.A1.A8" target="_blank">'. RC_Lang::get('orders::order.about_order_list') .'</a>') . '</p>'
		);
		
		RC_Script::enqueue_script('order_query', RC_Uri::home_url('content/apps/orders/statics/js/order_query.js'));
		RC_Script::enqueue_script('order_delivery', RC_Uri::home_url('content/apps/orders/statics/js/order_delivery.js'));
		
		$order_query = RC_Loader::load_app_class('order_query', 'orders');
		$order_list = $order_query->get_order_list();
    	
		/* 模板赋值 */
		$this->assign('ur_here', 		RC_Lang::get('system::system.02_order_list'));
		$this->assign('action_link', 	array('href' => RC_Uri::url('orders/admin/order_query'), 'text' => RC_Lang::get('system::system.03_order_query')));
		$this->assign('status_list', 	RC_Lang::get('orders::order.cs'));
		$this->assign('order_list', 	$order_list);
		$this->assign('filter',			$order_list['filter']);
		$this->assign('count', 			$order_list['count']);
		
		$this->assign('form_action', 	RC_Uri::url('orders/admin/operate', array('batch' => 1)));
		
		$this->assign('os', RC_Lang::get('orders::order.os'));
		$this->assign('ps', RC_Lang::get('orders::order.ps'));
		$this->assign('ss', RC_Lang::get('orders::order.ss'));
		
		$search_url = $this->get_search_url();
		$this->assign('search_url', $search_url);
		
		$this->display('order_list.dwt');
	}
	
	/**
	 * 订单详情页面
	 */	
	public function info() {
		$this->admin_priv('order_view');
		
		/* 根据订单id或订单号查询订单信息 */
		if (isset($_GET['order_id'])) {
			$order_id	= intval($_GET['order_id']);
			$order		= order_info($order_id);
		} elseif (isset($_GET['order_sn'])) {
			$order_sn	= trim($_GET['order_sn']);
			$order		= order_info(0, $order_sn);
		}
		if (empty($order)) {
			return $this->showmessage(RC_Lang::get('orders::order.not_exist_order'), ecjia::MSGTYPE_HTML | ecjia::MSGSTAT_ERROR, array('links' => array(array('text' => RC_Lang::get('orders::order.return_list'), 'href' => RC_Uri::url('orders/admin/init')))));
		}
		
		/*判断订单来源，自助购订单*/
		$storebuy_order = false;
		if ($order['referer'] == 'ecjia-storebuy') {
			$storebuy_order = true;
		}
		
		/*发票抬头和发票识别码处理*/
		if (!empty($order['inv_payee'])) {
			if (strpos($order['inv_payee'],",") > 0) {
				$inv = explode(',', $order['inv_payee']);
				$this->assign('inv_payee', $inv['0']);
				$this->assign('inv_tax_no', $inv['1']);
			}
		} else {
			$this->assign('inv_payee', $order['inv_payee']);
		}
		
		/* 根据订单是否完成检查权限 */
		if (order_finished($order)) {
			$this->admin_priv('order_view_finished', ecjia::MSGTYPE_JSON);
		} else {
			$this->admin_priv('order_view', ecjia::MSGTYPE_JSON);
		}
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('system::system.02_order_list'), RC_Uri::url('orders/admin/init')));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('orders::order.order_info')));
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> RC_Lang::get('orders::order.overview'),
			'content'	=> '<p>' . RC_Lang::get('orders::order.order_info_help') . '</p>'
		));
			
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . RC_Lang::get('orders::order.more_info') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:订单列表#.E6.9F.A5.E7.9C.8B.E8.AF.A6.E6.83.85" target="_blank">'. RC_Lang::get('orders::order.about_order_info') .'</a>') . '</p>'
		);
		
		RC_Script::enqueue_script('order_delivery', RC_Uri::home_url('content/apps/orders/statics/js/order_delivery.js'));

		$where = array();
		/* 取得上一个、下一个订单号 */
		$composite_status = RC_Cookie::get('composite_status');

		if (!empty($composite_status)) {
			$filter['composite_status'] = $composite_status;
			if (!empty($filter['composite_status'])) {
				$order_query = RC_Loader::load_app_class('order_query','orders');
				
				//综合状态
				switch ($filter['composite_status']) {
					case CS_AWAIT_PAY :
						$where = array_merge($where, $order_query->order_await_pay());
						break;
					case CS_AWAIT_SHIP :
						$where = array_merge($where, $order_query->order_await_ship());
						break;
					case CS_FINISHED :
						$where = array_merge($where, $order_query->order_finished());
						break;
					default:
						if ($filter['composite_status'] != -1) {
							$where['order_status'] = $filter['composite_status'];
						}
				}
			}
		}
		$getlast = $this->db_order_info->where(array_merge(array('order_id' => array('lt' => $order_id), 'is_delete' => array('eq' => '0')), $where))->max('order_id');
		$getnext = $this->db_order_info->where(array_merge(array('order_id' => array('gt' => $order_id), 'is_delete' => array('eq' => '0')), $where))->min('order_id');
		
		$this->assign('prev_id', $getlast);
		$this->assign('next_id', $getnext);
		unset($where);
		/* 取得用户名 */
		if ($order['user_id'] > 0) {
			$user = user_info($order['user_id']);
			if (is_ecjia_error($user)) {
				$user = array();
			}
			if (!empty($user)) {
				$order['user_name'] = $user['user_name'];
				$order['mobile_phone'] = $user['mobile_phone'];
			}
		}
		
		/* 取得该店铺属于个人还是企业 */
		$order['validate_type'] = RC_DB::table('store_franchisee')->where('store_id', $order['store_id'])->pluck('validate_type');
	
		/* 取得区域名 */
		$order['region'] = get_regions($order_id);

		/* 格式化金额 */
		if ($order['order_amount'] < 0) {
			$order['money_refund']			= abs($order['order_amount']);
			$order['formated_money_refund']	= price_format(abs($order['order_amount']));
		}
		
		//订单流程状态
		$time_key = 1;
		if (!$storebuy_order && $order['pay_time'] > 0) {
			$time_key = 2;
			$this->assign('pay_key', true);
		} elseif ($storebuy_order  && $order['pay_time'] > 0) {
			$time_key = 3;
			$this->assign('pay_key', true);
		}
		
		if (!$storebuy_order && $order['shipping_time'] > 0) {
			if ($order['shipping_status'] == SS_RECEIVED) {
				$time_key = 4;
			} else {
				$time_key = 3;
			}
		}
		
		$this->assign('time_key', $time_key);
		
		/* 其他处理 */
		$order['order_time']	= RC_Time::local_date(ecjia::config('time_format'), $order['add_time']);
		$order['pay_time']		= $order['pay_time'] > 0 ? RC_Time::local_date(ecjia::config('time_format'), $order['pay_time']) : RC_Lang::get('orders::order.ps.'.PS_UNPAYED);
		$order['shipping_time']	= $order['shipping_time'] > 0 ? RC_Time::local_date(ecjia::config('time_format'), $order['shipping_time']) : RC_Lang::get('orders::order.ss.'.SS_UNSHIPPED);
		$order['format_shipping_time']	= $order['shipping_time'];
		$order['format_confirm_time']	= $order['confirm_time'] > 0 ? RC_Time::local_date(ecjia::config('time_format'), $order['confirm_time']) : '未确认';
		$order['unformat_status'] = $order['status'];
		$order['status']		= RC_Lang::get('orders::order.os.'.$order['order_status']) . ',' . RC_Lang::get('orders::order.ps.'.$order['pay_status']) . ',' . RC_Lang::get('orders::order.ss.'.$order['shipping_status']);
		$order['invoice_no']	= $order['shipping_status'] == SS_UNSHIPPED || $order['shipping_status'] == SS_PREPARING ? RC_Lang::get('orders::order.ss.'.SS_UNSHIPPED) : $order['invoice_no'];
		
		if ($storebuy_order) {
			$order['status'] = RC_Lang::get('orders::order.os.'.OS_CONFIRMED).','.RC_Lang::get('orders::order.ps.'.$order['pay_status']);
			if ($order['pay_status'] == PS_PAYED) {
				$order['status'] .= ',' . RC_Lang::get('orders::order.cs.'.CS_FINISHED);
			}
		}
		
		/* 取得订单的来源 */
		if ($order['from_ad'] == 0) {
			$order['referer'] = empty($order['referer']) ? RC_Lang::get('orders::order.from_self_site') : $order['referer'];
		} elseif ($order['from_ad'] == -1) {
			$order['referer'] = RC_Lang::get('orders::order.from_goods_js') . ' ('.RC_Lang::get('orders::order.from') . $order['referer'].')';
		} else {
		}
		
		/* 取得订单商品总重量 */
		$weight_price			= order_weight_price($order_id);
		$order['total_weight']	= $weight_price['formated_weight'];
		
		/* 取得用户信息 */
		if ($order['user_id'] > 0) {
			$db_user_rank = RC_DB::table('user_rank');
			/* 用户等级 */
			if ($user['user_rank'] > 0) {
				$db_user_rank->where('rank_id', $user['user_rank']);
			} else {
				$db_user_rank->where('min_points', '<=', intval($user['rank_points']))->orderby('min_points', 'desc');
			}
			$user['rank_name'] = $db_user_rank->pluck('rank_name');

			// 用户红包数量
			$day	= RC_Time::local_getdate();
			$today	= RC_Time::local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
			
			$user['bonus_count'] = RC_DB::table('bonus_type as bt')
				->leftJoin('user_bonus as ub', RC_DB::raw('bt.type_id'), '=', RC_DB::raw('ub.bonus_type_id'))
				->where(RC_DB::raw('ub.user_id'), $order['user_id'])
				->where(RC_DB::raw('ub.order_id'), 0)
				->where(RC_DB::raw('bt.use_start_date'), '<=', $today)
				->where(RC_DB::raw('bt.use_end_date'), '>=', $today)
				->count();
			
			$this->assign('user', $user);
		
			// 地址信息
			$data = RC_DB::table('user_address')->where('user_id', $order['user_id'])->get();
			$this->assign('address_list', $data);
		}
		
		/* 取得订单商品及货品 */
		$goods_list = array();
		$goods_attr = array();
		
		$data = RC_DB::table('order_goods as o')
			->leftJoin('products as p', RC_DB::raw('p.product_id'), '=', RC_DB::raw('o.product_id'))
			->leftJoin('goods as g', RC_DB::raw('o.goods_id'), '=', RC_DB::raw('g.goods_id'))
			->leftJoin('brand as b', RC_DB::raw('g.brand_id'), '=', RC_DB::raw('b.brand_id'))
			->selectRaw("o.*, IF(o.product_id > 0, p.product_number, g.goods_number) AS storage, 
					o.goods_attr, g.suppliers_id, IFNULL(b.brand_name, '') AS brand_name, 
					p.product_sn, g.goods_img, g.goods_sn as goods_sn")
			->where(RC_DB::raw('o.order_id'), $order_id)
			->get();
		
		if (!empty($data)) {
			foreach ($data as $key => $row) {
				$row['formated_subtotal']		= price_format($row['goods_price'] * $row['goods_number']);
				$row['formated_goods_price']	= price_format($row['goods_price']);
				$row['goods_img']				= get_image_path($row['goods_id'],$row['goods_img']);
				$goods_attr[] = explode(' ', trim($row['goods_attr'])); //将商品属性拆分为一个数组
			
				if ($row['extension_code'] == 'package_buy') {
					$row['storage']				= '';
					$row['brand_name']			= '';
					$row['package_goods_list']	= get_package_goods($row['goods_id']);
				}
				$goods_list[]	= $row;
			}
		}
		
		$attr	= array();
		$arr	= array();
		if (isset($goods_attr)) {
			foreach ($goods_attr AS $index => $array_val) {
				foreach ($array_val AS $value) {
					$arr = explode(':', $value);//以 : 号将属性拆开
					$attr[$index][] =  @array('name' => $arr[0], 'value' => $arr[1]);
				}
			}
		}

		$this->assign('goods_attr', $attr);
		$this->assign('goods_list', $goods_list);
		
		/* 取得能执行的操作列表 */
		$operable_list = operable_list($order);
		$this->assign('operable_list', $operable_list);

		/* 取得订单操作记录 */
		$act_list = array();
		$data = RC_DB::table('order_action')->where('order_id', $order['order_id'])->orderby('log_time', 'asc')->orderby('action_id', 'asc')->get();
		
		if (!empty($data)) {
			foreach ($data as $key => $row) {
				$row['order_status']	= RC_Lang::get('orders::order.os.'.$row['order_status']);
				$row['pay_status']		= RC_Lang::get('orders::order.ps.'.$row['pay_status']);
				$row['shipping_status']	= RC_Lang::get('orders::order.ss.'.$row['shipping_status']);
				$row['action_time']		= RC_Time::local_date(ecjia::config('time_format'), $row['log_time']);
				$act_list[]				= $row;
			}
		}
		$this->assign('action_list', $act_list);
		
		$express_info = RC_DB::table('express_order')->where('order_sn', $order['order_sn'])->orderBy('express_id', 'desc')->first();
		$order['express_user'] = $express_info['express_user'];
		$order['express_mobile'] = $express_info['express_mobile'];
		
		/* 取得是否存在实体商品 */
		$this->assign('exist_real_goods', exist_real_goods($order['order_id']));
		$this->assign_lang();
		/* 是否打印订单，分别赋值 */
		if (isset($_GET['print'])) {
			return false;
// 			/* 此订单的发货备注(此订单的最后一条操作记录) 打印订单中用到*/
// 			$order['invoice_note'] = RC_DB::table('order_action')
// 				->where('order_id', $order['order_id'])
// 				->where('shipping_status', 1)
// 				->orderby('log_time', 'desc')
// 				->pluck('action_note');
			
// 			$this->assign('shop_name', 		ecjia::config('shop_name'));
// 			$this->assign('shop_url', 		RC_Config::system('CUSTOM_WEB_SITE_URL'));
// 			$this->assign('shop_address', 	ecjia::config('shop_address'));
// 			$this->assign('service_phone', 	ecjia::config('service_phone'));
// 			$this->assign('print_time', 	RC_Time::local_date(ecjia::config('time_format')));
// 			$this->assign('action_user', 	$_SESSION['admin_name']);
// 			/* 参数赋值：订单 */
// 			$this->assign('order', $order);
		
// 			$this->display('order_print.dwt');
		} elseif (isset($_GET['shipping_print'])) {
			return false;
			/* 打印快递单 */
			$this->assign('print_time', RC_Time::local_date(ecjia::config('time_format')));
			//发货地址所在地
			$region_array	= array();
			$region_id	= ecjia::config('shop_province', ecjia::CONFIG_CHECK) ? ecjia::config('shop_country') . ',' : '';
			$region_id	.= ecjia::config('shop_country', ecjia::CONFIG_CHECK) ? ecjia::config('shop_province') . ',' : '';
			$region_id	.= ecjia::config('shop_city', ecjia::CONFIG_CHECK) ? ecjia::config('shop_city') . ',' : '';
			$region_id	= substr($region_id, 0, -1);
			
			if (!is_array($region_id)) {
				$region_id = explode(',', $region_id);
			}
			$region = ecjia_region::getRegions($region_id);
			if (!empty($region)) {
				foreach ($region as $region_data) {
					$region_array[$region_data['region_id']] = $region_data['region_name'];
				}
			}
			$this->assign('shop_name', ecjia::config('shop_name'));
			$this->assign('order_id', $order_id);
			$this->assign('province', $region_array[ecjia::config('shop_province')]);
			$this->assign('city', $region_array[ecjia::config('shop_city')]);
			$this->assign('shop_address', ecjia::config('shop_address'));
			$this->assign('service_phone', ecjia::config('service_phone'));
			$this->assign('order', $order);
			$shipping = RC_DB::table('shipping')->where('shipping_id', $order['shipping_id'])->first();

			//打印单模式
			if ($shipping['print_model'] == 2) {
				/* 可视化 快递单*/
				/* 判断模板图片位置 */
				if (!empty($shipping['print_bg']) && trim($shipping['print_bg']) !='') {
					$uploads_dir_info    = RC_Upload::upload_dir();
					$shipping['print_bg'] = $uploads_dir_info[baseurl] . $shipping['print_bg'];
				} else {
					/* 使用插件默认快递单图片 */
					$plugin_handle = ecjia_shipping::channel($shipping['shipping_code']);
					$shipping['print_bg'] = $plugin_handle->printBcakgroundImage();
				}
				
				/* 取快递单背景宽高 */
				if (!empty($shipping['print_bg'])) {
					$_size = @getimagesize($shipping['print_bg']);
					if ($_size != false) {
						$shipping['print_bg_size'] = array('width' => $_size[0], 'height' => $_size[1]);
					}
				}
		
				if (empty($shipping['print_bg_size'])) {
					$shipping['print_bg_size'] = array('width' => '1024', 'height' => '600');
				}
		
				/* 标签信息 */
				$lable_box = array();
				$lable_box['t_shop_country']		= $region_array[ecjia::config('shop_country')]; //网店-国家
				$lable_box['t_shop_city']			= $region_array[ecjia::config('shop_city')]; //网店-城市
				$lable_box['t_shop_province']		= $region_array[ecjia::config('shop_province')]; //网店-省份
				$lable_box['t_shop_name']			= ecjia::config('shop_name'); //网店-名称
				$lable_box['t_shop_district']		= ''; //网店-区/县
				$lable_box['t_shop_tel']			= ecjia::config('service_phone'); //网店-联系电话
				$lable_box['t_shop_address']		= ecjia::config('shop_address'); //网店-地址

				$lable_box['t_customer_country']	= !empty($region_array[$order['country']]) 	? $region_array[$order['country']] 	: ''; //收件人-国家
				$lable_box['t_customer_province']	= !empty($region_array[$order['province']]) ? $region_array[$order['province']] : ''; //收件人-省份
				$lable_box['t_customer_city']		= !empty($region_array[$order['city']]) 	? $region_array[$order['city']] 	: ''; //收件人-城市
				$lable_box['t_customer_district']	= !empty($region_array[$order['district']]) ? $region_array[$order['district']] : ''; //收件人-区/县
				
				$lable_box['t_customer_tel']		= $order['tel']; //收件人-电话
				$lable_box['t_customer_mobel']		= $order['mobile']; //收件人-手机
				$lable_box['t_customer_post']		= $order['zipcode']; //收件人-邮编
				$lable_box['t_customer_address']	= $order['address']; //收件人-详细地址
				$lable_box['t_customer_name']		= $order['consignee']; //收件人-姓名
				$gmtime_utc_temp = RC_Time::gmtime(); //获取 UTC 时间戳
				$lable_box['t_year']				= date('Y', $gmtime_utc_temp); //年-当日日期
				$lable_box['t_months']				= date('m', $gmtime_utc_temp); //月-当日日期
				$lable_box['t_day']					= date('d', $gmtime_utc_temp); //日-当日日期
				$lable_box['t_order_no']			= $order['order_sn']; //订单号-订单
				$lable_box['t_order_postscript']	= $order['postscript']; //备注-订单
				$lable_box['t_order_best_time']		= $order['best_time']; //送货时间-订单
				$lable_box['t_pigeon']				= '√'; //√-对号
				$lable_box['t_custom_content']		= ''; //自定义内容
		
				//标签替换
				$temp_config_lable = explode('||,||', $shipping['config_lable']);
				if (!is_array($temp_config_lable)) {
					$temp_config_lable[] = $shipping['config_lable'];
				}
				foreach ($temp_config_lable as $temp_key => $temp_lable) {
					$temp_info = explode(',', $temp_lable);
					$temp_info[1] = '';
					if (is_array($temp_info)) {
						$temp_info[1] = !empty($lable_box[$temp_info[0]]) ? $lable_box[$temp_info[0]] : '';
					}
					$temp_config_lable[$temp_key] = implode(',', $temp_info);
				}
				$shipping['config_lable'] = implode('||,||',  $temp_config_lable);
				$this->assign('shipping', 	$shipping);
				$this->display('print.dwt');
			} elseif (!empty($shipping['shipping_print'])) {
				/* 代码 */
				echo $this->fetch_string($shipping['shipping_print']);
			} else { 
				$shipping_code = RC_DB::table('shipping')->where('shipping_id', $order['shipping_id'])->pluck('shipping_code');

				if ($shipping_code) {
//					TODO:暂时注释,
//					include_once(ROOT_PATH . 'includes/modules/shipping/' . $shipping_code . '.php');
				}
		
				//@todo 语言包调用待定 不存在
				if (RC_Lang::lang('shipping_print')) {
					echo $this->fetch_string(RC_Lang::lang('shipping_print'));
				} else {
					echo RC_Lang::get('orders::order.no_print_shipping');
				}
			}
		} else {
			$this->assign('ur_here', 		RC_Lang::get('orders::order.order_info'));
			$this->assign('action_link', 	array('href' => RC_Uri::url('orders/admin/init'), 'text' => RC_Lang::get('system::system.02_order_list')));
			$this->assign('form_action', 	RC_Uri::url('orders/admin/operate'));
			$this->assign('remove_action', 	RC_Uri::url('orders/admin/remove_order'));
			
			//无效订单 只能查看和删除 不能进行其他操作
			$invalid_order = false;
			if ($order['order_status'] == OS_INVALID) {
				$invalid_order = true;
			}
			$this->assign('invalid_order', $invalid_order);
			
			/* 参数赋值：订单 */
			if ($storebuy_order) {
				$order['formated_order_amount'] = price_format($order['goods_amount'] - ($order['discount'] + $order['integral_money'] + $order['bonus']));
			}
			$this->assign('order', $order);
			$this->assign('order_id', $order_id);
			
			if ($order['order_amount'] < 0 ) {
				$anonymous = $order['user_id'] <= 0 ? 1 : 0;
				$this->assign('refund_url', RC_Uri::url('orders/admin/process', 'func=load_refund&anonymous='.$anonymous.'&order_id='.$order['order_id'].'&refund_amount='.$order['money_refund']));
			}
			$order_finishied = 0;
			if (in_array($order['order_status'], array(OS_CONFIRMED, OS_SPLITED)) && in_array($order['shipping_status'], array(SS_SHIPPED, SS_RECEIVED)) && in_array($order['pay_status'], array(PS_PAYED, PS_PAYING))) {
				$order_finishied = 1;
				$this->assign('order_finished', $order_finishied);
			}
			if ($storebuy_order) {
				$this->display('storebuy_order_info.dwt');
			} else {
				$this->display('order_info.dwt');
			}
		}
	}
	
	
	/**
	 * 根据订单号与订单id查询
	 */
	public function query_info() {
		$this->admin_priv('order_view', ecjia::MSGTYPE_JSON);
		$ordercount = "order_id = '".$_POST['keywords'] ."' OR order_sn = '".$_POST['keywords']."'";
		
		$query_id = RC_DB::table('order_info')->whereRaw($ordercount)->pluck('order_id');
		if (!empty($query_id)) {
			$url = RC_Uri::url("orders/admin/info", "order_id=".$query_id);
			return $this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('url' => $url));
		} else {
			return $this->showmessage(RC_Lang::get('orders::order.search_order_confirm'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
	
	/**
	 * 订单查询页面
	 */
	public function order_query() {
		/* 检查权限 */
		$this->admin_priv('order_view');
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('system::system.02_order_list'), RC_Uri::url('orders/admin/init')));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('system::system.03_order_query')));
		
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> RC_Lang::get('orders::order.overview'),
			'content'	=> '<p>' . RC_Lang::get('orders::order.order_query_help') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . RC_Lang::get('orders::order.more_info') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:订单查询" target="_blank">'. RC_Lang::get('orders::order.about_order_query') .'</a>') . '</p>'
		);
		
		RC_Script::enqueue_script('order_query', RC_Uri::home_url('content/apps/orders/statics/js/order_query.js'));
		
		$shipping_method = RC_Loader::load_app_class("shipping_method", "shipping");
		$payment_method	= RC_Loader::load_app_class("payment_method", "payment");
		
		/* 载入配送方式 */
		$this->assign('shipping_list', $shipping_method->shipping_list());
		/* 载入支付方式 */
		$this->assign('pay_list', $payment_method->available_payment_list());
		/* 载入省份 */
		$provinces = ecjia_region::getSubarea(ecjia::config('shop_country'));
		$this->assign('provinces', $provinces);
		
		/* 载入订单状态、付款状态、发货状态 */
		$this->assign('os_list', get_status_list('order'));
		$this->assign('ps_list', get_status_list('payment'));
		$this->assign('ss_list', get_status_list('shipping'));
		$this->assign('ur_here', RC_Lang::get('system::system.03_order_query'));
		$this->assign('action_link', array('href' => RC_Uri::url('orders/admin/init'), 'text' => RC_Lang::get('system::system.02_order_list')));
		$this->assign('form_action', RC_Uri::url('orders/admin/init'));
		
		$this->display('order_query.dwt');
	}
	
	/**
	 * 合并订单
	 */
	public function merge() {
		$this->admin_priv('order_os_edit');
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('orders::order.merge_order')));
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> RC_Lang::get('orders::order.overview'),
			'content'	=> '<p>' . RC_Lang::get('orders::order.order_merge_help') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . RC_Lang::get('orders::order.more_info') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:合并订单" target="_blank">'. RC_Lang::get('orders::order.about_order_merge') .'</a>') . '</p>'
		);
		
		RC_Script::enqueue_script('order_merge', RC_Uri::home_url('content/apps/orders/statics/js/order_merge.js'));
		RC_Script::localize_script('order_merge', 'js_lang', RC_Lang::get('orders::order.js_lang'));
		
		/* 取得满足条件的订单 */
		$order_query = RC_Loader::load_app_class('order_query');
		$where = array();
		$where['o.user_id'] = array('gt' => 0);
		$where['o.extension_code'] = "";
		$where = array_merge($where, $order_query->order_unprocessed());
		$query = $this->db_order_view->join('users')->where($where)->select();
		
		$this->assign('order_list', $query);
		$this->assign('ur_here', RC_Lang::get('orders::order.merge_order'));
		$this->assign('action_link', array('href' => RC_Uri::url('orders/admin/init'), 'text' => RC_Lang::get('system::system.02_order_list')));
		$this->assign('form_action', RC_Uri::url('orders/admin/ajax_merge_order'));
		
		$this->assign_lang();
		$this->display('order_merge.dwt');
	}
	
	/**
	 * 合并订单操作 
	 */
	public function ajax_merge_order() {
		$this->admin_priv('order_os_edit', ecjia::MSGTYPE_JSON);
		
		$from_order_sn	= empty($_POST['from_order_sn'])	? '' : $_POST['from_order_sn'];
		$to_order_sn	= empty($_POST['to_order_sn'])		? '' : $_POST['to_order_sn'];

		/* 参数验证  */
		$m_result	= merge_order($from_order_sn, $to_order_sn);
		$result		= array('error' => 0, 'content' => '');
		if ($m_result === true) {
			return $this->showmessage(RC_Lang::get('orders::order.act_ok'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
		} else {
			return $this->showmessage(RC_Lang::get('orders::order.act_false'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
	
	/**
	 * 添加订单（载入页面）
	 */
// 	public function add() {
// 		/* 检查权限 */
// 		$this->admin_priv('order_edit');
		
// 		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('system::system.08_add_order')));
// 		ecjia_screen::get_current_screen()->add_help_tab(array(
// 			'id'		=> 'overview',
// 			'title'		=> RC_Lang::get('orders::order.overview'),
// 			'content'	=> '<p>' . RC_Lang::get('orders::order.add_order_help') . '</p>'
// 		));
		
// 		ecjia_screen::get_current_screen()->set_help_sidebar(
// 			'<p><strong>' . RC_Lang::get('orders::order.more_info') . '</strong></p>' .
// 			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:添加订单" target="_blank">'. RC_Lang::get('orders::order.about_add_order') .'</a>') . '</p>'
// 		);
		
// 		/* 取得参数 order_id */
// 		$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// 		/* 取得参数 step */
// 		$step_list	= array('user_select','user', 'goods', 'consignee', 'shipping', 'payment', 'other', 'money');
// 		$step		= isset($_GET['step']) && in_array($_GET['step'], $step_list) ? $_GET['step'] : 'user_select';
		
// 		$key = array_search($step, $step_list);
// 		$this->assign('time_key', $key);
// 		/* 取得参数 act */
// 		$act = ROUTE_A; 
// 		$this->assign('order_id', $order_id);
// 		$this->assign('step', $step);
// 		$this->assign('step_act', $act);
// 		/* 取得订单信息 */
// 		if ($order_id > 0) {
// 			$order = order_info($order_id);
// 			/* 发货单格式化 */
// 			$order['invoice_no'] = str_replace('<br>', ',', $order['invoice_no']);
			
// 			if ($order['user_id'] > 0) {
// 				$user_info = RC_Api::api('user', 'user_info', array('user_id' => $order['user_id']));

// 				$this->assign('user_name', $user_info['user_name']);
// 			} else {
// 				$this->assign('user_name', RC_Lang::get('orders::order.anonymous'));
// 			}
// 			$this->assign('order', $order);
// 		} 
		
// 		if ($step == 'user_select') {
// 			$ur_here = RC_Lang::get('orders::order.pls_add_orders_user_type');
// 		}
// 		/* 选择会员 */
// 		if ('user' == $step) {
// 			$ur_here = RC_Lang::get('orders::order.pls_add_orders_user');
// 			// 无操作
// 		} elseif ('goods' == $step) {
// 			/* 增删改商品 */
// 			$ur_here = RC_Lang::get('orders::order.add_order_goods_info');
// 			/* 取得订单商品 */
// 			$goods_list = order_goods($order_id);

// 			if (!empty($goods_list)) {
// 				foreach ($goods_list AS $key => $goods) {
// 					/* 计算属性数 */
// 					$attr = $goods['goods_attr'];
// 					if ($attr == '') {
// 						$goods_list[$key]['rows'] = 1;
// 					} else {
// 						$goods_list[$key]['rows'] = count(explode(chr(13), $attr));
// 					}
// 				}
// 			}
// 			$this->assign('goods_list', $goods_list);
// 			/* 取得商品总金额 */
// 			$this->assign('goods_amount', price_format(order_amount($order_id)));
// 		} elseif ('consignee' == $step) {
// 			// 设置收货人
// 			$ur_here = RC_Lang::get('orders::order.confirm_shipping_address');
// 			/* 查询是否存在实体商品 */
// 			$exist_real_goods = exist_real_goods($order_id);
// 			$this->assign('exist_real_goods', $exist_real_goods);
			
// 			/* 取得收货地址列表 */
// 			if ($order['user_id'] > 0) {
// 				$address_info = RC_DB::table('user_address as ua')
// 					->leftJoin('users as u', RC_DB::raw('ua.address_id'), '=', RC_DB::raw('u.address_id'))
// 					->where(RC_DB::raw('u.user_id'), $order['user_id'])
// 					->select(RC_DB::raw('ua.*'))
// 					->first();

// 				if (empty($address_info)) {
// 					$field = "ua.*, IFNULL(c.region_name, '') as country_name, IFNULL(p.region_name, '') as province_name, IFNULL(t.region_name, '') as city_name,IFNULL(d.region_name, '') as district_name";
// 					$orderby = 'ua.address_id desc';
// 				} else {
// 					$field = "ua.*, IF(address_id=".$address_info['address_id'].",1,0) as default_address, IFNULL(c.region_name, '') as country_name, IFNULL(p.region_name, '') as province_name, IFNULL(t.region_name, '') as city_name, IFNULL(d.region_name, '') as district_name";
// 					$orderby = 'default_address desc';
// 				}
				
// 				$row = RC_DB::table('user_address as ua')
// 					->leftJoin('region as c', RC_DB::raw('c.region_id'), '=', RC_DB::raw('ua.country'))
// 					->leftJoin('region as p', RC_DB::raw('p.region_id'), '=', RC_DB::raw('ua.province'))
// 					->leftJoin('region as t', RC_DB::raw('t.region_id'), '=', RC_DB::raw('ua.city'))
// 					->leftJoin('region as d', RC_DB::raw('d.region_id'), '=', RC_DB::raw('ua.district'))
// 					->selectRaw($field)
// 					->orderbyRaw($orderby)
// 					->where('user_id', $order['user_id'])
// 					->get();
					
// 				$this->assign('address_list', $row);
// 			}
// 			if ($exist_real_goods) {
// 				/* 取得国家 */
// 				$this->assign('country_list', $this->get_regions());
// 				if ($order['country'] > 0) {
// 					/* 取得省份 */
// 					$this->assign('province_list', $this->get_regions(1, $order['country']));
// 					if ($order['province'] > 0) {
// 						/* 取得城市 */
// 						$this->assign('city_list', $this->get_regions(2, $order['province']));
// 						if ($order['city'] > 0) {
// 							/* 取得区域 */
// 							$this->assign('district_list', $this->get_regions(3, $order['city']));
// 						}
// 					}
// 				}
// 			}
// 		} elseif ('shipping' == $step) {
// 			/* 查询是否存在实体商品 */
// 			$exist_real_goods = exist_real_goods($order_id);

// 			if ($exist_real_goods) {
// 				// 选择配送方式
// 				$ur_here = RC_Lang::get('orders::order.add_order_shipping');
// 				/* 取得可用的配送方式列表 */
// 				$region_id_list = array(
// 					$order['country'], $order['province'], $order['city'], $order['district']
// 				);
// 				$shipping_method = RC_Loader::load_app_class("shipping_method", "shipping");
// 				$shipping_list = $shipping_method->available_shipping_list($region_id_list, $order['store_id']);
				
// 				/* 取得配送费用 */
// 				$total = order_weight_price($order_id);
// 				if (!empty($shipping_list)) {
// 					foreach ($shipping_list AS $key => $shipping) {
// 						$shipping_fee = $shipping_method->shipping_fee($shipping['shipping_code'], unserialize($shipping['configure']), $total['weight'], $total['amount'], $total['number']);
// 						$shipping_list[$key]['shipping_fee']		= $shipping_fee;
// 						$shipping_list[$key]['format_shipping_fee']	= price_format($shipping_fee);
// 						$shipping_list[$key]['free_money']			= price_format($shipping['configure']['free_money']);
// 					}
// 				}
				
// 				$this->assign('shipping_list', $shipping_list);
// 			}
			
// 			// 选择支付方式
// 			$ur_heres = RC_Lang::get('orders::order.add_order_payment');
// 			/* 取得可用的支付方式列表 */
// 			$payment_method = RC_Loader::load_app_class('payment_method','payment');
// 			if (exist_real_goods($order_id)) {
// 				$shipping_method = RC_Loader::load_app_class('shipping_method','shipping');
// 				/* 存在实体商品 */
// 				$region_id_list = array(
// 					$order['country'], $order['province'], $order['city'], $order['district']
// 				);
// 				$shipping_area	= $shipping_method->shipping_area_info($order['shipping_id'], $region_id_list);
// 				$pay_fee		= ($shipping_area['support_cod'] == 1) ? $shipping_area['pay_fee'] : 0;
// 				$payment_list	= $payment_method->available_payment_list(true, $pay_fee);
// 			} else {
// 				/* 不存在实体商品 */
// 				$payment_list = $payment_method->available_payment_list(false);
// 			}
// 			/* 过滤掉使用余额支付 */
// 			foreach ($payment_list as $key => $payment) {
// 				if ($payment['pay_code'] == 'balance' ||$payment['pay_code'] == 'pay_balance') {
// 					unset($payment_list[$key]);
// 				}
// 			}
// 			$this->assign('ur_heres', $ur_heres);
// 			$this->assign('exist_real_goods', $exist_real_goods);
// 			$this->assign('payment_list', $payment_list);
// 		} elseif ('other' == $step) {
// 			// 选择包装、贺卡
// 			$ur_here = RC_Lang::get('orders::order.add_order_other_info');
// 			/* 查询是否存在实体商品 */
// 			$exist_real_goods = exist_real_goods($order_id);
// 			$this->assign('exist_real_goods', $exist_real_goods);
// 		} elseif ('money' == $step) {
// 			// 费用
// 			$ur_here = RC_Lang::get('orders::order.add_order_money_info');
// 			/* 查询是否存在实体商品 */
// 			$exist_real_goods = exist_real_goods($order_id);
// 			$this->assign('exist_real_goods', $exist_real_goods);
		
// 			/* 取得用户信息 */
// 			if ($order['user_id'] > 0) {
// 				$user = user_info($order['user_id']);
// 				/* 计算可用余额 */
// 				$this->assign('available_user_money', $order['surplus'] + $user['user_money']);
// 				/* 计算可用积分 */
// 				$this->assign('available_pay_points', $order['integral'] + $user['pay_points']);
// 				/* 取得用户可用红包 */
// 				RC_Loader::load_app_func('admin_bonus', 'bonus');
// 				$user_bonus = user_bonus($order['user_id'], $order['goods_amount']);
				
// 				if ($order['bonus_id'] > 0) {
// 					$bonus			= bonus_info($order['bonus_id']);
// 					$user_bonus[]	= $bonus;
// 				}
// 				$this->assign('available_bonus', $user_bonus);
// 			}
// 		} 
// 		$this->assign('ur_here', $ur_here);
// 		$this->assign_lang();
// 		$this->display('order_step.dwt');
// 	}
	
	/**
	 * 修改订单（载入页面）
	 */
	public function edit() {
		/* 检查权限 */
		$this->admin_priv('order_edit');
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('orders::order.edit_order')));

		/* 取得参数 order_id */
		$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
		
		/* 取得参数 step */
		$step_list = array('user', 'goods', 'consignee', 'shipping', 'payment', 'other', 'money');
		$step = isset($_GET['step']) && in_array($_GET['step'], $step_list) ? $_GET['step'] : 'user';
		
		/* 取得参数 act */
		$act = ROUTE_A; // $_GET['act'];
		$this->assign('order_id', $order_id);
		$this->assign('step', $step);
		$this->assign('step_act', $act);
		
		/* 取得订单信息 */
		if ($order_id > 0) {
			$order = order_info($order_id);
			/* 发货单格式化 */
			$order['invoice_no'] = str_replace('<br>', ',', $order['invoice_no']);
		
			/* 如果已发货，就不能修改订单了（配送方式和发货单号除外） */
			if ($order['shipping_status'] == SS_SHIPPED || $order['shipping_status'] == SS_RECEIVED) {
				if ($step != 'shipping') {
					return $this->showmessage(RC_Lang::get('orders::order.order_ship'), ecjia::MSGTYPE_HTML | ecjia::MSGSTAT_ERROR);
				} else {
					$step = 'invoice';
					$this->assign('step', $step);
				}
			}
			$this->assign('order', $order);
		}
		
		/* 选择会员 */
		if ('user' == $step) {
			// 无操作
		} elseif ('goods' == $step) {
			return false; //去除编辑订单商品
			/* 增删改商品 */
			$ur_here = RC_Lang::get('orders::order.edit_goods');
			/* 取得订单商品 */
			$goods_list = order_goods($order_id);
			if (!empty($goods_list)) {
				foreach ($goods_list AS $key => $goods) {
					/* 计算属性数 */
					$attr = $goods['goods_attr'];
					if ($attr == '') {
						$goods_list[$key]['rows'] = 1;
					} else {
						$goods_list[$key]['rows'] = count(explode(chr(13), $attr));
					}
				}
			}
			$this->assign('goods_list', $goods_list);
			/* 取得商品总金额 */
			$this->assign('goods_amount', order_amount($order_id));
		} elseif ('consignee' == $step) {
			// 设置收货人
			$ur_here = RC_Lang::get('orders::order.edit_order_receiver');
			/* 查询是否存在实体商品 */
			$exist_real_goods = exist_real_goods($order_id);
			$this->assign('exist_real_goods', $exist_real_goods);
			
			/* 取得收货地址列表 */
			if ($order['user_id'] > 0) {
				$this->assign('address_list', address_list($order['user_id']));
				$address_id = isset($_GET['address_id']) ? intval($_GET['address_id']) : 0;
				if ($address_id > 0) {
					$address = address_info($address_id);
					if ($address) {
						$order['consignee']		= $address['consignee'];
						$order['country']		= $address['country'];
						$order['province']		= $address['province'];
						$order['city']			= $address['city'];
						$order['district']		= $address['district'];
						$order['email']			= $address['email'];
						$order['address']		= $address['address'];
						$order['zipcode']		= $address['zipcode'];
						$order['tel']			= $address['tel'];
						$order['mobile']		= $address['mobile'];
						$order['sign_building']	= $address['sign_building'];
						$order['best_time']		= $address['best_time'];
						$this->assign('order'	, $order);
					}
				}
			}
			if ($exist_real_goods) {
		        $province = ecjia_region::getSubarea(ecjia::config('shop_country'));
				$city = ecjia_region::getSubarea($order['province']);
				$district = ecjia_region::getSubarea($order['city']);
				$street = ecjia_region::getSubarea($order['district']);
		        
		        $this->assign('province', $province);
		        $this->assign('city', $city);
		        $this->assign('district', $district);
		        $this->assign('street', $street);
			}
		} elseif ('shipping' == $step) {
			/* 查询是否存在实体商品 */
			$exist_real_goods = exist_real_goods($order_id);
			if ($exist_real_goods) {	
				// 选择配送方式
				$ur_here = RC_Lang::get('orders::order.edit_order_shipping');
				/* 取得可用的配送方式列表 */
				$region_id_list = array(
						$order['country'], $order['province'], $order['city'], $order['district']
				);
				$shipping_method = RC_Loader::load_app_class('shipping_method','shipping');
				$shipping_list = $shipping_method->available_shipping_list($region_id_list, $order['store_id']);
				
				if (empty($shipping_list)) {
					$this->assign('shipping_list_error', 1);
				}
				/* 取得配送费用 */
				$total = order_weight_price($order_id);
				if (!empty($shipping_list)) {
					foreach ($shipping_list AS $key => $shipping) {
						$shipping_fee = $shipping_method->shipping_fee($shipping['shipping_code'], unserialize($shipping['configure']), $total['weight'], $total['amount'], $total['number']);
						$shipping_list[$key]['shipping_fee']		= $shipping_fee;
						$shipping_list[$key]['format_shipping_fee']	= price_format($shipping_fee);
						$shipping_list[$key]['free_money']			= price_format($shipping['configure']['free_money']);
					}
				}
				$this->assign('shipping_list', $shipping_list);
			}
			// 选择支付方式
			$ur_heres = RC_Lang::get('orders::order.edit_order_payment');
			/* 取得可用的支付方式列表 */
			$payment_method = RC_Loader::load_app_class('payment_method','payment');
			if (exist_real_goods($order_id)) {
				/* 存在实体商品 */
				$region_id_list = array(
					$order['country'], $order['province'], $order['city'], $order['district']
				);
				$shipping_method = RC_Loader::load_app_class('shipping_method','shipping');
				$shipping_area = $shipping_method->shipping_area_info($order['shipping_id'], $region_id_list);
				$pay_fee = ($shipping_area['support_cod'] == 1) ? $shipping_area['pay_fee'] : 0;
				$payment_list = $payment_method->available_payment_list(true, $pay_fee);
			} else {
				/* 不存在实体商品 */
				$payment_list = $payment_method->available_payment_list(false);
			}
			
			/* 过滤掉使用余额支付 */
			foreach ($payment_list as $key => $payment) {
				if ($payment['pay_code'] == 'balance') {
					unset($payment_list[$key]);
				}
			}
			$this->assign('ur_heres', $ur_heres);
			$this->assign('exist_real_goods', $exist_real_goods);
			$this->assign('payment_list', $payment_list);
		} elseif ('other' == $step) {
			// 选择包装、贺卡
			$ur_here = __('编辑订单其他信息');
			/* 查询是否存在实体商品 */
			$exist_real_goods = exist_real_goods($order_id);
			$this->assign('exist_real_goods', $exist_real_goods);
		} elseif ('money' == $step) {
			// 费用
			$ur_here = RC_Lang::get('orders::order.edit_order_money_info');
			/* 查询是否存在实体商品 */
			$exist_real_goods = exist_real_goods($order_id);
			$this->assign('exist_real_goods', $exist_real_goods);
			/* 取得用户信息 */
			if ($order['user_id'] > 0) {
				$user = user_info($order['user_id']);
				/* 计算可用余额 */
				$this->assign('available_user_money', $order['surplus'] + $user['user_money']);
				/* 计算可用积分 */
				$this->assign('available_pay_points', $order['integral'] + $user['pay_points']);
				/* 取得用户可用红包 */
				RC_Loader::load_app_func('admin_bonus', 'bonus');
				$user_bonus = user_bonus($order['user_id'], $order['goods_amount']);
				if ($order['bonus_id'] > 0) {
					$bonus			= bonus_info($order['bonus_id']);
					$user_bonus[]	= $bonus;
				}
				
				$this->assign('available_bonus', $user_bonus);
			}
		} elseif ('invoice' == $step) {
			// 发货后修改配送方式和发货单号
			$ur_here = RC_Lang::get('orders::order.edit_order_ship');
			/* 如果不存在实体商品 */
			if (!exist_real_goods($order_id)) {
				return $this->showmessage('Hacking Attemp', ecjia::MSGTYPE_HTML | ecjia::MSGSTAT_ERROR);
			}
		
			/* 取得可用的配送方式列表 */
			$region_id_list = array(
				$order['country'], $order['province'], $order['city'], $order['district']
			);
			$shipping_method = RC_Loader::load_app_class("shipping_method","shipping");
			$shipping_list = $shipping_method->available_shipping_list($region_id_list, $order['store_id']);
		
			/* 取得配送费用 */
			$total = order_weight_price($order_id);
			foreach ($shipping_list AS $key => $shipping) {
				$shipping_fee = $shipping_method->shipping_fee($shipping['shipping_code'], unserialize($shipping['configure']), $total['weight'], $total['amount'], $total['number']);
				$shipping_list[$key]['shipping_fee'] = $shipping_fee;
				$shipping_list[$key]['format_shipping_fee'] = price_format($shipping_fee);
				$shipping_list[$key]['free_money'] = price_format($shipping['configure']['free_money']);
			}
			$this->assign('shipping_list', $shipping_list);
		}
		$this->assign('ur_here', $ur_here);

		$this->display('order_step.dwt');
	}	
	
	/**
	 * 修改订单（处理提交）
	 */
	public function step_post() {
		/* 检查权限 */
		$this->admin_priv('order_edit', ecjia::MSGTYPE_JSON);
		
		/* 取得参数 step */
		$step_list	= array('user', 'edit_goods', 'add_goods', 'goods', 'consignee', 'shipping', 'payment', 'other', 'money', 'invoice');
		$step		= isset($_GET['step']) && in_array($_GET['step'], $step_list) ? $_GET['step'] : 'user';

		/* 取得参数 order_id */
		$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
		if ($order_id > 0) {
			$old_order = order_info($order_id);
		}
		
		/* 取得参数 step_act 添加还是编辑 */
		$step_act = isset($_GET['step_act']) ? $_GET['step_act'] : 'add';
		
		/* 插入订单信息 */
		if ('user' == $step || ('user_select' == $step && $_GET['user'] == '0')) {
			/* 取得参数：user_id */
			$user_id = (!empty($_POST['anonymous']) && $_POST['anonymous'] == 1) ? 0 : intval($_POST['user']);
			
			/* 插入新订单，状态为无效 */
			$order = array(
				'user_id'			=> $user_id,
				'add_time'			=> RC_Time::gmtime(),
				'order_status'		=> OS_INVALID,
				'shipping_status'	=> SS_UNSHIPPED,
				'pay_status'		=> PS_UNPAYED,
				'from_ad'			=> 0,
				'referer'			=> RC_Lang::get('orders::order.admin')
			);
			$order['order_sn'] = get_order_sn();
			
			$order_id = RC_DB::table('order_info')->insertGetId($order);

			if (!$order_id) {
				return $this->showmessage(RC_Lang::get('orders::order.pls_try_again'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			ecjia_admin::admin_log($order['order_sn'], 'add', 'order');		
			/* 插入 pay_log */
			$data = array(
				'order_id'		=> $order_id,
				'order_amount'	=> 0,
				'order_type'	=> PAY_ORDER,
				'is_paid'		=> 0,
			);
			RC_DB::table('pay_log')->insert($data);
			
			/* 下一步 */
			$url = RC_Uri::url('orders/admin/'.$step_act,"order_id=" . $order_id . "&step=goods");
			return $this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('url' => $url));
		} elseif ('edit_goods' == $step) {
			/* 编辑商品信息 */
			if (isset($_POST['rec_id'])) {
				foreach ($_POST['rec_id'] AS $key => $rec_id) {
					$goods_number_all = RC_DB::table('goods')->where('goods_id', $_POST['goods_id'][$key])->pluck('goods_number');
					
					/* 取得参数 */
					$goods_price	= floatval($_POST['goods_price'][$key]);
					$goods_number	= intval($_POST['goods_number'][$key]);
					$goods_attr		= $_POST['goods_attr'][$key];
					$product_id		= intval($_POST['product_id'][$key]);
					if ($product_id) {
						$goods_number_all = RC_DB::table('products')->where('product_id', $_POST['product_id'][$key])->pluck('product_number');
					}
					
					if ($goods_number_all >= $goods_number) {
						/* 修改 */
						$data = array(
							'goods_price'	=> $goods_price,
							'goods_number'	=> $goods_number,
							'goods_attr'	=> $goods_attr,
						);
						RC_DB::table('order_goods')->where('rec_id', $rec_id)->update($data);
					} else {
						return $this->showmessage(RC_Lang::get('orders::order.goods_num_err'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
					}
				}
		
				/* 更新商品总金额和订单总金额 */
				$goods_amount = order_amount($order_id);
				update_order($order_id, array('goods_amount' => $goods_amount));
				update_order_amount($order_id);
		
				/* 更新 pay_log */
				update_pay_log($order_id);
		
				/* todo 记录日志 */
				$sn			= RC_Lang::get('orders::order.log_edit_goods');
				$new_order	= order_info($order_id);
				if ($old_order['total_fee'] != $new_order['total_fee']) {
					$sn .= sprintf(RC_Lang::get('orders::order.order_amount_change'), $old_order['total_fee'], $new_order['total_fee']).'，';
				}
				$sn .= RC_Lang::get('orders::order.order_is').$old_order['order_sn'];
				ecjia_admin::admin_log($sn, 'edit', 'order');
				$order = order_info($order_id);
                order_action($order['order_sn'], OS_CONFIRMED, SS_UNSHIPPED, PS_UNPAYED, $sn);		
			}
			/* 商品 */
			/* 下一步 */
			if (isset($_POST['next'])) {
			    $url=RC_Uri::url('orders/admin/'.$step_act,"order_id=" . $order_id . "&step=consignee");
			    return $this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('url' => $url));
			} elseif (isset($_POST['finish'])) {
			    /* 完成 */
			    /* 初始化提示信息和链接 */
			    $msgs	= array();
			    $links	= array();
			    /* 如果已付款，检查金额是否变动，并执行相应操作 */
			    $order = order_info($order_id);
			    handle_order_money_change($order, $msgs, $links);
			    /* 显示提示信息 */
			    if (!empty($msgs)) {
			        return $this->showmessage(join(chr(13), $msgs), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			    } else {
			        /* 跳转到订单详情 */
			        $url=RC_Uri::url('orders/admin/info', "order_id=" . $order_id . "");
			        return $this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('url' => $url));
			    }
			}
		} elseif ('add_goods' == $step) {
			/* 添加商品 */
			/* 取得参数 */
			$goods_id = intval($_POST['goodslist']);
			if (empty($goods_id)) {
				return $this->showmessage(RC_Lang::get('orders::order.pls_search_add'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			} else {
				$goods_price = $_POST['add_price'] != 'user_input' ? floatval($_POST['add_price']) : floatval($_POST['input_price']);
				$goods_attr	 = '0';
				for ($i = 0; $i < $_POST['spec_count']; $i++) {
					if (is_array($_POST['spec_' . $i])) {
						$temp_array = $_POST['spec_' . $i];
						$temp_array_count = count($_POST['spec_' . $i]);
						for ($j = 0; $j < $temp_array_count; $j++) {
							if ($temp_array[$j]!==NULL) {
								$goods_attr .= ',' . $temp_array[$j];
							}
						}
					} else {
						if ($_POST['spec_' . $i]!==NULL) {
							$goods_attr .= ',' . $_POST['spec_' . $i];
						}
					}
				}

				$goods_number	= $_POST['add_number'];
				$goods_attr		= explode(',', $goods_attr);
				$k				= array_search(0, $goods_attr);
				unset($goods_attr[$k]);
				
				$res = array();
				if (!empty($goods_attr)) {
					$res = RC_DB::table('goods_attr')->select('attr_value', 'attr_price')->whereIn('goods_attr_id', $goods_attr)->get();
				}

				if (!empty($res)) {
					foreach ($res as $row) {
						$attr_value[] = $row['attr_value'];
						$goods_price += $row['attr_price'];
					}
				}
				if (!empty($attr_value) && is_array($attr_value)) {
					$attr_value = implode(",", $attr_value);
				}
				
				$prod = RC_DB::table('products')->where('goods_id', $goods_id)->first();
				RC_Loader::load_app_func('admin_goods', 'goods');
				//商品存在规格 是货品 检查该货品库存
				if (is_spec($goods_attr) && !empty($prod)) {
					$product_info = get_products_info($_POST['goodslist'], $goods_attr);
					if (!empty($goods_attr)) {
						/* 取规格的货品库存 */
						if ($goods_number > $product_info['product_number']) {
							return $this->showmessage(RC_Lang::get('orders::order.goods_num_err'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
						}
					}
				} else {
					$goods_info = goods_info($goods_id);
					if ($goods_number > $goods_info['goods_number']) {
						return $this->showmessage(RC_Lang::get('orders::order.goods_num_err'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
					}
				}
				//判断该商品或货品是否在该订单中
				if (!isset($product_info['product_id'])) {
					$product_info['product_id'] = 0;
				}
				$db_order_goods = RC_DB::table('order_goods')->where('order_id', $order_id)->where('product_id', $product_info['product_id'])->where('goods_id', $goods_id);
				$count = $db_order_goods->count();
				if ($count != 0) {
					return $this->showmessage('该商品或货品已存在该订单中，请编辑商品数量', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
				}
				
				$row = RC_DB::table('goods')->selectRaw('goods_id, goods_name, goods_sn, market_price, is_real, extension_code')
					->where('goods_id', $goods_id)->first();

				if (is_spec($goods_attr) && !empty($prod)) {
					/* 插入订单商品 */
					$data = array(
						'order_id'			=> $order_id,
						'goods_id'			=> $row['goods_id'],
						'goods_name'		=> $row['goods_name'],
						'goods_sn'			=> $product_info['product_sn'],
						'product_id'		=> $product_info['product_id'],
						'goods_number'		=> $goods_number,
						'market_price'		=> $row['market_price'],
						'goods_price'		=> $goods_price,
						'goods_attr'		=> isset($attr_value) ? $attr_value : '',
						'is_real'			=> $row['is_real'],
						'extension_code'	=> $row['extension_code'],
						'parent_id'			=> 0,
						'is_gift'			=> 0,
						'goods_attr_id'		=> implode(',',$goods_attr),
					);
				} else {
					$data = array(
						'order_id'			=> $order_id,
						'goods_id'			=> $row['goods_id'],
						'goods_name'		=> $row['goods_name'],
						'goods_sn'			=> $row['goods_sn'],
						'goods_number'		=> $goods_number,
						'market_price'		=> $row['market_price'],
						'goods_price'		=> $goods_price,
						'goods_attr'		=> isset($attr_value) ? $attr_value : '',
						'is_real'			=> $row['is_real'],
						'extension_code'	=> $row['extension_code'],
						'parent_id'			=> 0,
						'is_gift'			=> 0,
					);
				}
				RC_DB::table('order_goods')->insert($data);
				/* 如果使用库存，且下订单时减库存，则修改库存 */
				if (ecjia::config('use_storage') == '1' && ecjia::config('stock_dec_time') == SDT_PLACE) {
					//（货品）
					if (!empty($product_info['product_id']))
					{
						RC_DB::table('products')
							->where('product_id', $product_info['product_id'])
							->decrement('product_number', '"'.$goods_number.'"');
					}
					RC_DB::table('goods')
						->where('goods_id', $goods_id)
						->limit(1)
						->decrement('goods_number', '"'.$goods_number.'"');
				}

				/* 更新商品总金额和订单总金额 */
				update_order($order_id, array('goods_amount' => order_amount($order_id)));
				update_order_amount($order_id);
				/* 更新 pay_log */
				update_pay_log($order_id);
				
				/* todo 记录日志 */
				$sn			= RC_Lang::get('orders::order.log_add_goods');
				$new_order	= order_info($order_id);
				if ($old_order['total_fee'] != $new_order['total_fee']) {
					$sn .= sprintf(RC_Lang::get('orders::order.order_amount_change'), $old_order['total_fee'], $new_order['total_fee']).'，';
				}
				$sn .= RC_Lang::get('orders::order.order_is').$old_order['order_sn'];
				ecjia_admin::admin_log($sn, 'edit', 'order');

				/* 跳回订单商品 */
				return $this->showmessage(RC_Lang::get('orders::order.goods_add_order_ok'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('url' => RC_Uri::url('orders/admin/'.$step_act, "step=goods&order_id=".$order_id)));
			}
		} elseif ('goods' == $step) {
			/* 商品 */
			/* 下一步 */
			if (isset($_POST['next'])) {
				$url=RC_Uri::url('orders/admin/'.$step_act,"order_id=" . $order_id . "&step=consignee");
				return $this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('url' => $url));
			} elseif (isset($_POST['finish'])) {
				/* 完成 */
				/* 初始化提示信息和链接 */
				$msgs	= array();
				$links	= array();
				/* 如果已付款，检查金额是否变动，并执行相应操作 */
				$order = order_info($order_id);
				handle_order_money_change($order, $msgs, $links);
				/* 显示提示信息 */
				if (!empty($msgs)) {
					return $this->showmessage(join(chr(13), $msgs), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
				} else {
					/* 跳转到订单详情 */
					$url=RC_Uri::url('orders/admin/info', "order_id=" . $order_id . "");
					return $this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('url' => $url));
				}
			}
		} elseif ('consignee' == $step) {
			/* 保存收货人信息 */
			/* 保存订单 */
			$order = $_POST;
			
			if (isset($order['next'])) {
				unset($order['next']);
			}
			if (isset($order['finish'])) {
				unset($order['finish']);
			}
			//如果是会员订单则读取会员地址信息
			if ($order['user_address'] > 0 && $old_order['user_id'] > 0) {
				$orders = RC_DB::table('user_address')
					->selectRaw('consignee, email, country, province, city, district, street, address, zipcode, tel, mobile, sign_building, best_time')
					->where('user_id', $old_order['user_id'])
					->where('address_id', $order['user_address'])
					->first();
				update_order($order_id, $orders);
			} else {
				if (isset($order['user_address'])) {
					unset($order['user_address']);
				}
				update_order($order_id, $order);
			}

			/* todo 记录日志 */
			$sn = RC_Lang::get('orders::order.log_edit_order').$old_order['order_sn'];
			ecjia_admin::admin_log($sn, 'edit', 'order_consignee');
			
			if (isset($_POST['next'])) {
				/* 下一步 */
				if (exist_real_goods($order_id)) {
					/* 存在实体商品，去配送方式 */
					$url = RC_Uri::url('orders/admin/'.$step_act, "order_id=" . $order_id . "&step=shipping");
				} else {
					/* 不存在实体商品，去支付方式 */
					$url = RC_Uri::url('orders/admin/'.$step_act, "order_id=" . $order_id . "&step=payment");
				}
				return $this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('url' => $url));
			} elseif (isset($_POST['finish'])) {
				$url = RC_Uri::url('orders/admin/info', array('order_id' => $order_id));
				/* 如果是编辑且存在实体商品，检查收货人地区的改变是否影响原来选的配送 */
				if ('edit' == $step_act && exist_real_goods($order_id)) {
					$order = order_info($order_id);
					/* 取得可用配送方式 */
					$region_id_list = array(
						$order['country'], $order['province'], $order['city'], $order['district'], $order['street']
					);
					$shipping_method = RC_Loader::load_app_class("shipping_method","shipping");
					$shipping_list = $shipping_method->available_shipping_list($region_id_list, $order['store_id']);
		
					/* 判断订单的配送是否在可用配送之内 */
					$exist = false;
					foreach ($shipping_list AS $shipping) {
						if ($shipping['shipping_id'] == $order['shipping_id']) {
							$exist = true;
							break;
						}
					}
					/* 如果不在可用配送之内，提示用户去修改配送 */
					if (!$exist) {
						// 修改配送为空，配送费和保价费为0
						update_order($order_id, array('shipping_id' => 0, 'shipping_name' => ''));
						$links[] = array('text' => RC_Lang::get('orders::order.step.shipping'), 'href' => $url=RC_Uri::url('orders/admin/edit', "order_id=" . $order_id . "&step=shipping"));
						return $this->showmessage(RC_Lang::get('orders::order.continue_shipping'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR, array('links' => $links));
					}
				}
				return $this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('url' => $url));
			}
		} elseif ('shipping' == $step) {
			/* 保存配送信息 */
			/* 取得订单信息 */
			$order_info = order_info($order_id);
			$region_id_list = array($order_info['country'], $order_info['province'], $order_info['city'], $order_info['district']);
			/* 保存订单 */
		      
			$shipping_id	= $_POST['shipping'];
			$shipping_method = RC_Loader::load_app_class('shipping_method','shipping');
			$shipping		= $shipping_method->shipping_area_info($shipping_id, $region_id_list, $order_info['store_id']);
			$weight_amount	= order_weight_price($order_id);
			$shipping_fee	= $shipping_method->shipping_fee($shipping['shipping_code'], $shipping['configure'], $weight_amount['weight'], $weight_amount['amount'], $weight_amount['number']);
			$order = array(
				'shipping_id'	=> $shipping_id,
				'shipping_name'	=> addslashes($shipping['shipping_name']),
				'shipping_fee'	=> $shipping_fee
			);
			if (isset($_POST['insure'])) {
				/* 计算保价费 */
				$order['insure_fee'] = shipping_insure_fee($shipping['shipping_code'], order_amount($order_id), $shipping['insure']);
			} else {
				$order['insure_fee'] = 0;
			}
			update_order($order_id, $order);
			update_order_amount($order_id);
			
			/* 更新 pay_log */
			update_pay_log($order_id);
			
			/* todo 记录日志 */
			$sn = RC_Lang::get('orders::order.log_edit_shipping');
			$new_order = order_info($order_id);
			if ($old_order['total_fee'] != $new_order['total_fee']) {
				$sn .= sprintf(RC_Lang::get('orders::order.order_amount_change'), $old_order['total_fee'], $new_order['total_fee']).'，';
			}
			$sn .= RC_Lang::get('orders::order.log_edit_shipping').$old_order['order_sn'];
			ecjia_admin::admin_log($sn, 'edit', 'order');
			
			/* 保存支付信息 */
			/* 取得支付信息 */
			$pay_id = $_POST['payment'];
			$payment_method = RC_Loader::load_app_class('payment_method','payment');
			$payment = $payment_method->payment_info($pay_id);
			/* 计算支付费用 */
			$order_amount = order_amount($order_id);
			if ($payment['is_cod'] == 1) {
				$order = order_info($order_id);
				$region_id_list = array($order['country'], $order['province'], $order['city'], $order['district']);
				$shipping_method = RC_Loader::load_app_class('shipping_method','shipping');
				$shipping = $shipping_method->shipping_area_info($order['shipping_id'], $region_id_list);
				$pay_fee = pay_fee($pay_id, $order_amount, $shipping['pay_fee']);
			} else {
				$pay_fee = pay_fee($pay_id, $order_amount);
			}
			/* 保存订单 */
			$order = array(
				'pay_id'	=> $pay_id,
				'pay_name'	=> addslashes($payment['pay_name']),
				'pay_fee'	=> $pay_fee
			);
			update_order($order_id, $order);
			update_order_amount($order_id);

			/* 更新 pay_log */
			update_pay_log($order_id);
			
			/* todo 记录日志 */
			$sn = RC_Lang::get('orders::order.log_edit_payment');
			$new_order = order_info($order_id);

			if ($old_order['total_fee'] != $new_order['total_fee']) {
				$sn .= sprintf(RC_Lang::get('orders::order.order_amount_change'), $old_order['total_fee'], $new_order['total_fee']).'，';
			}
			$sn .= RC_Lang::get('orders::order.order_is').$old_order['order_sn'];
			ecjia_admin::admin_log($sn, 'edit', 'order');

			if (isset($_POST['next'])) {
				/* 下一步 */
				$url=RC_Uri::url('orders/admin/'.$step_act,"order_id=" . $order_id . "&step=other");
				return $this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('url' => $url));
			} elseif (isset($_POST['finish'])) {
				/* 初始化提示信息和链接 */
				$msgs	= array();
				$links	= array();
				/* 如果已付款，检查金额是否变动，并执行相应操作 */
				$order = order_info($order_id);
				handle_order_money_change($order, $msgs, $links);

				/* 显示提示信息 */
				if (!empty($msgs)) {
					return $this->showmessage(join(chr(13), $msgs), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
				} else {
					/* 完成 */
					$url=RC_Uri::url('orders/admin/info',"order_id=" . $order_id . "");
					return $this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('url' => $url));
				}
			}
		} elseif ('other' == $step) {
			/* 其他处理  */
			/* 保存订单 */
			$order = array();
			if (isset($_POST['pack']) && $_POST['pack'] > 0) {

			} else {
				$order['pack_id']	= 0;
				$order['pack_name']	= '';
				$order['pack_fee']	= 0;
			}
			if (isset($_POST['card']) && $_POST['card'] > 0) {
				$order['card_message']	= $_POST['card_message'];
			} else {
				$order['card_id']		= 0;
				$order['card_name']		= '';
				$order['card_fee']		= 0;
				$order['card_message']	= '';
			}
			$order['inv_type']		= $_POST['inv_type'];
			$order['inv_payee']		= $_POST['inv_payee'];
			$order['inv_content']	= $_POST['inv_content'];
			$order['how_oos']		= $_POST['how_oos'];
			$order['postscript']	= $_POST['postscript'];
			$order['to_buyer']		= $_POST['to_buyer'];
			update_order($order_id, $order);
			update_order_amount($order_id);
		
			/* 更新 pay_log */
			update_pay_log($order_id);
		
			/* todo 记录日志 */
			$sn = RC_Lang::get('orders::order.order_is').$old_order['order_sn'];
			ecjia_admin::admin_log($sn, 'edit', 'order');
		
			if (isset($_POST['next'])) {
				/* 下一步 */
				$url=RC_Uri::url('orders/admin/'.$step_act,"order_id=" . $order_id . "&step=money");
				return $this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('url' => $url));
			} elseif (isset($_POST['finish'])) {
				/* 完成 */
				$url=RC_Uri::url('orders/admin/info',"order_id=" . $order_id . "");
				return $this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('url' => $url));
			}
		} elseif ('money' == $step) {
			/* 订单生成 信息 */
			/* 取得订单信息 */
			$old_order = order_info($order_id);
			if ($old_order['user_id'] > 0) {
				/* 取得用户信息 */
				$user = user_info($old_order['user_id']);
			}
			
			/* 保存信息 */
			$order['goods_amount']		= $old_order['goods_amount'];
			$order['discount']			= isset($_POST['discount']) && floatval($_POST['discount']) >= 0 ? round(floatval($_POST['discount']), 2) : 0;
			$order['tax']				= round(floatval($_POST['tax']), 2);
			$order['shipping_fee']		= isset($_POST['shipping_fee']) && floatval($_POST['shipping_fee']) >= 0 ? round(floatval($_POST['shipping_fee']), 2) : 0;
			$order['insure_fee']		= isset($_POST['insure_fee']) && floatval($_POST['insure_fee']) >= 0 ? round(floatval($_POST['insure_fee']), 2) : 0;
			$order['pay_fee']			= floatval($_POST['pay_fee']) >= 0 ? round(floatval($_POST['pay_fee']), 2) : 0;
			$order['pack_fee']			= isset($_POST['pack_fee']) && floatval($_POST['pack_fee']) >= 0 ? round(floatval($_POST['pack_fee']), 2) : 0;
			$order['card_fee']			= isset($_POST['card_fee']) && floatval($_POST['card_fee']) >= 0 ? round(floatval($_POST['card_fee']), 2) : 0;
			$order['money_paid']		= $old_order['money_paid'];
			$order['surplus']			= 0;
			$order['integral']			= intval(isset($_POST['integral'])) >= 0 ? intval(isset($_POST['integral'])) : 0;
			$order['integral_money']	= 0;
			$order['bonus_id']			= 0;
			$order['bonus']				= 0;
			
			/* 计算待付款金额 */
			$order['order_amount']  = $order['goods_amount'] - $order['discount']
						+ $order['tax']
						+ $order['shipping_fee']
						+ $order['insure_fee']
						+ $order['pay_fee']
						+ $order['pack_fee']
						+ $order['card_fee']
						- $order['money_paid'];
			if ($order['order_amount'] > 0) {
				if ($old_order['user_id'] > 0) {
					/* 如果选择了红包，先使用红包支付 */
					if ($_POST['bonus_id'] > 0) {
						RC_Loader::load_app_func('admin_bonus', 'bonus');
						/* todo 检查红包是否可用 */
						$order['bonus_id']		= $_POST['bonus_id'];
						$bonus					= bonus_info($_POST['bonus_id']);
						$order['bonus']			= $bonus['type_money'];
						$order['order_amount']	-= $order['bonus'];
					}
		
					/* 使用红包之后待付款金额仍大于0 */
					if ($order['order_amount'] > 0) {
						if ($old_order['extension_code'] != 'exchange_goods') {
							/* 如果设置了积分，再使用积分支付 */
							if (isset($_POST['integral']) && intval($_POST['integral']) > 0) {
								/* 检查积分是否足够 */
								$order['integral'] = intval($_POST['integral']);
								$order['integral_money'] = value_of_integral(intval($_POST['integral']));
								if ($old_order['integral'] + $user['pay_points'] < $order['integral']) {
									return $this->showmessage(RC_Lang::get('orders::order.pay_points_not_enough'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
								}
								$order['order_amount'] -= $order['integral_money'];
							}
						} else {
							if (intval($_POST['integral']) > $user['pay_points']+$old_order['integral']) {
								return $this->showmessage(RC_Lang::get('orders::order.pay_points_not_enough'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
							}
						}
						if ($order['order_amount'] > 0) {
							/* 如果设置了余额，再使用余额支付 */
							if (isset($_POST['surplus']) && floatval($_POST['surplus']) >= 0) {
								/* 检查余额是否足够 */
								$order['surplus'] = round(floatval($_POST['surplus']), 2);
								if ($old_order['surplus'] + $user['user_money'] + $user['credit_line'] < $order['surplus']) {
									return $this->showmessage(RC_Lang::get('orders::order.user_money_not_enough'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
								}
								/* 如果红包和积分和余额足以支付，把待付款金额改为0，退回部分积分余额 */
								$order['order_amount'] -= $order['surplus'];
								if ($order['order_amount'] < 0) {
									$order['surplus']		+= $order['order_amount'];
									$order['order_amount']	= 0;
								}
							}
						} else {
							/* 如果红包和积分足以支付，把待付款金额改为0，退回部分积分 */
							$order['integral_money']	+= $order['order_amount'];
							$order['integral']			= integral_of_value($order['integral_money']);
							$order['order_amount']		= 0;
						}
					} else {
						/* 如果红包足以支付，把待付款金额设为0 */
						$order['order_amount'] = 0;
					}
				}
			}
			update_order($order_id, $order);
		
			/* 更新 pay_log */
			update_pay_log($order_id);
			
			/* todo 记录日志 */
			$sn = RC_Lang::get('orders::order.log_edit_money');
			$new_order = order_info($order_id);
			if ($old_order['total_fee'] != $new_order['total_fee']) {
				$sn .= sprintf(RC_Lang::get('orders::order.order_amount_change'), $old_order['total_fee'], $new_order['total_fee']).'，';
			}
			$sn .= RC_Lang::get('orders::order.order_is').$old_order['order_sn'];
			ecjia_admin::admin_log($sn, 'edit', 'order');
		
			/* 如果余额、积分、红包有变化，做相应更新 */
			if ($old_order['user_id'] > 0) {
				$user_money_change = $old_order['surplus'] - $order['surplus'];
				if ($user_money_change != 0) {
					$options = array(
						'user_id'		=> $user['user_id'],
						'user_money'	=> $user_money_change,
						'change_desc'	=> sprintf(RC_Lang::get('orders::order.change_use_surplus'), $old_order['order_sn'])
					);
					RC_Api::api('user', 'account_change_log', $options);
				}
				$pay_points_change = $old_order['integral'] - $order['integral'];

				if ($pay_points_change != 0) {
					$options = array(
						'user_id'		=> $user['user_id'],
						'pay_points'	=> $pay_points_change,
						'change_desc'	=> sprintf(RC_Lang::get('orders::order.change_use_integral'), $old_order['order_sn'])
					);
					RC_Api::api('user', 'account_change_log', $options);
				}
		
				if ($old_order['bonus_id'] != $order['bonus_id']) {
					if ($old_order['bonus_id'] > 0) {
						$data = array(
							'used_time'	=> 0,
							'order_id'	=> 0,
						);
						RC_DB::table('user_bonus')->where('bonus_id', $old_order['bonus_id'])->update($data);
					}
		
					if ($order['bonus_id'] > 0) {
						$data = array(
							'used_time'	=> RC_Time::gmtime(),
							'order_id'	=> $order_id,
						);
						RC_DB::table('user_bonus')->where('bonus_id', $order['bonus_id'])->update($data);
					}
				}
			}
			if (isset($_POST['finish'])) {
				/* 完成 */
				if ($step_act == 'add') {
					/* 订单改为已确认，（已付款） */
					$arr['order_status']	= OS_CONFIRMED;
					$arr['confirm_time']	= RC_Time::gmtime();
					if ($order['order_amount'] <= 0) {
						$arr['pay_status']	= PS_PAYED;
						$arr['pay_time']	= RC_Time::gmtime();
					}
					update_order($order_id, $arr);
				}
				/* 初始化提示信息和链接 */
				$msgs	= array();
				$links	= array();
				/* 如果已付款，检查金额是否变动，并执行相应操作 */
				$order = order_info($order_id);
				handle_order_money_change($order, $msgs, $links);
		
				/* 显示提示信息 */
				if (!empty($msgs)) {
					return $this->showmessage(join(chr(13), $msgs), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
				} else {
					$url = RC_Uri::url('orders/admin/info',"order_id=" . $order_id . "");
					return $this->showmessage("", ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('url' => $url));
				}
			}
		} elseif ('invoice' == $step) {
			/* 保存发货后的配送方式和发货单号 */
			/* 如果不存在实体商品，退出 */
			if (!exist_real_goods($order_id)) {
				return $this->showmessage('Hacking Attemp', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
// 			$shipping_method = RC_Loader::load_app_class("shipping_method", "shipping");
			/* 保存订单 */
			$shipping_id	= $_POST['shipping'];
			$shipping		= ecjia_shipping::pluginData($shipping_id);
			$invoice_no		= trim($_POST['invoice_no']);
			$invoice_no		= str_replace(',', '<br>', $invoice_no);
			$order = array(
				'shipping_id'	=> $shipping_id,
				'shipping_name'	=> addslashes($shipping['shipping_name']),
				'invoice_no'	=> $invoice_no
			);
			update_order($order_id, $order);
		
			/* todo 记录日志 */
			$sn	= RC_Lang::get('orders::order.order_is').$old_order['order_sn'];
			ecjia_admin::admin_log($sn, 'edit', 'order');
		
			if (isset($_POST['finish'])) {
				$url = RC_Uri::url('orders/admin/info',"order_id=" . $order_id . "");
				return $this->showmessage("", ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('url' => $url));
			}
		}
	}
	
	/**
	* 处理
	*/
	public function process() {
		/* 取得参数 func */
		$func = isset($_GET['func']) ? $_GET['func'] : '';
	
		/* 删除订单商品 */
		if ('drop_order_goods' == $func) {
			/* 检查权限 */
			$this->admin_priv('order_edit', ecjia::MSGTYPE_JSON);
		
			/* 取得参数 */
			$rec_id		= intval($_GET['rec_id']);
			$order_id	= intval($_GET['order_id']);

			/* 如果使用库存，且下订单时减库存，则修改库存 */
			if (ecjia::config('use_storage') == '1' && ecjia::config('stock_dec_time') == SDT_PLACE) {
				$goods = RC_DB::table('order_goods')->where('rec_id', $rec_id)->selectRaw('goods_id, goods_number')->first();

				RC_DB::table('goods')
					->where('goods_id', $goods['goods_id'])
					->increment('goods_number', '"'.$goods['goods_number'].'"');
			}
			/* 删除 */
			RC_DB::table('order_goods')->where('rec_id', $rec_id)->delete();
			
			/* 更新商品总金额和订单总金额 */
			update_order($order_id, array('goods_amount' => order_amount($order_id)));
			update_order_amount($order_id);
		
			/* 跳回订单商品 */
			return $this->showmessage(RC_Lang::get('orders::order.act_ok'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
		} elseif ('cancel_order' == $func) {
			/* 取消刚添加或编辑的订单 */
			$step_act = $_GET['step_act'];
			$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
			if ($step_act == 'add') {
				/* 如果是添加，删除订单，返回订单列表 */
				if ($order_id > 0) {
					RC_DB::table('order_info')->where('order_id', $order_id)->delete();
				}
				ecjia_admin::admin_log(RC_Lang::get('orders::order.cancel_add_new_order'), 'remove', 'order');
				
				$url = RC_Uri::url('orders/admin/init');
				return $this->showmessage("", ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('url' => $url));
			} else {
				/* 如果是编辑，返回订单信息 */
				$url = RC_Uri::url('orders/admin/info', "order_id=" . $order_id . "");
				return $this->showmessage("", ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('url' => $url));
			}
		} elseif ('refund' == $func) {
			/* 编辑订单时由于订单已付款且金额减少而退款 */
			/* 处理退款 */
			$order_id		= $_POST['order_id'];
			$refund_type	= $_POST['refund'];
			$refund_note	= $_POST['refund_note'];
			$refund_amount	= $_POST['refund_amount'];
			$order			= order_info($order_id);
			$result = order_refund($order, $refund_type, $refund_note, $refund_amount);
			if ($result == false) {
				return false;
			}
			/* 修改应付款金额为0，已付款金额减少 $refund_amount */
			update_order($order_id, array('order_amount' => 0, 'money_paid' => $order['money_paid'] - $refund_amount));
		
			/* 返回订单详情 */
			$url = RC_Uri::url('orders/admin/info', "order_id=" . $order_id . "");
			return $this->showmessage("", ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => $url));
		} elseif ('load_refund' == $func) {
			/* 载入退款页面 */
			$order_id		= intval($_GET['order_id']);
			$anonymous		= $_GET['anonymous'];
			$refund_amount	= floatval($_GET['refund_amount']);
			$refund['refund_amount']			= $refund_amount;
			$refund['formated_refund_amount']	= price_format($refund_amount);
			$refund['anonymous']				= $anonymous;
			$refund['order_id']					= $order_id;
			$refund['ur_here']					= RC_Lang::get('orders::order.refund');
			die(json_encode($refund));
		} else {
			return $this->showmessage(RC_Lang::get('orders::order.invalid_parameter'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
	
	/*生成发货单*/
	public function go_shipping() {
		/* 查询：检查权限 */
		$this->admin_priv('order_ss_edit');
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('orders::order.order_operating')));
		$order_id = $_GET['order_id'];
		$action_note = $_GET['action_note'];
		
		$order_id = intval(trim($order_id));
		$action_note = trim($action_note);
		
		/* 查询：根据订单id查询订单信息 */
		if (!empty($order_id)) {
			$order = order_info($order_id);
		} else {
			return $this->showmessage(RC_Lang::get('orders::order.not_order'), ecjia::MSGTYPE_HTML | ecjia::MSGSTAT_ERROR);
		}
		
		/* 查询：根据订单是否完成 检查权限 */
		if (order_finished($order)) {
			$this->admin_priv('order_view_finished');
		} else {
			$this->admin_priv('order_view');
		}
		
		/* 查询：取得用户名 */
		if ($order['user_id'] > 0) {
			$user = user_info($order['user_id']);
			if (!empty($user)) {
				$order['user_name'] = $user['user_name'];
			}
		}
			
		/* 查询：取得区域名 */
		$order['region'] = get_regions($order_id);

		/* 查询：其他处理 */
		$order['order_time'] = RC_Time::local_date(ecjia::config('time_format'), $order['add_time']);
		$order['invoice_no'] = $order['shipping_status'] == SS_UNSHIPPED || $order['shipping_status'] == SS_PREPARING ? RC_Lang::get('orders::order.ss.'.SS_UNSHIPPED) : $order['invoice_no'];
		
		/* 查询：是否保价 */
		$order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;
		
		/* 查询：是否存在实体商品 */
		$exist_real_goods = exist_real_goods($order_id);
		
		/* 查询：取得订单商品 */
		$_goods = get_order_goods(array('order_id' => $order['order_id'], 'order_sn' =>$order['order_sn']));
		
		$attr = $_goods['attr'];
		$goods_list = $_goods['goods_list'];
		unset($_goods);
		
		/* 查询：商品已发货数量 此单可发货数量 */
		if ($goods_list) {
			foreach ($goods_list as $key => $goods_value) {
				if (!$goods_value['goods_id']) {
					continue;
				}
				/* 超级礼包 */
				if (($goods_value['extension_code'] == 'package_buy') && (count($goods_value['package_goods_list']) > 0)) {
					$goods_list[$key]['package_goods_list'] = package_goods($goods_value['package_goods_list'], $goods_value['goods_number'], $goods_value['order_id'], $goods_value['extension_code'], $goods_value['goods_id']);
		
					foreach ($goods_list[$key]['package_goods_list'] as $pg_key => $pg_value) {
						$goods_list[$key]['package_goods_list'][$pg_key]['readonly'] = '';
						/* 使用库存 是否缺货 */
						if ($pg_value['storage'] <= 0 && ecjia::config('use_storage') == '1' && ecjia::config('stock_dec_time') == SDT_SHIP) {
							$goods_list[$key]['package_goods_list'][$pg_key]['send']		= RC_Lang::get('orders::order.act_good_vacancy');
							$goods_list[$key]['package_goods_list'][$pg_key]['readonly']	= 'readonly="readonly"';
						}
						/* 将已经全部发货的商品设置为只读 */
						elseif ($pg_value['send'] <= 0) {
							$goods_list[$key]['package_goods_list'][$pg_key]['send']		= RC_Lang::get('orders::order.act_good_delivery');
							$goods_list[$key]['package_goods_list'][$pg_key]['readonly']	= 'readonly="readonly"';
						}
					}
				} else {
					$goods_list[$key]['sended']	= $goods_value['send_number'];
					$goods_list[$key]['send']	= $goods_value['goods_number'] - $goods_value['send_number'];
		
					$goods_list[$key]['readonly'] = '';
					/* 是否缺货 */
					if ($goods_value['storage'] <= 0 && ecjia::config('use_storage') == '1'  && ecjia::config('stock_dec_time') == SDT_SHIP) {
						$goods_list[$key]['send']		= RC_Lang::get('orders::order.act_good_vacancy');
						$goods_list[$key]['readonly']	= 'readonly="readonly"';
					} elseif ($goods_list[$key]['send'] <= 0) {
						$goods_list[$key]['send']		= RC_Lang::get('orders::order.act_good_delivery');
						$goods_list[$key]['readonly']	= 'readonly="readonly"';
					}
				}
			}
		}
		
		if (!empty($order['shipping_id'])) {
			$shipping_info = ecjia_shipping::getPluginDataById($order['shipping_id']);
			$this->assign('shipping_code', $shipping_info['shipping_code']);
		}
		$this->assign('order', $order);
		$this->assign('exist_real_goods', $exist_real_goods);
		$this->assign('goods_attr', $attr);
		$this->assign('goods_list', $goods_list);
		$this->assign('order_id', $order_id); // 订单id
		$this->assign('operation', 'split'); // 订单id
		$this->assign('action_note', $action_note); // 发货操作信息
		/* 显示模板 */
		$this->assign('ur_here', RC_Lang::get('orders::order.order_operate') . RC_Lang::get('orders::order.op_split'));
		$this->assign('form_action', RC_Uri::url('orders/admin/operate_post'));
		
		$this->assign_lang();
		$this->display('order_delivery_info.dwt');
	}
	
	public function operate_note() {
		/* 检查权限 */
		$this->admin_priv('order_os_edit', ecjia::MSGTYPE_JSON);
		
		/* 取得订单id（可能是多个，多个sn）和操作备注（可能没有） */
		if (isset($_GET['order_id'])) {
			/* 判断是一个还是多个 */
			if (is_array($_GET['order_id'])) {
				$order_id = implode(',', $_GET['order_id']);
			} else {
				$order_id = $_GET['order_id'];
			}
		}
		
		/* 确认 */
		if (isset($_GET['confirm'])) {
			$require_note	= false;
			$action			= RC_Lang::get('orders::order.op_confirm');
			$operation		= 'confirm';
		} elseif (isset($_GET['pay'])) {
			/* 付款 */
			$require_note	= ecjia::config('order_pay_note') == 1 ? true : false;
			$action			= RC_Lang::get('orders::order.op_pay');
			$operation		= 'pay';
		} elseif (isset($_GET['unpay'])) {
			/* 未付款 */
			$require_note	= ecjia::config('order_unpay_note') == 1 ? true : false;
			$order			= order_info($order_id);
			if ($order['money_paid'] > 0) {
				$show_refund = true;
			}
			$anonymous		= $order['user_id'] == 0;
			$action			= RC_Lang::get('orders::order.op_unpay');
			$operation		= 'unpay';
		} elseif (isset($_GET['prepare'])) {
			/* 配货 */
			$require_note	= false;
			$action			= RC_Lang::get('orders::order.op_prepare');
			$operation		= 'prepare';
		} elseif (isset($_GET['unship'])) {
			/* 未发货 */
			$require_note	= ecjia::config('order_unship_note') == 1 ? true : false;
			$action			= RC_Lang::get('orders::order.op_unship');
			$operation		= 'unship';
		} elseif (isset($_GET['receive'])) {
			/* 收货确认 */
			$require_note	= ecjia::config('order_receive_note') == 1 ? true : false;
			$action			= RC_Lang::get('orders::order.op_receive');
			$operation		= 'receive';
		} elseif (isset($_GET['cancel'])) {
			/* 取消 */
			$require_note	= ecjia::config('order_cancel_note') == 1 ? true : false;
			$action			= RC_Lang::get('orders::order.op_cancel');
			$operation		= 'cancel';
			$show_cancel_note	= true;
			$order			= order_info($order_id);
			if ($order['money_paid'] > 0) {
				$show_refund = true;
			}
			$anonymous		= $order['user_id'] == 0;
		} elseif (isset($_GET['invalid'])) {
			/* 无效 */
			$require_note	= ecjia::config('order_invalid_note') == 1 ? true : false;
			$action			= RC_Lang::get('orders::order.op_invalid');
			$operation		= 'invalid';
		} elseif (isset($_GET['after_service'])) {
			/* 售后 */
			$require_note	= true;
			$action			= RC_Lang::get('orders::order.op_after_service');
			$operation		= 'after_service';
		} elseif (isset($_GET['return'])) {
			/* 退货 */
			$require_note	= ecjia::config('order_return_note') == 1 ? true : false;
			$order			= order_info($order_id);
			if ($order['money_paid'] > 0) {
				$show_refund = true;
			}
			$anonymous		= $order['user_id'] == 0;
			$action			= RC_Lang::get('orders::order.op_return');
			$operation		= 'return';
		}
		$result = array();

		/* 直接处理还是跳到详细页面 */
		if ($require_note || isset($show_invoice_no) || isset($show_refund)) {
			$result['result'] = false;
			$result['require_note']		=	$require_note;				// 是否要求填写备注
			$result['show_cancel_note']	=	isset($show_cancel_note);	// 是否要求填写备注
			$result['show_invoice_no']	=	isset($show_invoice_no);	// 是否显示发货单号
			$result['show_refund']		=	isset($show_refund);		// 是否显示退款
			$result['anonymous']		=	isset($anonymous) ? $anonymous : true; // 是否匿名
			if ($result['show_cancel_note'] || $result['show_invoice_no'] || $result['show_refund']) {
				$result['result'] = true;
			}
		}
		die(json_encode($result));
	}
	
	/**
	 * 操作订单状态（载入页面）
	 */
	public function operate() {
		/* 检查权限 */
		$this->admin_priv('order_os_edit', ecjia::MSGTYPE_JSON);
		$order_id = '';
		/* 取得订单id（可能是多个，多个sn）和操作备注（可能没有） */
		if (isset($_POST['order_id'])) {
			/* 判断是一个还是多个 */
			if (is_array($_POST['order_id'])) {
				$order_id = implode(',', $_POST['order_id']);
			} else {
				$order_id = $_POST['order_id'];
			}
		}	
		
		$batch			= isset($_GET['batch']);		// 是否批处理
		$action_note	= isset($_POST['action_note']) 	? trim($_POST['action_note']) 	: '';
		$operation		= isset($_POST['operation']) 	? $_POST['operation'] 			: '';			// 订单操作
		
		/* 确认 */
		if (isset($_GET['confirm'])) {
			$require_note	= false;
			$action			= RC_Lang::get('orders::order.op_confirm');
			$operation		= 'confirm';
		} elseif (isset($_GET['pay'])) {
			/* 付款 */
			/* 检查权限 */
			$this->admin_priv('order_ps_edit', ecjia::MSGTYPE_JSON);
			$require_note	= ecjia::config('order_pay_note');
			$action			= RC_Lang::get('orders::order.op_pay');
			$operation		= 'pay';
		} elseif (isset($_GET['unpay'])) {
			/* 未付款 */
			/* 检查权限 */
			$this->admin_priv('order_ps_edit', ecjia::MSGTYPE_JSON);
		
			$require_note	= ecjia::config('order_unpay_note');
			$order			= order_info($order_id);
			
			if ($order['money_paid'] > 0) {
				$show_refund = true;
			}
			$anonymous		= $order['user_id'] == 0;
			$action			= RC_Lang::get('orders::order.op_unpay');
			$operation		= 'unpay';
			/* 记录日志 */
			$sn = RC_Lang::get('orders::order.op_unpay').' '.RC_Lang::get('orders::order.order_is').$order['order_sn'];
			ecjia_admin::admin_log($sn, 'edit', 'order_status');
		} elseif (isset($_GET['prepare'])) {
			/* 配货 */
			$require_note	= false;
			$action			= RC_Lang::get('orders::order.op_prepare');
			$operation		= 'prepare';
		} elseif (isset($_GET['ship'])) {
			/* 生成发货单 */
			//内容新添了一个func处理
			$url = RC_Uri::url('orders/admin/go_shipping', 'order_id='.$order_id.'&action_note='.$action_note);
			return $this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('url' => $url));
		} elseif (isset($_GET['unship'])) {
			/* 未发货 */
			/* 检查权限 */
			$this->admin_priv('order_ss_edit', ecjia::MSGTYPE_JSON);
			$require_note	= ecjia::config('order_unship_note');
			$action			= RC_Lang::get('orders::order.op_unship');
			$operation		= 'unship';
		} elseif (isset($_GET['receive'])) {
			/* 收货确认 */
			$require_note	= ecjia::config('order_receive_note');
			$action			= RC_Lang::get('orders::order.op_receive');
			$operation		= 'receive';
		} elseif (isset($_GET['cancel'])) {
			/* 取消 */
			$require_note	= ecjia::config('order_cancel_note');
			$action			= RC_Lang::get('orders::order.op_cancel');
			$operation		= 'cancel';
			$show_cancel_note = true;
			$order			= order_info($order_id);
			if ($order['money_paid'] > 0) {
				$show_refund = true;
			}
			$anonymous		= $order['user_id'] == 0;
		} elseif (isset($_GET['invalid'])) {
			/* 无效 */
			$require_note	= ecjia::config('order_invalid_note');
			$action			= RC_Lang::get('orders::order.op_invalid');
			$operation		= 'invalid';
		} elseif (isset($_GET['after_service'])) {
			/* 售后 */
			$require_note	= true;
			$action			= RC_Lang::get('orders::order.op_after_service');
			$operation		= 'after_service';
		} elseif (isset($_GET['return'])) {
			/* 退货 */
			$require_note	= ecjia::config('order_return_note');
			$order			= order_info($order_id);
			if ($order['money_paid'] > 0) {
				$show_refund = true;
			}
			$anonymous		= $order['user_id'] == 0;
			$action			= RC_Lang::get('orders::order.op_return');
			$operation		= 'return';
		
		} elseif (isset($_GET['assign'])) {
			/* 指派 */
			/* 取得参数 */
			$new_agency_id  = isset($_POST['agency_id']) ? intval($_POST['agency_id']) : 0;
			if ($new_agency_id == 0) {
				return $this->showmessage(RC_Lang::get('orders::order.pls_select_agency'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			/* 查询订单信息 */
			$order = order_info($order_id);
			/* 操作成功 */
			$links[] = array('href' => RC_Uri::url('orders/admin/init'), 'text' => RC_Lang::get('system::system.02_order_list'));
			return $this->showmessage(RC_Lang::get('orders::order.act_ok'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('links' => $links));
		} elseif (isset($_GET['remove'])) {
			/* 订单删除 */
			$this->remove_order();
		} elseif (isset($_GET['print'])) {
			/* 赋值公用信息 */
			$this->assign('shop_name', ecjia::config('shop_name'));
			$this->assign('shop_url', SITE_URL);
			$this->assign('shop_address', ecjia::config('shop_address'));
			$this->assign('service_phone', ecjia::config('service_phone'));
			$this->assign('print_time', RC_Time::local_date(ecjia::config('time_format')));
			$this->assign('action_user', $_SESSION['admin_name']);
		
			$html = '';
			$order_id = $_GET['order_id'];
			$order_id_array = explode(',', $order_id);

			foreach ($order_id_array as $id) {
				/* 取得订单信息 */
				$order = order_info($id);
				if (empty($order)) {
					continue;
				}
		
				/* 根据订单是否完成检查权限 */
				if (order_finished($order)) {
					if (!$this->admin_priv('order_view_finished', ecjia::MSGTYPE_JSON, false)) {
						continue;
					}
				} else {
					if (!$this->admin_priv('order_view', ecjia::MSGTYPE_JSON, false)) {
						continue;
					}
				}
				/* 取得用户名 */
				if ($order['user_id'] > 0) {
					$user = user_info($order['user_id']);
					if (!empty($user)) {
						$order['user_name'] = $user['user_name'];
					}
				}
	
				/* 取得区域名 */
				$order['region'] = get_regions($order_id) ;
		
				/* 其他处理 */
				$order['order_time']	= RC_Time::local_date(ecjia::config('time_format'), $order['add_time']);
				$order['pay_time']		= $order['pay_time'] > 0 ? RC_Time::local_date(ecjia::config('time_format'), $order['pay_time']) : RC_Lang::get('orders::order.ps.'.PS_UNPAYED);
				$order['shipping_time']	= $order['shipping_time'] > 0 ? RC_Time::local_date(ecjia::config('time_format'), $order['shipping_time']) : RC_Lang::get('orders::order.ss.'.SS_UNSHIPPED);
				$order['status'] 		= RC_Lang::get('orders::order.os.'.$order['order_status']) . ',' . RC_Lang::get('orders::order.ps.'.$order['pay_status']) . ',' . RC_Lang::get('orders::order.ss.'.$order['shipping_status']);
				$order['invoice_no']	= $order['shipping_status'] == SS_UNSHIPPED || $order['shipping_status'] == SS_PREPARING ? RC_Lang::get('orders::order.ss.'.SS_UNSHIPPED) : $order['invoice_no'];
		
				/* 此订单的发货备注(此订单的最后一条操作记录) */
				$order['invoice_note'] = RC_DB::table('order_action')->where('order_id', $order['order_id'])->where('shipping_status', 1)->orderby('log_time', 'desc')->pluck('action_note');
				
				/* 参数赋值：订单 */
				$this->assign('order', $order);
		
				/* 取得订单商品 */
				$goods_list = array();
				$goods_attr = array();

				$data = RC_DB::table('order_goods as o')
					->leftJoin('goods as g', RC_DB::raw('o.goods_id'), '=', RC_DB::raw('g.goods_id'))
					->leftJoin('brand as b', RC_DB::raw('g.brand_id'), '=', RC_DB::raw('b.brand_id'))
					->selectRaw("o.*, g.goods_number AS storage, o.goods_attr, IFNULL(b.brand_name, '') AS brand_name")
					->where(RC_DB::raw('o.order_id'), $order['order_id'])
					->get();
				
				if (!empty($data)) {
					foreach ($data as $key => $row) {
						$row['formated_subtotal']		= price_format($row['goods_price'] * $row['goods_number']);
						$row['formated_goods_price']	= price_format($row['goods_price']);
							
						$goods_attr[] = explode(' ', trim($row['goods_attr'])); //将商品属性拆分为一个数组
						$goods_list[] = $row;
					}
				}
		
				$attr = array();
				$arr  = array();
				foreach ($goods_attr AS $index => $array_val) {
					foreach ($array_val AS $value) {
						$arr = explode(':', $value);//以 : 号将属性拆开
						$attr[$index][] =  @array('name' => $arr[0], 'value' => $arr[1]);
					}
				}
		
				$this->assign('goods_attr', $attr);
				$this->assign('goods_list', $goods_list);
		
				$this->template->template_dir = '../' . DATA_DIR;
				$this->assign_lang();
				$html .= $this->fetch('order_print.dwt') .'<div style="PAGE-BREAK-AFTER:always"></div>';
			}
			echo $html;
			exit;
			
		} elseif (isset($_GET['to_delivery'])) {
			$require_note = ecjia::config('order_ship_note');
			/*判断备注是否填写*/
			if ($require_note == 1 && empty($_POST['action_note']) && $batch != 1) {
				return $this->showmessage(__('请填写备注信息！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			/* 去发货 */
			$url = RC_Uri::url('orders/admin_order_delivery/init', 'order_sn='.$_GET['order_sn']);
			return $this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('url' => $url));
		}
		
		/*判断备注是否填写*/
		if ($require_note == 1 && empty($_POST['action_note']) && $batch != 1) {
			return $this->showmessage(__('请填写备注信息！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		/* 直接处理 */
		if (!$batch) {
			/* 一个订单 */
			$this->operate_post();
			return;
		} else {
			/* 多个订单 */
			$this->batch_operate_post();
			return;
		}
		return $this->showmessage(RC_Lang::get('orders::order.act_ok'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
	}
	
	/**
	 * 操作订单状态（处理批量提交）
	 */
	public function batch_operate_post() {
		/* 检查权限 */
		$this->admin_priv('order_os_edit', ecjia::MSGTYPE_JSON);
		
		/* 取得参数 */
		$order_id_list	= $_POST['order_id'];															// 订单id（逗号格开的多个订单id）
		$operation		= isset($_POST['operation']) ? $_POST['operation'] : $_GET['operation'];		// 订单操作
		$action_note	= isset($_POST['action_note']) ? $_POST['action_note'] : '';					// 操作备注

		if (!is_array($order_id_list)) {
			if (strpos($order_id_list, ',') === false) {
				$order_id_list = array($order_id_list);
			} else {
				$order_id_list = explode(',', $order_id_list);
			}
		}
		/* 初始化处理的订单sn */
		$sn_list		= array();
		$sn_not_list	= array();
		$url = RC_Uri::url('orders/admin/init');
		
		/* 确认 */
		if ('confirm' == $operation) {
			foreach($order_id_list as $id_order) {
				$order = RC_DB::table('order_info')->where('order_id', $id_order)->where('order_status', OS_UNCONFIRMED)->first();

				/* 检查能否操作 */
				if ($order) {
					$operable_list = operable_list($order);
					if (!isset($operable_list['confirm'])) {
						/* 失败  */
						$sn_not_list[] = $id_order;
						continue;
					} else {
						$order_id = $order['order_id'];

						/* 标记订单为已确认 */
						update_order($order_id, array('order_status' => OS_CONFIRMED, 'confirm_time' => RC_Time::gmtime()));
						update_order_amount($order_id);

						/* 记录log */
						order_action($order['order_sn'], OS_CONFIRMED, SS_UNSHIPPED, PS_UNPAYED, $action_note);

						/* 发送邮件 */
						if (ecjia::config('send_confirm_email') == '1') {
							$tpl_name = 'order_confirm';
							$tpl = RC_Api::api('mail', 'mail_template', $tpl_name);
							
							$order['formated_add_time'] = RC_Time::local_date(ecjia::config('time_format'), $order['add_time']);
							$this->assign('order', $order);
							$this->assign('shop_name', ecjia::config('shop_name'));
							$this->assign('send_date', RC_Time::local_date(ecjia::config('date_format')));

							$content = $this->fetch_string($tpl['template_content']);
							RC_Mail::send_mail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html']);

						}
						$sn_list[] = $order['order_sn'];
					}	
				} else {
					$sn_not_list[] = $id_order;
				}
			}
			$sn_str = RC_Lang::get('orders::order.confirm_order');
			$success_str =	RC_Lang::get('orders::order.confirm_ok_order');
		} elseif ('invalid' == $operation) {
			/* 无效 */
			foreach ($order_id_list as $id_order) {
				$order_query = RC_Loader::load_app_class('order_query','orders');
				$where = array();
				$where['order_id'] = $id_order;
				$where = array_merge($where, $order_query->order_unpay_unship());
				$order = $this->db_order_info->find($where);
				
				if ($order) {
					/* 检查能否操作 */
					$operable_list = operable_list($order);
					if (!isset($operable_list['invalid'])) {
						$sn_not_list[] = $id_order;
						continue;
					} else {
						$order_id = $order['order_id'];

						/* 标记订单为“无效” */
						update_order($order_id, array('order_status' => OS_INVALID));

						/* 记录log */
						order_action($order['order_sn'], OS_INVALID, SS_UNSHIPPED, PS_UNPAYED, $action_note);

						/* 如果使用库存，且下订单时减库存，则增加库存 */
						if (ecjia::config('use_storage') == '1' && ecjia::config('stock_dec_time') == SDT_PLACE) {
							change_order_goods_storage($order_id, false, SDT_PLACE);
						}

						/* 发送邮件 */
						if (ecjia::config('send_invalid_email') == '1') {
							$tpl_name = 'order_invalid';
							$tpl = RC_Api::api('mail', 'mail_template', $tpl_name);
							
							$this->assign('order'		, $order);
							$this->assign('shop_name'	, ecjia::config('shop_name'));
							$this->assign('send_date'	, RC_Time::local_date(ecjia::config('date_format')));
							$content = $this->fetch_string($tpl['template_content']);
							RC_Mail::send_mail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html']);
						}
						/* 退还用户余额、积分、红包 */
						return_user_surplus_integral_bonus($order);
						$sn_list[] = $order['order_sn'];
					}
				} else {
					$sn_not_list[] = $id_order;
				}
			}
			$sn_str = RC_Lang::get('orders::order.invalid_order');
			$success_str = RC_Lang::get('orders::order.invalid_ok_order');	
		} elseif ('cancel' == $operation) {
			/* 取消 */
			foreach ($order_id_list as $id_order) {
				$order_query = RC_Loader::load_app_class('order_query','orders');
				$where = array();
				$where['order_id'] = $id_order;
				$where = array_merge($where, $order_query->order_unpay_unship());
				
				$order = $this->db_order_info->find($where);
			
				if ($order) {
					/* 检查能否操作 */

					$operable_list = operable_list($order);
					if (!isset($operable_list['cancel'])) {
						/* 失败  */
						$links[] = array('text' => RC_Lang::get('orders::order.check_info'), 'href'=> RC_Uri::url('orders/admin/init'));
						return $this->showmessage(RC_Lang::get('orders::order.cancel_order'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR, array('links' => $links));
					} else {
						$order_id = $order['order_id'];

						/* 标记订单为“取消”，记录取消原因 */
						$cancel_note = trim($_POST['cancel_note']);
						update_order($order_id, array('order_status' => OS_CANCELED, 'to_buyer' => $cancel_note));

						/* 记录log */
						order_action($order['order_sn'], OS_CANCELED, $order['shipping_status'], PS_UNPAYED, $action_note);

						/* 如果使用库存，且下订单时减库存，则增加库存 */
						if (ecjia::config('use_storage') == '1' && ecjia::config('stock_dec_time') == SDT_PLACE) {
							change_order_goods_storage($order_id, false, SDT_PLACE);
						}

						/* 发送邮件 */
						if (ecjia::config('send_cancel_email') == '1') {
							$tpl_name = 'order_cancel';
							$tpl = RC_Api::api('mail', 'mail_template', $tpl_name);

							$this->assign('order', $order);
							$this->assign('shop_name', ecjia::config('shop_name'));
							$this->assign('send_date', RC_Time::local_date(ecjia::config('date_format')));
							$content = $this->fetch_string($tpl['template_content']);

							if (!RC_Mail::send_mail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html'])) {
								return $this->showmessage(RC_Lang::get('orders::order.send_mail_fail'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
							}
						} 
						/* 退还用户余额、积分、红包 */
						return_user_surplus_integral_bonus($order);
						$sn_list[] = $order['order_sn'];
					}
				} else {
					$sn_not_list[] = $id_order;
				}
				
			}
			$sn_str = RC_Lang::get('orders::order.cancel_order');
			$success_str =  RC_Lang::get('orders::order.cancel_ok_order');
		} elseif ('remove' == $operation) {
			$data = array(
				'is_delete' 	=> 1,
				'delete_time' 	=> RC_Time::local_date(ecjia::config('time_format'), RC_Time::gmtime())
			);
			/* 删除 */
			foreach ($order_id_list as $id_order) {
				/* 检查能否操作 */
				$order = order_info($id_order);	
				$operable_list = operable_list($order);

				if (!isset($operable_list['remove'])) {
					$sn_not_list[] = $id_order;
					continue;
				}
				
				/* 删除订单 */
				RC_DB::table('order_info')->where('order_id', $order['order_id'])->update($data);
				
				$action_array = array('delivery', 'back');
				del_delivery($order['order_id'], $action_array);

				ecjia_admin::admin_log($order['order_sn'], 'remove', 'order');
				$sn_list[] = $order['order_sn'];
			}
			$sn_str = RC_Lang::get('orders::order.remove_order');
			$success_str = RC_Lang::get('orders::order.delete_ok_order');
		} else {
			return $this->showmessage('invalid params', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR, array('pjaxurl' => $url));
		}

		/* 取得备注信息 */
		if (empty($sn_not_list)) {
			$sn_list = empty($sn_list) ? '' : $success_str . join($sn_list, ',');
			$msg = $sn_list;
			return $this->showmessage($msg, ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => $url));
		} else {
			$sn_str .="<br/>";

			$order_list_no_fail = array();
			$data = RC_DB::table('order_info')->whereIn('order_id', $sn_not_list)->get();

			foreach ($data as $key => $row) {
				$sn_str .= "<br>".RC_Lang::get('orders::order.label_order_sn').$row['order_sn'] .";&nbsp;&nbsp;&nbsp;";

				$order_list_fail = '';
				foreach (operable_list($row) as $key => $value) {
					if ($key != $operation) {
						$order_list_fail .= "&nbsp;".RC_Lang::get('orders::order.op_'.$key);
					}
				}
				$sn_str .= RC_Lang::get('orders::order.log_maybe_handle').substr($order_list_fail, 0);
			}
			return $this->showmessage($sn_str, ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR, array('pjaxurl' => $url));
		}
	}
	
	/**
	 * 操作订单状态（处理提交）
	 */
	public function operate_post() {
		/* 检查权限 */
		$this->admin_priv('order_os_edit', ecjia::MSGTYPE_JSON);

		/* 取得参数 */
		$order_id	= intval(trim($_POST['order_id']));	// 订单id
		$operation	= $_POST['operation'];				// 订单操作
		/* 取得备注信息 */
		$action_note = $_POST['action_note'];

		/* 查询订单信息 */
		$order = order_info($order_id);
		
		/* 检查能否操作 */
		$operable_list = operable_list($order);
		if (!isset($operable_list[$operation])) {
			return $this->showmessage(RC_Lang::get('orders::order.unable_operation_order'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		/* 确认 */
		if ('confirm' == $operation) {
			/* 标记订单为已确认 */
			update_order($order_id, array('order_status' => OS_CONFIRMED, 'confirm_time' => RC_Time::gmtime()));
			update_order_amount($order_id);

			/* 记录日志 */
			ecjia_admin::admin_log(RC_Lang::get('orders::order.os.'.OS_CONFIRMED).' '.RC_Lang::get('orders::order.order_is').$order['order_sn'], 'edit', 'order_status');
			/* 记录log */
			order_action($order['order_sn'], OS_CONFIRMED, SS_UNSHIPPED, PS_UNPAYED, $action_note);
		
			/* 如果原来状态不是“未确认”，且使用库存，且下订单时减库存，则减少库存 */
			if ($order['order_status'] != OS_UNCONFIRMED && ecjia::config('use_storage') == '1' && ecjia::config('stock_dec_time') == SDT_PLACE) {
				change_order_goods_storage($order_id, true, SDT_PLACE);
			}
		
			/* 发送邮件 */
			$cfg = ecjia::config('send_confirm_email');
			if ($cfg == '1') {
				$tpl_name = 'order_confirm';
				$tpl = RC_Api::api('mail', 'mail_template', $tpl_name);
				
				$this->assign('order', 		$order);
				$this->assign('shop_name', 	ecjia::config('shop_name'));
				$this->assign('send_date', 	RC_Time::local_date(ecjia::config('date_format')));
				
				$content = $this->fetch_string($tpl['template_content']);
				
				if (!RC_Mail::send_mail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html'])) {
					return $this->showmessage(RC_Lang::get('orders::order.send_mail_fail'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
				}
			} else {
				return $this->showmessage(RC_Lang::get('orders::order.act_ok'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
			}
		} elseif ('pay' == $operation) {
			/*判断备注是否填写*/
			$require_note = ecjia::config('order_pay_note');
			if ($require_note == 1 && empty($_POST['action_note'])) {
				return $this->showmessage(__('请填写备注信息！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			
			/* 付款 */
			/* 检查权限 */
			$this->admin_priv('order_ps_edit', ecjia::MSGTYPE_JSON);
		
			/* 标记订单为已确认、已付款，更新付款时间和已支付金额，如果是货到付款，同时修改订单为“收货确认” */
			if ($order['order_status'] != OS_CONFIRMED) {
				$arr['order_status']	= OS_CONFIRMED;
				$arr['confirm_time']	= RC_Time::gmtime();
			}
			$arr['pay_status']		= PS_PAYED;
			$arr['pay_time']		= RC_Time::gmtime();
			$arr['money_paid']		= $order['money_paid'] + $order['order_amount'];
			$arr['order_amount']	= 0;
			$payment_method = RC_Loader::load_app_class('payment_method','payment');
			$payment = $payment_method->payment_info($order['pay_id']);
			if ($payment['is_cod']) {
				$arr['shipping_status']		= SS_RECEIVED;
				$order['shipping_status']	= SS_RECEIVED;
			}
			update_order($order_id, $arr);
			
			/* 记录日志 */
			ecjia_admin::admin_log(RC_Lang::get('orders::order.ps.'.PS_PAYED).' '.RC_Lang::get('orders::order.order_is').$order['order_sn'], 'edit', 'order_status');
			/* 记录log */
			order_action($order['order_sn'], OS_CONFIRMED, $order['shipping_status'], PS_PAYED, $action_note);
		} elseif ('unpay' == $operation) {
			/*判断是否需要填写备注*/
			$require_note = ecjia::config('order_unpay_note');
			if ($require_note == 1 && empty($_POST['action_note'])) {
				return $this->showmessage(__('请填写操作备注！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			
			/* 设为未付款 */
			/* 检查权限 */
			$this->admin_priv('order_ps_edit', ecjia::MSGTYPE_JSON);
		
			/* todo 处理退款 */
			if ($order['money_paid'] > 0) {
				$refund_type = @$_POST['refund'];
				$refund_note = @$_POST['refund_note'];
				$result = order_refund($order, $refund_type, $refund_note);
				if ($result == false) {
					return false;
				}
			}
			
			/* 标记订单为未付款，更新付款时间和已付款金额 */
			$arr = array(
				'pay_status'	=> PS_UNPAYED,
				'pay_time'		=> 0,
				'money_paid'	=> 0,
				'order_amount'	=> $order['money_paid']
			);
			update_order($order_id, $arr);
			
			/* 记录日志 */
			ecjia_admin::admin_log(RC_Lang::get('orders::order.op_unpay').' '.RC_Lang::get('orders::order.order_is').$order['order_sn'], 'edit', 'order_status');
			/* 记录log */
			order_action($order['order_sn'], OS_CONFIRMED, SS_UNSHIPPED, PS_UNPAYED, $action_note);
		} elseif ('prepare' == $operation) {
			/* 配货 */
			/* 标记订单为已确认，配货中 */
			if ($order['order_status'] != OS_CONFIRMED) {
				$arr['order_status']	= OS_CONFIRMED;
				$arr['confirm_time']	= RC_Time::gmtime();
			}
			$arr['shipping_status']		= SS_PREPARING;
			update_order($order_id, $arr);
			/* 记录日志 */
			ecjia_admin::admin_log(RC_Lang::get('orders::order.ss.'.SS_PREPARING).' '.RC_Lang::get('orders::order.order_is').$order['order_sn'], 'edit', 'order_status');
			/* 记录log */
			order_action($order['order_sn'], OS_CONFIRMED, SS_PREPARING, $order['pay_status'], $action_note);
		} elseif ('split' == $operation) {
			/* 分单确认 */
			/* 检查权限 */
			$this->admin_priv('order_ss_edit', ecjia::MSGTYPE_JSON);
			
			/* 定义当前时间 */
			define('GMTIME_UTC', RC_Time::gmtime()); // 获取 UTC 时间戳
		
			/* 获取表单提交数据 */
			$suppliers_id = isset($_POST['suppliers_id']) ? intval(trim($_POST['suppliers_id'])) : '0'; //供货商
			
			array_walk($_POST['delivery'], 'trim_array_walk');
			array_walk($_POST['send_number'], 'trim_array_walk');
			array_walk($_POST['send_number'], 'intval_array_walk');
			
			$delivery = $_POST['delivery'];
			
			$send_number = $_POST['send_number'];
			$action_note = isset($_POST['action_note']) ? trim($_POST['action_note']) : '';
			
			$delivery['user_id']		= intval($delivery['user_id']);

			$delivery['country']		= trim($delivery['country']);
			$delivery['province']		= trim($delivery['province']);
			$delivery['city']			= trim($delivery['city']);
			$delivery['district']		= trim($delivery['district']);
			$delivery['street']			= trim($delivery['street']);

			$delivery['agency_id']		= intval($delivery['agency_id']);
			$delivery['insure_fee']		= floatval($delivery['insure_fee']);
			$delivery['shipping_fee']	= floatval($delivery['shipping_fee']);
		
			/* 订单是否已全部分单检查 */
			if ($order['order_status'] == OS_SPLITED) {
				/* 操作失败 */
				$links[] = array('text' => RC_Lang::get('orders::order.order_info'), 'href' => RC_Uri::url('orders/admin/info', 'order_id=' . $order_id));
				return $this->showmessage(sprintf(RC_Lang::get('orders::order.order_splited_sms'), $order['order_sn'], RC_Lang::get('orders::order.os.'.OS_SPLITED), RC_Lang::get('orders::order.ss.'.SS_SHIPPED_ING), ecjia::config('shop_name')), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR, array('links' => $links));
			}

			/* 取得订单商品 */
			$_goods = get_order_goods(array('order_id' => $order_id, 'order_sn' => $delivery['order_sn']));
			$goods_list = $_goods['goods_list'];
		    
			/* 检查此单发货数量填写是否正确 合并计算相同商品和货品 */
			if (!empty($send_number) && !empty($goods_list)) {
				$goods_no_package = array();
				foreach ($goods_list as $key => $value) {
					/* 去除 此单发货数量 等于 0 的商品 */
					if (!isset($value['package_goods_list']) || !is_array($value['package_goods_list'])) {
						// 如果是货品则键值为商品ID与货品ID的组合
						$_key = empty($value['product_id']) ? $value['goods_id'] : ($value['goods_id'] . '_' . $value['product_id']);
		
						// 统计此单商品总发货数 合并计算相同ID商品或货品的发货数
						if (empty($goods_no_package[$_key])) {
							$goods_no_package[$_key] = $send_number[$value['rec_id']];
						} else {
							$goods_no_package[$_key] += $send_number[$value['rec_id']];
						}
		
						//去除
						if ($send_number[$value['rec_id']] <= 0) {
							unset($send_number[$value['rec_id']], $goods_list[$key]);
							continue;
						}
					} else {
						/* 组合超值礼包信息 */
						$goods_list[$key]['package_goods_list'] = package_goods($value['package_goods_list'], $value['goods_number'], $value['order_id'], $value['extension_code'], $value['goods_id']);
		
						/* 超值礼包 */
						foreach ($value['package_goods_list'] as $pg_key => $pg_value) {
							// 如果是货品则键值为商品ID与货品ID的组合
							$_key = empty($pg_value['product_id']) ? $pg_value['goods_id'] : ($pg_value['goods_id'] . '_' . $pg_value['product_id']);
		
							//统计此单商品总发货数 合并计算相同ID产品的发货数
							if (empty($goods_no_package[$_key])) {
								$goods_no_package[$_key] = $send_number[$value['rec_id']][$pg_value['g_p']];
							} else {
								//否则已经存在此键值
								$goods_no_package[$_key] += $send_number[$value['rec_id']][$pg_value['g_p']];
							}
		
							//去除
							if ($send_number[$value['rec_id']][$pg_value['g_p']] <= 0) {
								unset($send_number[$value['rec_id']][$pg_value['g_p']], $goods_list[$key]['package_goods_list'][$pg_key]);
							}
						}
		
						if (count($goods_list[$key]['package_goods_list']) <= 0) {
							unset($send_number[$value['rec_id']], $goods_list[$key]);
							continue;
						}
					}
		
					/* 发货数量与总量不符 */
					if (!isset($value['package_goods_list']) || !is_array($value['package_goods_list'])) {
						$sended = order_delivery_num($order_id, $value['goods_id'], $value['product_id']);

						if (($value['goods_number'] - $sended - $send_number[$value['rec_id']]) < 0) {
							/* 操作失败 */
							$links[] = array('text' => RC_Lang::get('orders::order.order_info'), 'href' => RC_Uri::url('orders/admin/info', 'order_id=' . $order_id));
							return $this->showmessage(RC_Lang::get('orders::order.act_ship_num'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR, array('links' => $links));
						}
					} else {
						/* 超值礼包 */
						foreach ($goods_list[$key]['package_goods_list'] as $pg_key => $pg_value) {
							if (($pg_value['order_send_number'] - $pg_value['sended'] - $send_number[$value['rec_id']][$pg_value['g_p']]) < 0) {
								/* 操作失败 */
								$links[] = array('text' => RC_Lang::get('orders::order.order_info'), 'href' => RC_Uri::url('orders/admin/info', 'order_id=' . $order_id));
								return $this->showmessage(RC_Lang::get('orders::order.act_ship_num'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR, array('links' => $links));
							}
						}
					}
				}
			}

			/* 对上一步处理结果进行判断 兼容 上一步判断为假情况的处理 */
			if (empty($send_number) || empty($goods_list)) {
				/* 操作失败 */
				$links[] = array('text' => RC_Lang::get('orders::order.order_info'), 'href' => RC_Uri::url('orders/admin/info', 'order_id=' . $order_id));
				return $this->showmessage('发货数量或商品不能为空', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR, array('links' => $links));
			}
		
			/* 检查此单发货商品库存缺货情况 */
			/* $goods_list已经过处理 超值礼包中商品库存已取得 */
			$virtual_goods = array();
			$package_virtual_goods = array();
			foreach ($goods_list as $key => $value) {
				// 商品（超值礼包）
				if ($value['extension_code'] == 'package_buy') {
					foreach ($value['package_goods_list'] as $pg_key => $pg_value) {
						if ($pg_value['goods_number'] < $goods_no_package[$pg_value['g_p']] && 
							((ecjia::config('use_storage') == '1'  && ecjia::config('stock_dec_time') == SDT_SHIP) || 
							(ecjia::config('use_storage') == '0' && $pg_value['is_real'] == 0))) {
							/* 操作失败 */
							$links[] = array('text' => RC_Lang::get('orders::order.order_info'), 'href' => RC_Uri::url('orders/admin/info', 'order_id=' . $order_id));
							return $this->showmessage(sprintf(RC_Lang::get('orders::order.act_goods_vacancy'), $pg_value['goods_name']), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR, array('links' => $links));
						}
		
						/* 商品（超值礼包） 虚拟商品列表 package_virtual_goods*/
						if ($pg_value['is_real'] == 0) {
							$package_virtual_goods[] = array(
								'goods_id'		=> $pg_value['goods_id'],
								'goods_name'	=> $pg_value['goods_name'],
								'num'			=> $send_number[$value['rec_id']][$pg_value['g_p']]
							);
						}
					}
				} elseif ($value['extension_code'] == 'virtual_card' || $value['is_real'] == 0) {
					// 商品（虚货）
					$num = RC_DB::table('virtual_card')->where('goods_id', $value['goods_id'])->where('is_saled', 0)->count();
					
					if (($num < $goods_no_package[$value['goods_id']]) && !(ecjia::config('use_storage') == '1' && ecjia::config('stock_dec_time') == SDT_PLACE)) {
						/* 操作失败 */
						$links[] = array('text' => RC_Lang::get('orders::order.order_info'), 'href' => RC_Uri::url('orders/admin/info', 'order_id=' . $order_id));
						return $this->showmessage(sprintf(RC_Lang::get('system::system.virtual_card_oos'), $value['goods_name']), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR, array('links' => $links));
					}
		
					/* 虚拟商品列表 virtual_card*/
					if ($value['extension_code'] == 'virtual_card') {
						$virtual_goods[$value['extension_code']][] = array('goods_id' => $value['goods_id'], 'goods_name' => $value['goods_name'], 'num' => $send_number[$value['rec_id']]);
					}
				} else {
					// 商品（实货）、（货品）
					//如果是货品则键值为商品ID与货品ID的组合
					$_key = empty($value['product_id']) ? $value['goods_id'] : ($value['goods_id'] . '_' . $value['product_id']);
		
					/* （实货） */
					if (empty($value['product_id'])) {
						$num = RC_DB::table('goods')->where('goods_id', $value['goods_id'])->pluck('goods_number');
					} else {
					/* （货品） */
						$num = RC_DB::table('products')->where('goods_id', $value['goods_id'])->where('product_id', $value['product_id'])->pluck('product_number');
					}
		
					if (($num < $goods_no_package[$_key]) && ecjia::config('use_storage') == '1'  && ecjia::config('stock_dec_time') == SDT_SHIP) {
						/* 操作失败 */
						$links[] = array('text' => RC_Lang::get('orders::order.order_info'), 'href' => RC_Uri::url('orders/admin/info', 'order_id=' . $order_id));
						return $this->showmessage(sprintf(RC_Lang::get('orders::order.act_goods_vacancy'), $value['goods_name']), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR, array('links' => $links));
					}
				}
			}

			/* 生成发货单 */
			/* 获取发货单号和流水号 */
			$delivery['delivery_sn']	= get_delivery_sn();
			$delivery_sn = $delivery['delivery_sn'];
			/* 获取当前操作员 */
			$delivery['action_user']	= $_SESSION['admin_name'];
			/* 获取发货单生成时间 */
			$delivery['update_time']	= GMTIME_UTC;
			$delivery_time = $delivery['update_time'];

			$orderinfo		= RC_DB::table('order_info')->where('order_sn', $delivery['order_sn'])->first();
			
			$delivery['add_time'] = $orderinfo['add_time'];
			/* 获取发货单所属供应商 */
			$delivery['suppliers_id']	= $suppliers_id;
			/* 设置默认值 */
			$delivery['status']			= 2; // 正常
			$delivery['order_id']		= $order_id;
		
			
			/*平台发货时将用户地址转为坐标存入delivery_order表*/
			if (empty($orderinfo['longitude']) || empty($orderinfo['latitude'])) {
				$province_name 	= ecjia_region::getRegionName($delivery['province']);
				$city_name 		= ecjia_region::getRegionName($delivery['city']);
				$district_name 	= ecjia_region::getRegionName($delivery['district']);
				$street_name 	= ecjia_region::getRegionName($delivery['street']);
			
				$consignee_address = '';
				if (!empty($province_name)) {
					$consignee_address .= $province_name;
				}
				if (!empty($city_name)) {
					$consignee_address .= $city_name;
				}
				if (!empty($district_name)) {
					$consignee_address .= $district_name;
				}
				if (!empty($street_name)) {
					$consignee_address .= $street_name;
				}
				$consignee_address .= $delivery['address'];
				$consignee_address = urlencode($consignee_address);
			
				//腾讯地图api 地址解析（地址转坐标）
				$keys = ecjia::config('map_qq_key');
				$shop_point = RC_Http::remote_get("https://apis.map.qq.com/ws/geocoder/v1/?address=".$consignee_address."&key=".$keys);
				$shop_point = json_decode($shop_point['body'], true);
				if (isset($shop_point['result']) && !empty($shop_point['result']['location'])) {
					$delivery['longitude']	= $shop_point['result']['location']['lng'];
					$delivery['latitude']	= $shop_point['result']['location']['lat'];
				}
			}
			
			/* 过滤字段项 */
			$filter_fileds = array(
				'order_sn', 'add_time', 'user_id', 'how_oos', 'shipping_id', 'shipping_fee',
				'consignee', 'address', 'longitude', 'latitude','country', 'province', 'city', 'district', 'street', 'sign_building',
				'email', 'zipcode', 'tel', 'mobile', 'best_time', 'postscript', 'insure_fee',
				'agency_id', 'delivery_sn', 'action_user', 'update_time',
				'suppliers_id', 'status', 'order_id', 'shipping_name'
			);
			$_delivery = array();
			foreach ($filter_fileds as $value) {
				$_delivery[$value] = $delivery[$value];
			}
			$_delivery['store_id'] = $order['store_id'];
			
			/* 发货单入库 */
			$delivery_id = RC_DB::table('delivery_order')->insertGetId($_delivery);
			
			if ($delivery_id) {
				$data = array(
					'order_status' 	=> RC_Lang::get('orders::order.ss.'.SS_PREPARING),
					'order_id'   	=> $order_id,
					'message'		=> sprintf(RC_Lang::get('orders::order.order_prepare_message'), $order['order_sn']),
					'add_time'     	=> RC_Time::gmtime()
				);
				RC_DB::table('order_status_log')->insert($data);
			}	
			/* 记录日志 */
			ecjia_admin::admin_log(RC_Lang::get('orders::order.order_is').$order['order_sn'], 'produce', 'delivery_order');
			if ($delivery_id) {
				$delivery_goods = array();
				//发货单商品入库
				if (!empty($goods_list)) {
					foreach ($goods_list as $value) {
						// 商品（实货）（虚货）
						if (empty($value['extension_code']) || $value['extension_code'] == 'virtual_card') {
							$delivery_goods = array(
								'delivery_id'	=> $delivery_id,
								'goods_id'		=> $value['goods_id'],
								'product_id'	=> $value['product_id'],
								'product_sn'	=> $value['product_sn'],
								'goods_id'		=> $value['goods_id'],
								'goods_name'	=> addslashes($value['goods_name']),
								'brand_name'	=> addslashes($value['brand_name']),
								'goods_sn'		=> $value['goods_sn'],
								'send_number'	=> $send_number[$value['rec_id']],
								'parent_id'		=> 0,
								'is_real'		=> $value['is_real'],
								'goods_attr'	=> addslashes($value['goods_attr'])
							);
		
							/* 如果是货品 */
							if (!empty($value['product_id'])) {
								$delivery_goods['product_id'] = $value['product_id'];
							}
							$query = RC_DB::table('delivery_goods')->insertGetId($delivery_goods);
							
						} elseif ($value['extension_code'] == 'package_buy') {
							// 商品（超值礼包）
							foreach ($value['package_goods_list'] as $pg_key => $pg_value) {
								$delivery_pg_goods = array(
									'delivery_id'		=> $delivery_id,
									'goods_id'			=> $pg_value['goods_id'],
									'product_id'		=> $pg_value['product_id'],
									'product_sn'		=> $pg_value['product_sn'],
									'goods_name'		=> $pg_value['goods_name'],
									'brand_name'		=> '',
									'goods_sn'			=> $pg_value['goods_sn'],
									'send_number'		=> $send_number[$value['rec_id']][$pg_value['g_p']],
									'parent_id'			=> $value['goods_id'], // 礼包ID
									'extension_code'	=> $value['extension_code'], // 礼包
									'is_real'			=> $pg_value['is_real']
								);
								$query = RC_DB::table('delivery_goods')->insertGetId($delivery_pg_goods);
							}
						}
					}
				}
			} else {
				/* 操作失败 */
				$links[] = array('text' => RC_Lang::get('orders::order.order_info'), 'href' => RC_Uri::url('orders/admin/info', 'order_id=' . $order_id));
				return $this->showmessage(RC_Lang::get('orders::order.act_false'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR, array('links' => $links));
			}
			unset($filter_fileds, $delivery, $_delivery, $order_finish);
		
			/* 订单信息更新处理 */
			if (true) {
				/* 订单信息 */
				$_sended = & $send_number;
				foreach ($_goods['goods_list'] as $key => $value) {
					if ($value['extension_code'] != 'package_buy') {
						unset($_goods['goods_list'][$key]);
					}
				}
				foreach ($goods_list as $key => $value) {
					if ($value['extension_code'] == 'package_buy') {
						unset($goods_list[$key]);
					}
				}
				$_goods['goods_list'] = $goods_list + $_goods['goods_list'];
				unset($goods_list);
		
				/* 更新订单的虚拟卡 商品（虚货） */
				$_virtual_goods = isset($virtual_goods['virtual_card']) ? $virtual_goods['virtual_card'] : '';
				update_order_virtual_goods($order_id, $_sended, $_virtual_goods);
		
				/* 更新订单的非虚拟商品信息 即：商品（实货）（货品）、商品（超值礼包）*/
				update_order_goods($order_id, $_sended, $_goods['goods_list']);
		
				/* 标记订单为已确认 “发货中” */
				/* 更新发货时间 */
				$order_finish = get_order_finish($order_id);
				$shipping_status = SS_SHIPPED_ING;
				if ($order['order_status'] != OS_CONFIRMED && $order['order_status'] != OS_SPLITED && $order['order_status'] != OS_SPLITING_PART) {
					$arr['order_status']	= OS_CONFIRMED;
					$arr['confirm_time']	= GMTIME_UTC;
				}
		
				$arr['order_status']		= $order_finish ? OS_SPLITED : OS_SPLITING_PART; // 全部分单、部分分单
				$arr['shipping_status']		= $shipping_status;
				update_order($order_id, $arr);
			}
			/* 记录log */
			order_action($order['order_sn'], $arr['order_status'], $shipping_status, $order['pay_status'], $action_note);
			$url = RC_Uri::url('orders/admin/info', 'order_id=' . $order_id);
			return $this->showmessage(RC_Lang::get('orders::order.act_ok'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('url' => $url));
		} elseif ('unship' == $operation) {
			/*判断备注是否填写*/
			$require_note = ecjia::config('order_unship_note');
			if ($require_note == 1 && empty($_POST['action_note'])) {
				return $this->showmessage(__('请填写备注信息！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			
			/* 设为未发货 */
			/* 检查权限 */
			$this->admin_priv('order_ss_edit', ecjia::MSGTYPE_JSON);
		
			/* 标记订单为“未发货”，更新发货时间, 订单状态为“确认” */
			update_order($order_id, array('shipping_status' => SS_UNSHIPPED, 'shipping_time' => 0, 'invoice_no' => '', 'order_status' => OS_CONFIRMED));
		
			/* 记录log */
			order_action($order['order_sn'], $order['order_status'], SS_UNSHIPPED, $order['pay_status'], $action_note);
		
			/* 如果订单用户不为空，计算积分，并退回 */
			if ($order['user_id'] > 0) {
				/* 取得用户信息 */
				$user = user_info($order['user_id']);
		
				/* 计算并退回积分 */
				$integral = integral_to_give($order);
				$options = array(
					'user_id'		=> $order['user_id'],
					'rank_points'	=> (-1) * intval($integral['rank_points']),
					'pay_points'	=> (-1) * intval($integral['custom_points']),
					'change_desc'	=> sprintf(RC_Lang::get('orders::order.return_order_gift_integral'), $order['order_sn'])
				);
				RC_Api::api('user', 'account_change_log', $options);
		
				/* todo 计算并退回红包 */
				return_order_bonus($order_id);
			}
		
			/* 如果使用库存，则增加库存 */
			if (ecjia::config('use_storage') == '1' && ecjia::config('stock_dec_time') == SDT_SHIP) {
				change_order_goods_storage($order['order_id'], false, SDT_SHIP);
			}
		
			/* 删除发货单 */
			del_order_delivery($order_id);
		
			/* 将订单的商品发货数量更新为 0 */

			$data = array(
				'send_number' => 0,
			);
			RC_DB::table('order_goods')->where('order_id', $order_id)->update($data);
			
		} elseif ('receive' == $operation) {
			/*判断备注是否填写*/
			$require_note = ecjia::config('order_receive_note');
			if ($require_note == 1 && empty($_POST['action_note'])) {
				return $this->showmessage(__('请填写备注信息！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			
			/* 收货确认 */
			/* 标记订单为“收货确认”，如果是货到付款，同时修改订单为已付款 */
			$arr = array('shipping_status' => SS_RECEIVED);
			$payment_method = RC_Loader::load_app_class('payment_method','payment');
			$payment = $payment_method->payment_info($order['pay_id']);
			if ($payment['is_cod']) {
				$arr['pay_status']		= PS_PAYED;
				$order['pay_status']	= PS_PAYED;
			}
			$update = update_order($order_id, $arr);
			if ($update) {
				$data = array(
					'order_status' => RC_Lang::get('orders::order.ss.'.SS_RECEIVED),
					'order_id'     => $order_id,
					'message'      => RC_Lang::get('orders::order.order_goods_served'),
					'add_time'     => RC_Time::gmtime()
				);
				RC_DB::table('order_status_log')->insert($data);
				//update commission_bill
// 				RC_Api::api('commission', 'add_bill_detail', array('store_id' => $order['store_id'], 'order_type' => 'buy', 'order_id' => $order_id, 'order_amount' => $order['order_amount']));
				RC_Api::api('commission', 'add_bill_queue', array('order_type' => 'buy', 'order_id' => $order_id));
				RC_Api::api('goods', 'update_goods_sales', array('order_id' => $order_id));
			}
			
			/* 记录log */
			order_action($order['order_sn'], $order['order_status'], SS_RECEIVED, $order['pay_status'], $action_note);
		} elseif ('cancel' == $operation) {
			/*判断是否需要填写备注*/
			$require_note = ecjia::config('order_cancel_note');
			if ($require_note == 1 && empty($_POST['action_note'])) {
				return $this->showmessage(__('请填写操作备注！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			
			/* 取消 */
			/* todo 处理退款 */
			if ($order['money_paid'] > 0) {
				$refund_type = $_POST['refund'];
				$refund_note = $_POST['refund_note'];
				$result = order_refund($order, $refund_type, $refund_note);
				if ($result == false) {
					return false;
				}
			}
			/* 标记订单为“取消”，记录取消原因 */
			$cancel_note = isset($_POST['cancel_note']) ? trim($_POST['cancel_note']) : '';
			$arr = array(
				'order_status'	=> OS_CANCELED,
				'to_buyer'		=> $cancel_note,
				'pay_status'	=> PS_UNPAYED,
				'pay_time'		=> 0,
				'money_paid'	=> 0,
				'order_amount'	=> $order['money_paid']
			);
			
			$confirm_receive = update_order($order_id, $arr);
			if ($confirm_receive) {
				$data = array(
					'order_status' 	=> RC_Lang::get('orders::order.cs.'.CS_FINISHED),
					'order_id'		=> $order_id,
					'add_time'   	=> RC_Time::gmtime(),
				);
				RC_DB::table('order_status_log')->insert($data);
			}
				
			/* 记录log */
			order_action($order['order_sn'], OS_CANCELED, $order['shipping_status'], PS_UNPAYED, $action_note);
		
			/* 如果使用库存，且下订单时减库存，则增加库存 */
			if (ecjia::config('use_storage') == '1' && ecjia::config('stock_dec_time') == SDT_PLACE) {
				change_order_goods_storage($order_id, false, SDT_PLACE);
			}
		
			/* 退还用户余额、积分、红包 */
			return_user_surplus_integral_bonus($order);
		
			/* 发送邮件 */
			$cfg = ecjia::config('send_cancel_email');
			if ($cfg == '1') {
				$tpl_name = 'order_cancel';
				$tpl = RC_Api::api('mail', 'mail_template', $tpl_name);
				
				$this->assign('order', $order);
				$this->assign('shop_name', ecjia::config('shop_name'));
				$this->assign('send_date', RC_Time::local_date(ecjia::config('date_format')));
				$content = $this->fetch_string($tpl['template_content']);

				if (!RC_Mail::send_mail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html'])) {
					return $this->showmessage(RC_Lang::get('orders::order.send_mail_fail'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
				}
			}
			
		} elseif ('invalid' == $operation) {
			/*判断备注是否填写*/
			$require_note = ecjia::config('order_invalid_note');
			if ($require_note == 1 && empty($_POST['action_note'])) {
				return $this->showmessage(__('请填写备注信息！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			
			/* 设为无效 */
			/* 标记订单为“无效”、“未付款” */
			update_order($order_id, array('order_status' => OS_INVALID));
		
			/* 记录log */
			order_action($order['order_sn'], OS_INVALID, $order['shipping_status'], PS_UNPAYED, $action_note);
		
			/* 如果使用库存，且下订单时减库存，则增加库存 */
			if (ecjia::config('use_storage') == '1' && ecjia::config('stock_dec_time') == SDT_PLACE) {
				change_order_goods_storage($order_id, false, SDT_PLACE);
			}
		
			/* 发送邮件 */
			$cfg = ecjia::config('send_invalid_email');
			if ($cfg == '1') {
				$tpl_name = 'order_invalid';
				$tpl = RC_Api::api('mail', 'mail_template', $tpl_name);
				
				$this->assign('order', $order);
				$this->assign('shop_name', ecjia::config('shop_name'));
				$this->assign('send_date', RC_Time::local_date(ecjia::config('date_format')));
				$content = $this->fetch_string($tpl['template_content']);

				if (!RC_Mail::send_mail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html'])) {
					return $this->showmessage(RC_Lang::get('orders::order.send_mail_fail'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
				}
			}
			/* 退货用户余额、积分、红包 */
			return_user_surplus_integral_bonus($order);
		} 
// 		elseif ('return' == $operation) {
// 			/*判断备注是否填写*/
// 			$require_note = ecjia::config('order_return_note');
// 			if ($require_note == 1 && empty($_POST['action_note'])) {
// 				return $this->showmessage(__('请填写备注信息！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
// 			}
			
// 			/* 退货 */
// 			/* 定义当前时间 */
// 			define('GMTIME_UTC', RC_Time::gmtime()); // 获取 UTC 时间戳
		
// 			/* 过滤数据 */
// 			$_POST['refund'] = isset($_POST['refund']) ? $_POST['refund'] : '';
// 			$_POST['refund_note'] = isset($_POST['refund_note']) ? $_POST['refund'] : '';
		
// 			/* 标记订单为“退货”、“未付款”、“未发货” */
// 			$arr = array(
// 				'order_status'		=> OS_RETURNED,
// 				'pay_status'		=> PS_UNPAYED,
// 				'shipping_status'	=> SS_UNSHIPPED,
// 				'money_paid'		=> 0,
// 				'invoice_no'		=> '',
// 				'order_amount'		=> $order['money_paid']
// 			);
// 			update_order($order_id, $arr);
		
// 			/* todo 处理退款 */
// 			if ($order['pay_status'] != PS_UNPAYED) {
// 				$refund_type = $_POST['refund'];
// 				$refund_note = $_POST['refund'];
// 				order_refund($order, $refund_type, $refund_note);
// 			}
		
// 			/* 记录log */
// 			order_action($order['order_sn'], OS_RETURNED, SS_UNSHIPPED, PS_UNPAYED, $action_note);
		
// 			/* 如果订单用户不为空，计算积分，并退回 */
// 			if ($order['user_id'] > 0) {
// 				/* 取得用户信息 */
// 				$user = user_info($order['user_id']);
// 				$goods_num = RC_DB::table('order_goods')->select('goods_number', 'send_number')->where('order_id', $order['order_id'])->first();
		
// 				if ($goods_num['goods_number'] == $goods_num['send_number']) {
// 					/* 计算并退回积分 */
// 					$integral = integral_to_give($order);
// 					$options = array(
// 						'user_id'		=> $order['user_id'],
// 						'rank_points'	=> (-1) * intval($integral['rank_points']),
// 						'pay_points'	=> (-1) * intval($integral['custom_points']),
// 						'change_desc'	=> sprintf(RC_Lang::get('orders::order.return_order_gift_integral'), $order['order_sn'])
// 					);
// 					RC_Api::api('user', 'account_change_log', $options);
// 				}
// 				/* todo 计算并退回红包 */
// 				return_order_bonus($order_id);
// 			}
		
// 			/* 如果使用库存，则增加库存（不论何时减库存都需要） */
// 			if (ecjia::config('use_storage') == '1') {
// 				if (ecjia::config('stock_dec_time') == SDT_SHIP) {
// 					change_order_goods_storage($order['order_id'], false, SDT_SHIP);
// 				} elseif (ecjia::config('stock_dec_time') == SDT_PLACE) {
// 					change_order_goods_storage($order['order_id'], false, SDT_PLACE);
// 				}
// 			}
		
// 			/* 退货用户余额、积分、红包 */
// 			return_user_surplus_integral_bonus($order);
		
// 			/* 获取当前操作员 */
// 			$delivery['action_user'] = $_SESSION['admin_name'];
// 			/* 添加退货记录 */
// 			$delivery_list = array();
// 			$delivery_list = RC_DB::table('delivery_order')->where('order_id', $order['order_id'])->whereIn('status', array(0, 2))->get();
	
// 			if ($delivery_list) {
// 				foreach ($delivery_list as $list) {
// 					$data = array(
// 						'delivery_sn'	=> $list['delivery_sn'], 
// 						'order_sn'		=> $list['order_sn'],
// 						'order_id'		=> $list['order_id'],
// 						'add_time'		=> $list['add_time'],
// 						'shipping_id'	=> $list['shipping_id'],
// 						'user_id'		=> $list['user_id'],
// 						'action_user'	=> $delivery['action_user'],
// 						'consignee'		=> $list['consignee'],
// 						'address'		=> $list['address'],
// 						'country'		=> $list['country'],
// 						'province'		=> $list['province'],
// 						'city'			=> $list['city'],
// 						'district'		=> $list['district'],
// 						'sign_building'	=> $list['sign_building'],
// 						'email'			=> $list['email'],
// 						'zipcode'		=> $list['zipcode'],
// 						'tel'			=> $list['tel'],
// 						'mobile'		=> $list['mobile'],
// 						'best_time'		=> $list['best_time'],
// 						'postscript'	=> $list['postscript'],
// 						'how_oos'		=> $list['how_oos'],
// 						'insure_fee'	=> $list['insure_fee'],
// 						'shipping_fee'	=> $list['shipping_fee'],
// 						'update_time'	=> $list['update_time'],
// 						'suppliers_id'	=> $list['suppliers_id'],
// 						'return_time'	=> GMTIME_UTC,
// 						'agency_id'		=> $list['agency_id'],
// 					    'store_id'      => $list['store_id'],
// 						'invoice_no'	=> $list['invoice_no'],
// 					);					
// 					$back_id = RC_DB::table('back_order')->insertGetId($data);
		
// 					$query = RC_DB::table('delivery_goods')
// 						->selectRaw('goods_id, product_id, product_sn, goods_name, goods_sn, is_real, send_number, goods_attr')
// 						->where('delivery_id', $list['delivery_id'])
// 						->first();
					
// 					$source = array(
// 						'back_id'		=> $back_id,
// 						'goods_id'		=> $query['goods_id'],
// 						'product_id'	=> $query['product_id'],
// 						'product_sn'	=> $query['product_sn'],
// 						'goods_name'	=> $query['goods_name'],
// 						'goods_sn'		=> $query['goods_sn'],
// 						'is_real'		=> $query['is_real'],
// 						'send_number'	=> $query['send_number'],
// 						'goods_attr'	=> $query['goods_attr'],
// 					);
// 					RC_DB::table('back_goods')->insert($source);
// 				}
// 			}
	
// 			/* 修改订单的发货单状态为退货 */
// 			$data = array(
// 				'status' => 1,
// 			);
// 			RC_DB::table('delivery_order')->where('order_id', $order['order_id'])->whereIn('status', array(0, 2))->update($data);
	
// 			/* 将订单的商品发货数量更新为 0 */
// 			$data = array(
// 				'send_number' => 0,
// 			);
// 			RC_DB::table('order_goods')->where('order_id', $order_id)->update($data);
			
// 			//update commission_bill
// 			RC_Api::api('commission', 'add_bill_detail', array('store_id' => $order['store_id'], 'order_type' => '', 'order_id' => $order_id, 'order_amount' => $order['order_amount']));
				
// 		} 
		elseif ('after_service' == $operation) {
			/* 记录log */
			order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], '[' . RC_Lang::get('orders::order.op_after_service') . '] ' . $action_note);
		} else {
			return $this->showmessage('参数错误', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		/* 操作成功 */
		$links[] = array('text' => RC_Lang::get('orders::order.back_order_info'), 'href' => RC_Uri::url('orders/admin/info', 'order_id=' . $order_id));
		return $this->showmessage(RC_Lang::get('orders::order.act_ok'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('url' => RC_Uri::url('orders/admin/info', 'order_id=' . $order_id)));
	}
	
	/**
	 *  添加订单商品,获取商品信息
	 */
	public function goods_json() {
		$this->admin_priv('order_edit', ecjia::MSGTYPE_JSON);
		
		/* 取得商品信息 */
		$goods_id = $_POST['goods_id'];

		$goods = RC_DB::table('goods as g')
			->leftJoin('brand as b', RC_DB::raw('g.brand_id'), '=', RC_DB::raw('b.brand_id'))
			->leftJoin('category as c', RC_DB::raw('g.cat_id'), '=', RC_DB::raw('c.cat_id'))
			->selectRaw('goods_id, c.cat_name, goods_sn, goods_name, goods_img, b.brand_name,goods_number, market_price, shop_price, promote_price, promote_start_date, promote_end_date, goods_brief, goods_type, is_promote')
			->where('goods_id', $goods_id)
			->first();
			
		$today = RC_Time::gmtime();
		$goods['goods_price'] = ($goods['is_promote'] == 1 && $goods['promote_start_date'] <= $today && $goods['promote_end_date'] >= $today) ? $goods['promote_price'] : $goods['shop_price'];
		
		/* 取得会员价格 */
		$goods['user_price'] = RC_DB::table('member_price as mp')
			->leftJoin('user_rank as r', RC_DB::raw('mp.user_rank'), '=', RC_DB::raw('r.rank_id'))
			->selectRaw('mp.user_price, r.rank_name')
			->where(RC_DB::raw('mp.goods_id'), $goods_id)
			->get();
			

		/* 取得商品属性 */
		$data = RC_DB::table('goods_attr as ga')
			->leftJoin('attribute as a', RC_DB::raw('ga.attr_id'), '=', RC_DB::raw('a.attr_id'))
			->where(RC_DB::raw('ga.goods_id'), $goods_id)
			->selectRaw('a.attr_id, a.attr_name, ga.goods_attr_id, ga.attr_value, ga.attr_price, a.attr_input_type, a.attr_type')
			->get();

		$goods['attr_list'] = array();
		if (!empty($data)) {
			foreach ($data as $key => $row) {
				$goods['attr_list'][$row['attr_id']][] = $row;
			}
		}
		
		$goods['goods_name'] 		= $goods['goods_name'];
		$goods['short_goods_name'] 	= RC_String::str_cut($goods['goods_name'],26);
		$goods['attr_list'] 		= array_values($goods['attr_list']);
		$goods['goods_img'] 		= get_image_path($goods['goods_id'], $goods['goods_img']);
		$goods['preview_url'] 		= RC_Uri::url('goods/admin/preview', 'id='.$goods_id);
		
		return $this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('goods' => $goods));
	}
	
	/**
	 * 删除订单
	 */
	public function remove_order() {
		/* 检查权限 */
		$this->admin_priv('order_edit', ecjia::MSGTYPE_JSON);
		$order_id = intval($_REQUEST['order_id']);
		
		/* 检查订单是否允许删除操作 */
		$order = order_info($order_id);
		$operable_list = operable_list($order);
		
		if (!isset($operable_list['remove'])) {
			return $this->showmessage(RC_Lang::get('orders::order.unable_operation_order'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		$data = array(
			'is_delete' 	=> 1,
			'delete_time' 	=> RC_Time::local_date(ecjia::config('time_format'), RC_Time::gmtime())
		);
		RC_DB::table('order_info')->where('order_id', $order_id)->update($data);
		
		$action_array = array('delivery', 'back');
		del_delivery($order_id, $action_array);
		
		$url = RC_Uri::url('orders/admin/init');
		
		/* 记录日志 */
		ecjia_admin::admin_log(RC_Lang::get('orders::order.order_is').$order['order_sn'], 'remove', 'order');
		return $this->showmessage(RC_Lang::get('orders::order.order_removed'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('url' => $url));
	}
	
	/**
	 * 根据关键字和id搜索用户
	 */
	public function search_users() {
		/* 检查权限 */
		$this->admin_priv('order_edit', ecjia::MSGTYPE_JSON);
		$id_name = empty($_POST['id_name']) ? '' : trim($_POST['id_name']);

		$result = array();
		if (!empty($id_name)) {
			$data = RC_DB::table('users')
				->select('user_id', 'user_name', 'email')
				->where('email', 'like', '%'.mysql_like_quote($id_name).'%')
				->orWhere('user_name', 'like', '%'.mysql_like_quote($id_name).'%')
				->limit(50)
				->get();
							
			if (!empty($data)) {
				foreach ($data as $key => $row) {
					array_push($result, array('value' => $row['user_id'], 'text' => $row['user_name']." [ ".RC_Lang::get('orders::order.member_email_lable').$row['email']." ]")); 
				} 
			}
		} 
		return $this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('user' => $result));
	}
	
	/**
	 * 根据关键字搜索商品
	 */
	public function search_goods() {
		/* 检查权限 */
		$this->admin_priv('order_edit', ecjia::MSGTYPE_JSON);
		$keyword = empty($_POST['keyword']) ? '' : trim($_POST['keyword']);
		$order_id = !empty($_GET['order_id']) ? trim($_GET['order_id']) : '';
		$store_id = RC_DB::table('order_info')->where('order_id', $order_id)->pluck('store_id');

		$result = array();
		if (!empty($keyword)) {
		    $data = RC_DB::table('goods')
		              ->select('goods_id', 'goods_name', 'goods_sn')
		              ->where('is_delete', 0)->where('is_on_sale', 1)->where('is_alone_sale', 1)
		              ->where('store_id', $store_id)
		              ->whereRaw('(goods_id like "%'.mysql_like_quote($keyword).'%" or goods_name like "%'.mysql_like_quote($keyword).'%" or goods_sn like "%'.mysql_like_quote($keyword).'%")')
		              ->limit(50)
		              ->get();

			if (!empty($data)) {
				foreach ($data as $key => $row) {
					array_push($result, array('value' => $row['goods_id'], 'text' => $row['goods_name'] . '  ' . $row['goods_sn']));
				}
			} 
		} 
		return $this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('goods' => $result));
	}
	
	/**
	 * 编辑收货单号
	 */
	public function edit_invoice_no() {
		/* 检查权限 */
		$this->admin_priv('order_edit', ecjia::MSGTYPE_JSON);
			
		$no = empty($_POST['val']) ? 'N/A' : trim($_POST['val']);
		$no = $no == 'N/A' ? '' : $no;
		$order_id = empty($_POST['id']) ? 0 : intval($_POST['id']);
		
		if ($order_id == 0) {
			return $this->showmessage(RC_Lang::get('orders::order.not_exist_order'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		$data = array(
			'invoice_no' => $no
		);
		$query = RC_DB::table('order_info')->where('order_id', $order_id)->update($data);

		if ($query) {
			if (empty($no)) {
				return $this->showmessage(RC_Lang::get('orders::order.not_exist_order'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			} else {
				return $this->showmessage(stripcslashes($no), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => stripcslashes($no)));
			}
		} else {
			return $this->showmessage(RC_Lang::get('orders::order.update_error'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
	
	/**
	 * 编辑付款备注
	 */
	public function edit_pay_note() {
		/* 检查权限 */
		$this->admin_priv('order_edit', ecjia::MSGTYPE_JSON);
		
		$no = empty($_POST['val']) ? 'N/A' : trim($_POST['val']);
		$no = $no == 'N/A' ? '' : $no;
		$order_id = empty($_POST['id']) ? 0 : intval($_POST['id']);
		
		if ($order_id == 0) {
			return $this->showmessage(RC_Lang::get('orders::order.not_exist_order'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		$data = array(
			'pay_note' => $no
		);
		$query = RC_DB::table('order_info')->where('order_id', $order_id)->update($data);

		if ($query) {
			if (empty($no)) {
				return $this->showmessage(RC_Lang::get('orders::order.not_exist_order'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			} else {
				return $this->showmessage(stripcslashes($no), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
			}
		} else {
			return $this->showmessage(RC_Lang::get('orders::order.update_error'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
	
	/**
	 * 添加订单时选择用户展现用户信息的信息（json返回）
	 */
	public function user_info() {
		/* 检查权限 */
		$this->admin_priv('order_edit', ecjia::MSGTYPE_JSON);
		$id = $_POST['user_id'];
		$row = RC_Api::api('user', 'user_info', array('user_id' => $id));
		
		if ($row['user_rank'] > 0) {
			$user['user_rank'] = RC_DB::table('user_rank')->where('rank_id', $row['user_rank'])->pluck('rank_name');
		} else {
			$user['user_rank'] = RC_Lang::get('orders::order.no_special_grade');
		}
		
		if ($row) {
			$user['user_id']		= $row['user_id'];
			$user['user_name']		= "<a href='".RC_Uri::url('user/admin/info','id='.$row['user_id'])."' target='_blank'>".$row['user_name']."</a>";
			$user['email']			= $row['email'];
			$user['reg_time']		= RC_Time::local_date(ecjia::config('time_format'), $row['reg_time']);
			$user['mobile_phone']	= !empty($row['mobile_phone']) ? $row['mobile_phone'] : RC_Lang::get('orders::order.no_phone_number');
			$user['is_validated']	= $row['is_validated'] == 0 ? RC_Lang::get('orders::order.unverified') : RC_Lang::get('orders::order.verified');
			$user['last_login']		= RC_Time::local_date(ecjia::config('time_format'), $row['last_login']);
			$user['last_ip']		= $row['last_ip'].'('.RC_Ip::area($row['last_ip']).')';
			
			return $this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('user' => $user));
		} else {
			return $this->showmessage(RC_Lang::get('orders::order.not_member_inf_found'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
	
	private function get_search_url() {
		$arr = array();
		if (isset($_GET['order_sn'])) {
			$arr['order_sn'] = trim($_GET['order_sn']);
		}
		if (isset($_GET['start_time'])) {
			$arr['start_time'] = trim($_GET['start_time']);
		}
		if (isset($_GET['end_time'])) {
			$arr['end_time'] = trim($_GET['end_time']);
		}
		if (isset($_GET['email'])) {
			$arr['email'] = trim($_GET['email']);
		}
		if (isset($_GET['user_name'])) {
			$arr['user_name'] = trim($_GET['user_name']);
		}
		if (isset($_GET['consignee'])) {
			$arr['consignee'] = trim($_GET['consignee']);
		}
		if (isset($_GET['tel'])) {
			$arr['tel'] = trim($_GET['tel']);
		}
		if (isset($_GET['mobile'])) {
			$arr['mobile'] = trim($_GET['mobile']);
		}
		if (isset($_GET['merchants_name'])) {
			$arr['merchants_name'] = trim($_GET['merchants_name']);
		}
		if (isset($_GET['address'])) {
			$arr['address'] = trim($_GET['address']);
		}
		
		if (isset($_GET['zipcode'])) {
			$arr['zipcode'] = trim($_GET['zipcode']);
		}
		if (isset($_GET['order_status'])) {
			$arr['order_status'] = trim($_GET['order_status']);
		}
		if (isset($_GET['pay_status'])) {
			$arr['pay_status'] = intval($_GET['pay_status']);
		}
		if (isset($_GET['shipping_status'])) {
			$arr['shipping_status'] = intval($_GET['shipping_status']);
		}
		if (isset($_GET['shipping_id'])) {
			$arr['shipping_id'] = intval($_GET['shipping_id']);
		}
		if (isset($_GET['pay_id'])) {
			$arr['pay_id'] = intval($_GET['pay_id']);
		}
		if (isset($_GET['composite_status'])) {
			$arr['composite_status'] = intval($_GET['composite_status']);
		}
		if (isset($_GET['keywords'])) {
			$arr['keywords'] = trim($_GET['keywords']);
		}
		if (isset($_GET['merchant_keywords'])) {
			$arr['merchant_keywords'] = trim($_GET['merchant_keywords']);
		}
		
		return RC_Uri::url('orders/admin/init', $arr);
	}
}

// end