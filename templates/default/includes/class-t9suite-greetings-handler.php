<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class T9SuiteGreetingsHandler {

    /**
     * Render the greeting message
     *
     * @param string $display_name User's display name
     */
    public static function t9suite_render_greeting($display_name) {
        $current_hour = current_time('H'); // Get current hour based on WordPress timezone
        
        // Determine greeting based on time of day
        if ($current_hour < 12) {
            $greeting = esc_html__('Good morning', 't9admin-pro');
        } elseif ($current_hour < 18) {
            $greeting = esc_html__('Good afternoon', 't9admin-pro');
        } else {
            $greeting = esc_html__('Good evening', 't9admin-pro');
        }

        // Render the greeting
        ?>
        <h4 class="text-white fw-normal mt-5 pt-7 mb-1"><?php echo sprintf('%s, %s!', $greeting, esc_html($display_name)); ?></h4>
                  <h6 class="opacity-75 fw-normal text-white mb-0"><?php esc_html_e('Have a productive day!', 't9admin-pro'); ?></h6>
        <?php
    }
}
