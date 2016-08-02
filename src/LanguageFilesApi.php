<?php

namespace Language;

class LanguageFilesApi
{
    /**
     * String keys.
     */
    const API_TARGET = 'system_api';
    const API_MODE = 'language_api';
    const API_SYSTEM = 'LanguageFiles';
    const API_KEY_SYSTEM = 'system';
    const API_KEY_ACTION = 'action';
    const API_KEY_LANGUAGE = 'language';
    const API_KEY_APPLET = 'applet';
    const RESULT_OK = 'OK';
    const RESULT_KEY_STATUS = 'status';
    const RESULT_KEY_DATA = 'data';

    /**
     * Error messages.
     */
    const ERROR_GET_LANGUAGE_FILE = 'Error during getting language file: (%s/%s)';
    const ERROR_GET_APPLET_LANGUAGES = 'Getting languages for applet (%s) was unsuccessful: %s';
    const ERROR_GET_APPLET_FILE = 'Getting language xml for applet: (%s) on language: (%s) was unsuccessful: %s';
    const ERROR_API_CALL = 'Error during the api call';
    const ERROR_API_CONTENT = 'Wrong content!';

    /** @var callable */
    protected $apiCall;

    /**
     * Constructor.
     *
     * @param callable $apiCall     The external callable function or method.
     */
    public function __construct(callable $apiCall)
    {
        $this->apiCall = $apiCall;
    }

    /**
     * Gets the language file for the given language.
     *
     * @param string $application   The name of the application.
     * @param string $language      The identifier of the language.
     *
     * @throws \Exception           If there was an error during the download.
     *
     * @return string               The content of the language file.
     */
    public function getLanguageFile($application, $language)
    {
        try {
            return $this->doApiCall(__FUNCTION__, [
                self::API_KEY_LANGUAGE => $language,
            ]);
        } catch (\Exception $e) {
            throw new \Exception(sprintf(
                self::ERROR_GET_LANGUAGE_FILE,
                $application,
                $language
            ));
        }
    }

    /**
     * Gets the available languages for the given applet.
     *
     * @param string $applet        The applet identifier.
     *
     * @throws \Exception           If there was an error.
     *
     * @return array                The list of the available applet languages.
     */
    public function getAppletLanguages($applet)
    {
        try {
            return $this->doApiCall(__FUNCTION__, [
                self::API_KEY_APPLET => $applet,
            ]);
        } catch (\Exception $e) {
            throw new \Exception(sprintf(
                self::ERROR_GET_APPLET_LANGUAGES,
                $applet,
                $e->getMessage()
            ));
        }
    }

    /**
     * Gets a language xml for an applet.
     *
     * @param string $applet        The identifier of the applet.
     * @param string $language      The language identifier.
     *
     * @throws \Exception           If there was an error.
     *
     * @return string|false         The content of the language file (or false).
     */
    public function getAppletLanguageFile($applet, $language)
    {
        try {
            return $this->doApiCall(__FUNCTION__, [
                self::API_KEY_APPLET => $applet,
                self::API_KEY_LANGUAGE => $language,
            ]);
        } catch (\Exception $e) {
            throw new \Exception(sprintf(
                self::ERROR_GET_APPLET_FILE,
                $applet,
                $language,
                $e->getMessage()
            ));
        }
    }

    /**
     * Perform the LanguageFiles API call and check result.
     *
     * @param string $method        The API method name.
     * @param string $arguments     Arguments passed to the method.
     *
     * @throws \Exception           If the API call was not successful.
     *
     * @return string               Resulting data.
     */
    protected function doApiCall($method, $arguments)
    {
        $callable = $this->apiCall;
        $result = $callable(
            self::API_TARGET,
            self::API_MODE,
            [
                self::API_KEY_SYSTEM => self::API_SYSTEM,
                self::API_KEY_ACTION => $method,
            ],
            $arguments
        );

        // Error during the api call.
        if ($result === false || !isset($result[self::RESULT_KEY_STATUS])) {
            throw new \Exception(self::ERROR_API_CALL);
        }

        // Wrong response.
        if ($result[self::RESULT_KEY_STATUS] != self::RESULT_OK) {
            // FIXME: this code should be rewritten, string constants should go
            throw new \Exception('Wrong response: '
                .(!empty($result['error_type']) ? 'Type('.$result['error_type'].') ' : '')
                .(!empty($result['error_code']) ? 'Code('.$result['error_code'].') ' : '')
                .((string) $result[self::RESULT_KEY_DATA]));
        }


        // Wrong content.
        if ($result[self::RESULT_KEY_DATA] === false) {
            throw new \Exception(self::ERROR_API_CONTENT);
        }

        return $result[self::RESULT_KEY_DATA];
    }
}
