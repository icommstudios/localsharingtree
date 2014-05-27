<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

//define( 'GBS_DEV', TRUE );
@ini_set('log_errors','On');
@ini_set('display_errors','Off');
//@ini_set('error_reporting', E_ERROR);
@ini_set('error_log', dirname(__FILE__)  . '/wp-content/error_logs.txt');

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'localst_db_new_test');

/** MySQL database username */
define('DB_USER', 'localst_db_new2');

/** MySQL database password */
define('DB_PASSWORD', '76IU%YG&t9');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'k)**1m_{V$%h[o7WsA;Q`-jdY1v/:3=62-5>^I%.U.MjF,h}rdO*xz7Bn;({oeGf');
define('SECURE_AUTH_KEY',  'aAswHz>gs4N2y.ha(XRb|%EL{TAA+6(xv-gkin2eb<lY}VrBE4$7=AH@tb-g-*-~');
define('LOGGED_IN_KEY',    'ud#oL 9O4RyunDtN)m9Q6i{KWD.$XrL$bpt;yl%7l31h)h~23@1(yJ80a;2L7Aec');
define('NONCE_KEY',        'BpF;h+o(RN)-(FL1]~6OG#Pe+vp()`MOuZDH;-7dFV!2>GmJrH+K;6jWq}Gg!%[-');
define('AUTH_SALT',        'N3e+]yhaXC <HrHR|7U0hYtBW,c.8Z^8TQI7^Mwc=BW$#{I3B(1t(-waE`lwEHrx');
define('SECURE_AUTH_SALT', 'iD0q=zHe@eNJ@yUTl$+++zpgP~:~0~:n&s7pu3WdtU?l/q}*4cq~de%FZE$ 8RT.');
define('LOGGED_IN_SALT',   '+?gFpki58+lraT[BB5+_Xq;B}4|6o>wPAOCVZ)Wf L{;@TXX+!5vc7FyF#jB]K::');
define('NONCE_SALT',       '/ZOwSuNumwaRm3:7z_[?(*4r@@>OiBoSA!^1S@U2hMAewM1~W7o!{Hi&y$gs+.H+');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'deals_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/*
	Disable WP Theme Editor
*/
define('DISALLOW_FILE_EDIT', true);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
