protected function setupScriptfile()
{
    // Always reset to avoid leaking between multiple extension installs
    $this->parent->manifestClass = null;

    // If there is a manifest class file, load it
    $manifestScript = (string) $this->getManifest()->scriptfile;

    // When no script file is defined, do nothing
    if (!$manifestScript) {
        return;
    }

    // Build a child container to avoid overwriting the global one
    try {
        $container = new Container($this->getContainer());
    } catch (ContainerNotFoundException $e) {
        @trigger_error('Container must be set.', E_USER_DEPRECATED);

        // Fallback to the global container
        $container = new Container(Factory::getContainer());
    }

    // Get the full path of the manifest script file
    $manifestScriptFile = $this->parent->getPath('source') . '/' . $manifestScript;

    // Ensure the installer file exists
    if (!file_exists($manifestScriptFile)) {
        @trigger_error(
            'Installer file must exist when defined. In version 5.0 this will crash.',
            E_USER_DEPRECATED
        );

        return;
    }

    // Use require instead of require_once to allow multiple extensions to load correctly
    $installer = require $manifestScriptFile;

    // If the returned instance is a service provider, register it
    if ($installer instanceof ServiceProviderInterface) {
        $installer->register($container);
    }

    // If the returned object is an installer instance, use it directly
    if ($installer instanceof InstallerScriptInterface) {
        $container->set(InstallerScriptInterface::class, $installer);
    }

    // Fallback to legacy installer handling
    if (!$container->has(InstallerScriptInterface::class)) {
        @trigger_error(
            'Legacy installer files are deprecated and will be removed in 6.0. Use a service provider instead.',
            E_USER_DEPRECATED
        );

        $classname = $this->getScriptClassName();

        // Prevent class reuse across multiple installs
        if (!class_exists($classname, false)) {
            \JLoader::register($classname, $manifestScriptFile);
        }

        if (!class_exists($classname)) {
            return;
        }

        $container->set(
            InstallerScriptInterface::class,
            function (Container $container) use ($classname) {
                return new LegacyInstallerScript(new $classname($this));
            }
        );
    }

    // Create a fresh instance per extension install
    $this->parent->manifestClass = $container->get(InstallerScriptInterface::class);

    // Set application if supported
    if (method_exists($this->parent->manifestClass, 'setApplication')) {
        $this->parent->manifestClass->setApplication(Factory::getApplication());
    }

    // Set database if supported
    if ($this->parent->manifestClass instanceof DatabaseAwareInterface) {
        $this->parent->manifestClass->setDatabase(
            $container->get(DatabaseInterface::class)
        );
    }

    // Store manifest script reference
    $this->manifest_script = $manifestScript;
}