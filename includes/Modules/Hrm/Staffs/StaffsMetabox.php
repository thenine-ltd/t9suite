<?php
namespace T9AdminPro\Modules\Hrm\Staffs;

class StaffsMetabox {
    public static function init() {
        add_action('init', [self::class, 'register_department_taxonomy']);
        add_action('add_meta_boxes', [self::class, 'add_metaboxes']);
        add_action('save_post', [self::class, 'save_metabox_data']);
        add_action('acf/include_fields', [self::class, 'register_acf_fields']);
    }

    public static function register_department_taxonomy() {
        $labels = [
            'name'              => __('Departments', 't9admin-pro'),
            'singular_name'     => __('Department', 't9admin-pro'),
            'search_items'      => __('Search Departments', 't9admin-pro'),
            'all_items'         => __('All Departments', 't9admin-pro'),
            'edit_item'         => __('Edit Department', 't9admin-pro'),
            'update_item'       => __('Update Department', 't9admin-pro'),
            'add_new_item'      => __('Add New Department', 't9admin-pro'),
            'new_item_name'     => __('New Department Name', 't9admin-pro'),
            'menu_name'         => __('Departments', 't9admin-pro'),
        ];

        $args = [
            'labels'            => $labels,
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_in_menu'      => true,
            'show_admin_column' => true,
            'rewrite'           => ['slug' => 'department'],
        ];

        register_taxonomy('department', ['staffs'], $args);
    }

    public static function add_metaboxes() {
        add_meta_box(
            'staff_info',
            __('Staff Information', 't9admin-pro'),
            [self::class, 'render_info_metabox'],
            'staffs',
            'normal',
            'default'
        );
    }

    public static function render_info_metabox($post) {
        wp_nonce_field('staff_info_nonce_action', 'staff_info_nonce_name');

        $fields = [
            'status' => [
                'label' => __('Status', 't9admin-pro'),
                'type'  => 'select',
                'options' => [
                    'active' => __('Active', 't9admin-pro'),
                    'inactive' => __('Inactive', 't9admin-pro'),
                ],
            ],
            'identifier_code' => [
                'label' => __('Identifier Code (ID)', 't9admin-pro'),
                'type'  => 'text',
            ],
            'birth_date' => [
                'label' => __('Birth Date (dd/mm/yyyy)', 't9admin-pro'),
                'type'  => 'date',
            ],
            'sex' => [
                'label' => __('Sex', 't9admin-pro'),
                'type'  => 'select',
                'options' => [
                    'male' => __('Male', 't9admin-pro'),
                    'female' => __('Female', 't9admin-pro'),
                ],
            ],
            'nationality' => [
                'label' => __('Nationality', 't9admin-pro'),
                'type'  => 'select',
                'options' => [
                    'vietnam' => __('Vietnam', 't9admin-pro'),
                    'usa' => __('USA', 't9admin-pro'),
                    'japan' => __('Japan', 't9admin-pro'),
                    'other' => __('Other', 't9admin-pro'), // Có thể mở rộng danh sách
                ],
            ],
            'job_position' => [
                'label' => __('Job Position', 't9admin-pro'),
                'type'  => 'text',
            ],
            'workplace' => [
                'label' => __('Workplace', 't9admin-pro'),
                'type'  => 'text',
            ],
            'city' => [
                'label' => __('City (Permanent Address)', 't9admin-pro'),
                'type'  => 'text',
            ],
            'district' => [
                'label' => __('District (Permanent Address)', 't9admin-pro'),
                'type'  => 'text',
            ],
            'address' => [
                'label' => __('Address (Permanent Address)', 't9admin-pro'),
                'type'  => 'text',
            ],
            'phone' => [
                'label' => __('Phone Number', 't9admin-pro'),
                'type'  => 'tel',
            ],
            'email' => [
                'label' => __('Email', 't9admin-pro'),
                'type'  => 'email',
            ],
            'bank_account' => [
                'label' => __('Bank Account', 't9admin-pro'),
                'type'  => 'text',
            ],
            'name_of_account' => [
                'label' => __('Name of Account', 't9admin-pro'),
                'type'  => 'text',
            ],
            'bank_of_issue' => [
                'label' => __('Bank of Issue', 't9admin-pro'),
                'type'  => 'text',
            ],
            'personal_tax_code' => [
                'label' => __('Personal Tax Code', 't9admin-pro'),
                'type'  => 'text',
            ],
            'marital_status' => [
                'label' => __('Marital Status', 't9admin-pro'),
                'type'  => 'select',
                'options' => [
                    'single' => __('Single', 't9admin-pro'),
                    'married' => __('Married', 't9admin-pro'),
                ],
            ],
            'notes' => [
                'label' => __('Notes', 't9admin-pro'),
                'type'  => 'textarea',
            ],
        ];

        foreach ($fields as $key => $data) {
            $value = get_post_meta($post->ID, $key, true);

            echo '<p><label>' . esc_html($data['label']) . '</label>';
            switch ($data['type']) {
                case 'select':
                    echo '<select name="' . esc_attr($key) . '" class="widefat">';
                    foreach ($data['options'] as $option_key => $option_label) {
                        $selected = $value === $option_key ? 'selected' : '';
                        echo '<option value="' . esc_attr($option_key) . '" ' . $selected . '>' . esc_html($option_label) . '</option>';
                    }
                    echo '</select>';
                    break;

                case 'textarea':
                    echo '<textarea name="' . esc_attr($key) . '" class="widefat">' . esc_textarea($value) . '</textarea>';
                    break;

                default:
                    echo '<input type="' . esc_attr($data['type']) . '" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" class="widefat">';
                    break;
            }
            echo '</p>';
        }
    }

    // Cập nhật hàm save_metabox_data để lưu các field mới
    public static function save_metabox_data($post_id) {
        if (
            !isset($_POST['staff_info_nonce_name']) || 
            !wp_verify_nonce(sanitize_text_field($_POST['staff_info_nonce_name']), 'staff_info_nonce_action')
        ) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $fields = [
            'status',
            'identifier_code',
            'birth_date',
            'sex',
            'nationality',
            'job_position',
            'workplace',
            'city',
            'district',
            'address',
            'phone',
            'email',
            'bank_account',
            'name_of_account',
            'bank_of_issue',
            'personal_tax_code',
            'marital_status',
            'notes',
        ];

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $value = sanitize_text_field(wp_unslash($_POST[$field]));
                update_post_meta($post_id, $field, $value);
            } else {
                delete_post_meta($post_id, $field);
            }
        }
    }

    public static function register_acf_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group([
            'key' => 'group_staff_information',
            'title' => 'Staff Information',
            'fields' => [
                [
                    'key' => 'field_full_name',
                    'label' => 'Full Name',
                    'name' => 'full_name',
                    'type' => 'text',
                    'required' => 1,
                ],
                [
                    'key' => 'field_id_card',
                    'label' => 'ID Card',
                    'name' => 'id_card',
                    'type' => 'number',
                ],
                [
                    'key' => 'field_birth_date',
                    'label' => 'Birth Date',
                    'name' => 'birth_date',
                    'type' => 'date_picker',
                    'display_format' => 'd/m/Y',
                    'return_format' => 'd/m/Y',
                ],
                [
                    'key' => 'field_permanent_address',
                    'label' => 'Permanent Address',
                    'name' => 'permanent_address',
                    'type' => 'text',
                    'required' => 1,
                ],
                [
                    'key' => 'field_temporary_address',
                    'label' => 'Temporary Address',
                    'name' => 'temporary_address',
                    'type' => 'text',
                ],
                [
                    'key' => 'field_email',
                    'label' => 'Email',
                    'name' => 'email',
                    'type' => 'email',
                ],
                [
                    'key' => 'field_phone',
                    'label' => 'Phone Number',
                    'name' => 'phone_number',
                    'type' => 'number',
                ],
                [
                    'key' => 'field_reference_person',
                    'label' => 'Reference Person',
                    'name' => 'reference_person',
                    'type' => 'group',
                    'sub_fields' => [
                        [
                            'key' => 'field_ref_full_name',
                            'label' => 'Full Name',
                            'name' => 'ref_full_name',
                            'type' => 'text',
                        ],
                        [
                            'key' => 'field_ref_phone',
                            'label' => 'Phone Number',
                            'name' => 'ref_phone',
                            'type' => 'number',
                        ],
                        [
                            'key' => 'field_ref_relationship',
                            'label' => 'Relationship',
                            'name' => 'ref_relationship',
                            'type' => 'select',
                            'choices' => [
                                'father' => 'Father',
                                'mother' => 'Mother',
                                'brother' => 'Brother',
                                'sister' => 'Sister',
                            ],
                        ],
                    ],
                ],
                [
                    'key' => 'field_notes',
                    'label' => 'Notes',
                    'name' => 'notes',
                    'type' => 'textarea',
                    'placeholder' => 'Weight, Height, Special Signs',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'staffs',
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'seamless',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'active' => true,
        ]);
    }
}