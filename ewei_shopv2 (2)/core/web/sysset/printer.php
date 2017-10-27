<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
class Printer_EweiShopV2Page extends WebPage 
{
	public function main() 
	{
		global $_W;
		global $_GPC;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = 'uniacid=:uniacid and merchid=0';
		$params = array(':uniacid' => $_W['uniacid']);
		if (!(empty($_GPC['keyword']))) 
		{
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and title  like :keyword';
			$params[':keyword'] = '%' . $_GPC['keyword'] . '%';
		}
		$list = pdo_fetchall('SELECT * FROM ' . tablename('ewei_shop_member_printer_template') . ' WHERE ' . $condition . '  ORDER BY id asc limit ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
		$total = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('ewei_shop_member_printer_template') . ' WHERE ' . $condition, $params);
		$pager = pagination($total, $pindex, $psize);
		include $this->template();
	}
	public function add() 
	{
		$this->post();
	}
	public function edit() 
	{
		$this->post();
	}
	protected function post() 
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);
		if (!(empty($id))) 
		{
			$list = pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_member_printer_template') . ' WHERE id=:id and uniacid=:uniacid  and merchid=0', array(':id' => $id, ':uniacid' => $_W['uniacid']));
			$print_data = json_decode($list['print_data'], true);
			$keys = ((isset($print_data['key']) ? $print_data['key'] : array()));
			$values = ((isset($print_data['value']) ? $print_data['value'] : array()));
		}
		$kw = 0;
		if (isset($print_data['key'])) 
		{
			$kw = count($print_data['key']);
		}
		if ($_W['ispost']) 
		{
			$data = array('uniacid' => $_W['uniacid'], 'merchid' => 0, 'type' => intval($_GPC['type']), 'title' => trim($_GPC['title']), 'print_title' => trim($_GPC['print_title']), 'print_style' => trim($_GPC['print_style']), 'code' => intval($_GPC['code']), 'qrcode' => trim($_GPC['qrcode']));
			$data['print_data'] = json_encode(array('key' => (is_array($_GPC['key']) ? array_values($_GPC['key']) : array()), 'value' => (is_array($_GPC['value']) ? array_values($_GPC['value']) : array())));
			if (empty($id)) 
			{
				$data['createtime'] = time();
				pdo_insert('ewei_shop_member_printer_template', $data);
				$id = pdo_insertid();
				plog('sysset.printer.add', '添加打印机模板 ID: ' . $id . ' 标题: ' . $data['title'] . ' ');
			}
			else 
			{
				pdo_update('ewei_shop_member_printer_template', $data, array('id' => $id));
				plog('sysset.printer.edit', '编辑打印机模板 ID: ' . $id . ' 标题: ' . $data['title'] . ' ');
			}
			show_json(1, array('url' => webUrl('sysset/printer')));
		}
		$style_list = com_run('printer::style_list');
		$style_list = ((is_array($style_list) ? $style_list : array()));
		include $this->template();
	}
	public function delete() 
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);
		if (empty($id)) 
		{
			$id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
		}
		$items = pdo_fetchall('SELECT id,title FROM ' . tablename('ewei_shop_member_printer_template') . ' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid'] . ' AND  and merchid=0');
		foreach ($items as $item ) 
		{
			pdo_delete('ewei_shop_member_printer_template', array('id' => $id));
			plog('sysset.printer.delete', '删除群发模板 ID: ' . $item['id'] . ' 标题: ' . $item['title'] . ' ');
		}
		show_json(1, array('url' => referer()));
	}
	public function query() 
	{
		global $_W;
		global $_GPC;
		$kwd = trim($_GPC['keyword']);
		$params = array();
		$params[':uniacid'] = $_W['uniacid'];
		$condition = 'uniacid=:uniacid and merchid=0';
		if (!(empty($kwd))) 
		{
			$condition .= ' AND `title` LIKE :keyword';
			$params[':keyword'] = '%' . $kwd . '%';
		}
		$ds = pdo_fetchall('SELECT id,title FROM ' . tablename('ewei_shop_member_printer_template') . ' WHERE ' . $condition . ' order by id asc', $params);
		if ($_GPC['suggest']) 
		{
			exit(json_encode(array('value' => $ds)));
		}
		include $this->template();
	}
	public function tpl() 
	{
		global $_W;
		global $_GPC;
		$kw = intval($_GPC['kw']);
		$style_list = com_run('printer::style_list');
		$style_list = ((is_array($style_list) ? $style_list : array()));
		include $this->template();
	}
	public function printer_list() 
	{
		global $_W;
		global $_GPC;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = 'uniacid=:uniacid and merchid=0';
		$params = array(':uniacid' => $_W['uniacid']);
		if (!(empty($_GPC['keyword']))) 
		{
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and title  like :keyword';
			$params[':keyword'] = '%' . $_GPC['keyword'] . '%';
		}
		$list = pdo_fetchall('SELECT * FROM ' . tablename('ewei_shop_member_printer') . ' WHERE ' . $condition . '  ORDER BY id asc limit ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
		$total = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('ewei_shop_member_printer') . ' WHERE ' . $condition, $params);
		$pager = pagination($total, $pindex, $psize);
		$printer = com_run('printer::printer_list');
		include $this->template();
	}
	public function printer_add() 
	{
		$this->printer_post();
	}
	public function printer_edit() 
	{
		$this->printer_post();
	}
	protected function printer_post() 
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);
		if (!(empty($id))) 
		{
			$list = pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_member_printer') . ' WHERE id=:id and uniacid=:uniacid and merchid=0', array(':id' => $id, ':uniacid' => $_W['uniacid']));
			$print_data = json_decode($list['print_data'], true);
			$printer_365 = ((isset($print_data['printer_365']) ? $print_data['printer_365'] : array()));
			$printer_feie = ((isset($print_data['printer_feie']) ? $print_data['printer_feie'] : array()));
			$printer_yilianyun = ((isset($print_data['printer_yilianyun']) ? $print_data['printer_yilianyun'] : array()));
			$printer_yilianyun_new = ((isset($print_data['printer_yilianyun_new']) ? $print_data['printer_yilianyun_new'] : array()));
			$printer_365_s1 = ((isset($print_data['printer_365_s1']) ? $print_data['printer_365_s1'] : array()));
		}
		if ($_W['ispost']) 
		{
			$data = array('uniacid' => $_W['uniacid'], 'merchid' => 0, 'type' => intval($_GPC['type']), 'title' => trim($_GPC['title']));
			$data['print_data'] = json_encode(array('printer_365' => (is_array($_GPC['printer_365']) ? $_GPC['printer_365'] : array()), 'printer_feie' => (is_array($_GPC['printer_feie']) ? $_GPC['printer_feie'] : array()), 'printer_yilianyun' => (is_array($_GPC['printer_yilianyun']) ? $_GPC['printer_yilianyun'] : array()), 'printer_yilianyun_new' => (is_array($_GPC['printer_yilianyun_new']) ? $_GPC['printer_yilianyun_new'] : array()), 'printer_365_s1' => (is_array($_GPC['printer_365_s1']) ? $_GPC['printer_365_s1'] : array())));
			if (empty($id)) 
			{
				$data['createtime'] = time();
				pdo_insert('ewei_shop_member_printer', $data);
				$id = pdo_insertid();
				plog('sysset.printer.printer_add', '添加打印机 ID: ' . $id . ' 标题: ' . $data['title'] . ' ');
			}
			else 
			{
				pdo_update('ewei_shop_member_printer', $data, array('id' => $id));
				plog('sysset.printer.printer_edit', '编辑打印机 ID: ' . $id . ' 标题: ' . $data['title'] . ' ');
			}
			show_json(1, array('url' => webUrl('sysset/printer/printer_list')));
		}
		$printer = com_run('printer::printer_list');
		$printer = ((is_array($printer) ? $printer : array()));
		include $this->template('sysset/printer/printer_post');
	}
	public function printer_delete() 
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);
		if (empty($id)) 
		{
			$id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
		}
		$items = pdo_fetchall('SELECT id,title FROM ' . tablename('ewei_shop_member_printer') . ' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid'] . '  and merchid=0');
		foreach ($items as $item ) 
		{
			pdo_delete('ewei_shop_member_printer', array('id' => $id));
			plog('sysset.printer.printer_delete', '删除打印机 ID: ' . $item['id'] . ' 标题: ' . $item['title'] . ' ');
		}
		show_json(1, array('url' => referer()));
	}
	public function printer_query() 
	{
		global $_W;
		global $_GPC;
		$kwd = trim($_GPC['keyword']);
		$params = array();
		$params[':uniacid'] = $_W['uniacid'];
		$condition = 'uniacid=:uniacid and merchid=0';
		if (!(empty($kwd))) 
		{
			$condition .= ' AND `title` LIKE :keyword';
			$params[':keyword'] = '%' . $kwd . '%';
		}
		$ds = pdo_fetchall('SELECT id,title FROM ' . tablename('ewei_shop_member_printer') . ' WHERE ' . $condition . ' order by id asc', $params);
		if ($_GPC['suggest']) 
		{
			exit(json_encode(array('value' => $ds)));
		}
		include $this->template();
	}
	public function printer_tpl() 
	{
		global $_W;
		global $_GPC;
		$kw = intval($_GPC['kw']);
		include $this->template();
	}
	public function set() 
	{
		global $_W;
		global $_GPC;
		if ($_W['ispost']) 
		{
			$data = array();
			$data['order_printer'] = ((is_array($_GPC['order_printer']) ? implode(',', $_GPC['order_printer']) : ''));
			$data['order_template'] = intval($_GPC['order_template']);
			$data['printer_merch'] = intval($_GPC['printer_merch']);
			$data['ordertype'] = ((is_array($_GPC['ordertype']) ? implode(',', $_GPC['ordertype']) : ''));
			m('common')->updateSysset(array('printer' => $data));
			plog('sysset.printer.set', '修改系统设置-商城打印机设置');
			show_json(1);
		}
		$data = com_run('printer::getPrinterSet');
		$order_printer_array = $data['order_printer'];
		$ordertype = $data['ordertype'];
		$order_template = pdo_fetchall('SELECT * FROM ' . tablename('ewei_shop_member_printer_template') . ' WHERE uniacid=:uniacid and merchid=0', array(':uniacid' => $_W['uniacid']));
		include $this->template();
	}
}
?>