<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'poli_db' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'a{-w%41jnG(Y_NJ?EvUH_}1TU13rNTvZH!2!1*<CtFKw,DegE:]lK_q.QOZh6]]S' );
define( 'SECURE_AUTH_KEY',  'qN:].w!ZpzI6mUAQ2$cVVpY<TfYV/}FPz*Hw*Q3e9BFg2 GbC%l5$E.2KNuB( ;d' );
define( 'LOGGED_IN_KEY',    '`TE5<t}.Rp[<Q)qC$Z6CnJM&<Ei)04Jw=7l(Dh,IsHmQy[G#`&LiX_bS:B?njvY?' );
define( 'NONCE_KEY',        'N5yj?ZpsvDB99vVU%@Duz;=;?%KI/c /W?e}A@N;S=77O/3hk<$QHMnEd3Sg_YnV' );
define( 'AUTH_SALT',        'G)xyMtcTaL{[LRE_bBoqusd_hbN@e}8/Ayr>ni=j(z><8$N:S~(a#P%@I#UQ)}6I' );
define( 'SECURE_AUTH_SALT', 'TS*gS2S{o${A[Xd5WBqT2&yOp*4Cj.J)[HkcueZNt$?C$po1/((c-q)Nf%mWfx;^' );
define( 'LOGGED_IN_SALT',   'dkl~<,o_ hj }GJou6M:a#&z8k6T0:]zV.4,@gdBqk(.G)Z2kW:dd/2Z7($Q =&c' );
define( 'NONCE_SALT',       '1_`vd>Vpv,`oi2q3i`18VG`5)FeAYsWGLgF=Ei?yPDD9dDaV2KnadA<%qhcV_<+2' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
