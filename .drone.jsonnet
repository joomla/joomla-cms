local volumes = [
    {
        name: "composer-cache",
        path: "/tmp/composer-cache",
    },
];

local hostvolumes = [
    {
        name: "composer-cache",
        host: {path: "/tmp/composer-cache"}
    },
];

local composer(phpversion) = {
    name: "composer",
    image: "joomlaprojects/docker-images:php" + phpversion,
    volumes: volumes,
    commands: ["php -v", "composer install"]
};

local prepare(phpversion) = {
	name: "prepare",
	image: "joomlaprojects/docker-images:php" + phpversion,
	environment: {PGPASSWORD: "joomla_ut"},
	commands: [
		"php -v",
		"sleep 20",
		"mysql --host=mysql --user=joomla_ut --password=joomla_ut --database=joomla_ut < \"tests/unit/schema/mysql.sql\"",
		"psql -h postgres -d joomla_ut -U joomla_ut -a -f \"tests/unit/schema/postgresql.sql\""
	]
};

local phpunit(phpversion, ignore_result) = {
    name: "PHPUnit",
    image: "joomlaprojects/docker-images:php" + phpversion,
	[if ignore_result then "failure"]: "ignore",
    commands: ["libraries/vendor/bin/phpunit"]
};

local pipeline(phpversion, ignore_result) = {
    kind: "pipeline",
    name: "PHP " + phpversion,
    volumes: hostvolumes,
    steps: [
        composer(phpversion),
		prepare(phpversion),
        phpunit(phpversion, ignore_result)
    ],
	services: [
		{
			name: "mysql",
			image: "mysql:5.7",
			environment: {
				MYSQL_USER: "joomla_ut",
				MYSQL_PASSWORD: "joomla_ut",
				MYSQL_ROOT_PASSWORD: "joomla_ut",
				MYSQL_DATABASE: "joomla_ut"
			}
		},
		{
			name: "postgres",
			image: "postgres:11-alpine",
			ports: [5432],
			environment: {
				POSTGRES_USER: "joomla_ut",
				POSTGRES_PASSWORD: "joomla_ut",
				POSTGRES_DB: "joomla_ut"
			}
		},
		{
			name: "memcached",
			image: "memcached:alpine"
		},
		{
			name:"redis",
			image: "redis:alpine"
		}
	]
};

[
    {
        kind: "pipeline",
        name: "Codequality",
        volumes: hostvolumes,
        steps: [
            {
                name: "composer",
                image: "joomlaprojects/docker-images:php7.4",
                volumes: volumes,
                commands: [
                    "php -v",
                    "composer install",
                    "composer require phpmd/phpmd"
                ]
            },
			{
				name: "phpcs",
				image: "joomlaprojects/docker-images:php7.2",
				commands: [
					"php -v",
					"libraries/vendor/bin/phpcs --report=full --encoding=utf-8 --extensions=php -p --standard=build/phpcs/Joomla ."
				]
			},
			{
				name: "javascript",
				image: "joomlaprojects/docker-images:systemtests",
				commands: [
					"echo $(date)",
					"export DISPLAY=:0",
					"Xvfb -screen 0 1024x768x24 -ac +extension GLX +render -noreset > /dev/null 2>&1 &",
					"sleep 3",
					"fluxbox  > /dev/null 2>&1 &",
					"cd tests/javascript",
					"npm install",
					"cd ../..",
					"tests/javascript/node_modules/karma/bin/karma start karma.conf.js --single-run",
					"echo $(date)"
				]
			}
        ]
    },
    pipeline("5.3", false),
    pipeline("5.4", false),
    pipeline("5.5", false),
    pipeline("5.6", false),
    pipeline("7.0", false),
    pipeline("7.1", false),
    pipeline("7.2", false),
    pipeline("7.3", false),
    pipeline("7.4", false),
	pipeline("8.0", true),
	{
		kind: "pipeline",
		name: "package",
		steps: [
			{
				name: "packager",
				image: "joomlaprojects/docker-images:packager",
				environment: {
					FTP_USERNAME: {from_secret: "ftpusername"},
					FTP_PASSWORD: {from_secret: "ftppassword"},
					FTP_HOSTNAME: "ci.joomla.org",
					FTP_PORT: "21",
					FTP_DEST_DIR: "/artifacts",
					FTP_VERIFY: "false",
					FTP_SECURE: "true",
					HTTP_ROOT: "https://ci.joomla.org/artifacts",
					DRONE_PULL_REQUEST: "DRONE_PULL_REQUEST",
					DRONE_COMMIT: "DRONE_COMMIT",
					GITHUB_TOKEN: {from_secret: "github_token"}
				},
				commands: [
					"if [ $DRONE_REPO_NAME != 'joomla-cms' ]; then echo \"The packager only runs on the joomla/joomla-cms repo\"; exit 0; fi",
					"/bin/drone_build.sh"
				]
			}
		]
	},
	{
		kind: "pipeline",
		name: "Rips",
		steps: [
			{
				name: "analysis3x",
				image: "rips/rips-cli:3.2.2",
				when: {
					repo: ["joomla/joomla-cms", "joomla/cms-security"],
					branch: ["staging"]
				},
				commands: [
					"export RIPS_BASE_URI='https://api.rips.joomla.org'",
					"rips-cli rips:list --table=scans --parameter filter='{\"__and\":[{\"__lessThan\":{\"percent\":100}}]}'",
					"rips-cli rips:scan:start --progress --application=1 --threshold=0 --path=$(pwd) --remove-code --remove-upload --tag=$DRONE_REPO_NAMESPACE-$DRONE_BRANCH || { echo \"Please contact the security team at security@joomla.org\"; exit 1; }"
				],
				environment: {
					RIPS_EMAIL: {from_secret:"RIPS_EMAIL"},
					RIPS_PASSWORD: {from_secret: "RIPS_PASSWORD"}
				}
			}
		]
	}
]
