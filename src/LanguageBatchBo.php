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

    /** @var array */
    protected $applications;

    /** @var array */
    protected $applets;

    /**
     * Constructor.
     *
     * @param LanguageFilesApi $filesApi
     * @param Storage $storage
     * @param array $appletsList
     */
    public function __construct(
        LanguageFilesApi $filesApi = null,
        Storage $storage = null,
        $applicationsList = null,
        $appletsList = null
    ) {
        $this->languageFilesApi = $filesApi ?: new LanguageFilesApi(['\\Language\\ApiCall', 'call']);
        $this->storage = $storage ?: new Storage(Config::get('system.paths.root'));
        $this->applications = $applicationsList ?: Config::get('system.translated_applications');
        $this->applets = $appletsList ?: [
            'memberapplet' => 'JSM2_MemberApplet',
        ];
    }

    /**
     * @return LanguageFilesApi
     */
    protected function getLanguageFilesApi()
    {
        return $this->languageFilesApi;
    }

    /**
     * @return Storage
     */
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
        echo 'Generating language files...' . PHP_EOL;

        foreach ($this->applications as $application => $languages) {
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
        echo 'Getting applet language XMLs...' . PHP_EOL;

        foreach ($this->applets as $appletDirectory => $appletLanguageId) {
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
