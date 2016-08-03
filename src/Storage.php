<?php

namespace Language;

class Storage
{
    const CACHE_FILE_PHP = '/cache/%s/%s.php';
    const CACHE_FILE_XML = '/cache/flash/lang_%s.xml';

    protected $root;

    /**
     * Constructor.
     *
     * @param string $root          Application root path.
     */
    public function __construct($root)
    {
        $this->root = $root;
    }

    /**
     * Get FileStorage for language file.
     *
     * @param string $application   The name of the application.
     * @param string $language      The identifier of the language.
     *
     * @return FileStorage
     */
    public function getLanguageCacheFile($application, $language)
    {
        return new FileStorage(
            $this->root . sprintf(self::CACHE_FILE_PHP, $application, $language)
        );
    }

    /**
     * Get FileStorage for applet language file.
     *
     * @param string $language      The identifier of the language.
     *
     * @return FileStorage
     */
    public function getAppletLanguageCacheFile($language)
    {
        return new FileStorage(
            $this->root . sprintf(self::CACHE_FILE_XML, $language)
        );
    }
}
