<?php

class PubSubHubbub extends Plugin
{
    public function info()
    {
        return array(
            'url' => 'http://andrewhutchings.com/projects',
            'name' => 'PubSubHubbub',
            'description' => 'Extends Habari\'s Atom feed with the PubSubHubbub protocol.',
            'license' => 'Apache License 2.0',
            'author' => 'Andrew Hutchings',
            'authorurl' => 'http://andrewhutchings.com',
            'version' => '0.0.1'
        );
    }

    public function filter_plugin_config($actions, $plugin_id)
    {
        if ($plugin_id == $this->plugin_id()) {
            $actions[] = _t('Configure');
            }

        return $actions;
    }

    public function action_plugin_ui($plugin_id, $action)
    {
        if ( $plugin_id == $this->plugin_id() ) {
            switch ($action) {
                case _t('Configure'):
                    $form = new FormUI(strtolower(get_class($this)));
                    $form->append('textmulti', 'endpoints', 'pubsubhubbub__endpoints', _t('Custom hubs'));
                    $form->append('submit', 'save', 'Save');
                    $form->out();
                    break;
            }
        }
    }


    /**
     * Setup default options on activation.
     *
     * @param string $file the plugin file being activated
     */
    public function action_plugin_activation($file)
    {
        if ($file != str_replace('\\','/', $this->get_file())) {
        	return;
        }

    	foreach (self::default_options() as $name => $value) {
			if (Options::get("pubsubhubbub__$name") == null) {
				Options::set("pubsubhubbub__$name", $value);
			}
		}
    }

   	public function alias()
   	{
		return array(
			'post_to_endpoints' => array(
				'action_post_insert_after',
				'action_post_publish_after',
				'action_post_update_after'
			)
		);
   	}

    public function post_to_endpoints(Post $post)
    {
		$feeds = array(URL::get('atom_feed'));

		foreach (Options::get('pubsubhubbub__endpoints') as $endpoint) {
	        $p = new Publisher($endpoint);
        	$p->publish_update($feeds);
		}
    }

    /**
     * Retrieves the default options for the plugin.
     *
     * @return array
     */
    private static function default_options()
    {
        return array(
        	'endpoints' => array("http://pubsubhubbub.appspot.com")
        );
    }

    public function action_atom_create_wrapper($xml)
    {
    	foreach (Options::get('pubsubhubbub__endpoints') as $endpoint) {
			$link = $xml->addChild('link');
			$link->addAttribute('rel', 'hub');
			$link->addAttribute('href', $endpoint);
	    }
    }
}

?>
