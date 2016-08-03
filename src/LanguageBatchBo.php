<?php

namespace Language;

/**
 * Business logic related to generating language files.
 */
class LanguageBatchBo
{
    /** @var LanguageFilesApi */
    protected $languageFilesApi;

    protected function getLanguageFilesApi()
    {
        if (empty($this->languageFilesApi)) {
            $this->languageFilesApi = new LanguageFilesApi(['\\Language\\ApiCall', 'call']);
        }

        return $this->languageFilesApi;
    }

    /**
     * Starts the language file generation.
     */
    public function generateLanguageFiles()
    {
        // The applications where we need to translate.
        $applications = Config::get('system.translated_applications');

        echo "\nGenerating language files\n";
        foreach ($applications as $application => $languages) {
            echo '[APPLICATION: '.$application."]\n";
            foreach ($languages as $language) {
                echo "\t[LANGUAGE: ".$language.']';
                $data = $this->getLanguageFilesApi()->getLanguageFile($application, $language);
                $target = $this->getLanguageCacheFileName($application, $language);
                if (!$this->saveFile($target, $data)) {
                    throw new \Exception('Unable to generate language file!');
                }
                echo " OK\n";
            }
        }
    }

    /**
     * Gets the language file name for the given language.
     *
     * @param string $application   The name of the application.
     * @param string $language      The identifier of the language.
     *
     * @return string               The file name.
     */
    protected function getLanguageCacheFileName($application, $language)
    {
        return Config::get('system.paths.root')
            .'/cache/'.$application.'/'.$language.'.php';
    }

    /**
     * Gets the language files for the applet and puts them into the cache.
     *
     * @throws \Exception           If there was an error.
     */
    public function generateAppletLanguageXmlFiles()
    {
        // List of the applets [directory => applet_id].
        $applets = array(
            'memberapplet' => 'JSM2_MemberApplet',
        );

        echo "\nGetting applet language XMLs..\n";

        foreach ($applets as $appletDirectory => $appletLanguageId) {
            echo " Getting > $appletLanguageId ($appletDirectory) language xmls..\n";
            $languages = $this->getLanguageFilesApi()->getAppletLanguages($appletLanguageId);
            if (empty($languages)) {
                throw new \Exception('There is no available languages for the '.$appletLanguageId.' applet.');
            }

            echo ' - Available languages: '.implode(', ', $languages)."\n";
            foreach ($languages as $language) {
                $data = $this->getLanguageFilesApi()->getAppletLanguageFile($appletLanguageId, $language);
                $target = $this->getAppletLanguageCacheFileName($language);
                if (!$this->saveFile($target, $data)) {
                    throw new \Exception('Unable to save applet: ('.$appletLanguageId.') language: ('.$language
                        .') xml ('.$target.')!');
                }
                echo " OK saving $target was successful.\n";
            }
            echo " < $appletLanguageId ($appletDirectory) language xml cached.\n";
        }

        echo "\nApplet language XMLs generated.\n";
    }

    /**
     * Gets the language file name for an applet.
     *
     * @param string $language      The identifier of the language.
     *
     * @return string               The file name.
     */
    protected function getAppletLanguageCacheFileName($language)
    {
        return Config::get('system.paths.root')
            .'/cache/flash/lang_'.$language.'.xml';
    }


    /**
     * Save the data to the file.
     *
     * @param string $filename      The name of the file.
     * @param string $content       The data.
     *
     * @return bool                 True if operation was successful.
     */
    protected function saveFile($filename, $content)
    {
        $dirname = dirname($filename);

        // If there is no folder yet, we'll create it.
        if (!is_dir($dirname)) {
            mkdir($dirname, 0755, true);
        }

        return strlen($content) === file_put_contents($filename, $content);
    }
}
