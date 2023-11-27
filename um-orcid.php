<?php
/**
 * Plugin Name:     Ultimate Member - ORCID Integration
 * Description:     Extension to Ultimate Member for ORCID integration.
 * Version:         1.2.0
 * Requires PHP:    7.4
 * Author:          Miss Veronica
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Author URI:      https://github.com/MissVeronica
 * Text Domain:     ultimate-member
 * Domain Path:     /languages
 * UM version:      2.7.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'UM' ) ) return;

class UM_Orcid_Integration {

    function __construct() {

        add_filter( 'um_admin_field_validation_hook',             array( $this, 'custom_admin_field_validation_hook' ), 10, 1 );
        add_action( 'um_custom_field_validation_orcid_id_number', array( $this, 'um_custom_validate_orcid_id_number' ), 10, 3 );
        add_filter( 'um_predefined_fields_hook',                  array( $this, 'custom_predefined_fields_hook_orcid' ), 10, 1 );
        add_filter( 'um_account_tab_general_fields',              array( $this, 'um_account_tab_general_fields_orcid' ), 10, 1 );
        add_action( 'um_submit_account_errors_hook',              array( $this, 'um_submit_account_general_tab_errors_orcid' ), 10, 1 );
        add_filter( 'um_account_pre_updating_profile_array',      array( $this, 'um_account_pre_updating_profile_array_orcid' ), 10, 1 );
    }

    public function custom_admin_field_validation_hook( $array ) {

        $array['orcid_id'] = __( 'ORCID ID','ultimate-member' );

        return $array;
    }

    public function custom_predefined_fields_hook_orcid( $predefined_fields ) {

        $predefined_fields['orcid_id'] = array(
                                            'title'           => __( 'ORCID ID','ultimate-member' ),
                                            'metakey'         => 'orcid_id',
                                            'type'            => 'url',
                                            'label'           => __( 'ORCID ID','ultimate-member' ),
                                            'required'        => 0,
                                            'public'          => 1,
                                            'editable'        => 1,
                                            'url_target'      => '_blank',
                                            'url_rel'         => 'nofollow',
                                            'icon'            => 'fa-brands fa-orcid',
                                            'validate'        => 'custom',
                                            'custom_validate' => 'orcid_id_number',
                                            'url_text'        => 'ORCID',
                                            'advanced'        => 'social',
                                            'color'           => '#A6CE39',
                                            'match'           => 'https://orcid.org/',
                                        );

        return $predefined_fields;
    }

    public function um_account_tab_general_fields_orcid( $args ) {

        if ( ! strpos( $args, ',orcid_id' )) {
            $args = str_replace( ',single_user_password', ',orcid_id,single_user_password', $args );
        }

        return $args;
    }

    public function um_submit_account_general_tab_errors_orcid( $post_form ) {

        if ( isset( $post_form['um_account_nonce_general'] ) && isset( $post_form['orcid_id'] ) ) {

            if ( ! empty( $post_form['orcid_id'] )) {
                $this->um_custom_validate_orcid_id_number( 'orcid_id', array(), $post_form );
            }
        }
    }

    public function um_custom_validate_orcid_id_number( $key, $array, $args ) {

        if ( isset( $args[$key] ) && ! empty( $args[$key] )) {

            $value = str_replace( UM()->builtin()->predefined_fields['orcid_id']['match'], '', $args[$key] );

            // 0000-0001-2345-6789
            if ( strlen( $value ) == 19 ) {

                $groups = explode( '-', $value );
                if ( count( $groups ) == 4 ) {

                    $valid_id = true;
                    foreach( $groups as $group ) {
                        if ( strlen( $group ) != 4 || ! is_numeric( $group )) {
                            $valid_id = false;
                            break;
                        }
                    }

                    if ( $valid_id ) {
                        return;
                    }
                }
            }

            UM()->form()->add_error( $key, __( 'Please enter a valid ORCID ID number of 19 characters like 0000-0001-2345-6789 or the ORCID URL.', 'ultimate-member' ) );
        }
    }

    public function um_account_pre_updating_profile_array_orcid( $changes ) {

        if ( isset( $changes['orcid_id'] ) && ! empty( $changes['orcid_id'] )) {

            if ( substr( $changes['orcid_id'], 0, 18 ) != UM()->builtin()->predefined_fields['orcid_id']['match'] ) {
                if ( strlen( $changes['orcid_id'] ) == 19 ) {
                    $changes['orcid_id'] = UM()->builtin()->predefined_fields['orcid_id']['match'] . $changes['orcid_id'];
                }
            }
        }

        return $changes;
    }

}

new UM_Orcid_Integration();

