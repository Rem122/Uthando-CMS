<?php

// no direct access
defined( 'PARENT_FILE' ) or die( 'Restricted access' );

if ($this->authorize()) {
	
	$menuBar = array(
		'back' => '/admin/overview'
	);
		
	$this->content .= $this->makeToolbar($menuBar, 24);

	$js = file_get_contents(__SITE_PATH.'/components/media/js/filemanager.js');

	$params = array('SESSION_ID' => session_id());
		
	$this->addContent('<script>'.$this->templateParser($js, $params, '{', '}').'</script>');

	$this->registry->component_css = array(
		'/templates/'.$this->registry->template.'/css/FileManager.css',
		'/templates/'.$this->registry->template.'/css/Additions.css'
	);
	

} else {
	header("Location:" . $registry->config->get('web_url', 'SERVER'));
	exit();
}
?>