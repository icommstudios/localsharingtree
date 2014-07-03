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
define('AUTH_KEY',         '5^y|w-DZbj?P%%O9pg)EQf*q&D)a6,InvRfY:#r#7=Wf17YGgllzj*V9cI*HCRRT');
define('SECURE_AUTH_KEY',  'a+xDOu?`|o4<*fF(fO?-L.I:TBm6$v@7-+a:-*TpOB}]wH>aP%i:j.+D_-Ue7Srb');
define('LOGGED_IN_KEY',    'I`|APkIQBV9lF!9R-yl75p]zn5Rca3D~6U;+|J#rDBi;I!wyJ7T?tT:H9ei9&|+A');
define('NONCE_KEY',        '/Y;I TVbfB!zQ6*nX[m.Dp&9vJ|6=&>ack)%]<H{#5>Wa&aB+<+sK5Yt0+q(IRo2');
define('AUTH_SALT',        'xu~S.&-+22++F}xZ<tlA zt[u{zp)XU:92% r{%<N:Vmr2?P^O_bMYUvW+Z&m=.@');
define('SECURE_AUTH_SALT', 'cL&s$g=H @Aln0zJ@Ma]QgV?,2q8P+d$?, o|5zU+9!knwSgltm<%zkrgLiVN~g~');
define('LOGGED_IN_SALT',   '!:D,cgD y1ZC*}Y,&nKSy0ZFhkklWR3?Yj<KtE/|K#l=#5W|d3w U;l^yl?%cmzS');
define('NONCE_SALT',       'F+TEZOoGxNqCgTWy:q~yTd=YhgH@9#~|$,+-B+~hNPv06L+v}H v(~mXT(i[-cJ9');

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
