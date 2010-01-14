<?php

// no direct access
defined( 'PARENT_FILE' ) or die( 'Restricted access' );

if ($this->authorize()) {
	
	$menuBar = array(
		'cancel' => '/ushop/postage/overview',
		'save' => null,
   		'seperator' => null,
   		'customers' => '/ushop/customers',
   		'products' => '/ushop/products',
   		'tax' => '/ushop/tax'
	);
		
	$this->content .= $this->makeToolbar($menuBar, 24);
	
	$menuBar = array();
	
	$ushop = new UShopAdmin($this->registry);
	
	$post_zones = $this->getResult('post_zone_id, zone', $ushop->db_name.'post_zones');
	$post_levels = $this->getResult('post_level_id, post_level', $ushop->db_name.'post_levels',null, array('ORDER BY' => 'post_level ASC'));
	
	if ($post_zones && $post_levels) {
			
		$form = new HTML_QuickForm('add_cost', 'post', $_SERVER['REQUEST_URI']);
			
		$pz_s = $form->createElement('select', 'post_zone_id', 'Zone:');
		$pz_opts[0] = 'Select One';
		
		$pl_s = $form->createElement('select', 'post_level_id', 'Post Level:');
		$pl_opts[0] = 'Select One';
		
		foreach ($post_zones as $value) {
			$pz_opts[$value->post_zone_id] = $value->zone;
		}
		
		foreach ($post_levels as $value) {
			$pl_opts[$value->post_level_id] = $value->post_level;
		}
			
		// Remove name attribute for xhtml strict compliance.
		$form->removeAttribute('name');
			
		$form->addElement('html', '<fieldset>');
		$form->addElement('header','new_cost','New Post Cost');
		
		$form->addElement('text', 'cost', 'Cost:', array('size' => 5, 'maxlength' => 5, 'class' => 'inputbox'));
		
		$radio[] = $form->createElement('radio', null, null, 'Yes', '1');
		$radio[] = $form->createElement('radio', null, null, 'No', '0');
		$form->addGroup($radio, 'vat_inc', 'Include Tax:');
		
		$pl_s->loadArray($pl_opts);
		$form->addElement($pl_s);
	
		$pz_s->loadArray($pz_opts);
		$form->addElement($pz_s);
			
		$form->addElement('html', '</fieldset>');
		
		$form->addRule('cost', 'Please enter a post cost', 'required');
		$form->addRule('cost', 'post costs have to be a number', 'numeric');
		$form->addGroupRule('vat_inc', 'Please choose whether to include tax or not.', 'required', null, 1);
		$form->addRule('post_zone_id', 'Please select a post zone', 'nonzero');
		$form->addRule('post_level_id', 'Please select a post level', 'nonzero');
			
		if ($form->validate()) {
			
			$form->freeze();
			$values = $form->process(array(&$this, 'formValues'), false);
			
			$menuBar['add_cost'] = '/ushop/postage/action-new_cost';
			$menuBar['back'] = '/ushop/postage/overview';
			
			//check then enter the record.
			if (!$this->getResult('post_cost_id', $ushop->db_name.'post_costs', null, array('where' => "cost='$c'"))) {
				
				$res = $this->insert($values, $ushop->db_name.'post_costs');
			
				if ($res) {
					$params['TYPE'] = 'pass';
					$params['MESSAGE'] = '<h2>Post cost was successfully entered.</h2>';
				} else {
					$params['TYPE'] = 'error';
					$params['MESSAGE'] = '<h2>Post cost could not be entered into the database.</h2>';
				}
			
			} else {
				$params['TYPE'] = 'warning';
				$params['MESSAGE'] = '<h2>This post cost already exits.</h2>';
			}
				
			// done!
			
			
		} else {
				
			$renderer = new UthandoForm(__SITE_PATH . '/templates/' . $this->registry->admin_config->get ('admin_template', 'SERVER'));
			
			$renderer->setFormTemplate('form');
			$renderer->setHeaderTemplate('header');
			$renderer->setElementTemplate('element');
		
			$form->accept($renderer);
		
			// output the form
			$this->content .= $renderer->toHtml();
				
		}
			
	} else {
		$params['TYPE'] = 'info';
		
		if(!$post_zones) {
			$params['MESSAGE'] = '<h2>First define some post zones.</h2>';
		} elseif (!$post_levels) {
			$params['MESSAGE'] = '<h2>First define some post levels.</h2>';
		}
		
	}
	
	if (isset($params)) {
		$params['CONTENT'] = $this->makeToolbar($menuBar, 24);
		$this->content .= $this->message($params);
	}
	
} else {
	header("Location:" . $registry->config->get('web_url', 'SERVER'));
	exit();
}
?>