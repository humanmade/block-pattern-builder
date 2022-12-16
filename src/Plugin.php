<?php
/**
 * Primary plugin class.
 *
 * Launches the plugin components and acts as a simple container.
 *
 * @package   BlockPatternBuilder
 * @author    Justin Tadlock <justintadlock@gmail.com>
 * @copyright 2019, Justin Tadlock
 * @link      https://github.com/justintadlock/block-pattern-builder
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 */

namespace BlockPatternBuilder;

/**
 * Plugin class.
 *
 * @since  1.1.0
 * @access public
 */
class Plugin {

	/**
	 * Stores the plugin directory path.
	 *
	 * @since  1.1.0
	 * @access protected
	 * @var    string
	 */
	protected string $path;

	/**
	 * Stores the plugin directory URI.
	 *
	 * @since  1.1.0
	 * @access protected
	 * @var    string
	 */
	protected string $uri;

	/**
	 * Stores the the `public/mix-manifest.json` data as an array.
	 *
	 * @since  1.1.0
	 * @access private
	 * @var    array
	 */
	private array $mix = [];

	/**
	 * Holds an array of the plugin component objects.
	 *
	 * @since  1.1.0
	 * @access protected
	 * @var    array
	 */
	protected array $components = [];

	/**
	 * Sets up the object properties.
	 *
	 * @param string $path Plugin directory path.
	 * @param string $uri Plugin directory URI.
	 * @return void
	 * @since  1.1.0
	 * @access public
	 */
	public function __construct(string $path, string $uri)
	{

		$this->path = untrailingslashit($path);
		$this->uri = untrailingslashit($uri);

		$this->registerDefaultComponents();
	}

	/**
	 * Bootstraps the components.
	 *
	 * @return void
	 * @since  1.1.0
	 * @access public
	 */
	public function boot(): void
	{

		// Load translations.
		add_action('plugins_loaded', [$this, 'loadTextdomain']);

		// Bootstrap components.
		foreach ($this->components as $component) {
			$component->boot();
		}
	}

	/**
	 * Load the plugin textdomain.
	 *
	 * @return void
	 * @since  1.1.0
	 * @access public
	 */
	public function loadTextdomain(): void
	{

		load_plugin_textdomain(
			'block-pattern-builder',
			false,
			plugin_basename(dirname(__DIR__) . '/public/lang')
		);
	}

	/**
	 * Returns the plugin path.
	 *
	 * @param string $file
	 * @return string
	 * @since  1.1.0
	 * @access public
	 */
	public function path(string $file = ''): string
	{

		$file = ltrim($file, '/');

		return $file ? $this->path . "/$file" : $this->path;
	}

	/**
	 * Returns the plugin URI.
	 *
	 * @param string $file
	 * @return string
	 * @since  1.1.0
	 * @access public
	 */
	public function uri(string $file = ''): string
	{

		$file = ltrim($file, '/');

		return $file ? $this->uri . "/$file" : $this->uri;
	}

	/**
	 * Helper function for outputting an asset URL in the plugin.
	 *
	 * @param string $path A relative path/file to append to the `public` folder.
	 * @return string
	 * @since  1.1.0
	 * @access public
	 */
	public function asset(string $path): string
	{

		if (!$this->mix) {
			$file = $this->path('public/mix-manifest.json');
			$this->mix = (array)json_decode(file_get_contents($file), true);
		}

		// Make sure to trim any slashes from the front of the path.
		$path = '/' . ltrim($path, '/');

		if ($this->mix && isset($this->mix[$path])) {
			$path = $this->mix[ $path ];
		}

		return $this->uri('public' . $path);
	}

	/**
	 * Registers the default plugin components.
	 *
	 * @return void
	 * @since  1.1.0
	 * @access public
	 */
	protected function registerDefaultComponents(): void
	{

		$components = [
			Editor::class
		];

		foreach ($components as $component) {
			$this->registerComponent($component);
		}
	}

	/**
	 * Returns a plugin component.
	 *
	 * @param string $abstract
	 * @return object
	 * @since  1.1.0
	 * @access public
	 */
	public function getComponent(string $abstract): object
	{
		return $this->components[$abstract];
	}

	/**
	 * Registers a plugin component.
	 *
	 * @param string $abstract
	 * @return void
	 * @since  1.1.0
	 * @access public
	 */
	protected function registerComponent(string $abstract): void
	{
		$this->components[$abstract] = new $abstract();
	}
}
