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
    }

    // The widget form (for the backend )
    public function form( $instance ) {

        // Set widget defaults
        $defaults = array(
        'to' => '',
        'cc' => '',
        'bcc' => '',
        'subject' => ''
        );
        
        // Parse current settings with defaults
        extract(wp_parse_args(( array ) $instance, $defaults)); 
        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_enqueue_script( 'jquery-ui-widget' );
        wp_enqueue_script( 'jquery-ui-menu' );
        wp_enqueue_script( 'jquery-ui-position' );

        $plugin_dir_path = dirname(__FILE__);
        $loader = new \Twig\Loader\FilesystemLoader("$plugin_dir_path/templates");

        $twig = new \Twig\Environment($loader, ['strict_variables' => false]);

        $template = $twig->load('form.html');
        $template_fields = include "$plugin_dir_path/admin/get_fields.php";
        //echo $template->render(['fields' => $template_fields]);
        ?>



            <?php // Widget Title ?>
        <script type="module" src="https://mabelleneighbours.com/wp-content/plugins/dyn-mailto/public/form_textcomplete.js"></script>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('to')); ?>"><?php _e('To', 'text_domain'); ?></label>
            <textarea class="widefat ui-widget" id="<?php echo esc_attr($this->get_field_id('to')); ?>" name="<?php echo esc_attr($this->get_field_name('to')); ?>" type="text" value="<?php echo esc_attr($instance['to']); ?>" ></textarea>
        </p>

            <?php // Text Field ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('subject')); ?>"><?php _e('Subject', 'text_domain'); ?></label>
            <textarea class="widefat" id="<?php echo esc_attr($this->get_field_id('subject')); ?>" name="<?php echo esc_attr($this->get_field_name('subject')); ?>" type="text" value="<?php echo esc_attr($instance['subject']); ?>" ></textarea>
        </p>

            <?php // Textarea Field ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('body')); ?>"><?php _e('Body:', 'text_domain'); ?></label>
            <textarea class="widefat" id="<?php echo esc_attr($this->get_field_id('body')); ?>" name="<?php echo esc_attr($this->get_field_name('body')); ?>"><?php echo wp_kses_post($instance['body']); ?></textarea>
        </p>

    <?php }

    // Update widget settings
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['to']       = isset($new_instance['to']) ? wp_strip_all_tags($new_instance['to']) : '';  
        $instance['subject']  = isset($new_instance['subject']) ? wp_strip_all_tags($new_instance['subject']) : '';  
        $instance['body']       = isset($new_instance['body']) ? wp_strip_all_tags($new_instance['body']) : '';  
        return $instance;
    }

    // Display the widget
    public function widget( $args, $instance ) 
    {
        $loader = new \Twig\Loader\ArrayLoader([ ]);

        $twig = new \Twig\Environment($loader, ['strict_variables' => false]);

        $twig->addExtension(new \Twig\Extension\StringLoaderExtension());
        $plugin_dir_path = dirname(__FILE__);

        $sandbox_options = include "$plugin_dir_path/admin/get_sandbox_options.php";
        $twig->addExtension(new \Twig\Extension\SandboxExtension($sandbox_options));

        $template = array(
            'to' => $twig->createTemplate($instance['to']),
            'subject' => $twig->createTemplate($instance['subject']),
            'body' => $twig->createTemplate($instance['body'])
        );

        $template_fields = include "$plugin_dir_path/admin/get_fields.php";

        extract($args);

        // Check the widget options
        $display = "d";

        // Run templating
        $to = $template['to']->render(['tast' => 'hello']);
        $subject = $template['subject']->render(['tast' => 'hello']);
        $body = $template['body']->render(['tast' => 'hello']);

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
