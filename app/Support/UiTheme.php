<?php

namespace App\Support;

use App\Models\Organization;

class UiTheme
{
    /** Default stylesheet folder under public/ when no org or no valid theme. */
    public const DEFAULT_FOLDER = 'dark_theam';

    /**
     * @return array<string, string> folder => label
     */
    public static function options(): array
    {
        return config('ui.themes', []);
    }

    /**
     * @return list<string>
     */
    public static function allowedFolders(): array
    {
        return array_keys(self::options());
    }

    /**
     * Default theme for new forms / reset (always dark unless config overrides with a valid folder).
     */
    public static function defaultFolder(): string
    {
        $d = (string) config('ui.default_theme', self::DEFAULT_FOLDER);

        return in_array($d, self::allowedFolders(), true) ? $d : self::DEFAULT_FOLDER;
    }

    /**
     * Public path segment for theme CSS (e.g. dark_theam), from current session org.
     * No organization selected, missing theme, or invalid theme → {@see DEFAULT_FOLDER}.
     */
    public static function publicFolder(): string
    {
        return once(function () {
            $allowed = self::allowedFolders();
            $id = (int) session('current_organization_id', 0);
            if ($id <= 0) {
                return self::DEFAULT_FOLDER;
            }

            $theme = Organization::query()->whereKey($id)->value('theme');
            $folder = is_string($theme) ? trim($theme) : '';
            if ($folder === '' || ! in_array($folder, $allowed, true)) {
                return self::DEFAULT_FOLDER;
            }

            return $folder;
        });
    }
}
