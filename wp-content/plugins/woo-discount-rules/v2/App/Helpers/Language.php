<?php

namespace Wdr\App\Helpers;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Language
{
    /**
     * This will return the current language
     * @return string - current active language code
     */
    static function getCurrentLanguage()
    {
        if (defined('ICL_LANGUAGE_CODE')) {
            return ICL_LANGUAGE_CODE;
        }
        return NULL;
    }

    /**
     * Get all available languages
     * @return mixed|void
     */
    static function getAvailableLanguages()
    {
        $languages = apply_filters('wpml_active_languages', NULL, 'orderby=id&order=desc');
        if (empty($languages) && function_exists('icl_get_languages')) {
            $languages = icl_get_languages();
        }
        return $languages;
    }

    /**
     * Get the default language of the site
     * @return String|null
     */
    function getDefaultLanguage()
    {
        $current_lang = NULL;
        $wpml_options = get_option('icl_sitepress_settings');
        if (!empty($wpml_options)) {
            return (isset($wpml_options['default_language'])) ? $wpml_options['default_language'] : NULL;
        }
        if (function_exists('get_locale')) {
            $current_lang = get_locale();
            if (empty($current_lang)) {
                $current_lang = 'en';
            }
        }
        return $current_lang;
    }

    /**
     * Get site's default language
     * @return array
     */
    function getWpAvailableTranslations()
    {
        require_once(ABSPATH . 'wp-admin/includes/translation-install.php');
        if (function_exists('wp_get_available_translations')) {
            return wp_get_available_translations();
        }
        return array();
    }

    /**
     * get language label by lang code
     * @param $language_code
     * @return mixed|string|null
     */
    function getLanguageLabel($language_code)
    {
        if ($language_code == 'en_US' || $language_code == "en") {
            return "English";
        } else {
            $translations = $this->getWpAvailableTranslations();
            if (isset($translations[$language_code]['native_name'])) {
                return $translations[$language_code]['native_name'];
            }
        }
        return NULL;
    }
}