<?php
/*
 * Plugin Name: Filter Featured for WooCommerce
 * Plugin URI: https://github.com/ELY3M/filter-featured-products
 * Description: Improves WooCommerce back-end by adding capability to filter products based on their featured status.
 * Version: 1.0.3
 * Author: Modded by elymbmx - credit to Backpack.Studio
 * Author URI: https://bmx3r.com
 * Requires at least: 5.4
 * Requires PHP: 7.4
 * Text Domain: elymbmx
 * Domain Path: elymbmx
 */
/* I re-did the plugin down to one php file.  no more useless files/dirs */  

// Prevent direct access, use only exclusively as an include.
if (count(get_included_files()) == 1) {
    http_response_code(403);
    die();
}




class FilterFeatured
{

    /**
     * Name of GET variable.
     *
     * @var string
     * @since 1.0.1
     */
    const GVAR_FEATURED_FILTER = 'featured_status';

    /**
     * Accepted value for variable GET['featured_status'].
     *
     * @var string
     * @since 1.0.1
     */
    const ENUM_FEATURED_FILTER_FEATURED = 'featured';

    /**
     * Accepted value for variable GET['featured_status'].
     *
     * @var string
     * @since 1.0.1
     */
    const ENUM_FEATURED_FILTER_NORMAL = 'normal';

    /**
     * Disable object creation
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        ;
    }

    /**
     * Validates user input and returns sanitized value.
     *
     * @return string
     * @since 1.0.1
     */
    public static function getGetVarFilterFeatured()
    {
        static $status;
        if (is_null($status)) {
            // Validate input, only 'featured' or 'normal' or an empty string are used.
            $status = isset($_GET[self::GVAR_FEATURED_FILTER]) ? trim($_GET[self::GVAR_FEATURED_FILTER]) : '';
            $status = ($status == self::ENUM_FEATURED_FILTER_FEATURED || $status == self::ENUM_FEATURED_FILTER_NORMAL) ? $status : '';
        }
        return $status;
    }

    /**
     * Compare 2 values and if they match, return string of selected attribute.
     *
     * @param string $a            
     * @param string $b            
     * @return string
     * @since 1.0.0
     */
    private static function getIsSelected($a, $b)
    {
        if ($a == $b) {
            return ' selected="selected"';
        }
        return '';
    }

    /**
     * Add capability to filter WooCommerce products by featured status.
     *
     * @since 1.0.0
     */
    public static function addFilter()
    {
        global $typenow, $wp_query;
        if ($typenow == 'product') {
            $_featured = __('Featured', 'woocommerce');
            $status = self::getGetVarFilterFeatured();
            // Prepare HTML output
            $output = array();
            $output[] = '<!-- WooCommerce Filter Featured -->';
            $output[] = '<select name="featured_status" id="dropdown_featured_status">';
            $output[] = '<option value="" ' . self::getIsSelected($status, '') . '>' . __('Filter by', 'woocommerce') . ' ' . strtolower($_featured) . '</option>';
            $output[] = '<option value="featured"' . self::getIsSelected($status, 'featured') . '>' . $_featured . ': ' . __('Yes', 'woocommerce') . '</option>';
            $output[] = '<option value="normal"' . self::getIsSelected($status, 'normal') . '>' . $_featured . ': ' . __('No', 'woocommerce') . '</option>';
            $output[] = "</select>";
            $output[] = '<!-- // WooCommerce Filter Featured -->';
            // Print HTML
            echo implode(PHP_EOL, $output);
        }
    }

    /**
     * Filter WooCommerce products by their featured status.
     *
     * @param array $query            
     * @since 1.0.0
     */
    public static function filterProducts($query)
    {
        global $typenow;
        if ($typenow == 'product') {
            // Filter products
            $status = self::getGetVarFilterFeatured();
            if (! empty($status)) {
                if ($status == 'featured') {
                    $query->query_vars['tax_query'][] = array(
                        'taxonomy' => 'product_visibility',
                        'field' => 'slug',
                        'terms' => 'featured'
                    );
                } elseif ($status == 'normal') {
                    $query->query_vars['tax_query'][] = array(
                        'taxonomy' => 'product_visibility',
                        'field' => 'slug',
                        'terms' => 'featured',
                        'operator' => 'NOT IN'
                    );
                }
            }
        }
    }
}



/**
 * Hooks plugin functionality
 */
function filter_featured()
{
    // Check that Woocommerce is available
    if (class_exists('woocommerce')) {
        // Hook plugin functionality
        add_action('restrict_manage_posts', 'FilterFeatured::addFilter');
        add_filter('parse_query', 'FilterFeatured::filterProducts');
    } else {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-warning is-dismissible"><p>WooCommerce is required for usage of Filter Featured Products.</p></div>';
        });
    }
}

// Hook plugin
add_action('init', 'filter_featured', PHP_INT_MAX);


