<?php
/*************************************************************************************
 * php.php
 * --------
 * Author: Nigel McNie (oracle.shinoda@gmail.com)
 * Copyright: (c) 2004 Nigel McNie (http://qbnz.com/highlighter/)
 * Release Version: 1.0.7.5
 * CVS Revision Version: $Revision: 1.6 $
 * Date Started: 2004/06/20
 * Last Modified: $Date: 2005/10/22 07:52:59 $
 *
 * PHP language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2004/11/25 (1.0.3)
 *  -  Added support for multiple object splitters
 *  -  Fixed &new problem
 * 2004/10/27 (1.0.2)
 *  -  Added URL support
 *  -  Added extra constants
 * 2004/08/05 (1.0.1)
 *  -  Added support for symbols
 * 2004/07/14 (1.0.0)
 *  -  First Release
 *
 * TODO (updated 2004/07/14)
 * -------------------------
 * * Make sure the last few function I may have missed
 *   (like eval()) are included for highlighting
 * * Split to several files - php4, php5 etc
 *
 *************************************************************************************
 *
 *     This file is part of GeSHi.
 *
 *   GeSHi is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   GeSHi is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with GeSHi; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ************************************************************************************/

$language_data = array (
	'LANG_NAME' => 'PHP',
	'COMMENT_SINGLE' => array(1 => '//', 2 => '#'),
	'COMMENT_MULTI' => array('/*' => '*/'),
	'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
	'QUOTEMARKS' => array("'", '"'),
	'ESCAPE_CHAR' => '\\',
	'KEYWORDS' => array(
		1 => array(
			'include', 'require', 'include_once', 'require_once',
			'for', 'foreach', 'as', 'if', 'elseif', 'else', 'while', 'do', 'endwhile',
            'endif', 'switch', 'case', 'endswitch',
			'return', 'break', 'continue'
			),
		2 => array(
			'null', '__LINE__', '__FILE__',
			'false', '&lt;?php', '?&gt;', '&lt;?',
			'&lt;script language', '&lt;/script&gt;',
			'true', 'var', 'default',
			'function', 'class', 'new', '&amp;new',
			'__FUNCTION__', '__CLASS__', '__METHOD__', 'PHP_VERSION',
			'PHP_OS', 'DEFAULT_INCLUDE_PATH', 'PEAR_INSTALL_DIR', 'PEAR_EXTENSION_DIR',
			'PHP_EXTENSION_DIR', 'PHP_BINDIR', 'PHP_LIBDIR', 'PHP_DATADIR', 'PHP_SYSCONFDIR',
			'PHP_LOCALSTATEDIR', 'PHP_CONFIG_FILE_PATH', 'PHP_OUTPUT_HANDLER_START', 'PHP_OUTPUT_HANDLER_CONT',
			'PHP_OUTPUT_HANDLER_END', 'E_ERROR', 'E_WARNING', 'E_PARSE', 'E_NOTICE',
			'E_CORE_ERROR', 'E_CORE_WARNING', 'E_COMPILE_ERROR', 'E_COMPILE_WARNING', 'E_USER_ERROR',
			'E_USER_WARNING', 'E_USER_NOTICE', 'E_ALL'
			),
		3 => array(
			'zlib_get_coding_type','zend_version','zend_logo_guid','yp_order','yp_next',
			'yp_match','yp_master','yp_get_default_domain','yp_first','yp_errno','yp_err_string',
			'yp_cat','yp_all','xml_set_unparsed_entity_decl_handler','xml_set_start_namespace_decl_handler','xml_set_processing_instruction_handler','xml_set_object',
			'xml_set_notation_decl_handler','xml_set_external_entity_ref_handler','xml_set_end_namespace_decl_handler','xml_set_element_handler','xml_set_default_handler','xml_set_character_data_handler',
			'xml_parser_set_option','xml_parser_get_option','xml_parser_free','xml_parser_create_ns','xml_parser_create','xml_parse_into_struct',
			'xml_parse','xml_get_error_code','xml_get_current_line_number','xml_get_current_column_number','xml_get_current_byte_index','xml_error_string',
			'wordwrap','wddx_serialize_vars','wddx_serialize_value','wddx_packet_start','wddx_packet_end','wddx_deserialize',
			'wddx_add_vars','vsprintf','vprintf','virtual','version_compare','var_export',
			'var_dump','utf8_encode','utf8_decode','usort','usleep','user_error',
			'urlencode','urldecode','unserialize','unregister_tick_function','unpack','unlink',
			'unixtojd','uniqid','umask','uksort','ucwords','ucfirst',
			'uasort','trim','trigger_error','touch','token_name','token_get_all',
			'tmpfile','time','textdomain','tempnam','tanh','tan',
			'system','syslog','symlink','substr_replace','substr_count','substr',
			'strval','strtr','strtoupper','strtotime','strtolower','strtok',
			'strstr','strspn','strrpos','strrev','strrchr','strpos',
			'strncmp','strncasecmp','strnatcmp','strnatcasecmp','strlen','stristr',
			'stripslashes','stripcslashes','strip_tags','strftime','stream_wrapper_register','stream_set_write_buffer',
			'stream_set_timeout','stream_set_blocking','stream_select','stream_register_wrapper','stream_get_meta_data','stream_filter_prepend',
			'stream_filter_append','stream_context_set_params','stream_context_set_option','stream_context_get_options','stream_context_create','strcspn',
			'strcoll','strcmp','strchr','strcasecmp','str_word_count','str_shuffle',
			'str_rot13','str_replace','str_repeat','str_pad','stat','sscanf',
			'srand','sqrt','sql_regcase','sprintf','spliti','split',
			'soundex','sort','socket_writev','socket_write','socket_strerror','socket_shutdown',
			'socket_setopt','socket_set_timeout','socket_set_option','socket_set_nonblock','socket_set_blocking','socket_set_block',
			'socket_sendto','socket_sendmsg','socket_send','socket_select','socket_recvmsg','socket_recvfrom',
			'socket_recv','socket_readv','socket_read','socket_listen','socket_last_error','socket_iovec_set',
			'socket_iovec_free','socket_iovec_fetch','socket_iovec_delete','socket_iovec_alloc','socket_iovec_add','socket_getsockname',
			'socket_getpeername','socket_getopt','socket_get_status','socket_get_option','socket_create_pair','socket_create_listen',
			'socket_create','socket_connect','socket_close','socket_clear_error','socket_bind','socket_accept',
			'sleep','sizeof','sinh','sin','similar_text','shuffle',
			'show_source','shmop_write','shmop_size','shmop_read','shmop_open','shmop_delete',
			'shmop_close','shm_remove_var','shm_remove','shm_put_var','shm_get_var','shm_detach',
			'shm_attach','shell_exec','sha1_file','sha1','settype','setlocale',
			'setcookie','set_time_limit','set_socket_blocking','set_magic_quotes_runtime','set_include_path','set_file_buffer',
			'set_error_handler','session_write_close','session_unset','session_unregister','session_start','session_set_save_handler',
			'session_set_cookie_params','session_save_path','session_register','session_regenerate_id','session_name','session_module_name',
			'session_is_registered','session_id','session_get_cookie_params','session_encode','session_destroy','session_decode',
			'session_cache_limiter','session_cache_expire','serialize','sem_remove','sem_release','sem_get',
			'sem_acquire','rtrim','rsort','round','rmdir','rewinddir',
			'rewind','restore_include_path','restore_error_handler','reset','rename','register_tick_function',
			'register_shutdown_function','realpath','readlink','readgzfile','readfile','readdir',
			'read_exif_data','rawurlencode','rawurldecode','range','rand','rad2deg',
			'quotemeta','quoted_printable_decode','putenv','proc_open','proc_close','printf',
			'print_r','prev','preg_split','preg_replace_callback','preg_replace','preg_quote',
			'preg_match_all','preg_match','preg_grep','pow','posix_uname','posix_ttyname',
			'posix_times','posix_strerror','posix_setuid','posix_setsid','posix_setpgid','posix_setgid',
			'posix_seteuid','posix_setegid','posix_mkfifo','posix_kill','posix_isatty','posix_getuid',
			'posix_getsid','posix_getrlimit','posix_getpwuid','posix_getpwnam','posix_getppid','posix_getpid',
			'posix_getpgrp','posix_getpgid','posix_getlogin','posix_getgroups','posix_getgrnam','posix_getgrgid',
			'posix_getgid','posix_geteuid','posix_getegid','posix_getcwd','posix_get_last_error','posix_errno',
			'posix_ctermid','pos','popen','pi','phpversion','phpinfo',
			'phpcredits','php_uname','php_sapi_name','php_logo_guid','php_ini_scanned_files','pg_update',
			'pg_untrace','pg_unescape_bytea','pg_tty','pg_trace','pg_setclientencoding','pg_set_client_encoding',
			'pg_send_query','pg_select','pg_result_status','pg_result_seek','pg_result_error','pg_result',
			'pg_query','pg_put_line','pg_port','pg_ping','pg_pconnect','pg_options',
			'pg_numrows','pg_numfields','pg_num_rows','pg_num_fields','pg_meta_data','pg_lowrite',
			'pg_lounlink','pg_loreadall','pg_loread','pg_loopen','pg_loimport','pg_loexport',
			'pg_locreate','pg_loclose','pg_lo_write','pg_lo_unlink','pg_lo_tell','pg_lo_seek',
			'pg_lo_read_all','pg_lo_read','pg_lo_open','pg_lo_import','pg_lo_export','pg_lo_create',
			'pg_lo_close','pg_last_oid','pg_last_notice','pg_last_error','pg_insert','pg_host',
			'pg_getlastoid','pg_get_result','pg_get_pid','pg_get_notify','pg_freeresult','pg_free_result',
			'pg_fieldtype','pg_fieldsize','pg_fieldprtlen','pg_fieldnum','pg_fieldname','pg_fieldisnull',
			'pg_field_type','pg_field_size','pg_field_prtlen','pg_field_num','pg_field_name','pg_field_is_null',
			'pg_fetch_row','pg_fetch_result','pg_fetch_object','pg_fetch_assoc','pg_fetch_array','pg_fetch_all',
			'pg_exec','pg_escape_string','pg_escape_bytea','pg_errormessage','pg_end_copy','pg_delete',
			'pg_dbname','pg_copy_to','pg_copy_from','pg_convert','pg_connection_status','pg_connection_reset',
			'pg_connection_busy','pg_connect','pg_cmdtuples','pg_close','pg_clientencoding','pg_client_encoding',
			'pg_cancel_query','pg_affected_rows','pfsockopen','pclose','pathinfo','passthru',
			'parse_url','parse_str','parse_ini_file','pack','overload','output_reset_rewrite_vars',
			'output_add_rewrite_var','ord','openssl_x509_read','openssl_x509_parse','openssl_x509_free','openssl_x509_export_to_file',
			'openssl_x509_export','openssl_x509_checkpurpose','openssl_x509_check_private_key','openssl_verify','openssl_sign','openssl_seal',
			'openssl_public_encrypt','openssl_public_decrypt','openssl_private_encrypt','openssl_private_decrypt','openssl_pkey_new','openssl_pkey_get_public',
			'openssl_pkey_get_private','openssl_pkey_free','openssl_pkey_export_to_file','openssl_pkey_export','openssl_pkcs7_verify','openssl_pkcs7_sign',
			'openssl_pkcs7_encrypt','openssl_pkcs7_decrypt','openssl_open','openssl_get_publickey','openssl_get_privatekey','openssl_free_key',
			'openssl_error_string','openssl_csr_sign','openssl_csr_new','openssl_csr_export_to_file','openssl_csr_export','openlog',
			'opendir','octdec','ob_start','ob_list_handlers','ob_implicit_flush','ob_iconv_handler',
			'ob_gzhandler','ob_get_status','ob_get_level','ob_get_length','ob_get_flush','ob_get_contents',
			'ob_get_clean','ob_flush','ob_end_flush','ob_end_clean','ob_clean','number_format',
			'nl_langinfo','nl2br','ngettext','next','natsort','natcasesort',
			'mysql_unbuffered_query','mysql_thread_id','mysql_tablename','mysql_table_name','mysql_stat','mysql_selectdb',
			'mysql_select_db','mysql_result','mysql_real_escape_string','mysql_query','mysql_ping','mysql_pconnect',
			'mysql_numrows','mysql_numfields','mysql_num_rows','mysql_num_fields','mysql_listtables','mysql_listfields',
			'mysql_listdbs','mysql_list_tables','mysql_list_processes','mysql_list_fields','mysql_list_dbs','mysql_insert_id',
			'mysql_info','mysql_get_server_info','mysql_get_proto_info','mysql_get_host_info','mysql_get_client_info','mysql_freeresult',
			'mysql_free_result','mysql_fieldtype','mysql_fieldtable','mysql_fieldname','mysql_fieldlen','mysql_fieldflags',
			'mysql_field_type','mysql_field_table','mysql_field_seek','mysql_field_name','mysql_field_len','mysql_field_flags',
			'mysql_fetch_row','mysql_fetch_object','mysql_fetch_lengths','mysql_fetch_field','mysql_fetch_assoc','mysql_fetch_array',
			'mysql_escape_string','mysql_error','mysql_errno','mysql_dropdb','mysql_drop_db','mysql_dbname',
			'mysql_db_query','mysql_db_name','mysql_data_seek','mysql_createdb','mysql_create_db','mysql_connect',
			'mysql_close','mysql_client_encoding','mysql_affected_rows','mysql','mt_srand','mt_rand',
			'mt_getrandmax','move_uploaded_file','money_format','mktime','mkdir','min',
			'microtime','method_exists','metaphone','memory_get_usage','md5_file','md5',
			'mbsubstr','mbstrrpos','mbstrpos','mbstrlen','mbstrcut','mbsplit',
			'mbregex_encoding','mberegi_replace','mberegi','mbereg_search_setpos','mbereg_search_regs','mbereg_search_pos',
			'mbereg_search_init','mbereg_search_getregs','mbereg_search_getpos','mbereg_search','mbereg_replace','mbereg_match',
			'mbereg','mb_substr_count','mb_substr','mb_substitute_character','mb_strwidth','mb_strtoupper',
			'mb_strtolower','mb_strrpos','mb_strpos','mb_strlen','mb_strimwidth','mb_strcut',
			'mb_split','mb_send_mail','mb_regex_set_options','mb_regex_encoding','mb_preferred_mime_name','mb_parse_str',
			'mb_output_handler','mb_language','mb_internal_encoding','mb_http_output','mb_http_input','mb_get_info',
			'mb_eregi_replace','mb_eregi','mb_ereg_search_setpos','mb_ereg_search_regs','mb_ereg_search_pos','mb_ereg_search_init',
			'mb_ereg_search_getregs','mb_ereg_search_getpos','mb_ereg_search','mb_ereg_replace','mb_ereg_match','mb_ereg',
			'mb_encode_numericentity','mb_encode_mimeheader','mb_detect_order','mb_detect_encoding','mb_decode_numericentity','mb_decode_mimeheader',
			'mb_convert_variables','mb_convert_kana','mb_convert_encoding','mb_convert_case','max','mail',
			'magic_quotes_runtime','ltrim','lstat','long2ip','log1p','log10',
			'log','localtime','localeconv','linkinfo','link','levenshtein',
			'lcg_value','ksort','krsort','key_exists','key','juliantojd',
			'join','jewishtojd','jdtounix','jdtojulian','jdtojewish','jdtogregorian',
			'jdtofrench','jdmonthname','jddayofweek','is_writeable','is_writable','is_uploaded_file',
			'is_subclass_of','is_string','is_scalar','is_resource','is_real','is_readable',
			'is_object','is_numeric','is_null','is_nan','is_long','is_link',
			'is_integer','is_int','is_infinite','is_float','is_finite','is_file',
			'is_executable','is_double','is_dir','is_callable','is_bool','is_array',
			'is_a','iptcparse','iptcembed','ip2long','intval','ini_set',
			'ini_restore','ini_get_all','ini_get','ini_alter','in_array','import_request_variables',
			'implode','image_type_to_mime_type','ignore_user_abort','iconv_set_encoding','iconv_get_encoding','iconv',
			'i18n_mime_header_encode','i18n_mime_header_decode','i18n_ja_jp_hantozen','i18n_internal_encoding','i18n_http_output','i18n_http_input',
			'i18n_discover_encoding','i18n_convert','hypot','htmlspecialchars','htmlentities','html_entity_decode',
			'highlight_string','highlight_file','hexdec','hebrevc','hebrev','headers_sent',
			'header','gzwrite','gzuncompress','gztell','gzseek','gzrewind',
			'gzread','gzputs','gzpassthru','gzopen','gzinflate','gzgetss',
			'gzgets','gzgetc','gzfile','gzeof','gzencode','gzdeflate',
			'gzcompress','gzclose','gregoriantojd','gmstrftime','gmmktime','gmdate',
			'glob','gettype','gettimeofday','gettext','getservbyport','getservbyname',
			'getrusage','getrandmax','getprotobynumber','getprotobyname','getopt','getmyuid',
			'getmypid','getmyinode','getmygid','getmxrr','getlastmod','getimagesize',
			'gethostbynamel','gethostbyname','gethostbyaddr','getenv','getdate','getcwd',
			'getallheaders','get_resource_type','get_required_files','get_parent_class','get_object_vars','get_meta_tags',
			'get_magic_quotes_runtime','get_magic_quotes_gpc','get_loaded_extensions','get_included_files','get_include_path','get_html_translation_table',
			'get_extension_funcs','get_defined_vars','get_defined_functions','get_defined_constants','get_declared_classes','get_current_user',
			'get_class_vars','get_class_methods','get_class','get_cfg_var','get_browser','fwrite',
			'function_exists','func_num_args','func_get_args','func_get_arg','ftruncate','ftp_systype',
			'ftp_ssl_connect','ftp_size','ftp_site','ftp_set_option','ftp_rmdir','ftp_rename',
			'ftp_rawlist','ftp_quit','ftp_pwd','ftp_put','ftp_pasv','ftp_nlist',
			'ftp_nb_put','ftp_nb_get','ftp_nb_fput','ftp_nb_fget','ftp_nb_continue','ftp_mkdir',
			'ftp_mdtm','ftp_login','ftp_get_option','ftp_get','ftp_fput','ftp_fget',
			'ftp_exec','ftp_delete','ftp_connect','ftp_close','ftp_chdir','ftp_cdup',
			'ftok','ftell','fstat','fsockopen','fseek','fscanf',
			'frenchtojd','fread','fputs','fpassthru','fopen','fnmatch',
			'fmod','flush','floor','flock','floatval','filetype',
			'filesize','filepro_rowcount','filepro_retrieve','filepro_fieldwidth','filepro_fieldtype','filepro_fieldname',
			'filepro_fieldcount','filepro','fileperms','fileowner','filemtime','fileinode',
			'filegroup','filectime','fileatime','file_get_contents','file_exists','file',
			'fgetss','fgets','fgetcsv','fgetc','fflush','feof',
			'fclose','ezmlm_hash','extract','extension_loaded','expm1','explode',
			'exp','exif_thumbnail','exif_tagname','exif_read_data','exif_imagetype','exec',
			'escapeshellcmd','escapeshellarg','error_reporting','error_log','eregi_replace','eregi',
			'ereg_replace','ereg','end','easter_days','easter_date','each',
			'doubleval','dngettext','dl','diskfreespace','disk_total_space','disk_free_space',
			'dirname','dir','dgettext','deg2rad','defined','define_syslog_variables',
			'define','decoct','dechex','decbin','debug_zval_dump','debug_backtrace',
			'deaggregate','dcngettext','dcgettext','dba_sync','dba_replace','dba_popen',
			'dba_optimize','dba_open','dba_nextkey','dba_list','dba_insert','dba_handlers',
			'dba_firstkey','dba_fetch','dba_exists','dba_delete','dba_close','date',
			'current','ctype_xdigit','ctype_upper','ctype_space','ctype_punct','ctype_print',
			'ctype_lower','ctype_graph','ctype_digit','ctype_cntrl','ctype_alpha','ctype_alnum',
			'crypt','create_function','crc32','count_chars','count','cosh',
			'cos','copy','convert_cyr_string','constant','connection_status','connection_aborted',
			'compact','closelog','closedir','clearstatcache','class_exists','chunk_split',
			'chr','chown','chop','chmod','chgrp','checkdnsrr',
			'checkdate','chdir','ceil','call_user_method_array','call_user_method','call_user_func_array',
			'call_user_func','cal_to_jd','cal_info','cal_from_jd','cal_days_in_month','bzwrite',
			'bzread','bzopen','bzflush','bzerrstr','bzerror','bzerrno',
			'bzdecompress','bzcompress','bzclose','bindtextdomain','bindec','bind_textdomain_codeset',
			'bin2hex','bcsub','bcsqrt','bcscale','bcpow','bcmul',
			'bcmod','bcdiv','bccomp','bcadd','basename','base_convert',
			'base64_encode','base64_decode','atanh','atan2','atan','assert_options',
			'assert','asort','asinh','asin','arsort','array_walk',
			'array_values','array_unshift','array_unique','array_sum','array_splice','array_slice',
			'array_shift','array_search','array_reverse','array_reduce','array_rand','array_push',
			'array_pop','array_pad','array_multisort','array_merge_recursive','array_merge','array_map',
			'array_keys','array_key_exists','array_intersect_assoc','array_intersect','array_flip','array_filter',
			'array_fill','array_diff_assoc','array_diff','array_count_values','array_chunk','array_change_key_case',
			'apache_setenv','apache_response_headers','apache_request_headers','apache_note','apache_lookup_uri','apache_get_version',
			'apache_child_terminate','aggregation_info','aggregate_properties_by_regexp','aggregate_properties_by_list','aggregate_properties','aggregate_methods_by_regexp',
			'aggregate_methods_by_list','aggregate_methods','aggregate','addslashes','addcslashes','acosh',
			'acos','abs','_','echo', 'print', 'global', 'static', 'exit', 'array', 'empty',
            'eval', 'isset', 'unset', 'die', 'list'
			)
		),
	'SYMBOLS' => array(
		'(', ')', '[', ']', '{', '}', '!', '@', '%', '&', '*', '|', '/', '<', '>'
		),
	'CASE_SENSITIVE' => array(
		GESHI_COMMENTS => false,
		1 => false,
		2 => false,
		3 => false,
		),
	'STYLES' => array(
		'KEYWORDS' => array(
			1 => 'color: #b1b100;',
			2 => 'color: #000000; font-weight: bold;',
			3 => 'color: #000066;'
			),
		'COMMENTS' => array(
			1 => 'color: #808080; font-style: italic;',
			2 => 'color: #808080; font-style: italic;',
			'MULTI' => 'color: #808080; font-style: italic;'
			),
		'ESCAPE_CHAR' => array(
			0 => 'color: #000099; font-weight: bold;'
			),
		'BRACKETS' => array(
			0 => 'color: #66cc66;'
			),
		'STRINGS' => array(
			0 => 'color: #ff0000;'
			),
		'NUMBERS' => array(
			0 => 'color: #cc66cc;'
			),
		'METHODS' => array(
			1 => 'color: #006600;',
			2 => 'color: #006600;'
			),
		'SYMBOLS' => array(
			0 => 'color: #66cc66;'
			),
		'REGEXPS' => array(
			0 => 'color: #0000ff;'
			),
		'SCRIPT' => array(
			0 => '',
			1 => '',
			2 => '',
			3 => ''
			)
		),
	'URLS' => array(
		1 => '',
		2 => '',
		3 => 'http://www.php.net/{FNAME}',
		4 => ''
		),
	'OOLANG' => true,
	'OBJECT_SPLITTERS' => array(
		1 => '-&gt;',
		2 => '::'
		),
	'REGEXPS' => array(
		0 => "[\\$]{1,2}[a-zA-Z_][a-zA-Z0-9_]*",
		),
	'STRICT_MODE_APPLIES' => GESHI_MAYBE,
	'SCRIPT_DELIMITERS' => array(
		0 => array(
			'<?php' => '?>'
			),
		1 => array(
			'<?' => '?>'
			),
		2 => array(
			'<%' => '%>'
			),
		3 => array(
			'<script language="php">' => '</script>'
			)
		),
	'HIGHLIGHT_STRICT_BLOCK' => array(
		0 => true,
		1 => true,
		2 => true,
		3 => true
		)
);

?>