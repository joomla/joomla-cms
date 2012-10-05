JInputCli
=========

The JInputCli class is extended from JInput but is tailored to work with
command line input. Once again the get method is used to get values of
command line variables in short name format (one or more individual
characters following a single dash) or long format (a variable name
followed by two dashes). Additional arguments can be found be accessing
the args property of the input object.

An instance of JInputCli will rarely be instantiated directly. Instead,
it would be used implicitly as a part of an application built from
JAppcliationCli as shown in the following example.

    #!/usr/bin/php
    <?php
    /**
     * This file is saved as argv.php
     *
     * @package  Examples
     */

    /**
     * An example command line application.
     *
     * @package  Examples
     * @since    1.0
     */
    class Argv extends JApplicationCli
    {
        /**
         * Execute the application.
         *
         * @return  void
         *
         * @since   1.0
         */
        public function execute()
        {
            var_dump($this->input->get('a'));
            var_dump($this->input->get('set'));
            var_dump($this->input->args);
        }
    }

    > ./argv.php
    bool(false)
    bool(false)
    array(0) {}

    > ./argv.php -a --set=match
    bool(true)
    string(5) "match"
    array(0) {}

    > ./argv.php -a value
    string(5) "value"
    bool(false)
    array(0) {}

    > ./argv.php -a foo bar
    string(3) "foo"
    bool(false)
    array(1) {[0] => string(3) "bar"}
