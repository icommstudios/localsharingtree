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
define('AUTH_KEY',         '[ck+x%T$B{_q#*MAc^yfUP%_4zcbUo8`1nX;`+=A`:-k1YU/wTG#uSR{4QL(IsY0');
define('SECURE_AUTH_KEY',  'Z9BO3m>evE-(x]g}y$$YonUi(|9h:y. v_PQF!48n3-gf%?.(,*V2oG|`iW38J%;');
define('LOGGED_IN_KEY',    '),7`@(QH7%@MS@GP@|C?)$8RYc5p{u7He9lSWnk|vo79EI;@u#r^U?P6k.uaOqWw');
define('NONCE_KEY',        '6W{diOle|`JQ3|2Y5.4X)U$VRZ}C0G:^jF|~%xhv+C3| eG(zc1lP%8-ov.#{5JU');
define('AUTH_SALT',        'e(4o<RQYKL)tU-,oXJSY{:{&$&O#|-~,&giFH]r]Q; gS8m96,Y<UvNSMftsLlxO');
define('SECURE_AUTH_SALT', 'Ss=tn-Bvk-$A+fQ*npv,Q,]n3;|h|;FFu%O0Z9vs*ICe}PLo^Pw.q%t+BNZu&Tz;');
define('LOGGED_IN_SALT',   'iP^ib+%h2mtkTB`:1EfGd]w,#^A>H-wwrm-TY-#g2B[LD;lX3TGom[f-^22s#0Hu');
define('NONCE_SALT',       '1JpQ^)mnP(+-Tk46w:f._Atp5Z6zB=TJ`i~RWYS^e+uX/XhK$GeAgagq8KEJ=; Z');

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
