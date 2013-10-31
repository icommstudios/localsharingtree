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

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'localst_db');

/** MySQL database username */
define('DB_USER', 'localst_db');

/** MySQL database password */
define('DB_PASSWORD', 'PQ^aPTF^cYMB7f');

/** MySQL hostname */
define('DB_HOST', $_ENV{DATABASE_SERVER});

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
define('AUTH_KEY', 'rumfJ3Pb aqPhmI4p nGvhsRmb GnRncbTe ojd4zQp5');
define('SECURE_AUTH_KEY', 'nPTukxJq HrmyA4gp zAFlqTQY LFxiazc3 qNatB8z2');
define('LOGGED_IN_KEY', 'XXd2HAur v7oJGFlx u7AM8mqO T174zKPI zyd5WlsI');
define('NONCE_KEY', 'jUfiwAl5 NY2wZzdJ VrO25paU XJoYEjxn Glt3TpPh');
define('AUTH_SALT', 'mgyaiE5S VpUBf26U xxJVNySZ 4qAKMLaG IEkPY5uO');
define('SECURE_AUTH_SALT', 'EqcJjzno LHamLS5B InqWCARw 7uKpnkky w3WMZaUz');
define('LOGGED_IN_SALT', 'qx8V4jpM Dze41fyw ZtUybea3 CIFBBcxo wkbB2Wab');
define('NONCE_SALT', 'jyX1PFh1 pHKneFgo ET1sx4Up swWGzxJJ zUjJg7Tq');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_ki';

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

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
