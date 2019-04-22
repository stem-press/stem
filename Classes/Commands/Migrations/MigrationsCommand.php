<?php

namespace Stem\Commands\Migrations;

use Stem\Core\Command;
use Stem\Core\Context;
use Stem\Core\Log;
use Stem\Packages\PackageManager;
use Stem\Queue\Job;
use Stem\Queue\Queue;

class MigrationsCommand extends Command {
	/**
	 * Installs phinx
	 *
	 * @when after_wp_load
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	public function install($args, $assoc_args) {
		self::Out("Installing phinx package ... ");
		`composer global require robmorgan/phinx --prefer-source --quiet`;
		self::Out("Done.", true);

		$phinxPath = realpath(getenv('HOME')."/.config/composer/vendor/robmorgan/phinx/bin/phinx");

		self::Out("Linking $phinxPath to /usr/local/bin/phinx ... ");
		`sudo ln -s $phinxPath /usr/local/bin/phinx`;
		self::Out("Done.", true);
	}

	/**
	 * Creates a config file
	 *
	 * @when after_wp_load
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	public function init($args, $assoc_args) {
		$phinx = `which phinx`;
		if (empty($phinx)) {
			self::Error("phinx is not installed, type `wp migrations install` to setup.");
			exit(1);
		}

		$path = trailingslashit(ABSPATH);
		while(true) {
			if (file_exists($path.'/vendor/autoload.php')) {
				break;
			}

			$path = trailingslashit(realpath($path.'..'));

			if ($path == '/') {
				self::Out("Can not find vendor folder, exiting.", true);
				exit;
			}
		}

		$migrationPaths = [];

		foreach(PackageManager::registeredPackages() as $package) {
			if (!empty($package->migrationsPath())) {
				$migrationPaths[] = $package->migrationsPath();
			}
		}

		$themeMigrations = Context::current()->rootPath.'/migrations';
		if (!file_exists($themeMigrations)) {
			mkdir($themeMigrations);
		}
		$migrationPaths[] = $themeMigrations;

		ob_start();
		echo "<?php\n";
		?>
require_once "<?php echo $path; ?>vendor/autoload.php";

$dotenv = Dotenv\Dotenv::create("<?php echo $path; ?>");
$dotenv->load();

return [
	'paths' => [
		'migrations' => [
		<?php foreach($migrationPaths as $migrationPath): ?>
		"<?php echo $migrationPath; ?>",
		<?php endforeach; ?>
		]
	],
	'environments' => [
		'default_migration_table' => 'phinxlog',
		'default_database' => 'wordpress',
		'wordpress' => [
			'adapter' => 'mysql',
			'host' => env('DB_HOST'),
			'name' => env('DB_NAME'),
			'user' => env('DB_USER'),
			'pass' => env('DB_PASSWORD'),
			'port' => '3306',
			'charset' => 'utf8',
		]
	],
	'version_order' => 'creation'
];
		<?php
		$result = ob_get_clean();

		file_put_contents(Context::current()->rootPath.'/phinx.php', $result);

		self::Out("phinx.php configuration created.", true);
	}

	public static function Register() {
		\WP_CLI::add_command('migrations', __CLASS__);
	}
}