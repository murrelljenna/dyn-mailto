<?php

/**
 * PHP Version 7
 * Defines core widget that for running user-defined mailto templates.
 */

namespace jmurrell\DynMailto;

defined('ABSPATH') || exit;

/**
 * Core widget class - runs templating on user defined twig templates.
 */

class Widget extends \WP_Widget
{
    private $_template_fields = array();
    private $_autocomplete_fields = array();

    /* Error state for form entry */

    private $_syntax_error = false;
    
    /* Twig environment variables */

    private $_twig_loader;
    private $_twig;

    /**
     * Constructs a Widget instance.
     */
    public function __construct() 
    {
        parent::__construct(
            'widget',
            __('Dynamic Mailto Link', 'text_domain'),
            array(
            'customize_selective_refresh' => true,
            )
        );

        /* Load twig */

        $this->_twig_loader = new \Twig\Loader\FilesystemLoader(
            DYN_MAILTO_PLUGIN_DIR . "/templates"
        );
        $this->_twig = new \Twig\Environment($this->_twig_loader);

        include_once DYN_MAILTO_PLUGIN_DIR . "/admin/field_loader.php";

        /* Load our template fields and autocomplete fields. Former is used for twig templating, latter just used for js autocompletion of field names */

        $this->_template_fields = Field_Loader::get_template_fields();
        $this->_autocomplete_fields = Field_Loader::get_autocomplete_fields();
    }

    /**
     * WP_WIDGET::form - renders widget form. 
     *
     * @param array $instance
     *
     * @return void
     */
    public function form( $instance ) 
    {

        /* Get our jquery loaded */

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

        /* Run textcomplete.js with a list of all our parameter names. Enables basic syntax completion for the user
        when entering template */

        wp_localize_script('dyn-mailto-form-textcomplete', 'textcomplete_ajax_params', $this->_autocomplete_fields);

        $this->_render_widget_form($instance);
    }

    /**
     * check template and update if valid.
     *
     * @param array $new_instance
     * @param array $old_instance
     *
     * @return bool
     */
    public function update( $new_instance, $old_instance ) 
    {
        /* Check the syntax of entered values before saving. This is costly, but best option right now.*/

        try {
            isset($new_instance['display']) && $this->_render_from_string(sanitize_textarea_field($new_instance['display']), $this->_template_fields);
            isset($new_instance['to']) && $this->_render_from_string(sanitize_textarea_field($new_instance['to']), $this->_template_fields);
            isset($new_instance['cc']) && $this->_render_from_string(sanitize_textarea_field($new_instance['cc']), $this->_template_fields);
            isset($new_instance['bcc']) && $this->_render_from_string(sanitize_textarea_field($new_instance['bcc']), $this->_template_fields);
            isset($new_instance['subject']) && $this->_render_from_string(sanitize_textarea_field($new_instance['subject']), $this->_template_fields);
            isset($new_instance['body']) && $this->_render_from_string(sanitize_textarea_field($new_instance['body']), $this->_template_fields); 

            /* If theres is a syntax error, catch it */

        } catch (\Twig\Error\SyntaxError $e) {
            /* Leave error message. Twig template widget_form.html renders error when this is set to true. */
            $this->_syntax_error = true;

            /* Reject changes and reload form() */
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

    /**
     * widget - renders main widget.
     *
     * @param $args
     * @param $instance
     *
     * @return void
     */
    public function widget( $args, $instance ) 
    {
        /* We'll be loading templates from strings, not from the file system in this case. Add extension to do so */

        $this->_twig->addExtension(new \Twig\Extension\StringLoaderExtension());

        /* Add sandboxing options to our twig instance */

        $sandbox_options = include_once DYN_MAILTO_PLUGIN_DIR . "/admin/get_sandbox_options.php";
        $this->_twig->addExtension(new \Twig\Extension\SandboxExtension($sandbox_options));

        /* Load templates for each of these fields first */

        $template = array(
        'display' => $this->_twig->createTemplate(esc_attr($instance['display'])),
        'to' => $this->_twig->createTemplate(esc_attr($instance['to'])),
        'cc' => $this->_twig->createTemplate(esc_attr($instance['cc'])),
        'bcc' => $this->_twig->createTemplate(esc_attr($instance['bcc'])),
        'subject' => $this->_twig->createTemplate(esc_attr($instance['subject'])),
        'body' => $this->_twig->createTemplate(esc_attr($instance['body']))
        );

        /* Render the template using our template fields */

        $widget_fields = array(
        'display' => $template['display']->render($this->_template_fields),
        'to' => $template['to']->render($this->_template_fields),
        'cc' => $template['cc']->render($this->_template_fields),
        'bcc' => $template['bcc']->render($this->_template_fields),
        'subject' => $template['subject']->render($this->_template_fields),
        'body' => $template['body']->render($this->_template_fields),
        );

        /* With all our templates rendered, we can finally render the page itself */

        echo $args['before_widget'];
        $this->_render_widget($widget_fields);
        echo $args['after_widget'];

    }

    /**
     * Render widget with Twig template. Used by widget().
     *
     * @param array $fields
     *
     * @return void
     */

    private function _render_widget( $fields ) 
    {
        $template = $this->_twig->load('widget.html');
        echo $template->render($fields);
    }

    /**
     * Render form with Twig template. Used by form().
     *
     * @param array $instance
     *
     * @return void
     */
    private function _render_widget_form($instance) 
    {
        $template = $this->_twig->load('widget_form.html');

        $fields = array(
        
        /* Field ID's for each field. Used for labels in the form*/

        'field_id' => array(
        'display' => esc_attr($this->get_field_id('display')),
        'to' => esc_attr($this->get_field_id('to')),
        'cc' => esc_attr($this->get_field_id('cc')),
        'bcc' => esc_attr($this->get_field_id('bcc')),
        'subject' => esc_attr($this->get_field_id('subject')),
        'body' => esc_attr($this->get_field_id('body')),
        ),

        /* Field names for each field */

        'field_name' => array(
        'display' => esc_attr($this->get_field_name('display')),
        'to' => esc_attr($this->get_field_name('to')),
        'cc' => esc_attr($this->get_field_name('cc')),
        'bcc' => esc_attr($this->get_field_name('bcc')),
        'subject' => esc_attr($this->get_field_name('subject')),
        'body' => esc_attr($this->get_field_name('body')),
        ),

        /* Actual values for each field */

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

    /**
     * Utility function to quickly render Twig template from string
     *
     * @param string $template Template to render
     * @param array  $fields   Associative array of fields to render with
     *
     * @return string
     */
    private function _render_from_string( $template, $fields ) 
    {
        return $this->_twig->createTemplate($template)->render($fields);
    }
}
