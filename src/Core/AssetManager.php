<?php
/**
 * Asset Manager Class (Optimization & Conditional Loading)
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Core;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * AssetManager class for optimized asset loading
 */
final class AssetManager
{
    /**
     * Registered scripts
     *
     * @var array<string, array<string, mixed>>
     */
    private static array $scripts = [];

    /**
     * Registered styles
     *
     * @var array<string, array<string, mixed>>
     */
    private static array $styles = [];

    /**
     * Whether to use minified assets
     *
     * @var bool
     */
    private static bool $useMinified = true;

    /**
     * Initialize asset manager
     *
     * @return void
     */
    public static function init(): void
    {
        self::$useMinified = !defined('SCRIPT_DEBUG') || !SCRIPT_DEBUG;

        add_action('admin_enqueue_scripts', [self::class, 'enqueueConditionally'], 5);
    }

    /**
     * Register a script
     *
     * @param string $handle Script handle
     * @param string $src Script source (relative to assets/js/)
     * @param array<string> $deps Dependencies
     * @param array<string, mixed> $conditions Conditions for loading
     * @return void
     */
    public static function registerScript(
        string $handle,
        string $src,
        array $deps = [],
        array $conditions = []
    ): void {
        self::$scripts[$handle] = [
            'src' => $src,
            'deps' => $deps,
            'conditions' => $conditions,
        ];
    }

    /**
     * Register a style
     *
     * @param string $handle Style handle
     * @param string $src Style source (relative to assets/css/)
     * @param array<string> $deps Dependencies
     * @param array<string, mixed> $conditions Conditions for loading
     * @return void
     */
    public static function registerStyle(
        string $handle,
        string $src,
        array $deps = [],
        array $conditions = []
    ): void {
        self::$styles[$handle] = [
            'src' => $src,
            'deps' => $deps,
            'conditions' => $conditions,
        ];
    }

    /**
     * Conditionally enqueue assets
     *
     * @param string $hookSuffix Current admin page hook
     * @return void
     */
    public static function enqueueConditionally(string $hookSuffix): void
    {
        // Get current screen
        $screen = get_current_screen();

        // Enqueue scripts
        foreach (self::$scripts as $handle => $script) {
            if (self::shouldEnqueue($script['conditions'], $hookSuffix, $screen)) {
                $src = self::getAssetUrl('js', $script['src']);
                wp_enqueue_script(
                    $handle,
                    $src,
                    $script['deps'],
                    ADMINFORGE_VERSION,
                    true
                );
            }
        }

        // Enqueue styles
        foreach (self::$styles as $handle => $style) {
            if (self::shouldEnqueue($style['conditions'], $hookSuffix, $screen)) {
                $src = self::getAssetUrl('css', $style['src']);
                wp_enqueue_style(
                    $handle,
                    $src,
                    $style['deps'],
                    ADMINFORGE_VERSION
                );
            }
        }
    }

    /**
     * Check if asset should be enqueued
     *
     * @param array<string, mixed> $conditions Conditions array
     * @param string $hookSuffix Current hook
     * @param \WP_Screen|null $screen Current screen
     * @return bool
     */
    private static function shouldEnqueue(array $conditions, string $hookSuffix, ?\WP_Screen $screen): bool
    {
        // No conditions = always load
        if (empty($conditions)) {
            return true;
        }

        // Check hook suffix
        if (isset($conditions['hook']) && $hookSuffix !== $conditions['hook']) {
            return false;
        }

        // Check screen base
        if (isset($conditions['screen']) && $screen && $screen->base !== $conditions['screen']) {
            return false;
        }

        // Check post type
        if (isset($conditions['post_type']) && $screen && $screen->post_type !== $conditions['post_type']) {
            return false;
        }

        // Check custom callback
        if (isset($conditions['callback']) && is_callable($conditions['callback'])) {
            return call_user_func($conditions['callback']);
        }

        return true;
    }

    /**
     * Get asset URL with minification support
     *
     * @param string $type Asset type (js/css)
     * @param string $file File name
     * @return string
     */
    private static function getAssetUrl(string $type, string $file): string
    {
        $baseUrl = ADMINFORGE_URL . 'assets/' . $type . '/';

        // If minified version requested and exists
        if (self::$useMinified) {
            $minFile = str_replace('.' . $type, '.min.' . $type, $file);
            $minPath = ADMINFORGE_PATH . 'assets/' . $type . '/' . $minFile;

            if (file_exists($minPath)) {
                return $baseUrl . $minFile;
            }
        }

        return $baseUrl . $file;
    }

    /**
     * Minify JavaScript file
     *
     * @param string $file Source file path
     * @param string $output Output file path
     * @return bool
     */
    public static function minifyJS(string $file, string $output): bool
    {
        if (!file_exists($file)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('AdminForge: Cannot minify JS file - file not found: ' . $file);
            }
            return false;
        }

        $content = file_get_contents($file);
        if ($content === false) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('AdminForge: Cannot read JS file: ' . $file);
            }
            return false;
        }

        // Simple minification (remove comments and whitespace)
        $minified = preg_replace([
            // Remove single-line comments
            '/\/\/.*$/m',
            // Remove multi-line comments
            '/\/\*.*?\*\//s',
            // Remove whitespace
            '/\s+/',
        ], [
            '',
            '',
            ' ',
        ], $content);

        $result = file_put_contents($output, trim($minified));
        if ($result === false) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('AdminForge: Cannot write minified JS file: ' . $output);
            }
            return false;
        }

        return true;
    }

    /**
     * Minify CSS file
     *
     * @param string $file Source file path
     * @param string $output Output file path
     * @return bool
     */
    public static function minifyCSS(string $file, string $output): bool
    {
        if (!file_exists($file)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('AdminForge: Cannot minify CSS file - file not found: ' . $file);
            }
            return false;
        }

        $content = file_get_contents($file);
        if ($content === false) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('AdminForge: Cannot read CSS file: ' . $file);
            }
            return false;
        }

        // Simple minification
        $minified = preg_replace([
            // Remove comments
            '/\/\*.*?\*\//s',
            // Remove whitespace
            '/\s+/',
            // Remove space around punctuation
            '/\s*([{}|:;,>~+])\s*/',
        ], [
            '',
            ' ',
            '$1',
        ], $content);

        $result = file_put_contents($output, trim($minified));
        if ($result === false) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('AdminForge: Cannot write minified CSS file: ' . $output);
            }
            return false;
        }

        return true;
    }

    /**
     * Minify all assets
     *
     * @return array<string, int> Statistics
     */
    public static function minifyAll(): array
    {
        $stats = [
            'js' => 0,
            'css' => 0,
            'errors' => 0,
        ];

        $jsPath = ADMINFORGE_PATH . 'assets/js/';
        $cssPath = ADMINFORGE_PATH . 'assets/css/';

        // Minify JS files
        $jsFiles = glob($jsPath . '*.js');
        foreach ($jsFiles as $file) {
            if (strpos($file, '.min.js') !== false) {
                continue;
            }

            $output = str_replace('.js', '.min.js', $file);
            if (self::minifyJS($file, $output)) {
                $stats['js']++;
            } else {
                $stats['errors']++;
            }
        }

        // Minify CSS files
        $cssFiles = glob($cssPath . '*.css');
        foreach ($cssFiles as $file) {
            if (strpos($file, '.min.css') !== false) {
                continue;
            }

            $output = str_replace('.css', '.min.css', $file);
            if (self::minifyCSS($file, $output)) {
                $stats['css']++;
            } else {
                $stats['errors']++;
            }
        }

        return $stats;
    }

}

// Initialize
AssetManager::init();
