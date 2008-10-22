##################################################################################
# Migration script; adds modules, plugins and components to the extensions table
 TRUNCATE TABLE jos_extensions; 
 INSERT INTO jos_extensions SELECT 
     0,							# extension id (regenerate)
     name,						# name
     'plugin',					# type
     element,					# element
     folder,                    # folder
     client_id,                 # client_id
     published,                 # enabled 
     access,                    # access
     iscore,                    # protected
     '',                        # manifestcache
     params,                    # params
     '',                        # data
     checked_out,            	# checked_out
     checked_out_time,         	# checked_out_time
     ordering                   # ordering
     FROM jos_plugins;         	# #__extensions replaces the old #__plugins table
     
 INSERT INTO jos_extensions SELECT 
     0,                         # extension id (regenerate)
     name,						# name
     'component',				# type
     `option`,					# element
     '',                        # folder
     0,                         # client id (unused for components)
     enabled,                   # enabled 
     0,                         # access
     iscore,                    # protected
     '',                        # manifest cache
     params,                    # params
     '',                        # data
     '0',                       # checked_out
     '0000-00-00 00:00:00',     # checked_out_time
     0                          # ordering
     FROM jos_components        # #__extensions replaces #__components for install uninstall
                                # component menu selection still utilises the #__components table
     WHERE parent = 0;          # only get top level entries
     
 INSERT INTO jos_extensions SELECT DISTINCT
     0,                         # extension id (regenerate)
     module,                    # name
     'module',                  # type
     `module`,                  # element
     '',                        # folder
     client_id,                 # client id
     1,                         # enabled (module instances may be enabled/disabled in #__modules) 
     0,                         # access (module instance access controlled in #__modules)
     iscore,                    # protected
     '',                        # manifest cache
     '',                        # params (module instance params controlled in #__modules)
     '',                        # data
     '0',                       # checked_out (module instance, see #__modules)
     '0000-00-00 00:00:00',     # checked_out_time (module instance, see #__modules)
     0                          # ordering (module instance, see #__modules)
     FROM jos_modules			# #__extensions provides the install/uninstall control for modules
     WHERE id IN (SELECT id FROM jos_modules GROUP BY module ORDER BY id)     

