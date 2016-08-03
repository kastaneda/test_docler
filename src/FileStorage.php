<?php

namespace Language;

class FileStorage
{
    const DIR_MODE = 0755;
    const ERROR_MESSAGE = 'Unable to save file %s';
    
    protected $fileName;

    /**
     * Constructor.
     *
     * @param string $fileName      The name of the file.
     */
    public function __construct($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * Save the data to the file.
     *
     * @param string $content       The data.
     *
     * @throws \Exception           If there was an error during file saving.
     */
    public function store($data)
    {
        $dir = dirname($this->fileName);

        // If there is no folder yet, we'll create it.
        if (!is_dir($dir)) {
            mkdir($dir, self::DIR_MODE, true);
        }

        if (strlen($data) !== file_put_contents($this->fileName, $data)) {
            throw new \Exception(sprintf(self::ERROR_MESSAGE, $this->fileName));
        }
    }
}
