<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wp_Ai_Content_Generator
 * @subpackage Wp_Ai_Content_Generator/includes
 * @author     Senol Sahin <senols@gmail.com>
 */

//if ( ! defined( 'ABSPATH' ) ) exit;
class CHATGPT_OPEN_URL
{
const ORIGIN = 'https://api.openai.com';
const API_VERSION = 'v1';
const OPEN_AI_URL = self::ORIGIN . "/" . self::API_VERSION;

/**
* @deprecated
* @param string $engine
* @return string
*/
public static function completionURL(string $engine): string
{
return self::OPEN_AI_URL . "/engines/$engine/completions";
}

/**
* @return string
*/
public static function completionsURL(): string
{
return self::OPEN_AI_URL . "/completions";
}

/**
*
* @return string
*/
public static function editsUrl(): string
{
return self::OPEN_AI_URL . "/edits";
}

/**
* @param string $engine
* @return string
*/
public static function searchURL(string $engine): string
{
return self::OPEN_AI_URL . "/engines/$engine/search";
}

/**
* @param
* @return string
*/
public static function enginesUrl(): string
{
return self::OPEN_AI_URL . "/engines";
}

/**
* @param string $engine
* @return string
*/
public static function engineUrl(string $engine): string
{
return self::OPEN_AI_URL . "/engines/$engine";
}

/**
* @param
* @return string
*/
public static function classificationsUrl(): string
{
return self::OPEN_AI_URL . "/classifications";
}

/**
* @param
* @return string
*/
public static function moderationUrl(): string
{
return self::OPEN_AI_URL . "/moderations";
}

/**
* @param
* @return string
*/
public static function filesUrl(): string
{
return self::OPEN_AI_URL . "/files";
}

/**
* @param
* @return string
*/
public static function fineTuneUrl(): string
{
return self::OPEN_AI_URL . "/fine-tunes";
}

/**
* @param
* @return string
*/
public static function chatUrl(): string
{
return self::OPEN_AI_URL . "/chat/completions";
}

/**
* @param
* @return string
*/
public static function fineTuneModel(): string
{
return self::OPEN_AI_URL . "/models";
}

/**
* @param
* @return string
*/
public static function answersUrl(): string
{
return self::OPEN_AI_URL . "/answers";
}

/**
* @param
* @return string
*/
public static function imageUrl(): string
{
return self::OPEN_AI_URL . "/images";
}

/**
* @param
* @return string
*/
public static function transcriptionsUrl(): string
{
return self::OPEN_AI_URL . "/audio/transcriptions";
}

/**
* @param
* @return string
*/
public static function translationsUrl(): string
{
return self::OPEN_AI_URL . "/audio/translations";
}

/**
* @param
* @return string
*/
public static function embeddings(): string
{
return self::OPEN_AI_URL . "/embeddings";
}
}