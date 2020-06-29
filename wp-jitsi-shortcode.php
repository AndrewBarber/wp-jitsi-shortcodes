
<?php

/**
* @link              https://andrewbarber.me
* @since             0.1
* @package           wp-jitsi-shortcode
*
* @wordpress-plugin
* Plugin Name: WP Jitsi Shortcodes
* Plugin URI: https://andrewbarber.me
* Description: Allows you to use a simple shortcode to incorperate a Jitsi meeting into your webpage.
* Version: 0.1
* Author: Andrew A. Barber
* Author URI: https://andrewbarber.me/
* License: GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: wp-jitsi-shortcode
**/

if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action('admin_menu', 'jitwp_setup_menu');
function jitwp_setup_menu(){
    add_menu_page( 'Jitsi-Wordpress Settings', 'Jitsi-Wordpress Settings', 'manage_options', 'jitsi-wordpress-settings', 'jitwp_admin_page_init', 'dashicons-format-chat' );
    add_action( 'admin_init', 'jitwp_register_settings' );
}

function jitwp_register_settings(){
    register_setting( 'jitwp', 'jitwp_username_pull' );
    register_setting( 'jitwp', 'jitwp_email_pull' );
    register_setting( 'jitwp', 'jitwp_jwt' );
    register_setting( 'jitwp', 'jitwp_display_chat_above_footer' );
    register_setting( 'jitwp', 'jitwp_url' );
    register_setting( 'jitwp', 'jitwp_default_roomname' );
    register_setting( 'jitwp', 'jitwp_server_url' );
}

function jitwp_admin_page_init(){
    ?>
    <div class="wrap">
        <h1>Jitsi Wordpress Settings</h1>
        <p>If you like this plugin, please consider <a href="https://www.buymeacoffee.com/AndrewBarber" target="_blank">buying me a ☕  Coffee</a>!
        <br/><br/>
       
        <h2>ShortCode</h2>
        <p>You can use the following <a href="https://support.wordpress.com/shortcodes/" target="_blank">ShortCode</a> to create a button to start a new Meeting.</p>
        <p><code>[jitsi]</code></p>
        <p><b>Options</b>..</p>
        <ul>
            <li><i>(Optional)</i> Set the room to join <code>[jitsi roomname="mychatroom"]</code></li>
            <li><i>(Optional)</i> Set the width/height of the chat window<code>[jitsi width="700px" height="700px"]</code>(You can use `em` too.)</li>
            <li><i>(Optional)</i> Set the users name after joining <code>[jitsi username="Andrew A. Barber"]</code></li>
            <li><i>(Optional)</i> Set the users email address<code>[jitsi useremail="email@test.com"]</code></li>
            <li><i>(Optional)</i> Set the JSON Web Token (JWT)<code>[jitsi jwt="XXX"]</code>(You can also set this on backend.)</li>
        </ul>
        <p><b>Example</b>..</p>
        <code>[jitsi width="700px" height="700px" roomname="mychatroom" username="Andrew A. Barber" useremail="email@test.com"]</code>
        <br/><br/>
        <h2>Settings</h2>
        <form method="post" action="options.php">
        <?php settings_fields( 'jitwp' ); ?>
        <?php do_settings_sections( 'jitwp' ); ?>
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">Jitsi Server URL</th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><span>Jitsi Server URL</span></legend>
                                <label for="jitwp_url">
                                    <input type="text" name="jitwp_url" value="<?php echo get_option('jitwp_url') ?>" placeholder="eg. https://meet.domain.tld/" id="jitwp_url" size="100" class="" />
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">JSON Web Token (JWT)</th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><span>JSON Web Token (JWT)</span></legend>
                                <label for="jitwp_jwt">
                                    <input type="text" name="jitwp_jwt" value="<?php echo get_option('jitwp_jwt') ?>" placeholder="XXXXXXXXXXXX" id="jitwp_jwt" size="100" class="" />
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">User Details<br/>(for registered users)</th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><span>User Details for registered users</span></legend>
                                <label for="jitwp_username_pull">
                                    <input name="jitwp_username_pull" type="checkbox" id="jitwp_username_pull" value="1" <?php checked(1, get_option('jitwp_username_pull'), true); ?> />
                                    Use Wordpress username
                                </label>
                                <br />
                                <label for="jitwp_email_pull">
                                    <input name="jitwp_email_pull" type="checkbox" id="jitwp_email_pull" value="1" <?php checked(1, get_option('jitwp_email_pull'), true); ?> />
                                    Use users email address
                                </label>
                            </fieldset>
                            <p><b>NB.</b> These will <b>override</b> anything you have set in the ShortCode settings!</p>
                        </td>
                    </tr>
                  </tbody>
            </table>
            <?php submit_button(); ?>
        </form>
    
    </div>
    <?php
}

/**
 * Cofffffffeeeeeeeeeeeeeeeeeeee is life. ☕
 */
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'jitwp_coffee_link');
function jitwp_coffee_link( $links ) {
	$links[] = '<a href="https://www.buymeacoffee.com/AndrewBarber" target="_blank">☕  Coffee</a>';
	return $links;
}



add_action('wp_enqueue_scripts', 'jitwp_init');
function jitwp_init() {
    $jitwp_url = get_option('jitwp_url');
    if(isset($jitwp_url) === true && strlen($jitwp_url) > 0){
        $jitwp_url = esc_url( trailingslashit(get_option('jitwp_url')));
        $jitwp_jwt =  get_option('jitwp_jwt');
        $jitjwt = isset($jitwp_jwt) === true && strlen($jitwp_jwt) > 0 ? "const jwt = '$jitwp_jwt';" : "const jwt = document.getElementById(`jitwp_shortcode`).getAttribute(`jwt`);";
        
        $userNameDetails = 'const userName = document.getElementById(`jitwp_shortcode`).getAttribute(`username`);';
        $userEmailDetails = 'const userEmail = document.getElementById(`jitwp_shortcode`).getAttribute(`useremail`);';
        $jitwp_username_pull = get_option('jitwp_username_pull');
        $jitwp_email_pull = get_option('jitwp_email_pull');
        if ($jitwp_username_pull || $jitwp_email_pull ){
            $user_id = get_current_user_id(); 
            if($user_id > 0){
                $user_info = get_userdata($user_id);
                $userName = $user_info->user_login;
                $userEmail = $user_info->user_email;
                if ($jitwp_username_pull){
                    $userNameDetails = 'const userName = "'.$userName.'";';
                }
                if ($jitwp_email_pull){
                    $userEmailDetails = 'const userEmail = "'.$userEmail.'";';
                }
            }
           
        }

        wp_enqueue_script( 'jitsi-js', $jitwp_url . 'external_api.js', false );
        echo '
        <script>
        window.onload = () => {
            if(document.getElementById(`jitwp_shortcode`)){
                const roomName = document.getElementById(`jitwp_shortcode`).getAttribute(`roomname`) ;
                '.$jitjwt.'
                const width = document.getElementById(`jitwp_shortcode`).getAttribute(`width`) || `800px`;
                const height = document.getElementById(`jitwp_shortcode`).getAttribute(`height`) || `600px`;
                '.$userNameDetails.'
                '.$userEmailDetails.'
                const domain = `'.str_replace("http://", "", str_replace("https://", "" ,$jitwp_url)).'`;
                let options = {};

                roomName ? options.roomName = roomName : null;
                width ? options.width = width : null;
                height ? options.height = height : null;
                jwt ? options.jwt = jwt : null;
                userName || userEmail ? options.userInfo = {} : null;
                //Below doesn\'t work - https://github.com/jitsi/jitsi-meet/issues/5018
                userName ? options.userInfo.displayName = userName : null;
                userEmail ? options.userInfo.email = userEmail : null;
                
                options.parentNode = document.getElementById(`jitwp_shortcode`)

                const api = new JitsiMeetExternalAPI(domain, options);
                //Quick Fix - https://github.com/jitsi/jitsi-meet/issues/5018
                userName ? api.executeCommand (`displayName`, userName) : null;
                console.log(options);
            }
        }
        </script> 
        ';
    }
}


add_shortcode('jitsi', 'jitwp_shortcode');
function jitwp_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        //To ask... do room names with spaces do what?
        'roomname' => 'default',
        'width' => '800px',
        'height' => '600px',
        'username' => null,
        'useremail' => null,
        'jwt' => '',

    ), $atts, 'jitwp' );
        
    return '<div id="jitwp_shortcode" jwt="'.$atts['jwt'].'" width="'.$atts['width'].'" height="'.$atts['height'].'" roomname="'.$atts['roomname'].'" username="'.$atts['username'].'" useremail="'.$atts['useremail'].'" ></div>';
}
