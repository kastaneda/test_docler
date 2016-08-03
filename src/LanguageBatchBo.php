<?php

namespace Language;

/**
 * Business logic related to generating language files.
 */
class LanguageBatchBo
{
    /** @var LanguageFilesApi */
    protected $languageFilesApi;

    /** @var Storage */
    protected $storage;

    public function __construct()
    {
        $this->languageFilesApi = new LanguageFilesApi(['\\Language\\ApiCall', 'call']);
        $this->storage = new Storage(Config::get('system.paths.root'));
    }

    protected function getLanguageFilesApi()
    {
        return $this->languageFilesApi;
    }

    protected function getStorage()
    {
        return $this->storage;
    }

    /**
     * Starts the language file generation.
     *
     * @throws \Exception           If there was an error.
     */
    public function generateLanguageFiles()
    {
        // The applications where we need to translate.
        $applications = Config::get('system.translated_applications');

        echo 'Generating language files...' . PHP_EOL;
        foreach ($applications as $application => $languages) {
            echo ' * Application: ' . $application . PHP_EOL;
            foreach ($languages as $language) {
                echo ' * Application language: ' . $language . PHP_EOL;
                $this
                    ->getStorage()
                    ->getLanguageCacheFile($application, $language)
                    ->store($this
                        ->getLanguageFilesApi()
                        ->getLanguageFile($application, $language));
            }
        }
        echo 'Done.' . PHP_EOL . PHP_EOL;
    }

    /**
     * Gets the language files for the applet and puts them into the cache.
     *
     * @throws \Exception           If there was an error.
     */
    public function generateAppletLanguageXmlFiles()
    {
        // List of the applets [directory => applet_id].
        $applets = [
            'memberapplet' => 'JSM2_MemberApplet',
        ];

        echo 'Getting applet language XMLs...' . PHP_EOL;

        foreach ($applets as $appletDirectory => $appletLanguageId) {
            echo ' * Getting ' . $appletLanguageId . ' (' . $appletDirectory . ') language XMLs...' . PHP_EOL;
            $languages = $this->getLanguageFilesApi()->getAppletLanguages($appletLanguageId);
            if (empty($languages)) {
                throw new \Exception('There is no available languages for the ' . $appletLanguageId . ' applet.');
            }

            echo ' * Available languages: [' . join(', ', $languages) . ']' . PHP_EOL;
            foreach ($languages as $language) {
                $this
                    ->getStorage()
                    ->getAppletLanguageCacheFile($language)
                    ->store($this
                        ->getLanguageFilesApi()
                        ->getAppletLanguageFile($appletLanguageId, $language));
                echo '   Language: ' . $language . ' OK' . PHP_EOL;
            }
        }

        echo 'Done.' . PHP_EOL . PHP_EOL;
    }
}
