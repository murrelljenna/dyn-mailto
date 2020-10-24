<?php

class Dyn_Mailto_Widget extends WP_Widget
{
	private $_template_fields = array();
	private $_autocomplete_fields = array();
	private $_plugin_dir_path;

	/* Twig */

	private $_twig_loader;
	private $_twig;

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

		/* Load twig */

		$this->_twig_loader = new \Twig\Loader\FilesystemLoader("$this->_plugin_dir_path/templates");
		$this->_twig = new \Twig\Environment($this->_twig_loader);

		require_once "$this->_plugin_dir_path/admin/field_loader.php";

		$this->_template_fields = Dyn_Mailto_Field_Loader::get_template_fields();
		$this->_autocomplete_fields = Dyn_Mailto_Field_Loader::get_autocomplete_fields();
	}

	public function form( $instance ) 
	{
		wp_enqueue_script('jquery-ui-autocomplete');
		wp_enqueue_script('jquery-ui-widget');
		wp_enqueue_script('jquery-ui-menu');
		wp_enqueue_script('jquery-ui-position');


		// Autogrow textarea fields.
		wp_register_script('dyn-mailto-form-autogrow-dist', "https://mabelleneighbours.com/wp-content/plugins/dyn-mailto/includes/autosize.min.js", array(), null, false);
		wp_register_script('dyn-mailto-form-textcomplete', "https://mabelleneighbours.com/wp-content/plugins/dyn-mailto/js/form_textcomplete.js", array(), null, false);
		wp_register_script('dyn-mailto-form-autogrow', "https://mabelleneighbours.com/wp-content/plugins/dyn-mailto/js/form_autogrow.js", array(), null, false);

		wp_enqueue_script('dyn-mailto-form-autogrow-dist');
		wp_enqueue_script('dyn-mailto-form-textcomplete');
		wp_enqueue_script('dyn-mailto-form-autogrow');
		wp_localize_script('dyn-mailto-form-textcomplete', 'textcomplete_ajax_params', $this->_autocomplete_fields);
		$this->render_widget_form($instance);
	}

	public function update( $new_instance, $old_instance ) 
	{
		$instance = $old_instance;

		$instance['to']	= isset($new_instance['to']) ? wp_strip_all_tags($new_instance['to']) : '';
		$instance['subject'] = isset($new_instance['subject']) ? wp_strip_all_tags($new_instance['subject']) : '';
		$instance['body'] = isset($new_instance['body']) ? wp_strip_all_tags($new_instance['body']) : ''; 
		return $new_instance;
	}

	public function widget( $args, $instance ) 
	{

		$this->_twig->addExtension(new \Twig\Extension\StringLoaderExtension());
		$sandbox_options = require_once "$this->_plugin_dir_path/admin/get_sandbox_options.php";
		$this->_twig->addExtension(new \Twig\Extension\SandboxExtension($sandbox_options));

		$template = array(
		'to' => $this->_twig->createTemplate($instance['to']),
		'subject' => $this->_twig->createTemplate($instance['subject']),
		'body' => $this->_twig->createTemplate($instance['body'])
		);

		// Run templating
		$widget_fields = array(
		'display' => 't',
		'to' => $template['to']->render($this->_template_fields),
		'subject' => $template['subject']->render($this->_template_fields),
		'body' => $template['body']->render($this->_template_fields),
		);

		echo $args['before_widget'];
		$this->render_widget($widget_fields);
		echo $args['after_widget'];

	}

	// Render widget with Twig template. Used by widget().
	private function render_widget( $fields ) 
	{
		$template = $this->_twig->load('widget.html');
		echo $template->render($fields);
	}

	// Render form with Twig template. Used by form().
	private function render_widget_form($instance) 
	{
		$template = $this->_twig->load('widget_form.html');

		$fields = array(
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
			'to' => esc_attr(isset($instance['to']) ? $instance['to'] : ''),
			'subject' => esc_attr(isset($instance['subject']) ? $instance['subject'] : ''),
			'body' => esc_attr(isset($instance['body']) ? $instance['body'] : ''),
			),
		);

		echo $template->render($fields);
	}

	private function var_error_log( $object=null )
	{
		ob_start();
		var_dump($object);
		$contents = ob_get_contents();
		ob_end_clean();
		error_log($contents);
	}

}
