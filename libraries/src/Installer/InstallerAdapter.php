protected function setupScriptfile()
{
    // 🔥 IMPORTANT: Always reset to avoid leaking between multiple extension installs
    $this->parent->manifestClass = null;

    // If there is a manifest class file, lets load it
    $manifestScript = (string) $this->getManifest()->scriptfile;

    // When no script file, do nothing
    if (!$manifestScript) {
        return;
    }

    // Build a child container, so we do not overwrite the global one
    // and start from scratch when multiple extensions are installed
    try {
        $container = new Container($this->getContainer());
    } catch (ContainerNotFoundException $e) {
        @trigger_error('Container must be set.', E_USER_DEPRECATED);

        // Fallback to the global container
        $container = new Container(Factory::getContainer());
    }

    // The real location of the file
    $manifestScriptFile = $this->parent->getPath('source') . '/' . $manifestScript;

    // Load the installer from the file
    if (!file_exists($manifestScriptFile)) {
        @trigger_error(
            'Installer file must exist when defined. In version 5.0 this will crash.',
            E_USER_DEPRECATED
        );

        return;
    }

    // 🔥 FIX: use require (NOT require_once) so multiple extensions load correctly
    $installer = require $manifestScriptFile;

    // When the instance is a service provider, then register the container with it
    if ($installer instanceof ServiceProviderInterface) {
        $installer->register($container);
    }

    // When the returned object is an installer instance, use it directly
    if ($installer instanceof InstallerScriptInterface) {
        $container->set(InstallerScriptInterface::class, $installer);
    }

    // When none is set, then use the legacy way
    if (!$container->has(InstallerScriptInterface::class)) {
        @trigger_error(
            'Legacy installer files are deprecated and will be removed in 6.0. Use a service provider instead.',
            E_USER_DEPRECATED
        );

        $classname = $this->getScriptClassName();

        // 🔥 FIX: prevent class reuse across multiple installs
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

    // Create a new instance (fresh per extension)
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

    // Store manifest script reference for later use
    $this->manifest_script = $manifestScript;
}