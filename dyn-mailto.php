<?php
/**
 * Plugin that enables allows templating mailto links on page load.
 * php version 7.2.24

 * Plugin Name: Dynamic Mailto
 * Plugin URI: http://www.wpexplorer.com/create-widget-plugin-wordpress/
 * Description: Enables templating mailto links on page load.
 * Version: 1.0
 * @author: Jenna Murrell
 * @license: MIT
 */

// The widget class

require __DIR__ . '/vendor/autoload.php';

class Dyn_Mailto_Widget extends WP_Widget
{
	private $_template_fields = array();
	private $_plugin_dir_path;
	/**
	 * Main constructor 
	 */
	public function __construct() 
	{
		parent::__construct(
			'dyn_mailto_widget',
			__('Dynamic Mailto Link', 'text_domain'),
			array(
			'customize_selective_refresh' => true,
			)
		);

		$this->_plugin_dir_path = dirname(__FILE__);
		$_template_fields = include "$this->_plugin_dir_path/admin/get_fields.php";
	}

	// The widget form (for the backend )
	public function form( $instance ) 
	{

		// Set widget defaults
		$mailto = array(
		'to' => '',
		'cc' => '',
		'bcc' => '',
		'subject' => '',
		'body' => '',
		);
		
		// Parse current settings with defaults
		extract(wp_parse_args(( array ) $instance, $mailto)); 
		wp_enqueue_script('jquery-ui-autocomplete');
		wp_enqueue_script('jquery-ui-widget');
		wp_enqueue_script('jquery-ui-menu');
		wp_enqueue_script('jquery-ui-position');


		$this->_plugin_dir_path = dirname(__FILE__);

		$_template_fields = include "$this->_plugin_dir_path/admin/get_fields.php";

		wp_register_script('form-textcomplete', "https://mabelleneighbours.com/wp-content/plugins/dyn-mailto/public/form_textcomplete.js", array(), null, false);

		wp_enqueue_script('form-textcomplete');
		wp_localize_script('form-textcomplete', 'textcomplete_ajax_params', array_keys($_template_fields));
		$this->render_widget_form($instance);

		?>




	<?php }

	public function update( $new_instance, $old_instance ) 
	{
		$instance = $old_instance;
		$instance['to']	   = isset($new_instance['to']) ? wp_strip_all_tags($new_instance['to']) : '';  
		$instance['subject']  = isset($new_instance['subject']) ? wp_strip_all_tags($new_instance['subject']) : '';  
		$instance['body']	   = isset($new_instance['body']) ? wp_strip_all_tags($new_instance['body']) : '';  
		return $new_instance;
	}

	public function widget( $args, $instance ) 
	{
		$loader = new \Twig\Loader\ArrayLoader([ ]);

		$twig = new \Twig\Environment($loader, ['strict_variables' => false]);

		$twig->addExtension(new \Twig\Extension\StringLoaderExtension());
		$this->_plugin_dir_path = dirname(__FILE__);

		$sandbox_options = include "$this->_plugin_dir_path/admin/get_sandbox_options.php";
		$twig->addExtension(new \Twig\Extension\SandboxExtension($sandbox_options));

		$template = array(
		'to' => $twig->createTemplate($instance['to']),
		'subject' => $twig->createTemplate($instance['subject']),
		'body' => $twig->createTemplate($instance['body'])
		);


		extract($args);

		// Run templating
		$widget_fields = array(
		'display' => 't',
		'to' => $template['to']->render($_template_fields),
		'subject' => $template['subject']->render($_template_fields),
		'body' => $template['body']->render($_template_fields),
		);

		echo $before_widget;
		$this->render_widget($widget_fields);
		echo $after_widget;

	}

	private function initialize_fields() 
	{
		$fields = array();
		$fields = array_merge($fields, wp_get_current_user()["data"]);
	}

	// Render widget with Twig template. Used by widget().
	private function render_widget( $fields ) 
	{
		$loader = new \Twig\Loader\FilesystemLoader("$this->_plugin_dir_path/templates");
		$twig = new \Twig\Environment($loader, ['strict_variables' => false]);
		$template = $twig->load('widget.html');
	$this->var_error_log($fields);

		echo $template->render($fields);
	}

	// Render form with Twig template. Used by form().
	private function render_widget_form($instance) 
	{

		$loader = new \Twig\Loader\FilesystemLoader("$this->_plugin_dir_path/templates");
		$twig = new \Twig\Environment($loader, ['strict_variables' => false]);
		$template = $twig->load('widget_form.html');

		$_template_fields = array(
			'field_id' => array(
			'to' => esc_attr($this->get_field_id('to')),
			'subject' => esc_attr($this->get_field_id('subject')),
			'body' => esc_attr($this->get_field_id('body')),
			),
			'field_name' => array(
			'to' => esc_attr($this->get_field_name('to')),
			'subject' => esc_attr($this->get_field_name('subject')),
			'body' => esc_attr($this->get_field_name('body')),
			),
			'field_value' => array(
			'to' => esc_attr($instance['to']),
			'subject' => esc_attr($instance['subject']),
			'body' => esc_attr($instance['body']),
			),
		);

		echo $template->render($_template_fields);
	}

	private function var_error_log( $object=null )
	{
		ob_start();					// start buffer capture
		var_dump($object);		   // dump the values
		$contents = ob_get_contents(); // put the buffer into a variable
		ob_end_clean();				// end capture
		error_log($contents);		// log contents of the result of var_dump( $object )
	}

}

// Register the widget
function my_register_custom_widget() 
{
	register_widget('Dyn_Mailto_Widget');
}
add_action('widgets_init', 'my_register_custom_widget');
