<?php
/**
 * Plugin Name:     Ultimate Member - Profile Photo Placeholders
 * Description:     Extension to Ultimate Member for six new placeholders creating profile photo links and inline embedded photos with three different sizes in all UM notification emails.
 * Version:         1.2.0
 * Requires PHP:    7.4
 * Author:          Miss Veronica
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Author URI:      https://github.com/MissVeronica
 * Plugin URI:      https://github.com/MissVeronica/um-profile-photo-placeholders
 * Update URI:      https://github.com/MissVeronica/um-profile-photo-placeholders
 * Text Domain:     ultimate-member
 * Domain Path:     /languages
 * UM version:      2.10.5
 */

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'UM' ) ) return;

class UM_Profile_Photo_Placeholder {

    public $template = '';

    function __construct() {

        define( 'Plugin_Basename_PPP', plugin_basename( __FILE__ ));

        add_filter( 'um_template_tags_patterns_hook', array( $this, 'email_template_tags_patterns' ), 10, 1 );
        add_filter( 'um_template_tags_replaces_hook', array( $this, 'email_template_tags_replaces' ), 10, 1 );

        add_action( 'um_before_email_notification_sending', array( $this, 'profile_photo_placeholders_data_setup' ), 10, 3 );

        add_filter( 'plugin_action_links_' . Plugin_Basename_PPP, array( $this, 'plugin_settings_link' ), 10, 1 );

        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_filter( 'um_admin_settings_email_section_fields', array( $this, 'um_admin_settings_email_section' ), 10, 2 );
        }
    }

    public function plugin_settings_link( $links ) {

        $url = get_admin_url() . "admin.php?page=um_options&tab=email";
        $title = esc_html__( 'Settings for each email template', 'ultimate-member' );
        $links[] = '<a href="' . esc_url( $url ) . '" title="' . $title . '">' . esc_html__( 'Settings' ) . '</a>';

        return $links;
    }

    public function profile_photo_placeholders_data_setup( $email, $template, $args ) {

        if ( ! empty( $email ) && ! empty( $template ) ) {

            $this->template = $template;
        }
    }

    public function email_template_tags_patterns( $search ) {

        if ( UM()->options()->get( $this->template . '_profile_photo_placeholders_enabled' ) == 1 ) {

            $search[] = '{profile_photo_link_s}';
            $search[] = '{profile_photo_link_m}';
            $search[] = '{profile_photo_link_l}';

            if ( UM()->options()->get( $this->template . '_profile_photo_placeholders_embed' ) == 1 ) {

                $search[] = '{profile_photo_embed_s}';
                $search[] = '{profile_photo_embed_m}';
                $search[] = '{profile_photo_embed_l}';
            }
        }

        return $search;
    }

    public function email_template_tags_replaces( $replace ) {

        if ( UM()->options()->get( $this->template . '_profile_photo_placeholders_enabled' ) == 1 ) {

            $image_name = um_profile( 'profile_photo' );

            if ( empty( $image_name )) {

                $replace[] = '';
                $replace[] = '';
                $replace[] = '';

                if ( UM()->options()->get( $this->template . '_profile_photo_placeholders_embed' ) == 1 ) {

                    $replace[] = '';
                    $replace[] = '';
                    $replace[] = '';
                }

                return $replace;
            }

            $image_path = UM()->uploader()->get_upload_base_dir() . um_user( 'ID' ) . DIRECTORY_SEPARATOR . $image_name;
            $image_type = pathinfo( $image_path, PATHINFO_EXTENSION );

            $image_style = ( UM()->options()->get( $this->template . '_profile_photo_placeholders_circular' ) == 1 ) ? 'border-radius: 50%;' : '';

            $border = UM()->options()->get( $this->template . '_profile_photo_placeholders_border' );
            if ( ! empty( $border ) && $border != '0px' ) {

                $image_style .= ' border-width: ' . esc_attr( UM()->options()->get( $this->template . '_profile_photo_placeholders_border' )) . ';';
                $image_style .= ' border-style: solid;';
                $color = esc_attr( strtolower( UM()->options()->get( $this->template . '_profile_photo_placeholders_color' )));
                $image_style .= ' border-color: ' . ( ! empty( $color ) ? $color : 'white' ) . ';';
            }

            $image_style = ! empty( $image_style ) ? ' style="' . $image_style . '"' : '';
            $image_style .= ' alt="' . esc_html__( 'Profile photo', 'ultimate-member' ) . '"';
            $image_style .= ' width="##" height="##"';

            $image_sizes = array_map( array( $this, 'remove_px' ), UM()->files()->get_profile_photo_size( 'photo_thumb_sizes' ));
            $image_small = $image_sizes[array_key_first( $image_sizes )];
            $data = um_get_user_avatar_data( um_user( 'ID' ), null );
            $sml = 0;

            foreach( $image_sizes as $key => $image_size ) {

                $image_path_sml = str_replace( '.' . $image_type, "-{$image_size}." . $image_type, $image_path );

                if ( file_exists( $image_path_sml )) {
                    $page_url = '<img src="' . str_replace( $image_small, $image_size, $data['url'] ) . '"' . str_replace( '##', $key, $image_style ) . '/>';                
                }

                $replace[] = $page_url;
                if ( ++$sml == 3 ) break;
            }

            switch( $sml ) {
                case 1: $replace[] = '';
                case 2: $replace[] = '';
                case 3: break;
            }

            if ( UM()->options()->get( $this->template . '_profile_photo_placeholders_embed' ) == 1 ) {

                $image_base64 = '';
                $sml = 0;

                foreach( $image_sizes as $key => $image_size ) {

                    $image_path_sml = str_replace( '.' . $image_type, "-{$image_size}." . $image_type, $image_path );

                    if ( file_exists( $image_path_sml )) {

                        $image_content = file_get_contents( $image_path_sml );
                        if ( $image_content !== false ) {
                            $image_base64 = '<img src="data:image/' . $image_type . ';base64,' . base64_encode( $image_content ) . '"' . str_replace( '##', $key, $image_style ) . '/>';
                        }
                    }

                    $replace[] = $image_base64;
                    if ( ++$sml == 3 ) break;
                }

                switch( $sml ) {
                    case 1: $replace[] = '';
                    case 2: $replace[] = '';
                    case 3: break;
                }
            }
        }

        return $replace;
    }

    public function remove_px( $image_size ) {

        return str_replace( 'px', '', $image_size );
    }

    public function get_possible_plugin_update( $plugin ) {

        $plugin_data = get_plugin_data( __FILE__ );

        $documention = sprintf( ' <a href="%s" target="_blank" title="%s">%s</a>',
                                        esc_url( $plugin_data['PluginURI'] ),
                                        esc_html__( 'GitHub plugin documentation and download', 'ultimate-member' ),
                                        esc_html__( 'Documentation', 'ultimate-member' ));

        $description = sprintf( esc_html__( 'Plugin "Profile Photo Placeholders" version %s - Tested with UM 2.10.5 - %s', 'ultimate-member' ),
                                                                            $plugin_data['Version'], $documention );
        return $description;
    }

    public function um_admin_settings_email_section( $section_fields, $email_key ) {

        $prefix = '&nbsp; * &nbsp;';

        $section_fields[] = array(
                                    'id'             => $email_key . '_profile_photo_placeholders_header',
                                    'type'           => 'header',
                                    'label'          => $this->get_possible_plugin_update( 'profile_photo_placeholders' ),
                                );

        $section_fields[] = array(
                                    'id'             => $email_key . '_profile_photo_placeholders_enabled',
                                    'type'           => 'checkbox',
                                    'label'          => $prefix . esc_html__( 'Enable plugin', 'ultimate-member' ),
                                    'checkbox_label' => esc_html__( 'Click to enable the "Profile Photo Placeholders" plugin for this email template.', 'ultimate-member' ),
                                );

        $section_fields[] = array(
                                    'id'             => $email_key . '_profile_photo_placeholders_embed',
                                    'type'           => 'checkbox',
                                    'label'          => $prefix . esc_html__( 'Enable inline embedded Photos', 'ultimate-member' ),
                                    'checkbox_label' => esc_html__( 'Click to inline embed the Profile Photo base64 encoded image {profile_photo_embed_x} for this email template.', 'ultimate-member' ),
                                    'conditional'    => array( $email_key . '_profile_photo_placeholders_enabled', '=', 1 ),
                                );

        $section_fields[] = array(
                                    'id'             => $email_key . '_profile_photo_placeholders_circular',
                                    'type'           => 'checkbox',
                                    'label'          => $prefix . esc_html__( 'Enable circular photos', 'ultimate-member' ),
                                    'checkbox_label' => esc_html__( 'Click to make the Profile photo circular in this email template.', 'ultimate-member' ),
                                    'conditional'    => array( $email_key . '_profile_photo_placeholders_enabled', '=', 1 ),
                                );

        $section_fields[] = array(
                                    'id'             => $email_key . '_profile_photo_placeholders_border',
                                    'type'           => 'select',
                                    'label'          => $prefix . esc_html__( 'Select border width', 'ultimate-member' ),
                                    'description'    => esc_html__( 'Select a border width or 0px for no border.', 'ultimate-member' ),
                                    'size'           => 'small',
                                    'options'        => array( '0px' => '0px', '1px' => '1px', '2px' => '2px', '3px' => '3px', '4px' => '4px', '5px' => '5px', '6px' => '6px',
                                                               '7px' => '7px', '8px' => '8px', '9px' => '9px', '10px' => '10px', '11px' => '11px', '12px' => '12px' ),
                                    'conditional'    => array( $email_key . '_profile_photo_placeholders_enabled', '=', 1 ),
                                );

        $section_fields[] = array(
                                    'id'             => $email_key . '_profile_photo_placeholders_color',
                                    'type'           => 'text',
                                    'size'           => 'small',
                                    'label'          => $prefix . esc_html__( 'Enter border color', 'ultimate-member' ),
                                    'description'    => esc_html__( 'Enter border color either HTML color name or HEX code. Default color: white', 'ultimate-member' ) .
                                                        ' <a href="https://www.w3schools.com/tags/ref_colornames.asp" target="_blank">W3SCHOOL: HTML Color names</a>',
                                    'conditional'    => array( $email_key . '_profile_photo_placeholders_border', '!=', '0px' ),
                                );

        return $section_fields;
    }


}

new UM_Profile_Photo_Placeholder();
