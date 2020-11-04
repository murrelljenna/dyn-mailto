<?php

namespace jmurrell\DynMailto;

defined( 'ABSPATH' ) || exit;

class Widget extends \WP_Widget
{
    private $_template_fields = array();
    private $_autocomplete_fields = array();
    private $_plugin_dir_path;

    /* Error state for form entry */

    private $_syntax_error = false;
    
    /* Twig */

    private $_twig_loader;
    private $_twig;

    public function __construct() 
    {
        parent::__construct(
            'widget',
            __('Dynamic Mailto Link', 'text_domain'),
            array(
            'customize_selective_refresh' => true,
            )
        );

        $this->_plugin_dir_path = dirname(__FILE__);

        /* Load twig */

        $this->_twig_loader = new \Twig\Loader\FilesystemLoader(
            "$this->_plugin_dir_path/templates"
        );
        $this->_twig = new \Twig\Environment($this->_twig_loader);

        include_once "$this->_plugin_dir_path/admin/field_loader.php";

        $this->_template_fields = Field_Loader::get_template_fields();
        $this->_autocomplete_fields = Field_Loader::get_autocomplete_fields();
    }

    public function form( $instance ) 
    {
        wp_enqueue_script('jquery-ui-autocomplete');
        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_script('jquery-ui-menu');
        wp_enqueue_script('jquery-ui-position');

        // Load css
        wp_enqueue_style('dyn-mailto-widget-form', plugins_url('dyn-mailto/css/widget_form.css'));

        // Enables autocompletion of template fields in widget textarea.
        wp_enqueue_script('dyn-mailto-form-textcomplete', plugins_url('dyn-mailto/js/form_textcomplete.js'), array(), null, false);

        // Enables continual expansion of widget textarea without scrollbars.
        wp_enqueue_script('dyn-mailto-form-autogrow-dist', plugins_url('dyn-mailto/includes/autosize.min.js'), array(), null, false);
        wp_enqueue_script('dyn-mailto-form-autogrow', plugins_url('dyn-mailto/js/form_autogrow.js'), array(), null, false);

        wp_localize_script('dyn-mailto-form-textcomplete', 'textcomplete_ajax_params', $this->_autocomplete_fields);
        $this->render_widget_form($instance);
    }

    public function update( $new_instance, $old_instance ) 
    {
        /* Check the syntax of entered values before saving. */

        try {
            isset($new_instance['display']) && $this->render_from_string(sanitize_textarea_field($new_instance['display']), $this->_template_fields);
            isset($new_instance['to']) && $this->render_from_string(sanitize_textarea_field($new_instance['to']), $this->_template_fields);
            isset($new_instance['cc']) && $this->render_from_string(sanitize_textarea_field($new_instance['cc']), $this->_template_fields);
            isset($new_instance['bcc']) && $this->render_from_string(sanitize_textarea_field($new_instance['bcc']), $this->_template_fields);
            isset($new_instance['subject']) && $this->render_from_string(sanitize_textarea_field($new_instance['subject']), $this->_template_fields);
            isset($new_instance['body']) && $this->render_from_string(sanitize_textarea_field($new_instance['body']), $this->_template_fields); 
        } catch (\Twig\Error\SyntaxError $e) {
            /* Leave error message */
            $this->_syntax_error = true;
            return false;
        }

        $instance = $old_instance;
        $instance['display'] = isset($new_instance['display']) ? sanitize_textarea_field($new_instance['display']) : '';
        $instance['to'] = isset($new_instance['to']) ? sanitize_textarea_field($new_instance['to']) : '';
        $instance['cc'] = isset($new_instance['cc']) ? sanitize_textarea_field($new_instance['cc']) : '';
        $instance['bcc'] = isset($new_instance['bcc']) ? sanitize_textarea_field($new_instance['bcc']) : '';
        $instance['subject'] = isset($new_instance['subject']) ? sanitize_textarea_field($new_instance['subject']) : '';
        $instance['body'] = isset($new_instance['body']) ? sanitize_textarea_field($new_instance['body']) : ''; 
        return $new_instance;
    }

    public function widget( $args, $instance ) 
    {
        $this->_twig->addExtension(new \Twig\Extension\StringLoaderExtension());
        $sandbox_options = include_once "$this->_plugin_dir_path/admin/get_sandbox_options.php";
        $this->_twig->addExtension(new \Twig\Extension\SandboxExtension($sandbox_options));

        $template = array(
        'display' => $this->_twig->createTemplate(esc_attr($instance['display'])),
        'to' => $this->_twig->createTemplate(esc_attr($instance['to'])),
        'cc' => $this->_twig->createTemplate(esc_attr($instance['cc'])),
        'bcc' => $this->_twig->createTemplate(esc_attr($instance['bcc'])),
        'subject' => $this->_twig->createTemplate(esc_attr($instance['subject'])),
        'body' => $this->_twig->createTemplate(esc_attr($instance['body']))
        );

        // Run templating
        $widget_fields = array(
        'display' => $template['display']->render($this->_template_fields),
        'to' => $template['to']->render($this->_template_fields),
        'cc' => $template['cc']->render($this->_template_fields),
        'bcc' => $template['bcc']->render($this->_template_fields),
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
        'display' => esc_attr($this->get_field_id('display')),
        'to' => esc_attr($this->get_field_id('to')),
        'cc' => esc_attr($this->get_field_id('cc')),
        'bcc' => esc_attr($this->get_field_id('bcc')),
        'subject' => esc_attr($this->get_field_id('subject')),
        'body' => esc_attr($this->get_field_id('body')),
        ),
        'field_name' => array(
        'display' => esc_attr($this->get_field_name('display')),
        'to' => esc_attr($this->get_field_name('to')),
        'cc' => esc_attr($this->get_field_name('cc')),
        'bcc' => esc_attr($this->get_field_name('bcc')),
        'subject' => esc_attr($this->get_field_name('subject')),
        'body' => esc_attr($this->get_field_name('body')),
        ),
        'field_value' => array(
        'syntax_error' => $this->_syntax_error,
        'display' => isset($instance['display']) ? $instance['display'] : '',
        'to' => isset($instance['to']) ? $instance['to'] : '',
        'cc' => isset($instance['cc']) ? $instance['cc'] : '',
        'bcc' => isset($instance['bcc']) ? $instance['bcc'] : '',
        'subject' => isset($instance['subject']) ? $instance['subject'] : '',
        'body' => isset($instance['body']) ? $instance['body'] : '',
        ),
        );

        echo $template->render($fields);

        $_template_fields['syntax_error'] = false;
    }

    private function render_from_string( $template, $fields ) {
        return $this->_twig->createTemplate($template)->render($fields);
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
