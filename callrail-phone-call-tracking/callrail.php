<?php
/*
Plugin Name: CallRail Phone Call Tracking
Plugin URI: http://www.callrail.com/docs/web-integration/wordpress-plugin/
Description: Dynamically swap CallRail tracking phone numbers based on the visitor's referring source.
Author: CallRail, Inc.
Version: 0.4.9
Author URI: http://www.callrail.com
*/

add_action('admin_menu', 'callrail_menu');
add_action('admin_notices', 'callrail_admin_notice');
add_action('wp_footer', 'callrail_footer');
add_action('wp_loaded', 'callrail_first_party_check');

add_action('rest_api_init', 'calltrk_register_routes');

add_shortcode('callrail_form', 'callrail_form_shortcode_handler');

class CallRail {
  const BASE_URL   = "https://app.callrail.com";
  const SCRIPT_URL = "https://js.callrail.com";
}

/**
 * @return string The IP address of the client.
 */
function callrail_get_user_ip() {
  $keysToCheck = array(
    'HTTP_CLIENT_IP',
    'HTTP_X_FORWARDED_FOR',
    'HTTP_X_FORWARDED',
    'HTTP_X_CLUSTER_CLIENT_IP',
    'HTTP_FORWARDED_FOR',
    'HTTP_FORWARDED',
    'REMOTE_ADDR'
  );
  foreach ( $keysToCheck as $key ) {
    if ( array_key_exists( $key, $_SERVER ) === true ) {
      foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) {
        if ( filter_var( $ip, FILTER_VALIDATE_IP ) !== false ) {
          return $ip;
        }
      }
    }
  }

  // This should never happen because $_SERVER['REMOTE_ADDR'] will always be set.
  return null;
}

function callrail_wp_api_key() {
  $key = trim(get_option('masked_id_and_access_key'));
  return str_replace('x', '/', $key);
}

function callrail_should_use_first_party() {
  return callrail_first_party_usable() && get_option('server_side_dni') == 'true';
}

function calltrk_register_routes() {
  register_rest_route('calltrk/v1', '/store', array(
    'methods'  => 'POST',
    'callback' => 'calltrk_set_cookie',
    'permission_callback' => '__return_true',
  ));

  register_rest_route('calltrk', '/swap.js', array(
    'methods' => 'GET',
    'callback' => 'get_swapjs',
    'permission_callback' => '__return_true'
  ));

  register_rest_route('calltrk', '/sessions/(?P<path>[\S]+)', array(
    'methods' => 'GET',
    'callback' => 'get_session_routes',
    'permission_callback' => '__return_true'
  ));

  register_rest_route('calltrk', '/sessions/(?P<path>[\S]+)', array(
    'methods' => 'POST',
    'callback' => 'post_session_routes',
    'permission_callback' => '__return_true'
  ));
}

function callrail_first_party_check() {
  if (wp_doing_ajax() || !callrail_first_party_usable()) {
    return;
  }

  // only run if first party DNI is not explicitly set, or never tested.
  $first_party = get_option('server_side_dni');
  $first_party_tested_at = get_option('callrail_server_side_dni_tested_at');

  // we have tested the script and it's explicitly marked as enabled or disabled.
  // no need to check again.
  if ($first_party_tested_at && $first_party) {
    return;
  }

  // not configured.
  if (!callrail_wp_api_key()) {
    return;
  }

  // Attempt to fetch the javascript; if it succeeds, enable first-party.
  // If it fails, explicitly disabled first party.
  // This check will not run again unless the 'server_side_dni' option is
  // cleared from the database.
  $contents = callrail_fetch_swap_script_remote();
  update_option('callrail_server_side_dni_tested_at', time());

  if ($contents && preg_match("/CallTrkSwap/", $contents)) {
    update_option('server_side_dni', 'true');
  } else {
    update_option('server_side_dni', 'false');
  }
}

/**
 * @param WP_REST_Request $request
 */
function post_session_routes($request) {
  $bodyJson = json_decode($request->get_body(), true);
  $path = $request['path'];

  if ( $bodyJson === null || json_last_error() !== JSON_ERROR_NONE ) {
    return new WP_Error( 'rest_invalid_json', __( 'Invalid JSON body passed.' ), array(
      'status'             => WP_Http::BAD_REQUEST,
      'json_error_code'    => json_last_error(),
      'json_error_message' => json_last_error_msg(),
    ));
  }

  $bodyJson['end_user_ip'] = callrail_get_user_ip();

  if (strpos($path, 'form_capture') !== false) {
    $url = CallRail::BASE_URL . "/" . $path;
  } else {
    $url = CallRail::SCRIPT_URL . "/" . $path;
  }

  $options = array(
    'http' => array(
      'header'  => "Content-Type: application/json\r\n" .
                   "X-CallTrk-WP-Version: 0.4.9\r\n",
      'method'  => 'POST',
      'content' => json_encode($bodyJson)
    )
  );

  $context = stream_context_create($options);
  $result = file_get_contents($url, false, $context);

  echo $result;
  exit;
}

function get_session_routes($data) {
  header('X-CallTrk-WP-Version: 0.4.9');

  $path = $data['path'];

  // Not happy with this at the moment, I think we should probably have a separate route for this action.
  if (substr($path, 0, 6) === 'forms/') {
    header('Content-Type: text/html; charset=' . get_option('blog_charset'));
  } else {
    header('Content-Type: application/javascript; charset=' . get_option('blog_charset'));
  }

  if (strpos($path, 'form_capture') !== false) {
    $url = CallRail::BASE_URL . "/" . $path;
  } else {
    $url = CallRail::SCRIPT_URL . "/" . $path;
  }

  $script = file_get_contents($url);
  echo $script;
  exit;
}

function callrail_first_party_usable() {
  return ini_get('allow_url_fopen') == 1;
}

/* Fetch the swap.js DNI javascript from CallRail.
 * - Requires that 'allow_url_fopen' feature is enabled in php.ini.
 * - Requires that the server not block remote requests.
 * Returns NULL if the plugin is not configured or if file_get_contents fails.
 */
function callrail_fetch_swap_script_remote() {
  $api_key = callrail_wp_api_key();

  if (!$api_key) {
    return NULL;
  }

  $opts = array(
    'http' => array(
      'method' => "GET",
      'header' => "Accept-language: en\r\n" .
                  "User-agent: callrail_wp_plugin\r\n"
    )
  );

  $context = stream_context_create($opts);
  $swapjs = file_get_contents(CallRail::SCRIPT_URL . "/companies/{$api_key}/12/swap.js",
                              false,
                              $context);

  // file_get_contents may return FALSE for a variety of failure reasons.
  if ($swapjs === FALSE) {
    return NULL;
  }

  return $swapjs;
}

function get_swapjs() {
  header('Content-Type: application/javascript; charset=' . get_option('blog_charset'));
  header('Cache-Control: max-age=60, public');
  header('X-CallTrk-WP-Version: 0.4.9');

  $cached_script = get_transient('cached_swapjs');

  if (is_user_logged_in() || $cached_script === false) {
    $swapjs = callrail_fetch_swap_script_remote();

    if ($swapjs) {
      set_transient('cached_swapjs', $swapjs, 60 * 60);
    }
  } else {
    // if cached script, use it
    $swapjs = $cached_script;
  }

  echo $swapjs;
  exit;
}

function calltrk_set_cookie(WP_REST_Request $request) {
  $response = new WP_REST_Response(array());
  $response->set_status(204);

  $params = $request->get_json_params();

  $domain = $params['domain'];
  $duration = $params['duration'];

  $duration = isset($duration) ? time() + $duration : time() + 3600;

  $keys = array('calltrk_referrer', 'calltrk_landing', 'calltrk_session_id');
  foreach ($keys as $key) {
    if (array_key_exists($key, $params)) {
      setcookie($key, $params[$key], $duration, '/', $domain);
    }
  }

  return $response;
}

function callrail_menu() {
    add_options_page('CallRail Options', 'CallRail', 'manage_options', 'callrail', 'callrail_options');
}

function callrail_admin_notice() {
  $api_key = callrail_wp_api_key();

  $is_plugins_page = (substr($_SERVER["PHP_SELF"], -11) == 'plugins.php');

  if ($is_plugins_page && !$api_key && function_exists("admin_url")) {
    echo '<div class="error"><p><strong>' .
         sprintf(__('<a href="%s">Enter your WordPress Plugin Key</a> to enable dynamic tracking number insertion.', 'callrail'),
                 admin_url('options-general.php?page=callrail')) .
         '</strong></p></div>';
  }
}

function callrail_options() {
  //must check that the user has the required capability
  if (!current_user_can('manage_options')) {
    wp_die( __('You do not have sufficient permissions to access this page.') );
  }

  // Read in existing option value from database
  $masked_id_and_access_key = get_option('masked_id_and_access_key');
  $server_side_dni = get_option('server_side_dni');

  // See if the user has posted us some information
  // If they did, this hidden field will be set to 'Y'
  if(isset($_POST['callrail_hidden_field']) && $_POST['callrail_hidden_field'] == 'Y') {
    // Read their posted value
    $masked_id_and_access_key = trim($_POST['masked_id_and_access_key']);
    $server_side_dni = !empty($_POST['server_side_dni']) ? 'true' : 'false';

    // Change the delimiter from x to /
    // x allows double clicking to copy and paste
    $masked_id_and_access_key = str_replace('x', '/', $masked_id_and_access_key);

    // API key has changed, clear 1st party tested-at timestamp.
    if ($masked_id_and_access_key != callrail_wp_api_key()) {
      delete_option('callrail_server_side_dni_tested_at');
    }

    // Save the posted value in the database
    update_option('masked_id_and_access_key', $masked_id_and_access_key );
    update_option('server_side_dni', $server_side_dni);
    // Put an settings updated message on the screen
    echo '<div class="updated"><p><strong>Your CallRail settings were saved successfully.</strong></p></div>';
  }

  // Before showing it back to the user, change the delimeter from / to x
  $masked_id_and_access_key = str_replace('/', 'x', $masked_id_and_access_key);
?>
    <div class="wrap">
      <h2>CallRail Settings</h2>
      <p>Dynamically swap CallRail phone numbers based on the referring source.</p>
      <form method="POST" action="">
        <input type="hidden" name="callrail_hidden_field" value="Y">
        <table class="form-table" cellpadding="0" cellspacing="0">
          <tr valign="top">
            <th scope="row" style="padding-left: 0px">
              <label for="masked_id_and_access_key">CallRail WordPress Plugin Key</label>
            </th>
            <td>
              <input name="masked_id_and_access_key" type="text" id="masked_id_and_access_key"
                     class="regular-text code" size="20" value="<?php echo $masked_id_and_access_key ?>" />
            </td>
          </tr>
          <tr valign="top">
            <td colspan="2" style="padding-left: 0px">
              <span class="description">You can find this value in your
                <a href="http://app.callrail.com/wordpress" target="_blank">CallRail account</a>.
              </span>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row" style="padding-left: 0px">
              <label for="server_side_dni">Enable As First Party Script</label>
            </th>
            <td>
              <input name="server_side_dni" type="checkbox" id="server_side_dni"
                     class="regular-text code" size="20" value="false"
                     <?php
                       echo (callrail_should_use_first_party() ? 'checked' : '');
                       if (!callrail_first_party_usable()) {
                         echo ' disabled="disabled" title="Your Wordpress installation does not support this feature."';
                       }
                     ?>
              />
            </td>
          </tr>
        </table>
        <p class="submit">
          <input type="submit" name="Submit" class="button-primary" value="Save Changes" />
        </p>
      </form>
    </div>
<?php
}

function callrail_footer() {
  $api_key = callrail_wp_api_key();
  $server_side_dni = get_option('server_side_dni');

  if (!$api_key) {
    return;
  }

  // opt 1% of users into server_side DNI
  if ($server_side_dni === false && callrail_first_party_usable() && explode('/', $api_key)[0] % 100 == 0) {
    update_option('server_side_dni', 'true');
  }

  $server_side_dni = callrail_should_use_first_party();

  echo "\r\n<!-- CallRail WordPress Integration -->\r\n";

  if ($server_side_dni) {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    if (!preg_match("/(Googlebot|HubSpot|Google-Adwords|BingPreview|bingbot|Halebot|ProductAdsBot|Crawler|Google Page Speed|BingPreview|MerchantCentricBot|aiHitBot|spider|Yandex|slurp|MSNBot|SNK Screenshot|Pingdom)/", $user_agent)) {
      echo "<script type=\"text/javascript\">window.crwpVer = 2;</script>";
      echo "<script type=\"text/javascript\" src=\"/index.php?rest_route=/calltrk/swap.js\"></script>\r\n\r\n";
    }
  } else {
    echo "<script type=\"text/javascript\">window.crwpVer = 1;</script>";
    echo "<script type=\"text/javascript\" src=\"//cdn.callrail.com/companies/{$api_key}/12/swap.js\"></script>\r\n\r\n";
  }
}

function callrail_form_shortcode_handler($attributes) {
  $form_id = $attributes['form_id'];

  if (!$form_id) {
    return '';
  }

  return "<div id=\"cr-form-$form_id\"></div>";
}
