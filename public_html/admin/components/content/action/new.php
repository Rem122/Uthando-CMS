<?php

// no direct access
defined( 'PARENT_FILE' ) or die( 'Restricted access' );

if ($this->authorize()):

	$sql = "
		SELECT page_id, page, mdate
		FROM ".$this->registry->core."pages
	";
	$result = $this->registry->db->query($sql);
	
	$num_pages = count($result);
	
	if ($num_pages <= $this->registry->settings['pages'] || $this->registry->settings['pages'] == -1):
		
		$form = new HTML_QuickForm('contentPage', 'post', $_SERVER['REQUEST_URI']);
			
		// Remove name attribute for xhtml strict compliance.
		$form->removeAttribute('name');
			
		$menuBar = array(
			'html' => '',
			'edit' => '',
			'params' => '',
			'cancel' => '/content/overview',
			'save' => ''
		);
			
		$this->content .= $this->makeToolbar($menuBar, 24);
		
		$menuBar = array();
	
		$form->addElement('html', '<div id="edit_params">');
		
		$form->addElement('text', 'page', 'Page Title:', array('size' => 50, 'maxlength' => 255, 'class' => 'inputbox'));
	
		$form->addElement('html', '<fieldset>');
		
		$form->addElement('header','parameters','Parameters');
	
		$params = array('show_title', 'show_cdate', 'show_mdate');
	
		foreach ($params as $value):
			$radio_set = array(
				$form->createElement('radio', null, null, 'Yes', '1'),
				$form->createElement('radio', null, null, 'No', '0')
			);
				
			$form->addGroup($radio_set, 'params['.$value.']', ucwords(str_replace('_', ' ', $value)).':');
		endforeach;
	
		$form->addElement('html', '</fieldset>');
	
		$form->addElement('html', '<fieldset>');
		$form->addElement('header','metadata','Metadata');
		
		$form->addElement('textarea', 'params[metadata][description]', 'Description:');
		$form->addElement('textarea', 'params[metadata][keywords]', 'Keywords:');
			
		$form->addElement('html', '</fieldset>');
	
		$form->addElement('html', '</div>');
	
		$form->addElement('html', '<div id="edit_html">');
	
		$form->addElement('textarea', 'content', null, array('id' => 'content_textarea', 'class' => 'mceEditor'));
		
		$form->addElement('html', '</div>');
		
		$form->addRule('page', 'Please enter a title', 'required');
			
			
		if ($form->validate()):
				
			// Apply form element filters.
			$form->freeze();
			$values = $form->process(array(&$this, 'formValues'));
			
			$values['params'] = serialize($values['params']);
			
			foreach ($values as $key => $value) $values[$key] = "'$value'";
			
			$values['cdate'] = "NOW()";
			
			$res = $this->insert($values, $this->registry->core.'pages', false);
			
			$menuBar['back'] = '/content/overview';
			
			if ($res):
				$params['TYPE'] = 'pass';
				$params['MESSAGE'] = '<h2>Page was successfully created.</h2>';
			else:
				$params['TYPE'] = 'error';
				$params['MESSAGE'] = '<h2>Page could not be created.</h2>';
			endif;
		else:
	
			$form->setDefaults(array(
				'params' => array(
					'show_title' => 1,
					'show_cdate' => 1,
					'show_mdate' => 1
				)
			));
			
			$renderer = new UthandoForm(__SITE_PATH . '/templates/' . $this->get ('admin_config.site.template'));
					
			$renderer->setFormTemplate('form');
			$renderer->setHeaderTemplate('header');
			$renderer->setElementTemplate('element');
			
			$form->accept($renderer);
					
			// output the form
			$this->content .= $renderer->toHtml();
	
			$this->loadJavaScript(array(
				'/Common/editor/tiny_mce/tiny_mce_gzip.js',
				'/components/content/js/tinyMCEGz.js',
				'/Common/editor/CodeMirror/js/codemirror.js'
			));
	
			$this->registry->component_js = array(
				'/components/content/js/content.js',
				'/components/content/js/editor.js',
				'/components/content/js/editorConfig.js'
			);
	
			$this->registry->component_css = array(
				'/templates/'.$this->get ('admin_config.site.template').'/css/FileManager.css',
				'/templates/'.$this->get ('admin_config.site.template').'/css/Additions.css'
			);
			
			$session = Utility::encodeString(session_id());
			$this->addScriptDeclaration("UthandoAdmin.sid = ['" . $session[0] . "','" . $session[1] . "'];");
		endif;
	else:
		$params['TYPE'] = 'info';
		$params['MESSAGE'] = '<h2>You have reach your page limit. To add more pages please contact your administrator.</h2>';
		$menuBar['back'] = '/content/overview';
	endif;
	
	if (isset($params)):
		$params['CONTENT'] = $this->makeMessageBar($menuBar, 24);
		$this->content .= $this->message($params);
	endif;
endif;
?>