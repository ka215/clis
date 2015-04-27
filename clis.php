<?php
/*
  Plugin Name: Custom Login Suite
  Plugin URI: http://ka2.org/
  Description: For all the people that want to thoroughly customize around WordPress login.
  Version: 0.0.1
  Author: ka2
  Author URI: http://ka2.org
  Copyright: 2015 monauralsound (email : ka2@ka2.org)
  License: GPL2 - http://www.gnu.org/licenses/gpl.txt
  Text Domain: custom-login-suite
  Domain Path: /langs
*/

/*  Copyright 2015 ka2 (http://ka2.org)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('CLIS_PLUGIN_VERSION', '0.0.1');
define('CLIS_DB_VERSION', '1.0');
define('CLIS', 'custom-login-suite'); // This plugin domain name

require_once plugin_dir_path(__FILE__) . 'functions.php';

CustomLoginSuite\Core\clis( 'set_global' );
