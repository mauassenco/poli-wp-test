<?php
namespace WprAddons\Modules\ThemeBuilder\PostComments;

use WprAddons\Base\Module_Base;
use WprAddons\Classes\Utilities;

class Module extends Module_Base {

	public function __construct() {
		parent::__construct();

		// This is here for extensibility purposes - go to town and make things happen!
	}
	
	public function get_name() {
		return 'wpr-post-comments';
	}

	public function get_widgets() {
		return [
			'Wpr_Post_Comments', // This should match the widget/element class.
		];
	}
	
}