<?php

if (!current_user_can('manage_options')) {
    return;
}

// The values you requested
$build_path = plugin_dir_path(__FILE__) . 'public/build';
$build_url = plugin_dir_url(__FILE__) . 'public/build';

// Check for the Vite 'hot' file to determine environment and dev server URL
$is_dev = false;
$vite_url = '';
$hot_file_build = $build_path . '/hot';
$hot_file_public = plugin_dir_path(__FILE__) . 'public/hot';

if (file_exists($hot_file_build)) {
    $is_dev = true;
    $vite_url = trim(file_get_contents($hot_file_build));
} elseif (file_exists($hot_file_public)) {
    $is_dev = true;
    $vite_url = trim(file_get_contents($hot_file_public));
}

?>

<div class="wrap">
    <h1>Mark Dionnie</h1>

    <div class="card" style="max-width: 800px; margin-top: 20px; padding: 20px;">
        <h2 class="title">Environment VariablesX</h2>
        <p>Here are the current build paths and URLs configured for this plugin:</p>

        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Property</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Environment Mode</strong></td>
                    <td>
                        <?php if ($is_dev): ?>
                            <span style="color: #d63638; font-weight: bold;">Development (HMR)</span>
                        <?php else: ?>
                            <span style="color: #00a32a; font-weight: bold;">Live (Production)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Vite Dev Server URL</strong></td>
                    <td><code><?php echo $is_dev ? esc_url($vite_url) : 'N/A'; ?></code></td>
                </tr>
                <tr>
                    <td><strong>Build Path</strong><br><em>(Absolute server path)</em></td>
                    <td><code><?php echo esc_html($build_path); ?></code></td>
                </tr>
                <tr>
                    <td><strong>Build URL</strong><br><em>(Public web URL)</em></td>
                    <td><code><?php echo esc_url($build_url); ?></code></td>
                </tr>
            </tbody>
        </table>

        <h2 class="title" style="margin-top: 30px;">Developer Notes</h2>
        <p>To enable <strong>Hot Module Replacement (HMR)</strong> for local development, you need to start the Vite dev server. Open your terminal in the plugin directory and run:</p>
        <p style="background: #f0f0f1; padding: 10px; border-left: 4px solid #00a32a; font-family: monospace; font-size: 14px;">npm run dev --host</p>
        <p><em><strong>Note:</strong> The <code>--host</code> flag exposes the dev server to your local network, which is required if you are using Laragon, Docker, or other virtualized local development environments. </em></p>
        <p><em>Once the server is running, a <code>hot</code> file is generated in the build directory, and this dashboard will detect the development environment and switch over automatically. To prepare the plugin for production, stop the dev server and run <code>npm run build</code>.</em></p>
    </div>
</div>