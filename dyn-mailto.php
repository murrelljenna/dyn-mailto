<?php
/*
Plugin Name: Dynamic Mailto
Plugin URI: http://www.wpexplorer.com/create-widget-plugin-wordpress/
Description: This plugin adds a custom widget.
Version: 1.0
Author: AJ Clarke
Author URI: http://www.wpexplorer.com/create-widget-plugin-wordpress/
License: GPL2
*/

// The widget class

require __DIR__ . '/vendor/autoload.php';

class Dyn_Mailto_Widget extends WP_Widget
{
    private $template_fields = array();
    private $plugin_dir_path;
    // Main constructor
    public function __construct() 
    {
        parent::__construct(
            'dyn_mailto_widget',
            __('Dynamic Mailto Link', 'text_domain'),
            array(
            'customize_selective_refresh' => true,
            )
        );

        $this->plugin_dir_path = dirname(__FILE__);
    }

    // The widget form (for the backend )
    public function form( $instance ) {

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
        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_enqueue_script( 'jquery-ui-widget' );
        wp_enqueue_script( 'jquery-ui-menu' );
        wp_enqueue_script( 'jquery-ui-position' );


        $this->plugin_dir_path = dirname(__FILE__);

        $template_fields = include "$this->plugin_dir_path/admin/get_fields.php";

        wp_register_script( 'form-textcomplete', "https://mabelleneighbours.com/wp-content/plugins/dyn-mailto/public/form_textcomplete.js", array(), null, false);

        wp_enqueue_script( 'form-textcomplete');
        wp_localize_script( 'form-textcomplete', 'textcomplete_ajax_params', array_keys($template_fields));
        $this->render_widget_form($instance);

        ?>




    <?php }

    // Update widget settings
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['to']       = isset($new_instance['to']) ? wp_strip_all_tags($new_instance['to']) : '';  
        $instance['subject']  = isset($new_instance['subject']) ? wp_strip_all_tags($new_instance['subject']) : '';  
        $instance['body']       = isset($new_instance['body']) ? wp_strip_all_tags($new_instance['body']) : '';  
        return $new_instance;
    }

    // Display the widget
    public function widget( $args, $instance ) 
    {
        $loader = new \Twig\Loader\ArrayLoader([ ]);

        $twig = new \Twig\Environment($loader, ['strict_variables' => false]);

        $twig->addExtension(new \Twig\Extension\StringLoaderExtension());
        $this->plugin_dir_path = dirname(__FILE__);

        $sandbox_options = include "$this->plugin_dir_path/admin/get_sandbox_options.php";
        $twig->addExtension(new \Twig\Extension\SandboxExtension($sandbox_options));

        $template = array(
            'to' => $twig->createTemplate($instance['to']),
            'subject' => $twig->createTemplate($instance['subject']),
            'body' => $twig->createTemplate($instance['body'])
        );

        $template_fields = include "$this->plugin_dir_path/admin/get_fields.php";

        extract($args);

        // Check the widget options
        $display = "d";

        // Run templating
        $to = $template['to']->render($template_fields);
        $subject = $template['subject']->render($template_fields);
        $body = $template['body']->render($template_fields);

        // WordPress core before_widget hook (always include )
        echo $before_widget;

        // Display the widget
        echo '<div class="widget-text wp_widget_plugin_box">';
            echo "<a href='mailto:{$to}?subject={$subject}&body={$body}'>{$display}</a>";
        echo '</div>';

        // WordPress core after_widget hook (always include )
        echo $after_widget;

    }

    private function initialize_fields() {
        $fields = array();
        $fields = array_merge($fields, wp_get_current_user()["data"]);
    }

    // Render widget with Twig template. Used by widget().
    private function render_widget() {

    }

    // Render form with Twig template. Used by form().
    private function render_widget_form($instance) {

        $loader = new \Twig\Loader\FilesystemLoader("$this->plugin_dir_path/templates");
        $twig = new \Twig\Environment($loader, ['strict_variables' => false]);
        $template = $twig->load('widget_form.html');

        $template_fields = array(
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

        echo $template->render($template_fields);
    }

    private function var_error_log( $object=null ){
        ob_start();                    // start buffer capture
        var_dump( $object );           // dump the values
        $contents = ob_get_contents(); // put the buffer into a variable
        ob_end_clean();                // end capture
        error_log( $contents );        // log contents of the result of var_dump( $object )
    }

}

// Register the widget
function my_register_custom_widget() 
{
    register_widget('Dyn_Mailto_Widget');
}
add_action('widgets_init', 'my_register_custom_widget');
