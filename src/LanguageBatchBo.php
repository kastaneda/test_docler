<?php

namespace Language;

/**
 * Business logic related to generating language files.
 */
class LanguageBatchBo
{
    /**
     * Starts the language file generation.
     *
     * @return void
     */
    public static function generateLanguageFiles()
    {
        // The applications where we need to translate.
        $applications = Config::get('system.translated_applications');

        echo "\nGenerating language files\n";
        foreach ($applications as $application => $languages) {
            echo "[APPLICATION: " . $application . "]\n";
            foreach ($languages as $language) {
                echo "\t[LANGUAGE: " . $language . "]";
                $data = self::getLanguageFile($application, $language);
                $target = self::getLanguageCacheFileName($application, $language);
                if (self::saveFile($target, $data)) {
                    echo " OK\n";
                } else {
                    throw new \Exception('Unable to generate language file!');
                }
            }
        }
    }

    /**
     * Gets the language file for the given language.
     *
     * @param string $application   The name of the application.
     * @param string $language      The identifier of the language.
     * @throws \Exception           If there was an error during the download of the language file.
     * @return string               The content of the language file.
     */
    protected static function getLanguageFile($application, $language)
    {
        try {
            return self::callLanguageFilesApi('getLanguageFile', ['language' => $language]);
        } catch (\Exception $e) {
            throw new \Exception('Error during getting language file: (' . $application . '/' . $language . ')');
        }
    }

    /**
     * Gets the language file name for the given language.
     *
     * @param string $application   The name of the application.
     * @param string $language      The identifier of the language.
     * @return string               The file name.
     */
    protected function getLanguageCacheFileName($application, $language)
    {
        return Config::get('system.paths.root')
            . '/cache/' . $application . '/' . $language . '.php';
    }

    /**
     * Gets the language files for the applet and puts them into the cache.
     *
     * @throws \Exception           If there was an error.
     * @return void
     */
    public static function generateAppletLanguageXmlFiles()
    {
        // List of the applets [directory => applet_id].
        $applets = array(
            'memberapplet' => 'JSM2_MemberApplet',
        );

        echo "\nGetting applet language XMLs..\n";

        foreach ($applets as $appletDirectory => $appletLanguageId) {
            echo " Getting > $appletLanguageId ($appletDirectory) language xmls..\n";
            $languages = self::getAppletLanguages($appletLanguageId);
            if (empty($languages)) {
                throw new \Exception('There is no available languages for the ' . $appletLanguageId . ' applet.');
            } else {
                echo ' - Available languages: ' . implode(', ', $languages) . "\n";
            }
            $path = Config::get('system.paths.root') . '/cache/flash';
            foreach ($languages as $language) {
                $xmlContent = self::getAppletLanguageFile($appletLanguageId, $language);
                $xmlFile    = $path . '/lang_' . $language . '.xml';
                if (self::saveFile($xmlFile, $xmlContent)) {
                    echo " OK saving $xmlFile was successful.\n";
                } else {
                    throw new \Exception('Unable to save applet: (' . $appletLanguageId . ') language: (' . $language
                        . ') xml (' . $xmlFile . ')!');
                }
            }
            echo " < $appletLanguageId ($appletDirectory) language xml cached.\n";
        }

        echo "\nApplet language XMLs generated.\n";
    }

    /**
     * Gets the available languages for the given applet.
     *
     * @param string $applet   The applet identifier.
     * @return array           The list of the available applet languages.
     */
    protected static function getAppletLanguages($applet)
    {
        try {
            return self::callLanguageFilesApi('getAppletLanguages', ['applet' => $applet]);
        } catch (\Exception $e) {
            throw new \Exception('Getting languages for applet (' . $applet . ') was unsuccessful ' . $e->getMessage());
        }
    }


    /**
     * Gets a language xml for an applet.
     *
     * @param string $applet      The identifier of the applet.
     * @param string $language    The language identifier.
     * @return string|false       The content of the language file or false if weren't able to get it.
     */
    protected static function getAppletLanguageFile($applet, $language)
    {
        try {
            return self::callLanguageFilesApi('getAppletLanguageFile', ['applet' => $applet, 'language' => $language]);
        } catch (\Exception $e) {
            throw new \Exception('Getting language xml for applet: ('
                . $applet . ') on language: (' . $language . ') was unsuccessful: '
                . $e->getMessage());
        }
    }

    /**
     * Perform the LanguageFiles API call and check result.
     *
     * @param string $method        The API method name.
     * @param string $language      Arguments passed to the method.
     * @throws \Exception           If the API call was not successful.
     * @return string               Resulting data.
     */
    protected static function callLanguageFilesApi($method, $arguments)
    {
        $result = ApiCall::call(
            'system_api',
            'language_api',
            [
                'system' => 'LanguageFiles',
                'action' => $method,
            ],
            $arguments
        );

        // Error during the api call.
        if ($result === false || !isset($result['status'])) {
            throw new \Exception('Error during the api call');
        }

        // Wrong response.
        if ($result['status'] != 'OK') {
            throw new \Exception('Wrong response: '
                . (!empty($result['error_type']) ? 'Type(' . $result['error_type'] . ') ' : '')
                . (!empty($result['error_code']) ? 'Code(' . $result['error_code'] . ') ' : '')
                . ((string)$result['data']));
        }

        // Wrong content.
        if ($result['data'] === false) {
            throw new \Exception('Wrong content!');
        }

        return $result['data'];
    }

    protected static function saveFile($filename, $content)
    {
        $dirname = dirname($filename);

        // If there is no folder yet, we'll create it.
        if (!is_dir($dirname)) {
            mkdir($dirname, 0755, true);
        }

        return strlen($content) === file_put_contents($filename, $content);
    }
}
