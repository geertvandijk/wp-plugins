<?php
include_once(ABSPATH . 'wp-config.php');
include_once(ABSPATH . 'wp-includes/wp-db.php');
include_once(ABSPATH . 'wp-includes/pluggable.php');

/*
Plugin Name: Site-instellingen
Description: Door 182code ontwikkelde instellingenplugin voor uw website.
Author: Geert van Dijk
Version: 3.1.1
*/

// todo
// [o] validatie (icm 182code validator?) - +0.2
// 		[n] easypeasy!

class g182_Settings {
    private $options;
        
    private $prefix;
    private $title;
    private $option_group;

    private $settings_name;
    private $settings_page;
    
    private $settings = array();
    private $settings_callbacks = array();
    
    private $sections = array();

    public function __construct() {
    	// dit hier aanpassen:
    	$this->prefix = '7x7_';
    	$this->title = 'Site-instellingen';
    	$this->option_group = $this->prefix . 'option_group';

    	$this->settings_name = $this->prefix . 'settings';
	    $this->settings_page = $this->prefix . 'setting-admin';
	    // klaar met aanpassen

        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'page_init'));
    }

    function add_section($id, $title, $info) {
    	$this->sections[$id] = array('title' => $title, 'info' => $info);
    }

    function add_setting($sectionid, $name, $desc, $type) {
    	$this->settings[$sectionid][$name] = array('desc' => $desc, 'type' => $type);
    }
    
    public function add_settings_page()
    {
        add_options_page(
            'Extra instellingen', 
            $this->title, 
            'manage_options', 
            $this->settings_page, 
            array($this, 'create_admin_page')
    	);
    }

    public function create_admin_page()
    {
        $this->options = get_option($this->settings_name);
        ?>
        <div class="wrap">
            <h2><?php echo $this->title; ?></h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields($this->option_group);   
                do_settings_sections($this->settings_page);
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    public function page_init()
    {        
        register_setting(
            $this->option_group,
            $this->settings_name,
            array($this, 'sanitize')
        );

        add_settings_section(
            $this->section_id,
            $this->section_title,
            array($this, 'print_section_info'),
            $this->settings_page
        );

        foreach ($this->sections as $id => $section) {
			add_settings_section(
	            $id,
	            $title,
	            array($this, 'print_section_info'),
	            $this->settings_page
	        );

	        foreach ($this->settings[$id] as $name => $setting) {
	        	$this->settings_callbacks[] = $this->prefix . $id . '-' . $name;
	    		add_settings_field($name, $setting['desc'], array($this, $this->prefix . $id . '-' . $name), $this->settings_page, $id);
	    	}        	
        }
    }

    // internal use; faster
    private function get_val($name) {
    	$name = $this->prefix . $name;
    	$setting = isset($this->options[$name]) ? esc_attr($this->options[$name]) : '';
    	return $setting;
    }

    // external use; works in theme files etc.
    public function get_setting($name) {
    	$name = $this->prefix.$name;
    	$options = get_option($this->settings_name);
		$setting = $options[$name];
		return $setting;
    }

    function __call($func, $params) {  	
		if (in_array($func, $this->settings_callbacks)) {
			// validatie erin prikken?
			$func_no_prefix = str_replace($this->prefix, '', $func);
			$func_no_prefix = split('-', $func_no_prefix);
			$sectionid = $func_no_prefix[0];
			$settingname = $func_no_prefix[1];
			$setting = $this->settings[$sectionid][$settingname];
			
			$value = $this->get_val($settingname);
			switch($setting['type']) {
				case 'textbox':
					echo '<input type="text" id="' . $func . '" name="' . $this->settings_name . '[' . $func . ']' . '" value="' . $value . '" />';
					break;
				case 'textarea':
					echo '<textarea id="' . $func . '" name="' . $this->settings_name . '[' . $func . ']' . '">' . $value . '</textarea>';
					break;
				case 'checkbox':
					echo '<input type="checkbox" id="' . $func . '" name="' . $this->settings_name . '[' . $func . ']' . '" value="1" . ' . checked(1, $value, false) . '/>';
					break;
			}
		}
	}

    public function sanitize($input)
    {
        return $input;
    }

    public function print_section_info($args)
    {
        if (!!$this->sections[$args['id']]) {
        	echo '<h3>' . $this->sections[$args['id']]['title'] . '</h3>';
        	echo '<p>' . $this->sections[$args['id']]['info'] . '</p>';
        }
    }

}

add_action("init", "g182_Settings_Init", 1);
function g182_Settings_Init() { global $g182_Settings; $g182_Settings = new g182_Settings(); }
?>