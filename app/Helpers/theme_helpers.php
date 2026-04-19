<?php

use App\Support\UiTheme;

if (! function_exists('theme_asset')) {
    /**
     * Build URL to a CSS (or other) file inside the active organization theme folder under public/.
     *
     * @param  string  $path  Relative path inside the theme folder, e.g. "admin-custom.css"
     */
    function theme_asset(string $path): string
    {
        $folder = UiTheme::publicFolder();

        return asset($folder.'/'.ltrim($path, '/'));
    }
}
