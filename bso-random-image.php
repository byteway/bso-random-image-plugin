<?php
/*
Plugin Name: BSO Random Image Plugin
Plugin URI: https://byteway.eu/wp/random-image-wordpress-plugin/
Description: A plugin to display random images from a folder.
Version: 1.0
License: GPLv2 or later
Author: Byteway Software Ontwikkeling

**Explanation of the functions in the plugin**
These functions work together to provide a customizable experience for displaying random images with overlay text in WordPress.

**Admin Settings Functions**
rip_add_admin_menu: Adds the plugin options page to the WordPress admin menu.
rip_settings_init: Initializes the plugin settings, including the number of images, overlay text position, image size, and space between images.
rip_number_of_images_render: Renders the input field for the number of images.
rip_overlay_position_render: Renders the dropdown for the overlay text position.
rip_image_size_render: Renders the dropdown for selecting the image size.
rip_image_spacing_render: Renders the input field for defining the space between images.
rip_settings_section_callback: Callback function for the settings section.
rip_options_page: Displays the plugin options page in the admin dashboard.

**Image Display Functions**
rip_show_random_images: Displays random images with overlay text from corresponding text files based on the settings. It retrieves the number of images, overlay text position, image size, and space between images from the settings, and applies these settings to the displayed images.

**Shortcode Function**
rip_image_form_shortcode: Creates a form and displays random images when the form is submitted. It outputs the form with a button to show random images and checks if the form is submitted to call rip_show_random_images to display the images.


*/

// Add settings menu to the WordPress admin dashboard
add_action('admin_menu', 'rip_add_admin_menu');
add_action('admin_init', 'rip_settings_init');

// Function to add the plugin options page to the admin menu
function rip_add_admin_menu() {
    add_options_page('Random Image Plugin', 'Random Image Plugin', 'manage_options', 'random_image_plugin', 'rip_options_page');
}

// Function to initialize plugin settings
function rip_settings_init() {
    register_setting('pluginPage', 'rip_settings');

    add_settings_section(
        'rip_pluginPage_section', 
        __('Settings', 'wordpress'), 
        'rip_settings_section_callback', 
        'pluginPage'
    );

    add_settings_field( 
        'rip_number_of_images', 
        __('Number of Images', 'wordpress'), 
        'rip_number_of_images_render', 
        'pluginPage', 
        'rip_pluginPage_section' 
    );

    add_settings_field( 
        'rip_overlay_position', 
        __('Overlay Text Position', 'wordpress'), 
        'rip_overlay_position_render', 
        'pluginPage', 
        'rip_pluginPage_section' 
    );

    add_settings_field( 
        'rip_image_size', 
        __('Image Size', 'wordpress'), 
        'rip_image_size_render', 
        'pluginPage', 
        'rip_pluginPage_section' 
    );

    add_settings_field( 
        'rip_image_spacing', 
        __('Space Between Images (px)', 'wordpress'), 
        'rip_image_spacing_render', 
        'pluginPage', 
        'rip_pluginPage_section' 
    );

    add_settings_field( 
        'rip_image_padding', 
        __('Padding Around Images (px)', 'wordpress'), 
        'rip_image_padding_render', 
        'pluginPage', 
        'rip_pluginPage_section' 
    );
}

// Function to render the number of images input field
function rip_number_of_images_render() {
    $options = get_option('rip_settings');
    ?>
    <input type='number' name='rip_settings[rip_number_of_images]' value='<?php echo $options['rip_number_of_images']; ?>' min='1' max='10'>
    <?php
}

// Function to render the overlay text position dropdown
function rip_overlay_position_render() {
    $options = get_option('rip_settings');
    ?>
    <select name='rip_settings[rip_overlay_position]'>
        <option value='top' <?php selected($options['rip_overlay_position'], 'top'); ?>>Top</option>
        <option value='bottom' <?php selected($options['rip_overlay_position'], 'bottom'); ?>>Bottom</option>
        <option value='left' <?php selected($options['rip_overlay_position'], 'left'); ?>>Left</option>
        <option value='right' <?php selected($options['rip_overlay_position'], 'right'); ?>>Right</option>
    </select>
    <?php
}

// Function to render the image size dropdown
function rip_image_size_render() {
    $options = get_option('rip_settings');
    ?>
    <select name='rip_settings[rip_image_size]'>
        <option value='small' <?php selected($options['rip_image_size'], 'small'); ?>>Small (200x200)</option>
        <option value='medium' <?php selected($options['rip_image_size'], 'medium'); ?>>Medium (400x400)</option>
        <option value='big' <?php selected($options['rip_image_size'], 'big'); ?>>Big (600x600)</option>
    </select>
    <?php
}

// Function to render the image spacing input field
function rip_image_spacing_render() {
    $options = get_option('rip_settings');
    ?>
    <input type='number' name='rip_settings[rip_image_spacing]' value='<?php echo $options['rip_image_spacing']; ?>' min='0'>
    <?php
}

// Function to render the image padding input field
function rip_image_padding_render() {
    $options = get_option('rip_settings');
    ?>
    <input type='number' name='rip_settings[rip_image_padding]' value='<?php echo $options['rip_image_padding']; ?>' min='0'>
    <?php
}

// Callback function for the settings section
function rip_settings_section_callback() {
    echo __('Configure the settings for the Random Image Plugin.', 'wordpress');
}

// Function to display the plugin options page
function rip_options_page() {
    ?>
    <form action='options.php' method='post'>
        <h2>Random Image Plugin</h2>
        <?php
        settings_fields('pluginPage');
        do_settings_sections('pluginPage');
        submit_button();
        ?>
    </form>
    <?php
}

// Function to display random images with overlay text from corresponding text files
function rip_show_random_images() {
    $options = get_option('rip_settings');
    $number_of_images = isset($options['rip_number_of_images']) ? $options['rip_number_of_images'] : 1;
    $overlay_position = isset($options['rip_overlay_position']) ? $options['rip_overlay_position'] : 'bottom';
    $image_size = isset($options['rip_image_size']) ? $options['rip_image_size'] : 'medium';
    $image_spacing = isset($options['rip_image_spacing']) ? $options['rip_image_spacing'] : 10;
    $image_padding = isset($options['rip_image_padding']) ? $options['rip_image_padding'] : 0;

    // Define image size dimensions
    $size_dimensions = array(
        'small' => '200px',
        'medium' => '400px',
        'big' => '600px'
    );
    $width = $size_dimensions[$image_size];
    $height = $size_dimensions[$image_size];

    // Get all image files from the 'images' folder
    $images = glob(plugin_dir_path(__FILE__) . 'images/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    
    // Check if there are any images
    if ($images) {
        // Shuffle and select the required number of images
        shuffle($images);
        $selected_images = array_slice($images, 0, $number_of_images);

        foreach ($selected_images as $random_image) {
            // Get the URL of the selected image
            $image_url = plugin_dir_url(__FILE__) . 'images/' . basename($random_image);
            // Get the path of the corresponding text file
            $text_file = plugin_dir_path(__FILE__) . 'images/' . pathinfo($random_image, PATHINFO_FILENAME) . '.txt';
            
            // Check if the text file exists
            $overlay_text = '';
            if (file_exists($text_file)) {
                // Read the content of the text file
                $overlay_text = file_get_contents($text_file);
            }

            // Display the image
            echo '<div style="position: relative; display: inline-block; margin: ' . esc_attr($image_spacing) . 'px; padding: ' . esc_attr($image_padding) . 'px;">';
            echo '<img src="' . esc_url($image_url) . '" alt="Random Image" style="width: ' . esc_attr($width) . '; height: ' . esc_attr($height) . ';" />';
            // Display the overlay text according to the selected position
            if ($overlay_text) {
                $position_styles = array(
                    'top' => 'top: 10px; left: 50%; transform: translateX(-50%);',
                    'bottom' => 'bottom: 10px; left: 50%; transform: translateX(-50%);',
                    'left' => 'top: 50%; left: 10px; transform: translateY(-50%);',
                    'right' => 'top: 50%; right: 10px; transform: translateY(-50%);'
                );
                echo '<div style="position: absolute; ' . $position_styles[$overlay_position] . ' color: white; background-color: rgba(0, 0, 0, 0.5); padding: 5px; font-size: 12px;">' . esc_html($overlay_text) . '</div>';
            }
            echo '</div>';
        }
    } else {
        // Display a message if no images are found
        echo 'No images found in the plugin folder.';
    }
}

// Function to create a form and display random images when the form is submitted
function rip_image_form_shortcode() {
    // Start output buffering
    ob_start();
    ?>
    <!-- HTML form with a button to show random images -->
    <form method="post">
        <button type="submit" name="show_images">Show Random Images</button>
    </form>
    <?php
    // Check if the form is submitted
    if (isset($_POST['show_images'])) {
        // Display random images
        rip_show_random_images();
    }
    // Return the form and image output
    return ob_get_clean();
}

// Register the shortcode [random_image_form] to display the form
add_shortcode('random_image_form', 'rip_image_form_shortcode');
?>
