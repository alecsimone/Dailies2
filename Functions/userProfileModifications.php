<?php
add_action( 'show_user_profile', 'my_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'my_show_extra_profile_fields' );

function my_show_extra_profile_fields( $user ) { ?>
 
    <h3>Extra profile information</h3> 
    <table class="form-table">
        <tr>
            <th><label for="rep">Rep</label></th>
            <td>
                <input type="text" name="rep" id="rep" value="<?php echo esc_attr( get_the_author_meta( 'rep', $user->ID ) ); ?>" class="regular-text" /><br />
                <span class="description">Your Rep</span>
            </td>
        </tr>
    </table>
    <table class="form-table">
        <tr>
            <th><label for="customProfilePic">Custom Profile Picture</label></th>
            <td>
                <input type="text" name="customProfilePic" id="customProfilePic" value="<?php echo esc_attr( get_the_author_meta( 'customProfilePic', $user->ID ) ); ?>" class="regular-text" /><br />
                <span class="description">Add a profile picture</span>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <th><label for="customProfilePic">Here's what that looks like: </label></th>
            <td>
            	<img src="<?php echo esc_attr( get_the_author_meta( 'customProfilePic', $user->ID ) ); ?>" class="adminCustomProfilePicture">
            </td>
        </tr>
    </table>    
<?php }

add_action( 'personal_options_update', 'my_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'my_save_extra_profile_fields' );
add_action( 'admin_head', 'custom_profile_pic_css');
function custom_profile_pic_css() {
	echo '<style>img.adminCustomProfilePicture {max-width: 500px; height: auto;}</style>';
}


function my_save_extra_profile_fields( $user_id ) {
 
    if ( !current_user_can( 'edit_users', $user_id ) )
        return false;
    update_user_meta( absint( $user_id ), 'rep', wp_kses_post( $_POST['rep'] ) );
    update_user_meta( absint( $user_id ), 'customProfilePic', wp_kses_post( $_POST['customProfilePic'] ) );
}
?>